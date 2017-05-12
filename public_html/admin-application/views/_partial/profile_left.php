<aside class="grid_1">
	<div class="avtararea">
		<figure class="pic">
			<form id="frmProfileImg">
				<div id="userProfileImg_div"><img src="<?php echo Utilities::generateUrl('image','user', array(!empty($adminProfile['admin_image'])?$adminProfile['admin_image']:'X',"LARGE"),CONF_WEBROOT_URL); ?>" alt=""></div>
				<span class="uploadavtar">
					<i class="icon ion-android-camera"></i> Update Profile Picture 
					<input type="file" id="admin_image" onchange="submitProfileImageUploadForm(); return false;">
				</span>
			</form>    
		</figure>
		<div class="picinfo">
			<span class="name"><?php echo $adminProfile['admin_full_name'];?></span>
			<span class="mailinfo"><?php echo $adminProfile['admin_email'];?></span>
		</div>
	</div>
	
	<div class="contactarea">
		<h3>Contact Info </h3>
		<ul class="contactlist">
			<li><i class="icon ion-android-mail"></i><?php echo $adminProfile['admin_email'];?></li>
			
		</ul>
	</div>
	
</aside>