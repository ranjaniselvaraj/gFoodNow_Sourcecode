function listPages(p){
	var frm = document.frmSearchSellerSubscriptions;
	frm.page.value = p;
	frm.submit();
}
$("document").ready(function(){
	$('form[rel=search]' ).submit(function( event ) {	
		var frm = document.frmSearchSellerSubscriptions;
		frm.page.value = 1;
		frm.submit();
	});
});
