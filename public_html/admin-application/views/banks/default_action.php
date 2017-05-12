<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<div id="body">
    	<!--leftPanel start here-->    
        <?php include Utilities::getViewsPartialPath().'left-cms.php'; ?>
        <!--leftPanel end here--> 
        <!--rightPanel start here-->    
       <section class="rightPanel">
        	<ul class="breadcrumb">
                <li><a href="<?php echo Utilities::generateUrl('home'); ?>"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/home.png" alt=""> </a></li>
                <li>Banks</li>
            </ul>
			 <?php echo Message::getHtml();?>
		   
						 
           <div id="form-div"></div>
			<section class="box" >
                        	<div class="box_head"><h3>Manage Banks</h3> <a href="<?php echo Utilities::generateUrl('banks', 'form'); ?>" class="fr button green"> Add Bank</a></div>
                            <div class="box_content clearfix toggle_container">
							<?php if (count($arr_listing)>0):?>
                            <table class="dataTable" id="dtTable">
							  <thead>
							  <tr>
								  <th width="90%">Title</th>
								  <th class="text-center">Actions</th>
							  </tr>
						  </thead>   
						  <tbody>
						  <?php foreach ($arr_listing as $sn=>$row) {  ?>
							<tr>
								<td><?php echo trim($row["bank_name"])?></td>
								<td class="text-center" nowrap="nowrap">
								<ul class="iconbtns">
                                        <li><a href="<?php echo Utilities::generateUrl('banks', 'form', array($row['bank_id']))?>" title="Edit"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/pencil.png" alt="" class="whiteicon pencil_icon"></a></li>
                                        <li><a onclick="return(confirm('Are you sure to delete this record?'));" href="<?php echo Utilities::generateUrl('banks', 'delete', array($row['bank_id']))?>" title="Delete"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/dustbin.png" alt="" class="whiteicon delete_icon"></a></li>
                                    </ul>
								
								</td>
							</tr>
							<?php }?>
							<?php else: ?>
							 <p>We are unable to find any record corresponding to your selection in this section.</p>
							<?php endif;?>
						</tbody>
					  </table>  
                           
                           
						   
						   <div class="gap"></div> 
                            <div class="fullWrap"> 
								<?php if ($total_records>0):?>     
                                <div class="results">
                                    <span>Showing Results <?php echo $start_record?> - <?php echo $end_record?> of <?php echo $total_records?></span>
                                </div>
								<?php endif; ?>
                                 <ul class="pagination">
								 <?php unset($search_parameter["url"]); ?>
								 <?php echo Utilities::renderView(Utilities::getViewsPartialPath().'pagination.php', array(
									'start_record' => $start_record,
									'end_record' => $end_record,
									'total_records' => $total_records,
									'pages' => $pages,
									'page' => $page,
									'controller' => 'banks',
									'action' => 'default_action',
									'url_vars' => array(),
									'query_vars' => $search_parameter,
									)); ?>
                                </ul>
                            </div>
						   
                           
                         </div>
           
                        </section>
						
                   
           
        </section>
        <!--rightPanel end here-->  
        
        
    </div>
			