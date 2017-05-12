function listPages(p){
	var frm = document.frmSearchBuyerOrder;
	frm.page.value = p;
	frm.submit();
}
$( 'form[rel=search]' ).submit(function( event ) {
	var frm = document.frmSearchBuyerOrder;
	frm.page.value = 1;
	frm.submit();
});
