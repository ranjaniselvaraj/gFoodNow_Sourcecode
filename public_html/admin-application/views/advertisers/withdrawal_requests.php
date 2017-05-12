<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $status_arr;global $button_status_arr;?> 
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
            <li>Advertisers</li>
			<li>Advertiser Withdrawal Requests</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4>Search Advertiser Withdrawal Requests</h4>									
						</div>
						<div class="sectionbody space togglewrap" style="display:none;">
							<?php echo $search_form->getFormHtml(); ?>			
						</div>
					</section>
					<section class="section"> <div id="form-div"></div>
                        <div class="sectionhead"><h4>Manage - Advertiser Withdrawal Requests</h4></div>
						
                        <div class="sectionbody">                            
                         <?php if ((count($arr_listing)>0) && (!empty($arr_listing))) :?>
                          <table class="table table-responsive" id="dtTable">
                                        <thead>
                                           <tr>
											  <th width="8%">ID</th>
											  <th width="20%">Advertiser Details</th>
											  <th width="12%">Amount</th>
                                              <th width="12%">Payment Mode</th>
											  <th width="25%">Account Details</th>
											  <th width="10%">Date</th>
											  <th width="10%">Status</th>
											  <th class="text-center">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                          <?php foreach ($arr_listing as $sn=>$row) {  
										  switch($row['adwithdrawal_payment_mode']) {
									        case 'bank':
            									$payment_details="<b>".Utilities::getLabel('L_Bank_Name')."</b>: ".$row["adwithdrawal_bank_name"]."<br/><b>".Utilities::getLabel('L_ABA/BSB_number_Branch_Number')."</b>: ".$row["adwithdrawal_bank_branch_number"]."<br/><b>".Utilities::getLabel('L_SWIFT_Code')."</b>: ".$row["adwithdrawal_bank_swift_code"]."<br/><b>".Utilities::getLabel('L_Account_Name').": ".$row["adwithdrawal_bank_account_name"]."</b><br/><b>".Utilities::getLabel('L_Account_Number')."</b>: ".$row["adwithdrawal_bank_account_number"];
											break;
						        			case 'paypal':
		        	    						$payment_details="<b>".Utilities::getLabel('L_PayPal_Email_Account')."</b>: ".$row["adwithdrawal_paypal"];
						        		    break;
											case 'cheque':
		        	    						$payment_details="<b>".Utilities::getLabel('L_Cheque_Payee_Name')."</b>: ".$row["adwithdrawal_cheque"];
						        		    break;
				}
										   ?>
											<tr>
												<td>#<?php echo str_pad($row["adwithdrawal_id"],6,'0',STR_PAD_LEFT);?></td>
												<td><strong>N</strong>: <?php echo $row["advertiser_name"]?><br/><strong>U</strong>: <?php echo $row["advertiser_username"]?><br/><strong>E</strong>: <?php echo $row["advertiser_email"]?></td>
												<td><?php echo Utilities::displayMoneyFormat($row["adwithdrawal_amount"])?></td>
                                                <td><?php echo ucfirst($row["adwithdrawal_payment_mode"])?></td>
												<td><?php echo $payment_details?><br/><strong>Comments/Instructions</strong>: <?php echo $row['adwithdrawal_comments']?></td>
												<td><?php echo Utilities::formatDate($row["adwithdrawal_request_date"])?></td>
												<td>
												<?php $labelInfo=$button_status_arr[$status_arr[$row["adwithdrawal_status"]]];?>
												<span class="label <?php echo ($labelInfo!='')?$labelInfo:'label-info'; ?>">												
												<?php echo $status_arr[$row["adwithdrawal_status"]]?></span>
												</td>
												<td nowrap="nowrap" class="text-center">
												<ul class="actions">
													<?php if ($row["adwithdrawal_status"]==0):?>
													<li><a href="<?php echo Utilities::generateUrl('advertisers', 'withdrawal_request_status', array($row['adwithdrawal_id'], 'approve'))?>" title="Approve"><i class="ion-checkmark-circled icon"></i></a></li>
													<li><a  onclick="return(confirm('Are you sure to decline this record?'));" href="<?php echo Utilities::generateUrl('advertisers', 'withdrawal_request_status', array($row['adwithdrawal_id'], 'decline'))?>" title="Decline"><i class="ion-close-circled-circled icon"></i></a></li><?php endif;?>													
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
										<?php unset($search_parameter["url"]); ?>
                                         <?php echo Utilities::renderView(Utilities::getViewsPartialPath().'pagination.php', array(
									'start_record' => $start_record,
									'end_record' => $end_record,
									'total_records' => $total_records,
									'pages' => $pages,
									'page' => $page,
									'controller' => 'advertisers',
									'action' => 'withdrawal_requests',
									'url_vars' => array(),
									'query_vars' => $search_parameter,
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