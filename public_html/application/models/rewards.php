<?php
class Rewards extends Model {
	
	function __construct() {
		$this->db = Syspage::getdb();
    }
	
	function getData() {
        return $this->attributes;
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
	
	function search($criteria){
        $srch = new SearchBase('tbl_user_reward_points', 'tur');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tur.urp_user_id=tu.user_id', 'tu');
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
		        switch($key) {
				 	case 'page':
						$srch->setPageNumber($val);
					break;	
					case 'pagesize':
						$srch->setPageSize($val);
					break;
					case 'user':
	            		$srch->addCondition('tur.urp_user_id', '=', intval($val));
		    	    break;
					case 'id':
	            		$srch->addCondition('tur.urp_reward_id', '=', intval($val));
		    	    break;
	    	    }
    	}
		$srch->addOrder('tur.urp_reward_id', 'desc');
		return $srch;
	}
	
	function getRewardPointRecordById($id, $add_criteria=array()) {
		if ($id>0){
	    	$add_criteria['id'] = $id;
		}
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	
	function getRewardPoints($criterias,$pagesize){
		$srch = self::search($criterias);
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}else{
			$srch->doNotLimitRecords();
		}
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	
	
	/*function addRewardPoints($data){
		$user_id = intval($data['urp_user_id']);
		unset($data['urp_user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_user_reward_points');
		$data['urp_user_id'] = $user_id;
		$data["urp_date_added"]='mysql_func_NOW()';
		$record->assignValues($data);
		if($record->addNew()){
			return intval($record->getId());
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}*/
	
	function addRewardPoints($data){
		$user_id = intval($data['urp_user_id']);
		unset($data['urp_user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_user_reward_points');
		$data['urp_user_id'] = $user_id;
		$data["urp_date_added"]=date('Y-m-d H:i:s');
		$record->assignValues($data);
		if($record->addNew()){
			$rewardId=intval($record->getId());	
			self::getAndSetRewardsPointBreakup($rewardId);	
			return $rewardId;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	/*function getUserRewardsPointsBalance($user_id) {
		$query = $this->db->query("SELECT SUM(urp_points) AS total FROM tbl_user_reward_points turp WHERE urp_user_id = '" . (int)$user_id . "' AND (turp.urp_date_expiry >= CURRENT_DATE() OR turp.urp_date_expiry = '0000-00-00' )");
		$record_query = $this->db->fetch($query);
		return $record_query['total'];
	}	*/
	
	function getUserRewardsPointsBalance($user_id) {
		$query = $this->db->query("SELECT SUM(urpdetail_points) AS total FROM tbl_user_reward_points turp INNER JOIN tbl_user_reward_point_breakup turpb on (turpb.urpdetail_urp_reward_id=turp.urp_reward_id) WHERE turp.urp_user_id = '" . (int)$user_id . "' AND (turpb.urpdetail_expiry >= CURRENT_DATE() OR turpb.urpdetail_expiry = '0000-00-00') AND turpb.urpdetail_used = '0' AND turpb.urpdetail_points > 0");		
		$record_query = $this->db->fetch($query);
		return $record_query['total'];
	}
	
	function getTotalUserRewardsByReferrerId($user_id,$referrer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM tbl_user_reward_points WHERE urp_user_id = '" . (int)$user_id . "' AND urp_referrer_id = '" . (int)$referrer_id . "' AND urp_points > 0");
		$record_query = $this->db->fetch($query);
		return $record_query['total'];
	}	
	
	/********************   Functions Written By Anup  ******************************/
	function getAndSetRewardsPointBreakup($rewardId){
		$rewardId=intval($rewardId);
		if($rewardId < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$srch = new SearchBase('tbl_user_reward_points', 'tur');
		$srch->addCondition('tur.urp_reward_id', '=', intval($rewardId));
		$rs = $srch->getResultSet();
		$result=$this->db->fetch($rs);
		if(!empty($result)){
			$assignValues=array(
				'urpdetail_urp_reward_id'=>$result['urp_reward_id'],
				'urpdetail_points'=>$result['urp_points'],
				'urpdetail_expiry'=>$result['urp_date_expiry'],
				'urpdetail_used'=>0	,
				'urpdetail_used_ref_id'=>$result['urp_referrer_id'],
			);
			self::addRewardsPointsBreakup($assignValues);
			
			if($result['urp_points']<0){
				$userRewardPoints=abs($result['urp_points']);
				
				$criteria=array('urpdetail_used'=>0,'user'=>$result['urp_user_id']);	
				$unUsedRewardsPointsArr=self::getUserRewardsPointsSearch($criteria);
				foreach($unUsedRewardsPointsArr as $val){
					
					if($userRewardPoints==0){break;}
					
					if($val['urpdetail_points']>0){
						if($val['urpdetail_points']<=$userRewardPoints){
							$userRewardPoints=$userRewardPoints-$val['urpdetail_points'];
							$updateValues=array('urpdetail_used'=>1);
							$whr=array('smt' => 'urpdetail_id = ?', 'vals' => array($val['urpdetail_id']));		
							$this->db->update_from_array('tbl_user_reward_point_breakup',$updateValues,$whr);
						}else{
							$difference=$val['urpdetail_points']-$userRewardPoints;
							
							$updateValues=array('urpdetail_used'=>1,'urpdetail_points'=>$userRewardPoints);
							$whr=array('smt' => 'urpdetail_id = ?', 'vals' => array($val['urpdetail_id']));		
							$this->db->update_from_array('tbl_user_reward_point_breakup',$updateValues,$whr);
							
							$insertValuesArr=array(
								'urpdetail_urp_reward_id'=>$val['urpdetail_urp_reward_id'],
								'urpdetail_points'=>$difference,
								'urpdetail_expiry'=>$val['urpdetail_expiry'],
								'urpdetail_used'=>0,
								'urpdetail_used_ref_id'=>$val['urp_referrer_id'],
							);
							self::addRewardsPointsBreakup($insertValuesArr);							
							$userRewardPoints=0;
						}					
					}
				}														
			}			
		}		
	}
		
	function addRewardsPointsBreakup($data){
		$reward_id = intval($data['urpdetail_urp_reward_id']);
		unset($data['urpdetail_urp_reward_id']);
		if($reward_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_user_reward_point_breakup');
		$data['urpdetail_urp_reward_id'] = $reward_id;
		$record->assignValues($data);
		if($record->addNew()){
			$urpdetail_id=intval($record->getId());				
			return $urpdetail_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;		
	}
	
	function getUserRewardsPointsSearch($criteria){$srch = new SearchBase('tbl_user_reward_point_breakup', 'turb');
		$srch->addFld("turb.*,tur.*, if( urp_date_expiry = '0000-00-00', 1, 0 ) AS sort ");	
		$srch->joinTable('tbl_user_reward_points', 'INNER JOIN', 'turb.urpdetail_urp_reward_id=tur.urp_reward_id', 'tur');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tur.urp_user_id=tu.user_id', 'tu');
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
		        switch($key) {
				 	case 'page':
						$srch->setPageNumber($val);
					break;	
					case 'pagesize':
						$srch->setPageSize($val);
					break;
					case 'user':
	            		$srch->addCondition('tur.urp_user_id', '=', intval($val));
		    	    break;
					case 'urpdetail_used':
	            		$srch->addCondition('turb.urpdetail_used', '=', intval($val));
						$srch->addCondition('turb.urpdetail_points', '>', 0);
						if( intval($val)==0){						
							$srch->addDirectCondition("(turb.urpdetail_expiry >= CURRENT_DATE() OR turb.urpdetail_expiry = '0000-00-00')");
							$srch->addOrder('tur.urp_date_added','asc');
							$srch->addOrder('sort','asc');
							$srch->addOrder('turb.urpdetail_expiry','asc');
						}
		    	    break;					
					case 'reward_id':
					case 'urp_reward_id':
	            		$srch->addCondition('tur.urp_reward_id', '=', intval($val));
		    	    break;
	    	    }
    	}
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
	}
	
	/******************* End Functions Written By Anup ************/
	
}