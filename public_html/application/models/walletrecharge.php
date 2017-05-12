<?php
class WalletRecharge {
	function __construct($recharge_txn_id){
		$this->db = Syspage::getdb();
        if (!is_numeric($recharge_txn_id))
            $recharge_txn_id = 0;
        $this->recharge_txn_id = intval($recharge_txn_id);
        if (!($this->recharge_txn_id > 0)) {
            return;
        }
        $this->loadData();
    }
	
	function getError() {
        return $this->error;
    }
	
	function getData() {
        return $this->attributes;
    }
	
	protected function loadData() {
        $this->attributes = $this->getWalletRequests(array("id"=>$this->recharge_txn_id,"pagesize"=>1));
    }
	
	function wallet_recharge_invoice_format(){
		$value=Settings::getSetting("CONF_RECHARGE_INVOICE_FORMAT");
		$new_value=(int)$value+1;
		$new_value=str_pad($new_value,7,'0',STR_PAD_LEFT);
		if (!$this->db->update_from_array('tbl_configurations', array('conf_val' => $new_value),
		array('smt' => 'conf_var = ? ', 'vals' => array('CONF_RECHARGE_INVOICE_FORMAT')))){
			$this->error = $this->db->getError();
		}
		$new_value=date("ymd")."-".$new_value;
		return $new_value;
	}
	
	function saveRechargeRequest($data){
		$member_id = intval($data['member_id']);
		$member_type = $data['member_type'];
		unset($data['member_id']);
		if($member_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$invoice_number = $this->wallet_recharge_invoice_format();
		$record = new TableRecord('tbl_recharge_wallet_requests');
		$assign_fields = array(
						'rwr_member_id'=>$member_id,
						'rwr_member_type'=>$member_type,
						'rwr_invoice_number'=>$invoice_number,
						'rwr_amount'=>$data['amount'],
						'rwr_date'=>date('Y-m-d H:i:s'),
						'rwr_status'=>0,
					);
		$record->assignValues($assign_fields);
		if($record->addNew()){
			return intval($record->getId());
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}	
	
	function getWalletRequests($criteria=array()){
        $srch = new SearchBase('tbl_recharge_wallet_requests', 'trwr');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'trwr.rwr_member_id=tu.user_id and trwr.rwr_member_type="U"', 'tu');
		foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
	        switch($key) {
    		    case 'id':
        		    $srch->addCondition('trwr.rwr_id', '=', intval($val));
            	break;
				case 'user':
        		    $cndCondition = $srch->addCondition('trwr.rwr_member_id', '=', intval($val));
					$cndCondition->attachCondition('trwr.rwr_member_type', '=', 'U','AND');
            	break;
				case 'advertiser':
        		    $cndCondition = $srch->addCondition('trwr.rwr_member_id', '=', intval($val));
					$cndCondition->attachCondition('trwr.rwr_member_type', '=', 'A','AND');
            	break;
				case 'status':
        		    $srch->addCondition('trwr.rwr_status', '=', intval($val));
            	break;
				case 'pagesize':
	            	$srch->setPageSize($val);
	    	    break;
	        }
        }
		//die($srch->getquery());
		$srch->addOrder('rwr_id','DESC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $criteria["pagesize"]==1?$this->db->fetch($rs):$this->db->fetch_all($rs);
	}
	
	function getPaymentGatewayAmount(){
		$order_info=$this->attributes;
		return round($order_info['rwr_amount'],2);
	}
	
	function getWalletRechargePrimaryinfo(){
		 $arr_order=array();
		 $recharge_txn_info=$this->attributes;
		 if ($recharge_txn_info && is_array($recharge_txn_info)){
			 $arr_order=array(
			 				"id"=>$recharge_txn_info["rwr_id"],
							"invoice"=>$recharge_txn_info["rwr_invoice_number"],
							"cust_id"=>$recharge_txn_info["user_id"],
							"name"=>$recharge_txn_info["user_name"],
							"email"=>$recharge_txn_info["user_email"],
							"phone"=>$recharge_txn_info["user_phone"],
							"currency"=>Settings::getSetting("CONF_CURRENCY"),
							"payment_status"=>$recharge_txn_info["rwr_status"],
							"system_name"=>Settings::getSetting("CONF_WEBSITE_NAME"),
							"admin_email"=>Settings::getSetting("CONF_ADMIN_EMAIL"),
							"paypal_bn"=>"FATbit_SP",
						 );
		 }
		 return $arr_order;
	}
	
	function markWalletRechargeRequestPaid($payment_method_name,$txn_id, $response = '') {
		$recharge_txn_id=$this->recharge_txn_id;
		$recharge_info = $this->getWalletRequests(array("id"=>$this->recharge_txn_id,"pagesize"=>1));
		$recharge_info=$this->attributes;
		if ($recharge_info) {
			if (!$this->db->update_from_array('tbl_recharge_wallet_requests', array('rwr_payment_mode' => $payment_method_name, 'rwr_payment_txn_id' => $txn_id,'rwr_response'=>$response,'rwr_status' => 1), array('smt'=>'`rwr_id` = ?', 'vals'=> array($recharge_txn_id))))		{
				$this->error = $this->db->getError();
				return false;
			}
			$formatted_txn_value="#".$recharge_info["rwr_invoice_number"];
			if ($recharge_info['rwr_member_type']=="U"){
				$txnArray["utxn_user_id"]=$recharge_info["rwr_member_id"];
				$txnArray["utxn_credit"]=$recharge_info['rwr_amount'];
				$txnArray["utxn_debit"]=0;
				$txnArray["utxn_status"]=1;
				$txnArray["utxn_comments"]=sprintf(Utilities::getLabel('L_RECHARGE_TXN_PLACED_NUMBER'),$formatted_txn_value);
				$transObj=new Transactions();
				if($txn_id=$transObj->addTransaction($txnArray)){
					$emailNotificationObj=new Emailnotifications();
					$emailNotificationObj->sendTxnNotification($txn_id);
				}
			}elseif ($recharge_info['rwr_member_type']=="A"){
				$txnArray["atxn_advertiser_id"]=$recharge_info["rwr_member_id"];
				$txnArray["atxn_credit"]=$recharge_info['rwr_amount'];
				$txnArray["atxn_debit"]=0;
				$txnArray["atxn_status"]=1;
				$txnArray["atxn_comments"]=sprintf(Utilities::getLabel('L_RECHARGE_TXN_PLACED_NUMBER'),$formatted_txn_value);
				$transObj=new Advertisertransactions();
				if($txn_id=$transObj->addAdvertiserTransaction($txnArray)){
					$emailNotificationObj=new Emailnotifications();
					$emailNotificationObj->sendAdvertiserTxnNotification($txn_id);
				}
			}
			
			return true;		
		}else{
			$this->error = "Invalid Record";
			return false;
		}
		
	}
		
}