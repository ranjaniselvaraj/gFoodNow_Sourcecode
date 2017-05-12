<?php defined('SYSTEM_INIT') or die('Invalid Usage');  global $return_status_arr;
$request_amount=($request_detail["opr_customer_buying_price"]+$request_detail["opr_customer_customization_price"])*$request_detail["refund_qty"];	
$request_amount=$request_amount+round($request_amount*$request_detail["order_vat_perc"]/100,2);
?> 
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
					<section class="section"> <div id="form-div"></div>
                        <div class="sectionhead"><h4>View Return Request</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('returnrequests'); ?>"> Back to Return Requests</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						
                        <div class="sectionbody">                            
						<table class="table table-responsive" id="dtTable">
							<tr>
								<th width="25%">ID</th>
								<th width="25%">Product</th>
								<th width="25%">Qty</th>
								<th width="25%">Request Type</th>
							</tr>  
							<tr>
								<td><?php echo Utilities::format_return_request_number($request_detail["refund_id"])?></td>
								<td><?php echo $request_detail["opr_name"]?></td>
								<td><?php echo $request_detail["refund_qty"]?></td>
								<td><?php echo $request_detail['refund_or_replace']=="RP"?Utilities::getLabel('M_Replace'):Utilities::getLabel('M_Refund')?></td>
							</tr>
							<tr>
								<th>Reason</th>
								<th>Date</th>
								<th>Status</th>
								<th>Amount</th>
							</tr>
							<tr>
								<td><?php echo $request_detail['returnreason_title']?></td>
								<td><?php echo Utilities::formatDate($request_detail["refund_request_date"])?></td>
								<td><?php echo $return_status_arr[$request_detail["refund_request_status"]]; ?>
								<?php if ($request_detail["refund_request_action_by"]=="A"): 
									echo "By ".Settings::getSetting("CONF_WEBSITE_NAME"); 
								endif; ?>
								</td>
								<td><?php echo Utilities::displayMoneyFormat($request_amount)?></td>
							 </tr>
						</table>
						 <table class="table table-responsive" id="dtTable">
							  <thead>
							   <tr>
								   <th colspan="2">Messages Exchanged<th>
							  </tr>
							</thead>  
							<tbody>
								 <?php foreach($request_detail["messages"] as $key=>$val):
									$attachment_link=""; 
									if ($val["refmsg_attachment"]!=""):
										$attachment_link='<br/><a target="_blank" href="'.Utilities::generateUrl('returnrequests', 'download_attachment',array($val["refmsg_id"])).'"><img src="'.CONF_WEBROOT_URL.'images/attachment.png" class="marginRight">'.$val["refmsg_attachment"].'</a>';
									endif;	  ?>
								<tr>
        	                    	<td valign="top" width="10%">
                                    <?php if (is_null($val["admin_id"])) {?>
		            			    <img src="<?php echo Utilities::generateUrl('image', 'user',array($val["message_sent_by_profile"],'SMALL'),CONF_WEBROOT_URL)?>" alt="">
				                    <?php } else {?>    
	            			        <img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO")), CONF_WEBROOT_URL)?>" alt="" width="75">
				                    <?php } ?>
                    				</td>
									<td>
	                                    <span><?php echo Utilities::formatDate($val["refmsg_date"])?></span><br/>
                                        <?php echo is_null($val["admin_id"])?$val["message_sent_by_username"]:Settings::getSetting("CONF_WEBSITE_NAME")?>
                                       <p><?php echo nl2br($val["refmsg_text"])?></p>
                                       <?php echo $attachment_link?>
                                    </td>
								</tr>
							<?php  endforeach; ?>
							</tbody>
						 </table>
						</div>
					</section>
					<section class="section">
						<div class="sectionhead">
							<h4><?php echo Settings::getSetting("CONF_WEBSITE_NAME")?> Says</h4>																
						</div>
						<div class="sectionbody"><?php echo $frm->getFormHtml(); ?></div>
					</section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				