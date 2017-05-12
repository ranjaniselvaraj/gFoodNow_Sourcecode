<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $txn_status_arr; ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_My_Wallet')?></h3>
          
          <div>
            <div class="mycredits space-lft-right">
                <div class="box">
                  <div class="crr-blnc"><?php echo Utilities::getLabel('L_Current_Balance')?>: <strong><?php echo Utilities::displayMoneyFormat($user_details["totUserBalance"])?></strong> </div>
                  <a class="btn primary-btn" href="<?php echo Utilities::generateUrl('account','request_withdrawal')?>"><?php echo Utilities::getLabel('L_Request_Withdrawal')?></a> </div>
                <div class="box last">
	                <?php echo str_replace("<br>",'',$walletfrm->getFormHtml()); ?>
    	         </div>
	         </div>
          <div class="gap"></div>
          <div class="clearfix"></div>
          <?php if (count($my_credits)>0):?>
          <div class="darkgray-form clearfix">
            <div class="left-txt"><?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?></div>
          </div>
          <div class="tbl-listing">
            <table>
              <tbody>
                <tr>
                  <th width="14%"><?php echo Utilities::getLabel('L_ID')?></th>
                  <th width="14%"><?php echo Utilities::getLabel('L_Date')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Credit')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Debit')?></th>
                  <th width="12%"><?php echo Utilities::getLabel('L_Balance')?></th>
                  <th width="46%"><?php echo Utilities::getLabel('L_Description')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Status')?></th>
                </tr>
                <?php foreach ($my_credits as $key=>$val): $txn=new Transactions($val["utxn_id"]);?>
            <tr>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_ID')?></span><?php echo $val["formatted_transaction_number"];?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($val["utxn_date"])?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Credit')?></span><span class="nowrap"><?php echo Utilities::displayMoneyFormat($val["utxn_credit"])?></span></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Debit')?></span><span class="nowrap"><?php echo Utilities::displayMoneyFormat($val["utxn_debit"])?></span></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Balance')?></span><span class="nowrap"><?php echo Utilities::displayMoneyFormat($val["balance"])?></span></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Description')?></span><?php echo Utilities::renderHtml($val["formatted_comments"]);?></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span><span class="txtHightlight <?php if ($val["utxn_status"]==1):?>green<?php else:?>yellow<?php endif;?>"><?php echo $txn_status_arr[$val["utxn_status"]]?></span></span></td>
            </tr>
				<?php endforeach;?>
             </tbody>
            </table>
            <?php if ($pages>1):?>
            <div class="pager">
              <ul>
              <?php echo getPageString('<li><a href="javascript:void(0)" onclick="listPages(xxpagexx);">xxpagexx</a></li>', $pages, $page,'<li class="active"><a  href="javascript:void(0)">xxpagexx</a></li>', '<li>...</li>');?>
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
  