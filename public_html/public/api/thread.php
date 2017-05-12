<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/view_thread_messages">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="thread_id" value="" />
<input type="submit" />
</form> 