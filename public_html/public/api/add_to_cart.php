<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/add_to_cart">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="product_id"  />
<input type="submit" />
</form> 