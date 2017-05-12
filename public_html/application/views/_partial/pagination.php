<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php if($total_records>0!=true) return; ?>
        <?php
			$QueryString = '';
			foreach ($query_vars as $Key => $Value){
				if ($Value!="")	
		        $QueryString .= "&" . $Key . '=' . $Value;
			}
				
            if ($pages > 1) :
				if ($page > 1):
						$url = Utilities::generateUrl($controller, $action, (array)$url_vars)."?page=1".$QueryString;
						echo '<li><a href="'.$url.'">&laquo;</a></li> ';
					endif;
					
				$url = Utilities::generateUrl($controller, $action, (array)$url_vars)."?page=xxpagexx".$QueryString;
                echo getPageString('<li><a href="'.$url.'">xxpagexx</a></li> ', $pages, $page, '<li class="selected active"><a  href="'.$url.'" >xxpagexx</a></li> ');
				
					if ($page < $pages):
						$url = Utilities::generateUrl($controller, $action, (array)$url_vars)."?page={$pages}".$QueryString;
						echo '<li><a href="'.$url.'">&raquo;</a></li>';
					endif;
            endif;
        ?>
