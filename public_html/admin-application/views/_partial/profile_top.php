<ul class="centered_nav">
	<li><a class="<?php echo ($action=='profile')?'active':'';?>" href="<?php echo Utilities::generateUrl('admin', 'profile'); ?>">My Profile</a></li>	                  
	<li><a class="<?php echo ($action=='change_password')?'active':'';?>" href="<?php echo Utilities::generateUrl('admin', 'change_password'); ?>" >Change Password</a></li>
</ul>