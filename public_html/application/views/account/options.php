<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $conf_option_types;   ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('M_Options_Variants')?></h3>
          <ul class="arrowTabs">
              <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'options')?>"><?php echo Utilities::getLabel('M_Options_List')?></a></li>
              <li><a href="<?php echo Utilities::generateUrl('account', 'option_form')?>"><?php echo Utilities::getLabel('L_Add_Option')?></a></li>
           </ul>
          
         <?php if (count($arr_listing)>0 && !empty($arr_listing)):?>
          <div class="tbl-listing">
            <table>
              <tr>
                <th width="40%"><?php echo Utilities::getLabel('L_Name')?></th>
                <th width="50%"><?php echo Utilities::getLabel('L_Type')?></th>
                <th width="10%"><?php echo Utilities::getLabel('L_Action')?></th>
              </tr>
              <?php $cnt=0; foreach ($arr_listing as $sn=>$row): $sn++;  ?>
              <tr>
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Name')?></span><?php echo $row["option_name"]?></td>
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Type')?></span><?php echo $conf_option_types[$row["option_type"]] ?></td>
                                      <td>
                                         <a class="actions" title="<?php echo Utilities::getLabel('L_View_Request')?>" href="<?php echo Utilities::generateUrl('account','option_form',array($row["option_id"]))?>"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/retina/tag.svg"></a>
                                      </td>
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
        		      <?php echo Utilities::getLabel('L_You_do_not_have_any_option')?>
		          </div>
              </div> 
          <?php endif;?>
        </div>
        
      </div>
    </div>
  </div>
  