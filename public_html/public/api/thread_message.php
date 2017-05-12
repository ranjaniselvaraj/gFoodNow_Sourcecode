<? require_once("include.php")?>
<form method="post" action="<?php echo $conf_url?>api/send_thread_message">
<input type="text" name="_token" value="<?php echo $token?>" />
<input type="text" name="thread_id" value="" />
<textarea name="message"></textarea>
<input type="submit" />
</form> 