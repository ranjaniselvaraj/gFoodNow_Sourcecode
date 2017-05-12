<?php
class Paypaladaptive_pay extends Model {
	protected $db;
	private $key_name=CONF_PAPYAL_ADAPTIVE_KEY;
	function __construct() {
		$this->db = Syspage::getdb();
    }
	
	function getError() {
        return $this->error;
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getChainedPayments($criteria) {
        $srch = self::search($criteria);
		$srch->addOrder('ppadappay_id','DESC');
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	
	function search($criteria){
		$srch = new SearchBase('tbl_order_pp_adaptive_payments','tpp');
		$srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'tpp.ppadappay_order_id = `tord`.order_id', 'tord');
		$srch->addCondition('ppadappay_primary_payment_status', '=', 1);
		foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
			case 'id':
                $srch->addCondition('tpp.ppadappay_id', '=', $val);
                break;
			case 'status':
                $srch->addCondition('tpp.ppadappay_chained_payments_status', '=', $val);
                break;
			case 'payment_status':
                $srch->addCondition('tpp.ppadappay_status', '=', $val);
                break;	
			case 'date_from':
                $srch->addCondition('tpp.ppadappay_to_be_executed_on', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tpp.ppadappay_to_be_executed_on', '<=', $val. ' 23:59:59');
                break;
			case 'keyword':
					$cndCondition=$srch->addCondition('tord.order_user_name', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tord.order_user_email', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tord.order_invoice_number', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tord.order_reference', 'like', '%' . $val . '%','OR');
                break;	
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;		
            }
        }
		return $srch;
	}
	
	function getDueChainedPayments($date = ''){
		if(!is_string($date) || strlen($date) < 1){
			$date = date('Y-m-d H:i:s');
		}
		return $this->getChainedPayments(array("date_to"=>$date,"payment_status"=>0,"status"=>0));
	}
	
	function updateAdaptivePayEntry($order_id, $pay_key, $execution_status){
		$order_id = intval($order_id);
		if($order_id < 1 || !is_string($pay_key) || strlen($pay_key) < 4){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		
		$values = array(
			'ppadappay_chained_payments_status'=>$execution_status
		);
		
		$whr = array(
			'smt'=>'`ppadappay_order_id` = ? AND `ppadappay_pay_key` = ?',
			'vals'=>array($order_id, $pay_key)
		);
		
		if($this->db->update_from_array('tbl_order_pp_adaptive_payments', $values, $whr)){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function updateAdaptivePayments($order_id, $pay_key, $data=array()){
		$order_id = intval($order_id);
		if($order_id < 1 || !is_string($pay_key) || strlen($pay_key) < 4){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		
		$values = array();
		if(!empty($data)){
			foreach($data as $key=>$val){
				switch($key){
					case 'PRIMARY_STATUS':
						$values['ppadappay_primary_payment_status']=$val;
					break;
				}
			}			
		}		
		
		$whr = array(
			'smt'=>'`ppadappay_order_id` = ? AND `ppadappay_pay_key` = ?',
			'vals'=>array($order_id, $pay_key)
		);
		
		if($this->db->update_from_array('tbl_order_pp_adaptive_payments', $values, $whr)){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function addAdaptivePayEntry($order_id, $data){
		$order_id = intval($order_id);
		if($order_id < 1 || !is_array($data) || sizeof($data) < 3){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$values = array(
			'ppadappay_order_id'=>$order_id,
			'ppadappay_pay_key'=>$data['pay_key'],
			'ppadappay_primary_payment_status'=>0,
			'ppadappay_chained_payments_status'=>$data['chained'],
			'ppadappay_to_be_executed_on'=>$data['execution_date'],
		);
		
		if($this->db->insert_from_array('tbl_order_pp_adaptive_payments', $values)){
			return $this->db->insert_id();
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function saveTransaction($order_id, $request_type, $request, $response){
		$order_id = intval($order_id);
		if($order_id < 1 || !is_array($response) || sizeof($response) < 3){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		
		$record = new TableRecord('tbl_order_pp_adaptive_payment_transactions');
		$data['ppaptrans_order_id'] = $order_id;
		$data['ppaptrans_type'] = $request_type;
		$status = '';
		if(array_key_exists('paymentExecStatus', $response)){
			$status = $response['paymentExecStatus'];
		}elseif(array_key_exists('status', $response)){
			$status = $response['status'];
		}
		$data['ppaptrans_status'] = $status;
		$data['ppaptrans_request'] = convertToJson($request);
		$data['ppaptrans_response'] = convertToJson($response);
		
		$record->assignValues($data);
		if($record->addNew()){
			return intval($record->getId());
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	function doDebitTransactionForPaidAmountToSeller($payment_data, $order_id){
		
		$invoice_arr = explode('-', $payment_data['receiver']['invoiceId']);
		$shop_id = array_shift($invoice_arr);
		$sliced_arr = array_slice($invoice_arr, 0, -1); 
		$invoice_id = implode('-', $sliced_arr);
		$txn_amount = round($payment_data['receiver']['amount'], 2);
		$srch = new SearchBase('tbl_shops', 'ts');
		$srch->addCondition('ts.shop_id', '=',$shop_id);
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		$user_id= $row['shop_user_id'];
		//mail("ravibhalla@dummyid.com","PayPal Debit",implode("#",$payment_data['receiver']));
		$transObj = new Transactions();
		$criteria = array(
			'user' => $user_id,
		);
		
		$srch = $transObj->search($criteria, false);
		$srch->addCondition('tut.utxn_order_id', '=', $order_id);
		$srch->addCondition('tut.utxn_debit', '=', $txn_amount);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if($row = $this->db->fetch($rs)){
			return false;
		}
		
		$txnArray["utxn_user_id"] = $user_id;
		$txnArray["utxn_debit"] = $txn_amount;
		$txnArray["utxn_status"] = 1;
		$txnArray["utxn_order_id"] = $order_id;
		$txnArray["utxn_comments"] = sprintf(Utilities::getLabel('L_Amount_Paid_Through_PayPal_Adaptive'),$invoice_id,$payment_data['transactionId']);
		//$txnArray["utxn_comments"] = 'Amount paid to Seller for order ID #' . $invoice_id . ', through Paypal Adaptive chained payments. Paypal transaction ID: ' . $payment_data['transactionId'];
		
		if($txn_id = $transObj->addTransaction($txnArray)){
			$emailNotificationObj = new Emailnotifications();
			$emailNotificationObj->sendTxnNotification($txn_id);
		}
		return true;
	}
	
	function getData($id) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function updatePPATxnStatus($ppa_id,$data_update=array()) {
		$ppa_id = intval($ppa_id);
		if($ppa_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_order_pp_adaptive_payments', $data_update, array('smt'=>'`ppadappay_id` = ?', 'vals'=> array($ppa_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function verifyPayPalAccount($payPalAccount,$firstName="",$lastName=""){
		require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/paypal-library/autoload.php');
		$pmObj = new PaymentSettings($this->key_name);
		$payment_settings = $pmObj->getPaymentSettings();
		$PayPalConfig = array(
					  'Sandbox' => !$payment_settings['pp_adaptive_transaction_mode'],
					  'DeveloperAccountEmail' => $payment_settings['pp_adaptive_merchant_email'],
					  'ApplicationID' => $payment_settings['pp_adaptive_api_app_id'],
					  'DeviceID' => $device_id,
					  'IPAddress' => $_SERVER['REMOTE_ADDR'],
					  'APIUsername' => $payment_settings['pp_adaptive_api_username'],
					  'APIPassword' => $payment_settings['pp_adaptive_api_password'],
					  'APISignature' => $payment_settings['pp_adaptive_api_signature'],
					  'APISubject' => '',
                      'PrintHeaders' => false, 
					  'LogResults' => false, 
					  'LogPath' => $log_path,
					);
					$PayPal = new angelleye\PayPal\Adaptive($PayPalConfig);
					// Prepare request arrays
					$GetVerifiedStatusFields = array(
							'EmailAddress' => $payPalAccount, 					// Required.  The email address of the PayPal account holder.
							'FirstName' => $firstName, 						// The first name of the PayPal account holder.  Required if MatchCriteria is NAME
							'LastName' => $lastName, 						// The last name of the PayPal account holder.  Required if MatchCriteria is NAME
							'MatchCriteria' => 'NAME'					// Required.  The criteria must be matched in addition to EmailAddress.  Currently, only NAME is supported.  Values:  NAME, NONE   To use NONE you have to be granted advanced permissions
						);
					$PayPalRequestData = array('GetVerifiedStatusFields' => $GetVerifiedStatusFields);
					// Pass data into class for processing with PayPal and load the response array into $PayPalResult
					$PayPalResult = $PayPal->GetVerifiedStatus($PayPalRequestData);
					/*print_r($PayPalResult);
					die();*/
					// Write the contents of the response array to the screen for demo purposes.
					return $PayPalResult["AccountStatus"]=="VERIFIED"?true:false;
				
	}
}