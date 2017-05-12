<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/cart_billing_address_update">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="address_id" />
<input type="submit" />
</form> 