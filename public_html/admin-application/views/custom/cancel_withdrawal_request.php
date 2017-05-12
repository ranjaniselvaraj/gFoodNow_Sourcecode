<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $status_arr; ?> 
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
                        <div class="sectionhead"><h4>Cancel Withdrawal Request</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('custom','withdrawal_requests'); ?>">Back to Withdrawal Requests</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						
                        <div class="sectionbody">                            
                             <table class="table_form_horizontal">
                             	<tr>
									<th width="20%" align="left">Sender Name</th>
									<td><?php echo $withdrawal_request["user_name"]?>(<?php echo $withdrawal_request["user_username"]?>)</td>
								</tr>
					            <tr>
									<th width="20%" align="left">Account Details</th>
									<td><strong>Bank Name:</strong> <?php echo trim($withdrawal_request["withdrawal_bank"])?><br/><strong>A/c Name</strong>: <?php echo $withdrawal_request['withdrawal_account_holder_name']?><br/><strong>A/c Number</strong>: <?php echo $withdrawal_request['withdrawal_account_number']?><br/><strong>IFSC Code/Swift Code</strong>: <?php echo $withdrawal_request['withdrawal_ifc_swift_code']?><br/><strong>Bank Address</strong>: <?php echo $withdrawal_request['withdrawal_bank_address']?><br/><strong>Comments/Instructions</strong>: <?php echo $withdrawal_request['withdrawal_comments']?></td>
								</tr>
					            <tr>
									<th width="20%" align="left">Amount</th>
									<td><?php echo (Utilities::displayMoneyFormat($withdrawal_request["withdrawal_amount"],"-"))?></td>
								</tr>
								<tr>
									<th width="20%" align="left">Status</th>
									<td><?php echo $status_arr[$withdrawal_request["withdrawal_status"]];?></td>
								</tr>
							</table>
						</div>	
                        
                        <?php if ($withdrawal_request["withdrawal_status"]==0):?>
        				<div class="gap"></div>
				        <section class="section">
							<div class="sectionhead">
								<h4>Reason for cancellation</h4>																
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