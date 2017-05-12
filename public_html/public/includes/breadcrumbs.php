<?php
class Breadcrumb{
	private $breadcrumbs = array();
	private $separator_start = '<li>';
	private $separator_end = '</li>';
	private $start = '<ul class="breadcrumb flat">';
	private $end = '</ul>';

	public function __construct($admin = true){
		if($admin){
			$this->breadcrumbs[] = array('title' => 'Home', 'href' => Utilities::generateUrl());
		}	
	}
	
	function add($title, $href ="javascript:;", $class =""){		
		if (!$title) return;
		$this->breadcrumbs[] = array('title' => $title, 'href' => $href,'class'=>$class);
	}
	
	function output(){
		if ($this->breadcrumbs) {
			$output = $this->start;
			foreach ($this->breadcrumbs as $key => $crumb) {
					$output .= $this->separator_start;
					$size = sizeof($this->breadcrumbs)-1;
					if ($size == $key) {
						$output .=  $crumb['title'] ;			
					} else {
						$output .= '<a href="' . $crumb['href'] . '">' . $crumb['title'] . '</a>';
					}
					$output .= $this->separator_end;
			}
			return $output . $this->end . PHP_EOL;
		}
	}
}
?>