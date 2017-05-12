$.fn.extend({
    clearFiles: function () {
        $(this).each(function () {
            var isIE = (window.navigator.userAgent.indexOf("MSIE ") > 0 || !! navigator.userAgent.match(/Trident.*rv\:11\./));
            if ($(this).prop("type") == 'file') {
                if (isIE == true) {
                    $(this).replaceWith($(this).val('').clone(true));
                } else {
                    $(this).val("");
                }
            }
        });
        return this;
    }
});

function validateFileSize(input){
	var file = input.files[0];
	var max_file_size = 2097152;
	var sizeinbytes = file.size;
	if (sizeinbytes > max_file_size){
		ShowJsSystemMessage(js_error_file_size.replace('{file_name}',file.name).replace('{file_size}',formatSizeUnits(max_file_size)),true,true);
		$("[type='file']").clearFiles();
		return;
	}
}