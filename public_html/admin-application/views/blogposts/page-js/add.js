(function() {
 	
	validatePost = function(frms, v) {
		
		$("#mbs_auto_id_post_content").html($("#idContentoEdit_mbs_auto_id_post_content").contents().find("html > body").html());	
		
		var post_content = $("#idContentoEdit_mbs_auto_id_post_content").contents().find("html > body").html();
		if(post_content == '<br>'){
			 
			$("#idContentoEdit_mbs_auto_id_post_content").contents().find("html > body").html('');
			$("#mbs_auto_id_post_content").html('');
		}
		
		if($("#mbs_auto_id_post_content").html() == "&lt;br /&gt;"){
			$("#mbs_auto_id_post_content").html('');	 
		}
	}		
})();
$(document).ready(function() {
	$("#add_more_field").click(function (e) {
		$("#image_div").append('<div><input type="file" name="post_image_file_name[]" accept="image/*"><button  class="delete">Delete</button></div>');
	});
	$("body").on("click", ".delete", function (e) {
		$(this).parent("div").remove();
	});
	 
	if($("#mbs_auto_id_post_content").html() == "&lt;br /&gt;"){
		$("#mbs_auto_id_post_content").html('');	 
	}
	
});
function setSeoName(el, fld_id){
	txt_val = el.value;
	txt_val=$.trim(txt_val.toLowerCase());
	/*txt_val=txt_val.replace(/[^a-zA-Z0-9 ]+/g,"-");*/
	txt_val=txt_val.replace(/[^a-zA-Z0-9 ]+/g,"-");
	txt_val=txt_val.replace(/\s+/g, "-");
	txt_val=$.trim(txt_val);
	txt_val=rtrim(txt_val, '-');
	$('#'+fld_id.id).val(txt_val);
	return;
}
function rtrim(str, chr) {
  var rgxtrim = (!chr) ? new RegExp('\\s+$') : new RegExp(chr+'+$');
  return str.replace(rgxtrim, '');
}
function cancelPost() {
	window.location.href = generateUrl('blogposts','');
}