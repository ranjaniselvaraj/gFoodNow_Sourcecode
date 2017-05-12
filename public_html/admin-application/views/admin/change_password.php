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
                <li>Change Password</li>
            </ul>
            <div class="fixed_container">
                <div class="row">                    
                  <div class="col-sm-12">                      
                    <h1>Change Password</h1> 
                    <div class="containerwhite">
                        <?php include Utilities::getViewsPartialPath().'profile_left.php'; ?>   
                        <aside class="grid_2">
                            <?php include Utilities::getViewsPartialPath().'profile_top.php'; ?> 
							<div class="areabody"> 						  
                              <div class="formhorizontal">
							    <h3><i class="ion-ios-unlocked icon"></i> Change Password</h3>
							   <?php echo $frmChangePass->getFormHTML();?>	                                     
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
<script>
 $(document).ready(function($) {
	$('#check-password').strength({
				strengthClass: 'strength',
				strengthMeterClass: 'strength_meter',
				strengthButtonClass: 'button_strength',
				strengthButtonText: '<?php echo Utilities::getLabel('M_Show_Password')?>',
				strengthButtonTextToggle: '<?php echo Utilities::getLabel('M_Hide_Password')?>',
				strengthVeryWeakText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_very_weak')?></p>',
				strengthWeakText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_weak')?></p>',
				strengthMediumText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_very_medium')?></p>',
				strengthStrongText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_strong')?></p>'
			});
	});
</script>     		