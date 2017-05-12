// JavaScript Document
function sumbmitProfileImage(){ $("#frmProfile").submit(); }
$(document).on('click', '#picRemove', function(event) {
	event.preventDefault();
	$('#remove_profile_img').val('1');
	$("#frmProfile").submit();
})