<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $supplier_approval_request_status; ?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<?php echo html_entity_decode($breadcrumb); ?>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>View Seller Request</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('users','supplier_approval_requests'); ?>">Back to Seller Requests</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						
                        <div class="sectionbody">                            
                             <table class="table_form_horizontal">
                             	<tr>
									<th width="20%" align="left">Reference Number</th>
									<td><?php echo $supplier_request["usuprequest_reference"]?></td>
								</tr>
					            <tr>
									<th width="20%" align="left">Status</th>
									<td><?php echo $supplier_approval_request_status[$supplier_request["usuprequest_status"]]?></td>
								</tr>
					            <tr>
									<th width="20%" align="left">Comments/Reason</th>
									<td><?php echo nl2br(Utilities::displayNotApplicable($supplier_request["usuprequest_comments"],"-"))?></td>
								</tr>
								<tr>
									<th width="20%" align="left">Name</th>
									<td><?php echo $supplier_request["user_name"]?></td>
								</tr>
								<tr>
									<th align="left">Email</th>
									<td><?php echo $supplier_request["user_email"]?></td>
								</tr>
								<tr>
									<th align="left">Username</th>
									<td><?php echo $supplier_request["user_username"]?></td>
								</tr>
								<?php foreach($supplier_request["field_values"] as $skey=>$sval):?> 
								<tr>
									<th align="left"><?php echo $sval["sformfield_caption"]?></th>
									<td><?php if ($sval["sformfield_type"]!="file") { echo Utilities::displayNotApplicable(nl2br($sval["sfreqvalue_text"]),"-"); } else { ?><a href="<?php echo Utilities::generateUrl('users','download_attachment',array($sval["sfreqvalue_text"])); ?>"><?php echo Utilities::displayNotApplicable($sval["sfreqvalue_text"],"-");?></a> <?php } ?></td>
								</tr>
								<?php endforeach;?>
							</table>
						</div>	
                        
                        <?php if ($supplier_request["usuprequest_status"]==0):?>
        				<div class="gap"></div>
				        <section class="section">
							<div class="sectionhead">
								<h4>Update Seller Request</h4>																
							</div>
							<div class="sectionbody"><?php echo $frm->getFormHtml(); ?></div>
						</section>						
    				    <?php endif;?>
															
					</section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				