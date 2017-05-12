<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_View_Message')?></h3>
          <div class="tbl-listing">
          <table>
              <tbody>
                <tr>
                  <th width="25%"><?php echo Utilities::getLabel('L_Date')?></th>
                  <th width="25%">
					  	<?php if ($thread["thread_type"]=="O") {
							echo Utilities::getLabel('L_Order'); 
						}  elseif ($thread["thread_type"]=="S"){ 
							echo Utilities::getLabel('L_Shop'); 
						} else { echo Utilities::getLabel('L_Product');
						}?></th>
                  <th width="25%">
					  	<?php if ($thread["thread_type"]=="O") {
							echo Utilities::getLabel('L_Amount'); 
						}  elseif ($thread["thread_type"]=="P") { 
							echo Utilities::getLabel('L_Price');
						}?>
                        
				  </th>
                  <th width="25%"><?php if ($thread["thread_type"]=="O"):?><?php echo Utilities::getLabel('L_Status')?><?php endif; ?></th>
                </tr>
                <tr>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($thread["thread_start_date"])?></td>
                  <td><span class="cellcaption"><?php if ($thread["thread_type"]=="O") {
							echo Utilities::getLabel('L_Order'); 
						}  elseif ($thread["thread_type"]=="S"){ 
							echo Utilities::getLabel('L_Shop'); 
						} else { echo Utilities::getLabel('L_Product');
						}?></span><?php if ($thread["thread_type"]=="O"):?>
                      	<?php if ($thread["thread_started_by"]==$user_details["user_id"]) : ?>
	                        <?php echo $thread["opr_order_invoice_number"]?>
                        <?php else:?>
                            <?php echo $thread["opr_order_invoice_number"]?>
                        <?php endif; ?>
                        <?php elseif ($thread["thread_type"]=="S"):?>
                        	<?php echo $thread["shop_name"]; ?>
                        <?php else :?>
                           	<?php echo $thread["prod_name"]?>
						<?php endif;?>
                  </td>
                  <td><span class="cellcaption"><?php if ($thread["thread_type"]=="O") {
							echo Utilities::getLabel('L_Amount'); 
						}  elseif ($thread["thread_type"]=="P") { 
							echo Utilities::getLabel('L_Price');
						}?></span>
						<?php if ($thread["thread_type"]=="O") {
							echo Utilities::displaymoneyformat($thread["opr_net_charged"]); 
						}  elseif ($thread["thread_type"]=="S"){ 
							echo ""; 
						} else { echo Utilities::displayMoneyFormat($thread["prod_sale_price"]);
						}?>
                        </td>
                  <td><?php if ($thread["thread_type"]=="O"):?>
						 <?php echo $thread["orders_status_name"]?>
					<?php endif; ?></td>
                </tr>
              </tbody>
            </table>
            </div>
          <div class="space-lft-right">
            <?php foreach($messages as $key=>$val):?>
                                   <div class="detailList" id="lbl<?php echo $val["message_id"]?>">
                                        <aside class="grid_1">
                                            <figure class="photo"><img alt="" src="<?php echo Utilities::generateUrl('image', 'user',array($val["message_sent_by_profile"],'THUMB'))?>"></figure>
                                        </aside>
                                        <aside class="grid_2">
                                        		<span class="datetext"><?php echo Utilities::formatDate($val["message_date"],true)?></span>
                                        		<span class="postedname"><a href="javascript:void(0);"><?php echo $val["message_sent_by_username"]?></a></span>
                                                <p><?php echo nl2br($val["message_text"])?></p>
                                               
                                        </aside>
                       			   </div>
			<?php endforeach;?>
            
            
            <div class="detailList">
                    <aside class="grid_1">
                        <figure class="photo"><img alt="" src="<?php echo Utilities::generateUrl('image', 'user',array($user_details["user_profile_image"],'THUMB'))?>"></figure>
                        
                    </aside>
                    <aside class="grid_2">
                           <span class="postedname"><a href="#"><?php echo $user_details["user_username"]?></a> <span><?php echo Utilities::getLabel('L_says')?></span></span>
                           <div class="form_msg"><?php echo $frm->getFormHtml(); ?></div>
                    </aside>
               </div>
          
            
	        
          </div>
        </div>
        
      </div>
    </div>
  </div>
<script type="text/javascript">
$(document ).ready(function() {
		$('html, body').animate({
		    scrollTop: ($('#lbl<?php echo $message?>').offset().top)
		},2000);
});
</script>