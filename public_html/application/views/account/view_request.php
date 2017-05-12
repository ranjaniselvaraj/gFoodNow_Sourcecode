<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $status_arr; ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>	
          <div class="box-head">
            <h3><?php echo Utilities::getLabel('L_View_Request')?></h3>
            <div class="padding20 fr"> <a href="<?php echo Utilities::generateUrl('account', 'credits')?>" class="btn small">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_Credit_Summary')?></a> </div>
          </div>
          
          <div class="space-lft-right">
            <table class=" tbl-normal">
              <tbody>
														<tr>
															<th width="35%"><?php echo Utilities::getLabel('L_ID')?></th>
															<td>#<?php echo str_pad($request_detail["withdrawal_id"],6,'0',STR_PAD_LEFT);?></td>
														</tr>                                                        
														<tr>
															<th><?php echo Utilities::getLabel('L_Amount')?></th>
															<td><?php echo Utilities::displayMoneyFormat($request_detail["withdrawal_amount"])?></td>
														</tr>                                                        
														<tr>
															<th><?php echo Utilities::getLabel('L_Bank_Name')?></th>
															<td><?php echo $request_detail['withdrawal_bank']?></td>
														</tr>                                                        
														<tr>
															<th><?php echo Utilities::getLabel('M_Account_Holder_Name')?></th>
															<td><?php echo $request_detail['withdrawal_account_holder_name']?></td>
														</tr>
														<tr>
															<th><?php echo Utilities::getLabel('M_Account_Number')?></th>
															<td><?php echo $request_detail['withdrawal_account_number']?></td>
														</tr>
                                                        <tr>
															<th><?php echo Utilities::getLabel('M_IFSC_Swift_Code')?></th>
															<td><?php echo $request_detail['withdrawal_ifc_swift_code']?></td>
														</tr>    
														<tr>    
															<th><?php echo Utilities::getLabel('M_Bank_Address')?></th>
															<td><?php echo $request_detail['withdrawal_bank_address']?></td>
														</tr>
                                                        <tr>    
															<th><?php echo Utilities::getLabel('L_Withdrawal_Comments')?></th>
															<td><?php echo Utilities::displayNotApplicable($request_detail['withdrawal_comments'])?></td>
														</tr>
                                                        <tr>    
															<th><?php echo Utilities::getLabel('L_Date')?></th>
															<td><?php echo Utilities::formatDate($request_detail["withdrawal_request_date"])?></td>
														</tr>
                                                        <tr>    
															<th><?php echo Utilities::getLabel('L_Status')?></th>
															<td><strong><?php echo $status_arr[$request_detail["withdrawal_status"]]; ?></strong></td>
														</tr>
													</tbody>
    						        </table>
				       </div>
       	  
        </div>
        
      </div>
    </div>
  </div>
