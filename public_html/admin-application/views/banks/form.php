<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<div id="body">
    	<!--leftPanel start here-->    
        <?php include Utilities::getViewsPartialPath().'left-catalog.php'; ?>
        <!--leftPanel end here--> 
        <!--rightPanel start here-->    
       <section class="rightPanel">
        	<ul class="breadcrumb">
                <li><a href="<?php echo Utilities::generateUrl('home'); ?>"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/home.png" alt=""> </a></li>
                <li><a href="<?php echo Utilities::generateUrl('banks'); ?>">Banks</a></li>
                <li>Bank Setup</li>
            </ul>
			 <?php echo Message::getHtml();?>
	         <section class="box box_content">
                        		<h5>Bank Setup</h5>
                                <div class="box_content toggle_container">
								<?php echo $frm->getFormHtml(); ?>
							</div>
              	 </section>
        </section>
        <!--rightPanel end here-->  
    </div>
