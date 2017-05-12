function setSeoName(el, fld_id){
	txt_val = el.value;
	txt_val=$.trim(txt_val.toLowerCase());
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
function cancelCategory() {
	window.location.href = generateUrl('blogcategories','');
}