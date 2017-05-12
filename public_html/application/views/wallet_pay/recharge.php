<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="payment-page">
  <div class="wallet-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="reff total-pay">
      <p class="fl"><?php echo Utilities::getLabel('L_Payable_Amount')?>: <strong><?php echo Utilities::displayMoneyFormat($recharge_info['rwr_amount'])?></strong> </p>
      <p class="fr"><?php echo Utilities::getLabel('L_Txn_Invoice')?>: <strong><?php echo Utilities::displayNotApplicable($recharge_info["rwr_invoice_number"])?></strong> </p>
      
    </div>
    <div class="payment-from">
    	<section id="payment" class="checkout-panel clearfix active">
             
            <div class="box-content">
              <div id="walletpaymenttab">
                <ul class="resp-tabs-list" id="payment_methods_tab">
                	<?php foreach($payment_methods as $sn=>$val){?>	
                		<li class="<?php echo (($sn==0)?'resp-tab-active':''); ?>" data-filter="<?php echo $val["ppcpmethod_id"]?>"><a href="<?php echo Utilities::generateUrl("wallet_pay",'payment_tab',array($recharge_info["rwr_id"],$val["ppcpmethod_id"]));?>"><i><img src="<?php echo Utilities::generateUrl('image','ppcpayment_icon',array($val["ppcpmethod_icon"]));?>" width="22"  alt=""/></i><span><?php echo $val["ppcpmethod_name"]?></span></a></li>
    	          <?php } ?>
                </ul>
                <div class="resp-tabs-container">
                  <div id="personalTabId" class="tabs-cn-area resp-tab-content">
	                  <div class="amount-desc">
                        <ul>
                          <li class="total"><?php echo Utilities::getLabel('M_Net_Payable')?> <span class="figure"><?php echo Utilities::displayMoneyFormat($recharge_info['rwr_amount'])?></span></li>
                        </ul>
                        <div id="tabs-container">	
    	          	 	 	<?php echo Utilities::getLabel('M_Loading_please_wait')?> 
                         </div>
	                  </div>
                </div>
                </div>
              </div>
            </div>
          </section>
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
</body>
</head>