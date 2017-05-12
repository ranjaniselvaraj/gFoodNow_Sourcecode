<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/buy_product">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="ttkn" value="" />
<input type="submit" />
</form> 