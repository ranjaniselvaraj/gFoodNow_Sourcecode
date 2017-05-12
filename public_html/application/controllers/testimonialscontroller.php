<?php
class TestimonialsController extends CommonController{
	function default_action(){
		$testimonials=$this->Testimonials->getTestimonials(array());
		$this->set('testimonials',$testimonials);
		$this->_template->render();	
	}
}
