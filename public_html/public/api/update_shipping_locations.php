<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/update_cart_items_shipping_method">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="product_key" />
<input type="text" name="pship_id" />
<input type="submit" />
</form> 