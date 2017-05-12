<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/cart_apply_coupon">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="coupon" />
<input type="submit" />
</form> 