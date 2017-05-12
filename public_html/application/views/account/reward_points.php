<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_Reward_Points')?></h3>
          
          <div>
          	<div class="mycredits space-lft-right">
                <div class="box">
                  <div class="crr-blnc"><?php echo Utilities::getLabel('L_Current_Balance')?>: <strong><?php echo $user_details["totUserRewardPoints"]?></strong> </div>
	         </div>
             
            <!--<div class="mycredits"> <span class="highlighted_text"><?php echo Utilities::getLabel('L_Current_Balance')?>: <strong><?php echo $user_details["totUserRewardPoints"]?></strong> </span></div>-->
          </div>
          <div class="gap"></div>
          <div class="clearfix"></div>
          <?php if (count($my_rewards)>0):?>
          <div class="darkgray-form clearfix">
            <div class="left-txt"><?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?></div>
          </div>
          <div class="tbl-listing">
            <table>
              <tbody>
                <tr>
                  <th width="10%"><?php echo Utilities::getLabel('L_Points')?></th>
                  <th width="46%"><?php echo Utilities::getLabel('L_Description')?></th>
                  <th width="14%"><?php echo Utilities::getLabel('L_Added_Date')?></th>
                  <th width="14%"><?php echo Utilities::getLabel('L_Expiry_Date')?></th>
                </tr>
                <?php foreach ($my_rewards as $key=>$val): ?>
            <tr>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Points')?></span><?php echo $val["urp_points"];?></td>
                <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Description')?></span><?php echo strip_tags(Utilities::renderHtml($val["urp_description"]));?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Added_Date')?></span><?php echo Utilities::formatDate($val["urp_date_added"])?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Expiry_Date')?></span><?php echo Utilities::displayNotApplicable(Utilities::formatDate($val["urp_date_expiry"]))?></td>
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
                    <?php echo Utilities::getLabel('L_YOU_NOT_HAVE_ANY_REWARD_RECORD')?>
                </div>
            </div>    
          <?php endif;?>
        </div>
        
      </div>
    </div>
  </div>
  