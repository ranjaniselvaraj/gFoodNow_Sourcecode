function searchBlogCatogries(frm) {
    var data = "";
    if (typeof (frm) != "undefined") {
        var data = getFrmData(frm);
    }
    data += "&category_parent=" + catId;
    showHtmlElementLoading($('#category-type-list'));
    callAjax(generateUrl('blogcategories', 'listBlogCategories'), data, function (t) {
        $('#listing-div').html(t);
    });
}
function listPages(p) {
    var frm = document.paginateForm;
    frm.page.value = p;
    searchBlogCatogries(frm);
}
$(document).ready(function () {
    searchBlogCatogries(document.frmSearch);
});
function clearSearch() {
    document.frmSearch.reset();
	$("#frmSearch input[type=hidden]").val("");
    searchBlogCatogries(document.frmSearch);
}