<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ;?> 
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
            <li>Smart Recommendations</li>
			<li>Weightages</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Weightage Settings </h4> 
                        </div>
						
                        <div class="tabs_nav_container responsive flat">
                            
                            <ul class="tabs_nav detailTabs">
                                <li><a class="active" rel="tabs_1" href="javascript:void(0)" name="general">Products</a></li>
                            </ul> 
								<form class="web_form" action="?" method="post">
                                <div class="tabs_panel_wrap">
                                        
                                        <span class="togglehead active" rel="tabs_1">Products</span>
                                        <!--tab 1 start here-->
										<div id="tabs_1" class="tabs_panel">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
											<tr>
											<td colspan="2">
												<table id="commission_form_table" class="table_listing table">
													<thead>
													<tr>
														<th width="25%">Event</th>
														<th width="25%">Weightage</th>
													</tr>
												</thead>
												<tbody>
	                                            <?php foreach($product_weightage_events["products"] as $wkey=>$wevent) {?>
												<tr>
													<td><?php echo $wevent['caption']?></td>
													<td><input type="text" name="weightage_settings[products#<?php echo $wkey?>]" value="<?php echo $wevent['value']?>" autocomplete="off" /> <?php echo $wevent['extra']?></td>
												</tr>
                    	                        <?php } ?>
												</tbody>
											</table>
											</td>
											</tr>
										</table>
                                        </div>
										<!--tab 1 end here-->
										
                                  </div> 
                                  <div class="gap"></div>
                                  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
									<tr><td><input type="submit" value="Submit" /></td></tr>
                                  </table>     
								</form>
                        </div>
                        									
					</section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				
