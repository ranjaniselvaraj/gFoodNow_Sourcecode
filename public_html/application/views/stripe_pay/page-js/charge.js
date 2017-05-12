(function($){
	var _this			= false;
	var _subText 		= false;
	$(document).ready(function() {
		$(window).load(function(){
			try{
				
				if(typeof publishable_key != typeof undefined){
					// this identifies your website in the createToken call below
					Stripe.setPublishableKey(publishable_key);
					function stripeResponseHandler(status, response) {
						$submit = true;
						if(_this && _subText){
							_this.find('input[type=submit]').val(_subText);
						}
						
						if (response.error) {
							$("#frmPaymentForm").prepend('<div class="alert alert-danger">'+response.error.message+'</div>');
						} else {
							
							var form$ = $("#frmPaymentForm");
							// token contains id, last4, and card type
							var token = response['id'];
							// insert the token into the form so it gets submitted to the server
							form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
									// and submit
							form$.get(0).submit();
							
						}
						
					}
					$submit = true;
					$("#frmPaymentForm").submit(function(event) {
						/* alert('Work is in progress');
						return false; */
						$('.alert-danger, .success-message').remove();
						$('.error').removeClass('error');
						
						var _this			= $(this);
						var _numberWrap 	= $('#cc_number');
						var _cvvWrap	 	= $('#cc_cvv');
						var _expMonthWrap 	= $('#cc_expire_date_month');
						var _expYearWrap 	= $('#cc_expire_date_year');
						var _subText 		= _this.find('input[type=submit]').val();
						
						
						if($submit && _numberWrap.length > 0 && _cvvWrap.length > 0 && _expMonthWrap.length > 0 && _expYearWrap.length > 0 ){
							
							var _numberValue 	= _numberWrap.val().trim();
							var _cvvValue 		= _cvvWrap.val().trim();
							var _expMonthValue 	= _expMonthWrap.val().trim();
							var _expYearValue 	= _expYearWrap.val().trim();
							
							if( _numberValue != '' && _cvvValue != '' && _expMonthValue != '' && _expYearValue != '' ){
								$submit = false;
								_subText = _this.find('input[type=submit]').val();
								_this.find('input[type=submit]').val('Please Wait...');
								
								Stripe.createToken({
									number: _numberValue,
									cvc: _cvvValue,
									exp_month: _expMonthValue,
									exp_year: _expYearValue
								}, stripeResponseHandler);
							}
							
						}
						return $submit; // submit from callback
					});
					
				}
				
			}catch(e){
				console.log(e.message);
			}
		});
	});
})(jQuery);