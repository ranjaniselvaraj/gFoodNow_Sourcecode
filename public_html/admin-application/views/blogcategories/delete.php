<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
?>
<section class="box">
	<div class="box_content clearfix toggle_container">
		<p>You can't recover "Category" once deleted, are you sure to delete? <a class="edit" href="<?php echo $delete_link; ?>">Yes</a> / <a class="edit" href="<?php echo Utilities::generateUrl('blogcategories'); ?>">Cancel</a></p>
	</div>
</section>