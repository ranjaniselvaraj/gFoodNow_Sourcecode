<?php
class PromotionsController extends CommonController{
	function track_click($promotion_id){
		$prmObj=new Promotions();
		$promotion_id = intval($promotion_id);
		$promotion = $prmObj->getPromotion($promotion_id);
		if ($promotion){
			$arrData=array('promotion_id'=>$promotion_id);	
			$prmObj->addPromotionAnalysisRecord($arrData,"clicks");
			Utilities::redirectUser($promotion['promotion_banner_url']);
		}
	}
}
