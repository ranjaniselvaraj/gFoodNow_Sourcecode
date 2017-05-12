<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
	  
      <div class="relative_div">
      <?php if ($order_payment_financials["order_payment_gateway_charge"] <= 0) { ?><div class="inactive_div"></div><?php } ?>	
      <div class="heading-bar clearfix">
        <div class="h4"><?=Utilities::getLabel('M_Select_Payment_Method')?></div>
      </div>
      <div class=" clearfix">
        <div class="checkout-page-data">
           <div id="paymenttab">
            <ul class="resp-tabs-list" id="payment_methods_tab">
              <?php foreach($payment_methods as $sn=>$val){?>	
                <li class="<?php echo (($sn==0)?'resp-tab-active':''); ?>" data-filter="<?=$val["subscriptionpmethod_id"]?>"><a href="<?php echo Utilities::generateUrl("subscription",'package_payment_tab',array($order_info["mporder_id"],$val["subscriptionpmethod_id"]));?>"><i><img src="<?php echo Utilities::generateUrl('image','subscriptionpayment_icon',array($val["subscriptionpmethod_icon"]));?>" width="22"  alt=""/></i><span><?=$val["subscriptionpmethod_name"]?></span></a></li>
              <?php } ?>
            </ul>
             <div class="resp-tabs-container">
             		<div class="tabs-cn-area resp-tab-content" id="personalTabId">
                    	
                            <div class="amount-desc">
							 <ul>
							
							  
                              <li class="total"><?=Utilities::getLabel('M_Net_Payable')?> <span class="figure"><?php echo Utilities::displayMoneyFormat($order_payment_financials["order_payment_gateway_charge"])?></span></li>
  
                            </ul>
                       
                                <div id="tabs-container" class="payment_tabs">	
                                    <?=Utilities::getLabel('M_Loading_please_wait')?> 
                                 </div>
						   </div>
					
                   </div>
	          </div>
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
     if(!tabObj || !tabObj.length){ return; }
     showCssElementLoading($(containerId));
     $(containerId).fadeOut('fast');
     $(containerId).load(tabObj.attr('href'), function(){
          $(containerId).fadeIn('fast');
     });
}
	
	
	
</script>    