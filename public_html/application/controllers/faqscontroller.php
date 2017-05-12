<?php
class FaqsController extends CommonController{
	function default_action(){
		$category=new Categories();
		$faq_categories=$category->getCategoriesHavingRecords(2);
		foreach($faq_categories as $key=>$val){
			$arr_faqs=array("faqs"=>$this->Faqs->getcategoryFaqs($val["category_id"]));
			$faq_cats[]=array_merge($val,$arr_faqs);
		}
		/*Utilities::printarray($faq_cats);
		die();*/
		$this->set('faq_categories', $faq_cats);
		$this->_template->render();	
	}
	
	function category($id,$faq=0){
		$cat_obj=new Categories();
		$faq_obj=new Faqs();
		$faq_categories=$cat_obj->getCategoriesHavingRecords(2);
		$this->set('faq_categories', $faq_categories);
		$faq_category=$cat_obj->getData($id);
		if (!$faq_category)
			Utilities::show404();
		$faq_obj=new Faqs();
		$arr_faqs=array("faqs"=>$faq_obj->getcategoryFaqs($id));
		$faq_cats=array_merge($faq_category,$arr_faqs);
		$this->set('faq_category', $faq_cats);
		$this->set('faq', $faq);
		$this->_template->render();	
	}
}
