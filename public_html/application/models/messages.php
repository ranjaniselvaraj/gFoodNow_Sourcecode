<?php
class Messages extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getMessageId() {
        return $this->message_id;
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getError() {
        return $this->error;
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
	
	function getAllMessages($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('ttm.message_id', 'desc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_threads', 'tth');
		$srch->joinTable('tbl_thread_messages', 'INNER JOIN', 'tth.thread_id=ttm.message_thread', 'ttm');
		$srch->joinTable('tbl_products', 'LEFT JOIN', 'tth.thread_record=tp.prod_id and tth.thread_type="P"', 'tp');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tth.thread_record=tord.opr_id and tth.thread_type="O"', 'tord');
		$srch->joinTable('tbl_orders_status', 'LEFT JOIN', 'tord.opr_status=ts.orders_status_id', 'ts');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ttm.message_from=tfr.user_id', 'tfr');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ttm.message_to=tse.user_id', 'tse');
		$srch->addCondition('ttm.message_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(ttm.message_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tth.*','ttm.*','tp.*','tord.*','ts.*','tfr.user_id as message_sent_by','tfr.user_username as message_sent_by_username','tfr.user_profile_image as message_sent_by_profile','tse.user_id as message_sent_to','tse.user_username as message_sent_to_username','tse.user_email as message_sent_to_email','tse.user_name as message_sent_to_name','tse.user_profile_image as message_sent_to_profile'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'thread':
                $srch->addCondition('ttm.message_thread', '=', intval($val));
                break;
			case 'id':
                $srch->addCondition('ttm.message_id', '=', intval($val));
                break;
			case 'from':
                $srch->addCondition('ttm.message_from', '=', intval($val));
                break;
			case 'to':
                $srch->addCondition('ttm.message_to', '=', intval($val));
                break;
			case 'all':
                	$cndCondition=$srch->addCondition('ttm.message_from', '=', intval($val));
					$cndCondition->attachCondition('message_to', '=', intval($val),'OR');
                	break;	
			case 'keyword':
                if ($val!=""){
					$val=urldecode($val);
					$cndCondition=$srch->addCondition('tth.thread_subject', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tfr.user_username', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tse.user_username', 'like', '%' . $val . '%','OR');
				}	
                break;
			case 'date_from':
                $srch->addCondition('ttm.message_date', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('ttm.message_date', '<=', $val. ' 23:59:59');
                break;	
			case 'pagesize':
					$srch->setPageSize($val);
					break;
			case 'page':
					$srch->setPageNumber($val);
					break;					
            }
        }
        //$srch->addOrder('tth.thread_id', 'desc');
        return $srch;
    }
    
	
	
	function SendMessageNotification($message_id){
			$message=self::getData($message_id);
			$url = 'http://' . $_SERVER['SERVER_NAME'] . Utilities::generateUrl('account', 'messages');
			$url='<a href="'.$url.'">'.Utilities::getLabel('L_Click_here').'</a>';
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{user_full_name}' => $message['message_sent_to_name'],
				'{username}' => $message['message_sent_by_username'],
				'{message_subject}' => $message['thread_subject']!=""?$message['thread_subject']:"-NA-",
				'{message}' => $message['message_text'],
				'{click_here}' => $url,
			);
		 	Utilities::sendMailTpl($message["message_sent_to_email"], "send_message", $arr_replacements);
			
	}
	
	function updateMessageBody($message_id,$data_update=array()) {
		$message_id = intval($message_id);
		if($message_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_thread_messages', $data_update, array('smt'=>'`message_id` = ?', 'vals'=> array($message_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($message_id){
		$message_id = intval($message_id);
		if($message_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_thread_messages', array('message_is_deleted' => 1), array('smt' => 'message_id = ?', 'vals' => array($message_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
   
}