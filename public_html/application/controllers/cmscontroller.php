<?php
class CmsController extends CommonController{
	function view($id){
		$hide_header_footer=Utilities::isHideHeaderFooter();
		$cmsPage = $this->Cms->getData($id);
		if (!$cmsPage && !$hide_header_footer)
			Utilities::show404();
		$this->set('row', $cmsPage);
		if($hide_header_footer){
			$this->set('hide_header_footer',$hide_header_footer);		
			$this->_template->render(false,false);	
		}else{
			$this->_template->render();	
		}
	}
}
