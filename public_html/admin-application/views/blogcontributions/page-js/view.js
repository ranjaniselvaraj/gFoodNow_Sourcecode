function updateStatus(el)
{
    var span = document.createElement('SPAN');
    span.id = 'loader';
    el.parentNode.appendChild(span);
    showHtmlElementLoading($('#loader'));
    var data = getFrmData(document.frmBlogContributions);
    callAjax(generateUrl('blogcontributions', 'updateStatus'), data, function (t) {
        var ans = parseJsonData(t);
        if (ans === false) {
            $('#loader').remove();
            window.adding_in_progress = false;
            return false;
        }
        $('#loader').html('');
        $('.system_message').append(ans.msg);
        $('.system_message').show();
		
        setTimeout(function () {
            location.reload(true);
        }, 300);
    });
}
function downloadFile(filename)
{
    window.location.href = '?filename=' + filename + '&msg=download';
}
function cancelContribution() {
    window.location.href = generateUrl('blogcontributions', '');
}