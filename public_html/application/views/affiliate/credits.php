<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $txn_status_arr;  ?> 
<div class="body clearfix">
    <?php include CONF_THEME_PATH . 'affiliate_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . 'affiliate_leftpanel.php'; ?>
        <div class="data-side">
          <h3><?php echo Utilities::getLabel('M_My_Transactions')?></h3>
          
          <div>
            <!--<div class="mycredits"> <span class="highlighted_text"><?php echo Utilities::getLabel('L_Current_Balance')?>: <strong><?php echo Utilities::displayMoneyFormat($affiliate_details['balance'])?></strong> </span> <a class="btn red large" href="<?php echo Utilities::generateUrl('affiliate','request_withdrawal')?>"><?php echo Utilities::getLabel('L_Request_Withdrawal')?></a> </div>-->
            <div class="mycredits space-lft-right">
                <div class="box">
                  <div class="crr-blnc"><?php echo Utilities::getLabel('L_Current_Balance')?>: <strong><?php echo Utilities::displayMoneyFormat($affiliate_details["balance"])?></strong> </div>
                  <a class="btn" href="<?php echo Utilities::generateUrl('affiliate','request_withdrawal')?>"><?php echo Utilities::getLabel('L_Request_Withdrawal')?></a> </div>
                
	         </div>
          </div>
          <div class="gap"></div>
          <div class="clearfix"></div>
          <?php if ($total_records>0):?>
          <div class="darkgray-form clearfix">
            <div class="left-txt"><?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?></div>
          </div>
          <div class="tbl-listing">
            <table>
              <tbody>
                <tr>
                  <th width="13%"><?php echo Utilities::getLabel('L_Date')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Credit')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Debit')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Balance')?></th>
                  <th width="47%"><?php echo Utilities::getLabel('L_Description')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Status')?></th>
                </tr>
                <?php foreach ($my_credits as $key=>$val): $description=str_replace("{AffiliateWithdrawalUrl}",Utilities::generateAbsoluteUrl('affiliate', 'view_withdrawal_request', array($val["atxn_withdrawal_id"]),CONF_WEBROOT_URL),$val["atxn_description"]);?>
            <tr>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($val["atxn_date"])?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Credit')?></span><span class="nowrap"><?php echo Utilities::displayMoneyFormat($val["atxn_credit"])?></span></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Debit')?></span><span class="nowrap"><?php echo Utilities::displayMoneyFormat($val["atxn_debit"])?></span></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Balance')?></span><span class="nowrap"><?php echo Utilities::displayMoneyFormat($val["balance"])?></span></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Description')?></span><?php echo Utilities::renderHtml($description);?></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span><?php echo $txn_status_arr[$val["atxn_status"]]?></td>
            </tr>
				<?php endforeach;?>
             </tbody>
            </table>
            <?php if ($pages>1):?>
            <div class="pager">
            	<ul>
								 <?php unset($search_parameter["url"]); ?>
								 <?php echo Utilities::renderView(Utilities::getViewsPartialPath().'pagination.php', array(
									'start_record' => $start_record,
									'end_record' => $end_record,
									'total_records' => $total_records,
									'pages' => $pages,
									'page' => $page,
									'controller' => 'affiliate',
									'action' => 'credits',
									'url_vars' => array(),
									'query_vars' => $search_parameter,
									)); ?>
                                </ul>
              
            </div>
            <?php endif;?>
          </div>
          <?php else:?>
          <div class="space-lft-right">    
           		<div class="alert alert-info">
	                  <?php echo Utilities::getLabel('L_You_not_have_any_wallet_record')?>
    	        </div>
            </div>
          <?php endif;?>
        </div>
        
      </div>
    </div>
  </div>
  