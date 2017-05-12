<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $duration_freq_arr; ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="box-head no-print">
            <h3><?php echo Utilities::getLabel('L_My_Promotion_clicks')?></h3>
            
            <div class="padding20 fr">  <a href="<?php echo Utilities::generateUrl('account', 'promote')?>" class="btn small blue"><?php echo Utilities::getLabel('L_Back_to_Promotions')?></a> </div>
          </div>
          
          <div class="clearfix"></div>
          <?php if (!empty($arr_listing) && is_array($arr_listing)):?>
          <div class="darkgray-form clearfix">
            <div class="left-txt"> <?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?> </div>
          </div>
          <div class="tbl-listing">
            <table>
              <tr>
                <th><?php echo Utilities::getLabel('L_SN')?></th>
                <th><?php echo Utilities::getLabel('L_IP_Address')?></th>
                <th><?php echo Utilities::getLabel('L_Date')?></th>
                <th><?php echo Utilities::getLabel('L_CPC')?></th>
              </tr>
              <?php $cnt=0; foreach ($arr_listing as $sn=>$row): $cnt++; $sn=($page-1)*$pagesize+$cnt;   ?>
              <tr>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_SN')?></span> <?php echo $sn; ?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_IP_Address')?></span> <?php echo $row["pclick_ip"] ?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span> <?php echo displayDate($row["pclick_datetime"],true) ?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_CPC')?></span> <?php echo Utilities::displayMoneyFormat($row["pclick_cost"]) ?></td>
              </tr> 
              <?php endforeach;?>
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
        	        <?php echo Utilities::getLabel('L_You_do_not_have_record_this_section')?>
            		</div>
                </div>
          <?php endif;?>
        </div>
        
      </div>
    </div>
  </div>
