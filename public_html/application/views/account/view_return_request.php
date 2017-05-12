<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $return_status_arr;
$request_amount=($request_detail["opr_customer_buying_price"]+$request_detail["opr_customer_customization_price"])*$request_detail["refund_qty"];
$tax=round($request_amount*$request_detail["order_vat_perc"]/100,2);
$tax_string=$tax>0?" (+ Tax: ".Utilities::displayMoneyFormat($tax).")":"";
//die($tax."=");
?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="box-head">
            <h3><?php echo Utilities::getLabel('M_View_Return_Request')?></h3>
            <div class="padding20 fr"> <a href="<?php echo Utilities::generateUrl('account', 'return_requests')?>" class="btn small"><?php echo Utilities::getLabel('L_Back_to_Return_Requests')?></a> </div>
          </div>
          
          <div class="space-lft-right">
            <h5><?php echo Utilities::getLabel('L_Vendor_Return_Address')?></h5>
            <p><strong><?php echo $request_detail["shop_contact_person"]?></strong><br/>
				<?php echo $request_detail["shop_address_line_1"]?>, <?php echo $request_detail["shop_address_line_2"]?><br/>
                <?php echo $request_detail["shop_city"]?>, <?php echo $request_detail["shop_state_name"]?> - <?php echo $request_detail["shop_postcode"]?><br/>
                <?php echo $request_detail["shop_country_name"]?><br/>
                <?php echo Utilities::getLabel('L_Phone')?> : <?php echo $request_detail["shop_phone"]?></p></p>
          </div>
          <div class="gap"></div>
           <? if (in_array($request_detail["refund_request_status"],array(0,1))): ?>
                        <div class="selectionbar clearfix">
                        	<? if ($request_detail["refund_request_status"]==0):?>	
                            	<a class="btn" href="<?php echo Utilities::generateUrl('account', 'escalate_request',array($request_detail["refund_id"]))?>"><?php echo sprintf(Utilities::getLabel('L_Escalate_to'),Settings::getSetting("CONF_WEBSITE_NAME"))?></a>
                            <? endif; ?>
							<? if ($user_details["user_id"]==$request_detail["refund_user_id"]) :?>
                            <a class="btn blue" href="<?php echo Utilities::generateUrl('account', 'withdraw_request',array($request_detail["refund_id"]))?>"><?php echo Utilities::getLabel('L_Withdraw_Request')?></a>
                            <? endif;?>
							<? if ($user_details["user_id"]==$request_detail["shop_user_id"]) :?>
                             <a class="btn blue" href="<?php echo Utilities::generateUrl('account', 'approve_request',array($request_detail["refund_id"]))?>"><?php echo Utilities::getLabel('L_Approve_Refund')?></a>
                            <? endif;?>
		                  </div>
        				  <div class="gap"></div>              
	        <? endif;?>
                        
          
          <div class="tbl-listing">
            <table>
              <tbody>
                <tr>
                  <th width="25%"><?php echo Utilities::getLabel('L_ID')?></th>
                  <th width="25%"><?php echo Utilities::getLabel('L_Product')?></th>
                  <th width="25%"><?php echo Utilities::getLabel('L_Qty')?></th>
                  <th width="25%"><?php echo Utilities::getLabel('L_Request_Type')?></th>
                </tr>
                <tr>
                  <td><span class="cellcaption">#</span><?php echo Utilities::format_return_request_number($request_detail["refund_id"])?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Product')?></span><?php echo $request_detail["opr_name"]?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Qty')?></span><?php echo $request_detail["refund_qty"]?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Request_Type')?></span><?php echo $request_detail['refund_or_replace']=="RP"?Utilities::getLabel('M_Replace'):Utilities::getLabel('M_Refund')?></td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="tbl-listing">
            <table>
              <tbody>
                <tr>
                  <th><?php echo Utilities::getLabel('L_Reason')?></th>
                  <th><?php echo Utilities::getLabel('L_Date')?></th>
                  <th><?php echo Utilities::getLabel('L_Status')?></th>
                  <th><?php echo Utilities::getLabel('L_Amount')?></th>
                </tr>
                <tr>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Reason')?></span><?php echo $request_detail['returnreason_title']?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($request_detail["refund_request_date"])?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span><?php echo $return_status_arr[$request_detail["refund_request_status"]]; ?>
                  <? if ($request_detail["refund_request_action_by"]=="A"): 
	                    echo " By <b>".Settings::getSetting("CONF_WEBSITE_NAME")."</b>"; 
    	           endif; ?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Amount')?></span><?php echo Utilities::displayMoneyFormat($request_amount).$tax_string?></td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="space-lft-right">
          	<? foreach($request_detail["messages"] as $key=>$val):  $attachment_link=""; 
									if ($val["refmsg_attachment"]!=""):
										$attachment_link='<br/><a target="_blank" href="'.Utilities::generateUrl('account', 'download_attachment',array($val["refmsg_id"])).'"><img src="'.CONF_WEBROOT_URL.'images/attachment.png" class="marginRight">'.$val["refmsg_attachment"].'</a>';
									endif;	 ?>
            <div id="lbl<?php echo $val["retmsg_id"]?>" class="detailList">
              <aside class="grid_1">
                <figure class="photo">
                	<? if (is_null($val["admin_id"])) {?>
		                <img src="<?php echo Utilities::generateUrl('image', 'user',array($val["message_sent_by_profile"],'THUMB'))?>" alt="">
                    <? } else {?>    
	                    <img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO")), CONF_WEBROOT_URL)?>" alt="">
                    <? } ?>
                    </figure>
                        
              </aside>
              <aside class="grid_2"> <span class="datetext"><?php echo Utilities::formatDate($val["refmsg_date"])?></span> <span class="postedname"><a href="#">
			  	<?php echo is_null($val["admin_id"])?$val["message_sent_by_username"]:Settings::getSetting("CONF_WEBSITE_NAME")?></a></span>
                <p><?php echo nl2br($val["refmsg_text"])?></p>
                <?php echo $attachment_link?>
              </aside>
            </div>
            <? endforeach; ?>
            
			<? if (!in_array($request_detail["refund_request_status"],array(2,3))):?>	
            <div class="detailList">
              <aside class="grid_1">
                <figure class="photo"><img src="<?php echo Utilities::generateUrl('image', 'user', array($user_details['user_profile_image'],'THUMB'))?>" alt=""></figure>
              </aside>
              <aside class="grid_2"> <span class="postedname"><a href="#"><?php echo $user_details['user_username']?></a> <span>says</span></span>
                <div class="form_msg">
                	<?php echo $frm->getFormHtml(); ?>
                  </div>
              </aside>
            </div>
            <div class="gap"></div> 
            <? endif; ?>
          </div>
          
          
        </div>
        
      </div>
    </div>
  </div>
  