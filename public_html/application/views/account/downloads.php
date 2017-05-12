<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $txn_status_arr; ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_My_Downloads')?></h3>
          
          <div>
            
          
          <?php if (count($my_downloads)>0):?>
          <div class="darkgray-form clearfix">
            <div class="left-txt"><?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?></div>
          </div><br/>
          <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i><?php echo Utilities::getLabel('L_Download_Note')?>
          </div>
          <div class="tbl-listing">
            <table>
              <tbody>
                <tr>
                  <th><?php echo Utilities::getLabel('L_Inv_Number')?></th>
                  <th><?php echo Utilities::getLabel('L_Name')?></th>
                  <th><?php echo Utilities::getLabel('L_Size')?></th>
                  <th><?php echo Utilities::getLabel('L_Date')?></th>
                  <th><?php echo Utilities::getLabel('L_Valid_Till')?></th>
                  <th><?php echo Utilities::getLabel('L_Downloads')?></th>
                  <th><?php echo Utilities::getLabel('L_Action')?></th>
                </tr>
                <?php foreach ($my_downloads as $key=>$val): if ($val['opf_file_can_be_downloaded_within_days']!="-1")
							{
								list($dt_year, $dt_month, $dt_day) = explode('-', $val['order_date_added']);
								$download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $val['opf_file_can_be_downloaded_within_days'], $dt_year);
								$linkExpire=date(CONF_DATE_FORMAT_PHP,($download_timestamp));
							}
							else
								$linkExpire=Utilities::getLabel('L_NA');
								$size = Utilities::getFileSize('product_downloads/'.$val['opf_file_name']);	
							$download_code = base64_encode($val['opf_id'].'.'.$val['opf_opr_id'].'.'.$user_id);	
								 ?>
            <tr>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Inv_Number')?></span><?php echo $val["opr_order_invoice_number"];?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Name')?></span><?php echo $val["opf_file_download_name"]?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Size')?></span><?php echo $size?></span></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($val["order_date_added"])?></span></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Valid_Till')?></span><?php echo $linkExpire?></span></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Downloads')?></span><?php echo $val['opf_remaining_downloaded_times']!="-1"?'<b>'.$val['opf_remaining_downloaded_times'].'</b> '.Utilities::getLabel('L_Downloads_Remaining'):'-' ?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Action')?></span><a href="<?php echo Utilities::generateUrl('account', 'download_file',array($download_code))?>" class="btn small" target="_blank"><?php echo Utilities::getLabel('L_Download')?></a></td>
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
	                  <?php echo Utilities::getLabel('L_You_do_not_have_any_downloads')?>
    	        </div>
            </div>    
          <?php endif;?>
        </div>
        
      </div>
    </div>
  </div>
  