<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/orders">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="page"/>
<input type="text" name="pagesize"/>
<input type="submit" />
</form> 