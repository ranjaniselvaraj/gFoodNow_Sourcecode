<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/child_order_detail">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="child_order_id" />
<input type="submit" />
</form> 