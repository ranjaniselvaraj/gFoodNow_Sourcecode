<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
         
          <div class="box-head">
            <h3><?php echo Utilities::getLabel('L_Promotion_Analytics')?></h3>
            <div class="padding20 fr">  <a href="<?php echo Utilities::generateUrl('account', 'promote')?>" class="btn small ">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_promotions')?></a></div>
          </div>
          
           <?php if ($total_records>0):?>
           <div class="darkgray-form">
            <div class="tabs-form">
              <div class="tabz-content">
              	<?php echo  str_replace("<br>", " ",$frm->getFormHtml()); ?>
              </div>
            </div>
          </div>
          <div class="tbl-listing">
            <h4><?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?></h4>
            <table>
              <tbody>
                <tr>
                  <th><?php echo Utilities::getLabel('L_Date')?></th>
                  <th><?php echo Utilities::getLabel('L_Impressions')?></th>
                  <th><?php echo Utilities::getLabel('L_Clicks')?></th>
                  <th><?php echo Utilities::getLabel('L_Orders')?></th>
                </tr>
                <?php $cnt=0;  foreach ($arr_listing as $sn=>$row): $sn++;  ?>
                <tr>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($row["lprom_date"]) ?></td>
                  <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Impressions')?></span><?php echo $row["lprom_impressions"] ?>
                	</td>
                    <td nowrap="nowrap"><span class="cellcaption"><?php echo Utilities::getLabel('L_Clicks')?></span> 
					<?php echo $row["lprom_clicks"] ?></td>
                    <td nowrap="nowrap"><span class="cellcaption"><?php echo Utilities::getLabel('L_Orders')?></span>
                    <?php echo $row["lprom_orders"] ?>
                   </td>
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
        	        <?php echo Utilities::getLabel('L_You_do_not_have_record_this_section')?>
            	</div>
             </div>   
		 <?php endif;?>
          
        </div>
        
      </div>
    </div>
  </div>
