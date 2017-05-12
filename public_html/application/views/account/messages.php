<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('M_Messages')?></h3>
          <?php if (count($arr_listing)>0):?>
          <div class="darkgray-form clearfix">
            <div class="search-dashboard">
              <?php echo $frm->getFormHtml();?>
            </div>
          </div>
          <div class="tbl-email">
            <table>
              <tbody>
              	<?php foreach ($arr_listing as $key=>$val):?>
                <tr class="<?php if (($val["message_is_unread"]) && ($val["message_from"]!=$user_id)):?>unread<?php endif;?>">
                  <td width="10%"><div class="avatar"><img src="<?php echo Utilities::generateUrl('image', 'user',array($val["message_sent_by_profile"],'THUMB'))?>" width="75" height="75" alt=""/></div></td>
                  <td width="23%"><strong><?php echo $val["message_sent_by_username"]?></strong><br>
                    <strong><?php echo Utilities::formatDate($val["message_date"],true)?></strong></td>
                  <td width="59%"><div class="email-txt"> <span><?php echo $val["thread_subject"]?></span><?php echo substringbywords($val["message_text"],250)?> </div></td>
                  <td width="8%"><div class="actions"> <a title="<?php echo Utilities::getLabel('M_View')?>" href="<?php echo Utilities::generateUrl('account', 'view_message',array($val["message_thread"],$val["message_id"]))?>"><img src="<?php echo CONF_WEBROOT_URL?>images/reply.png"   alt=""/></a> </div></td>
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
        		      <?php echo Utilities::getLabel('L_You_do_not_have_any_message')?>
		          </div>
              </div> 
          <?php endif;?>
          
        </div>
        
      </div>
    </div>
  </div>
