<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="content slide">
    <div class="body clearfix">
      
      <div class="fixed-container">
        <div class="page-heading"> <span> <?-Utilities::getLabel('L_Buy_Subscription');?> </span> </div>
      	<?php if ($error_warning) { ?>
  		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
	    	<button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
	  	<?php } ?>
        <?=Message::getHtml();?>
        <div class="cart-box">
          <?php 
		  if ($subpackages) { ?>
	      <form action="<?php echo Utilities::generateUrl('cart','edit')?>" method="post" enctype="multipart/form-data" id="cartFrm">	
          <table class="cart-tbl" width="100%" border="0" cellspacing="0" cellpadding="0">
            <thead>
              <tr>
                <th><?=Utilities::getLabel('M_Subscription_Name');?> </th>
                <th><?=Utilities::getLabel('M_Price')?></th>
                <th><?=Utilities::getLabel('M_Total')?></th>
                <th><?=Utilities::getLabel('L_Action')?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($subpackages as $subpackage) { 
				$package_price = ($subpackage['merchantsubpack_discounted_price'] > 0) ? $subpackage['merchantsubpack_discounted_price'] : $subpackage['price'];
				$package_price = Utilities::displayMoneyFormat($package_price);
			  ?>	
              <tr>
                <td><div class="product-name"><a href="<?php echo Utilities::generateUrl('account','packages')?>"><?php echo $subpackage["merchantpack_name"]?></a>
				 <br />
                  - <small><?php echo SubscriptionHelper::displayFormattedSubPackage($subpackage['merchantsubpack_actual_price'] , $subpackage['merchantsubpack_subs_frequency'], $subpackage['merchantsubpack_subs_period'] ) ; ?></small>
                  </div></td>
                <td nowrap="nowrap"><div class="price"><?php echo $package_price; ?></div></td>
	            <td nowrap="nowrap"><div class="sub-total"><?php echo $package_price.' /'.$subpackage['merchantsubpack_subs_frequency'].' Days'; ?></div></td> 
                <td><a href="javascript:void(0);" class="actions" onclick="subscription.remove('<?echo $subpackage['key']?>');"><img src="<?=CONF_WEBROOT_URL?>images/action-icn-delete.png" width="16" height="17" alt=""/></a></td>
            </tr>
              <?php } ?>
              
            </tbody>
          </table>
          </form>
          <div class="total-bottom clearfix">
            <div class="total-side">
              <div class="heading"><?=Utilities::getLabel('M_Total')?> : <?php echo Utilities::displayMoneyFormat($cart_summary["cart_total"]).'/'.$subpackages[0]['merchantsubpack_subs_frequency'].' Days'; ?> </div>
              <div class="btn-row"> <a href="<?php echo Utilities::generateUrl('account','packages');?>" class="btn gray-dark"><?=Utilities::getLabel('L_Back')?></a> <a href="<?php echo Utilities::generateUrl('cart','subscription_checkout')?>" class="btn blue "> <?=Utilities::getLabel('M_Checkout')?> </a></div>
               </div>
          </div>
        <?php } else { ?>
		<!--<div class="text-info text-center">Your shopping cart is empty!</div>-->
        <div class="alert alert-warning"><?=Utilities::getLabel('M_No_Package_Selected')?></div>
      <?php } ?>
      </div>
    </div>
    </div>
    
  </div>
<div class="gap"></div>
  