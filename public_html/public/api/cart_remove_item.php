<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/cart_remove_item">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="item_key" value="" />
<input type="submit" />
</form> 