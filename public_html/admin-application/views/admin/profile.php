<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<div id="body">
    
    	<!--left panel start here-->
		<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
		<!--left panel end here-->
        
        <!--right panel start here-->
        <?php include Utilities::getViewsPartialPath().'right.php'; ?>   
        <!--right panel end here-->
        		
        <!--main panel start here-->
        <div class="page">
			<ul class="breadcrumb flat">
                <li><a href="<?php echo Utilities::generateUrl('home'); ?>"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/home.png" alt=""> </a></li>
                <li>Profile</li>
            </ul>
            <div class="fixed_container">
                <div class="row">                    
                  <div class="col-sm-12">                      
                    <h1>My Profile</h1> 
                    <div class="containerwhite">
                        <?php include Utilities::getViewsPartialPath().'profile_left.php'; ?>  
                        <aside class="grid_2">
							 <?php include Utilities::getViewsPartialPath().'profile_top.php'; ?> 
                          <div class="areabody"> 						  
                              <div class="formhorizontal">
							    <h3><i class="ion-person icon"></i> Profile Information</h3>
							   	<?php echo $frmProfile->getFormHTML();?>	                                     
                               </div>  
                            </div>   
                        </aside>  
                    </div>
                   </div>               
                    
                </div>
            </div>
        </div>  
        
        <!--main panel end here-->
    </div>
    <!--body end here-->
    </div>		