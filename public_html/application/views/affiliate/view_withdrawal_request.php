<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $status_arr; ?>
<?
switch($request_detail['afwithdrawal_payment_mode']) {
	case 'bank':
		$payment_details=Utilities::getLabel('L_Bank_Name').": ".$request_detail["afwithdrawal_bank_name"]."<br/>".Utilities::getLabel('L_ABA/BSB_number_Branch_Number').": ".$request_detail["afwithdrawal_bank_branch_number"]."<br/>".Utilities::getLabel('L_SWIFT_Code').": ".$request_detail["afwithdrawal_bank_swift_code"]."<br/>".Utilities::getLabel('L_Account_Name').": ".$request_detail["afwithdrawal_bank_account_name"]."<br/>".Utilities::getLabel('L_Account_Number').": ".$request_detail["afwithdrawal_bank_account_number"]."<br/>";
	break;
	case 'paypal':
		$payment_details=Utilities::getLabel('L_PayPal_Email_Account').": ".$request_detail["afwithdrawal_paypal"];
	break;
	case 'cheque':
		$payment_details=Utilities::getLabel('L_Cheque_Payee_Name').": ".$request_detail["afwithdrawal_cheque"];
	break;
}
?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . 'affiliate_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . 'affiliate_leftpanel.php'; ?>
        <div class="data-side">
          	
          <div class="box-head">
            <h3><?php echo Utilities::getLabel('L_View_Withdrawal_Request')?></h3>
            <div class="padding20 fr"> <a href="<?php echo Utilities::generateUrl('affiliate', 'credits')?>" class="btn small">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_Credit_Summary')?></a> </div>
          </div>
          <div class="space-lft-right">
            <table class=" tbl-normal">
              <tbody>
														<tr>
															<th width="35%"><?php echo Utilities::getLabel('L_ID')?></th>
															<td>#<?php echo str_pad($request_detail["afwithdrawal_id"],6,'0',STR_PAD_LEFT);?></td>
														</tr>                                                        
														<tr>
															<th><?php echo Utilities::getLabel('L_Amount')?></th>
															<td><?php echo Utilities::displayMoneyFormat($request_detail["afwithdrawal_amount"])?></td>
														</tr>                                                        
														<tr>
															<th><?php echo Utilities::getLabel('L_Payment_Method')?></th>
															<td><?php echo ucfirst($request_detail['afwithdrawal_payment_mode'])?></td>
														</tr>                                                        
														<tr>
															<th><?php echo Utilities::getLabel('M_Payment_Details')?></th>
															<td><?php echo $payment_details?></td>
														</tr>
                                                        <tr>    
															<th><?php echo Utilities::getLabel('M_Other_Info_Instructions')?></th>
															<td><?php echo Utilities::displayNotApplicable($request_detail['afwithdrawal_comments'])?></td>
														</tr>
                                                        <tr>    
															<th><?php echo Utilities::getLabel('L_Date')?></th>
															<td><?php echo Utilities::formatDate($request_detail["afwithdrawal_request_date"])?></td>
														</tr>
                                                        <tr>    
															<th><?php echo Utilities::getLabel('L_Status')?></th>
															<td><strong><?php echo $status_arr[$request_detail["afwithdrawal_status"]]; ?></strong></td>
														</tr>
													</tbody>
    						        </table>
				       </div>
       	  
        </div>
        
      </div>
    </div>
  </div>
