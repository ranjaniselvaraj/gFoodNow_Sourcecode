function listPages(p){
	var frm = document.frmSearchSalesOrder;
	frm.page.value = p;
	frm.submit();
}
$( 'form[rel=search]' ).submit(function( event ) {
	var frm = document.frmSearchSalesOrder;
	frm.page.value = 1;
	frm.submit();
});
