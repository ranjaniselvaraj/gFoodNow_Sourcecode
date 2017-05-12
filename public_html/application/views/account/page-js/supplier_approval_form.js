$(document).ready(function() {     
	
	$('.date').datetimepicker({
			timepicker: false,
			format:'Y-m-d',
			formatDate:'Y-m-d',
			step: 10
	});
		
	$('.time').datetimepicker({
			datepicker: false,
			format:'H:i',
			step: 10
	});
		
	$('.datetime').datetimepicker({
			datepicker: true,
			timepicker: true,
			format:'Y-m-d H:i',
			step:10
	});
	
	$('[id^=\'button-upload\']').on('click', function() {
			var node = this;
			$('#form-upload').remove();
			$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" ><input type="file" name="file" /></form>');
			$('#form-upload input[name=\'file\']').trigger('click');
			if (typeof timer != 'undefined') {
				clearInterval(timer);
			}
			timer = setInterval(function() {
				if ($('#form-upload input[name=\'file\']').val() != '') {
					clearInterval(timer);
					$val = $(node).val();
					$.ajax({
						url: generateUrl('common', 'file_upload'),
						type: 'post',
						dataType: 'json',
						data: new FormData($('#form-upload')[0]),
						cache: false,
						contentType: false,
						processData: false,
						beforeSend: function() {
							$(node).val('Loading');
						},
						complete: function() {
							$(node).val($val);
						},
						success: function(json) {
								$('.text-danger').remove();
								if (json['error']) {
									$(node).parent().find('input[type=button]').after('<div class="text-danger">' + json['error'] + '</div>');
								}
								if (json['success']) {
									//alert(json['success']);
									$(node).parent().find('input[type=button]').after('<div class="text-success">' + json['success'] + '</div>');
									$(node).parent().find('input').attr('value', json['code']);
									
								}
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					}
				}, 500);
	})
	
	
});
