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
            <li>CMS</li>
			<li>FAQ Categories</li>
		</ul>
		<div class="fixed_container">
			<div class="row">				
			<div class="col-sm-12">		
				<section class="section">
					<?php if ($parent>0):?>
					<a href="<?php echo Utilities::generateUrl('categories', 'default_action'); ?>">Root Categories</a> 
				   <?php foreach($category_structure as $catKey=>$catVal): ?>
				   &raquo;&raquo; 
				   <?php  $cntInc++; if ($cntInc<count($category_structure)) :  ?>			 
						 <a href="<?php echo Utilities::generateUrl('categories', 'default_action',array($catVal["category_id"])); ?>">
					<?php endif;?>
						<?php echo $catVal["category_name"]?>
						<?php if ($cntInc<count($category_structure)) :?></a> <?php endif;?>
					<?php endforeach;?>
					<!--<div class="gap"></div>-->
					<br/><br/>
					<?php endif; ?>
				<div id="form-div"></div>
                        <div class="sectionhead"><h4>Manage - FAQ Categories</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('faqcategories', 'form',array(0,$parent)); ?>">Add FAQ Category</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						
                        <div class="sectionbody">                            
                         <?php if ((count($arr_listing)>0) && (!empty($arr_listing))) :?>
                          <table class="table table-responsive" id="dtTable">
                                        <thead>
                                           <tr>											 
											  <th width="60%">Name</th>
											  <th width="25%">Status</th>
											  <th class="text-center">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                          <?php foreach ($arr_listing as $sn=>$row) {  
										  $category_parent=$row["category_parent"]!=""?$row["category_parent"]:"-NA-";
										  ?>
											<tr style="color:<?php if (($row['category_status']==0)){ ?>#AAAAAA<?php }?>">
												
												<td><?php echo $row["category_name"]?></td>
								
												<td><?php echo $row['category_status']==1?"<span class='label label-success'>Active</span>":"<span class='label label-danger'>In-active</span>";?></td>
												<td class="text-center" nowrap="nowrap">
												
                                                <ul class="actions">
													<?php if ($row['category_status']==0):?>
														<li><a href="#"  title="Click to Enable" class="toggleswitch disabled" ><i onclick="UpdateStatus('<?php echo $row['category_id'] ?>', $(this));" class="ion-checkmark-circled icon"></i></a></li>
													<?php  else : ?>
														  <li> <a href="#"   title="Click to Disable" class="toggleswitch enabled" ><i onclick="UpdateStatus('<?php echo $row['category_id'] ?>', $(this));" class="ion-checkmark-circled icon"></i></a></li>
													<?php endif; ?>
													<li><a href="<?php echo Utilities::generateUrl('faqcategories', 'form', array($row['category_id']))?>" title="Edit"><i class="ion-edit icon"></i></a></li>
													<li><a  href="javascript:void(0);" onclick="ConfirmDelete('<?php echo $row['category_id'] ?>', $(this));" title="Delete"><i class="ion-android-delete icon"></i></a></li>													
													</ul>
												
												</td>
											</tr>
											<?php }?>
											<?php else: ?>
											 <p>We are unable to find any record corresponding to your selection in this section.</p>
											<?php endif;?>                                      
                                        </tbody>    
                                    </table>                                
                                </div>	
								<div class="gap"></div>
								<div class="footinfo">
                                <aside class="grid_1">
                                    <ul class="pagination">
										
                                        <?php echo Utilities::renderView(Utilities::getViewsPartialPath().'pagination.php', array(
									'start_record' => $start_record,
									'end_record' => $end_record,
									'total_records' => $total_records,
									'pages' => $pages,
									'page' => $page,
									'controller' => 'faqcategories',
									'action' => 'default_action',
									'url_vars' => array(),
									'query_vars' => array(),
									)); ?>
                                    </ul>
                                </aside>  
								<?php  if ($total_records>0):?>
                                <aside class="grid_2"><span class="info">Showing <?php echo $start_record?> to  <?php echo $end_record?> of <?php echo $total_records?> entries</span></aside>
								<?php endif; ?>
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