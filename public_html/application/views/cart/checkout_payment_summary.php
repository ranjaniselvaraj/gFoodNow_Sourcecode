<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
              <div id="paymenttab" class="relative_div">
              	
              	<?php if (isset($order_payment_financials) && $order_payment_financials["order_payment_gateway_charge"] <= 0) {?><div class="inactive_div"></div><?php } ?>
                <div class="payment_methods_list">	
	                <ul class="resp-tabs-list" id="payment_methods_tab">
        	          <?php foreach($payment_methods as $sn=>$val){ if ($val['pmethod_id']!=Settings::getSetting("CONF_COD_PAYMENT_METHOD") || Settings::getSetting("CONF_ENABLE_COD_PAYMENTS")) { 
					  
					  if ($sn==0)
					  	  $tabctive = $val["pmethod_id"];
						  
					  if (isset($_SESSION['shopping_cart']["payment_method"]) && $_SESSION['shopping_cart']["payment_method"]>0)
					  		$tabctive = $_SESSION['shopping_cart']["payment_method"];
					   		
					  ?>	
                		<li class="<?php echo (($tabctive==$val['pmethod_id'])?'resp-tab-active':''); ?>" data-filter="<?php echo $val["pmethod_id"]?>"><a href="<?php echo Utilities::generateUrl("cart",'payment_tab',array($order_info["order_id"],$val["pmethod_id"]));?>"><i><img src="<?php echo Utilities::generateUrl('image','payment_icon',array($val["pmethod_icon"]));?>" width="22"  alt=""/></i><span><?php echo $val["pmethod_name"]?></span></a></li>
    	          		<?php }
						} ?>
                	</ul>
                </div>
                <div class="resp-tabs-container">
                  <div id="personalTabId" class="tabs-cn-area resp-tab-content">
	                  <div class="amount-desc">
                        <ul>
                          <li class="total"><?php echo Utilities::getLabel('M_Net_Payable')?> <span class="figure"><?php echo Utilities::displayMoneyFormat($order_payment_financials["order_payment_gateway_charge"])?></span></li>
                        </ul>
                        <?php if ($payment_ready) {?>
                  		 <div id="tabs-container">	
    	          	 	 	<?php echo Utilities::getLabel('M_Loading_please_wait')?> 
                         </div>
                         <?php } else {?>
			              	<div class="alert alert-danger"><?php echo Utilities::getLabel('L_WARNING_ORDER_INVALID')?></div>
            			 <?php }?>
 
                  </div>
                </div>
              </div>
</div>
<script type="text/javascript">
var containerId = '#tabs-container';
var tabsId = '#payment_methods_tab';
$(document).ready(function(){
     if($(tabsId + ' LI.resp-tab-active A').length > 0){ 
         loadTab($(tabsId + ' LI.resp-tab-active A'));
     }
     $(tabsId + ' A').click(function(){ 
          if($(this).parent().hasClass('resp-tab-active')){ return false; }
          $(tabsId + ' LI.resp-tab-active').removeClass('resp-tab-active');
          $(this).parent().addClass('resp-tab-active');
          loadTab($(this));
          return false;
     
	 });
});
function loadTab(tabObj){
	 //HideJsSystemMessage();
     if(!tabObj || !tabObj.length){ return; }
     showCssElementLoading($(containerId));
     $(containerId).fadeOut('fast');
		callAjax(tabObj.attr('href'),'', function(t){
			var ans = parseJsonData(t);
			$(containerId).html(ans.html)
			$(containerId).fadeIn('fast');
			if (ans.msg!=""){
				ShowJsSystemMessage(ans.msg);
				setTimeout(function () {
							HideJsSystemMessage();
						}, 8000);
				reloadCheckoutSideBar();
				loadPaymentSummary();
			}

		})
	  
	 /*var href=tabObj.attr('href');*/
     /*$(containerId).load(tabObj.attr('href'), function(){
          $(containerId).fadeIn('fast');
     });*/
}
	
	
	
</script>    