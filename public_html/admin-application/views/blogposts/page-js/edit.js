(function() {
 	
	validatePost = function(frms, v) {
		
		$("#mbs_auto_id_post_content").html($("#idContentoEdit_mbs_auto_id_post_content").contents().find("html > body").html());	
		
		var post_content = $("#idContentoEdit_mbs_auto_id_post_content").contents().find("html > body").html();
		if(post_content == '<br>'){
			 
			$("#idContentoEdit_mbs_auto_id_post_content").contents().find("html > body").text('');
			$("#mbs_auto_id_post_content").html('');
		}
		 ;
		if($("#mbs_auto_id_post_content").html() == "&lt;br /&gt;"){
			$("#mbs_auto_id_post_content").html('');	 
		}
	}		
})();
$(document).ready(function() {
	
	if($("#mbs_auto_id_post_content").html() == "&lt;br /&gt;"){
		$("#mbs_auto_id_post_content").html('');	 
	}
	
});
function setMainImage(el, id, blog_post_id){
	id = parseInt(id);
	showHtmlElementLoading($('#post_imgs'));
	callAjax(generateUrl('blogposts', 'setMainImage'), 'imgid='+id+'&blog_post_id='+blog_post_id, function(t){
		$('#post_imgs').html(t);
	});
	return false;
}
function removeImage(el, id){
	id = parseInt(id);
	$(el).parent('.photosquare').remove();
	var rimg = $('#post_removed_images');
	var rimg_content = rimg.val();
	if(rimg_content != ''){
		var rimg_arr = rimg_content.split(',');
		rimg_arr.push(id);
		rimg_content = rimg_arr.join(',');
	}else{
		rimg_content = id;
	}
	rimg.val(rimg_content);
	return false;
}
function cancelPost() {
	window.location.href = generateUrl('blogposts','');
}