<?php global $conf_arr_seller_types; ?>
<div class="tabz-dashboard no-print">
            <ul class="tabz-ul">
              <?php if (($is_buyer_logged && $is_seller_logged) || ($is_buyer_logged && Settings::getSetting("CONF_BUYER_CAN_SEE_SELLER_TAB"))):?>	
              <li class="<?php if ($buyer_supplier_tab=="S") { echo 'active'; } ?>" ><a  href="<?php echo Utilities::generateUrl('account', 'dashboard_supplier')?>"><?php echo Utilities::getLabel('L_Seller')?></a></li>
              <li class="<?php if ($buyer_supplier_tab=="B") { echo 'active'; } ?>" ><a href="<?php echo Utilities::generateUrl('account', 'dashboard_buyer')?>"><?php echo Utilities::getLabel('L_Buyer')?></a></li>
              <?php endif; ?>
            </ul>
          </div>