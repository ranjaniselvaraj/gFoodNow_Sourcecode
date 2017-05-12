<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php require CONF_THEME_PATH . 'checkout_header.php'; ?>
<body>
<div class="wrapper">
  <div class="checkout-header clearfix">
    <div class="">
      <?php 
	 	 $mobile_logo_icon=Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON")!=""?Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON"):Settings::getSetting("CONF_FRONT_LOGO");
	  ?>		
      <div id="logo"><a href="<?php echo Utilities::getSiteUrl(); ?>"><span class="small-logo"><img src="<?php echo Utilities::generateUrl('image', 'mobile_icon_logo',array($mobile_logo_icon), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/></span> <span class="medium-logo"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO")), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/></span> </a> </div>
      <div class="right-info">
        <ul class="trust-banners">
          <li><i class="svg-icn">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48">
              <path d="M36 16h-2v-4c0-5.52-4.48-10-10-10S14 6.48 14 12v4h-2c-2.21 0-4 1.79-4 4v20c0 2.21 1.79 4 4 4h24c2.21 0 4-1.79 4-4V20c0-2.21-1.79-4-4-4zM24 5.8c3.42 0 6.2 2.78 6.2 6.2v4H18v-4h-.2c0-3.42 2.78-6.2 6.2-6.2zM36 40H12V20h24v20zm-12-6c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"/>
            </svg>
            </i>
            <p><?php echo Utilities::getLabel('L_Secure')?><br>
              <?php echo Utilities::getLabel('L_Payments')?></p>
          </li>
          <li><i class="svg-icn">
            <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 23.382 23.382" style="enable-background:new 0 0 23.382 23.382;" xml:space="preserve">
              <g>
                <path d="M14.58,15.181c0.438-0.732,1.082-1.107,1.936-1.117c0.854-0.01,1.285-0.445,1.299-1.301
		c0.012-0.852,0.383-1.496,1.115-1.932c0.734-0.438,0.893-1.027,0.475-1.773c-0.416-0.744-0.416-1.488,0-2.234
		c0.418-0.744,0.26-1.332-0.475-1.77c-0.732-0.439-1.104-1.082-1.115-1.938c-0.014-0.852-0.445-1.287-1.299-1.297
		c-0.854-0.012-1.498-0.385-1.936-1.117c-0.438-0.734-1.027-0.893-1.771-0.475c-0.744,0.416-1.49,0.416-2.234,0
		C9.83-0.19,9.241-0.032,8.803,0.702C8.366,1.435,7.721,1.808,6.868,1.819c-0.852,0.01-1.285,0.445-1.297,1.297
		C5.557,3.972,5.186,4.614,4.454,5.054C3.72,5.492,3.559,6.079,3.979,6.824c0.418,0.746,0.418,1.49,0,2.234
		c-0.42,0.746-0.26,1.336,0.475,1.773c0.732,0.436,1.104,1.08,1.117,1.932c0.012,0.855,0.445,1.291,1.297,1.301
		c0.854,0.01,1.498,0.385,1.936,1.117c0.438,0.734,1.027,0.893,1.771,0.473c0.744-0.412,1.49-0.412,2.234,0
		C13.553,16.073,14.143,15.915,14.58,15.181z M11.694,12.614c-2.582,0-4.674-2.092-4.674-4.674c0-2.58,2.092-4.672,4.674-4.672
		c2.58,0,4.672,2.092,4.672,4.672C16.366,10.522,14.274,12.614,11.694,12.614z"/>
                <path d="M6.793,14.749c-0.898,0-1.018-0.418-1.018-0.418l-3.507,6.893l2.812-0.316l1.555,2.34c0,0,3.24-6.76,3.24-6.715
		C8.196,16.491,8.864,14.794,6.793,14.749z"/>
                <path d="M17.563,14.601c-2.562,0.268-2.041,0.904-2.627,1.398c-0.674,0.719-1.516,0.578-1.516,0.578l3.955,6.805l0.973-2.52
		l2.766,0.361L17.563,14.601z"/>
                <polygon points="12.67,6.909 11.692,4.929 10.713,6.909 8.524,7.229 10.106,8.772 9.733,10.954 11.692,9.925 13.651,10.954 
		13.278,8.772 14.86,7.229 	"/>
              </g>
            </svg>
            </i>
            <p><?php echo Utilities::getLabel('L_Authentic')?><br>
              <?php echo Utilities::getLabel('L_Checkout_Products')?></p>
          </li>
          <li><i class="svg-icn">
            <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 315.377 315.377" style="enable-background:new 0 0 315.377 315.377;" xml:space="preserve">
              <g>
                <g>
                  <path d="M107.712,181.769l-7.938,7.705c-1.121,1.089-1.753,2.584-1.753,4.146v3.288c0,3.191,2.588,5.779,5.78,5.779h47.4
			c3.196,0,5.782-2.588,5.782-5.779v-4.256c0-3.191-2.586-5.78-5.782-5.78h-26.19l0.722-0.664
			c17.117-16.491,29.232-29.471,29.232-46.372c0-13.513-8.782-27.148-28.409-27.148c-8.568,0-16.959,2.75-23.629,7.74
			c-2.166,1.625-2.918,4.537-1.803,7.007l1.458,3.224c0.708,1.568,2.074,2.739,3.735,3.195c1.651,0.456,3.433,0.148,4.842-0.836
			c4.289-2.995,8.704-4.515,13.127-4.515c8.608,0,12.971,4.28,12.971,12.662C137.142,152.524,127.72,162.721,107.712,181.769z"/>
                </g>
                <g>
                  <path d="M194.107,114.096c-0.154-0.014-0.31-0.02-0.464-0.02h-1.765c-1.89,0-3.658,0.923-4.738,2.469l-35.4,50.66
			c-0.678,0.971-1.041,2.127-1.041,3.311v4.061c0,3.192,2.586,5.78,5.778,5.78h32.322v16.551c0,3.191,2.586,5.779,5.778,5.779h5.519
			c3.19,0,5.781-2.588,5.781-5.779v-16.551h5.698c3.192,0,5.781-2.588,5.781-5.78v-3.753c0-3.19-2.589-5.779-5.781-5.779h-5.698
			v-45.189c0-3.19-2.591-5.779-5.781-5.779h-5.519C194.419,114.077,194.261,114.083,194.107,114.096z M188.799,165.045h-17.453
			c4.434-6.438,12.015-17.487,17.453-25.653V165.045z"/>
                </g>
                <g>
                  <path d="M157.906,290.377c-68.023,0-123.365-55.342-123.365-123.365c0-64.412,49.625-117.443,112.647-122.895v19.665
			c0,1.397,0.771,2.681,2.003,3.337c0.558,0.298,1.169,0.444,1.778,0.444c0.737,0,1.474-0.216,2.108-0.643l44.652-30
			c1.046-0.702,1.673-1.879,1.673-3.139c0-1.259-0.627-2.437-1.673-3.139l-44.652-30c-1.159-0.779-2.654-0.857-3.887-0.198
			c-1.232,0.657-2.003,1.941-2.003,3.337v15.254C70.364,24.547,9.54,88.806,9.54,167.011c0,81.809,66.558,148.365,148.365,148.365
			c37.876,0,73.934-14.271,101.532-40.183l-17.111-18.226C219.38,278.512,189.4,290.377,157.906,290.377z"/>
                </g>
                <g>
                  <path d="M284.552,89.689c-5.111-8.359-11.088-16.252-17.759-23.456l-18.344,16.985c5.552,5.995,10.522,12.562,14.776,19.515
			L284.552,89.689z"/>
                </g>
                <g>
                  <path d="M280.146,150.258l24.773-3.363c-1.322-9.74-3.625-19.373-6.846-28.632l-23.612,8.211
			C277.135,134.163,279.047,142.165,280.146,150.258z"/>
                </g>
                <g>
                  <path d="M242.999,45.459c-8.045-5.643-16.678-10.496-25.66-14.427l-10.022,22.903c7.464,3.267,14.64,7.301,21.327,11.991
			L242.999,45.459z"/>
                </g>
                <g>
                  <path d="M253.208,245.353l19.303,15.887c6.244-7.587,11.75-15.817,16.363-24.462l-22.055-11.771
			C262.983,232.195,258.404,239.041,253.208,245.353z"/>
                </g>
                <g>
                  <path d="M280.908,176.552c-0.622,8.157-2.061,16.264-4.273,24.093l24.057,6.802c2.666-9.426,4.396-19.18,5.146-28.99
			L280.908,176.552z"/>
                </g>
              </g>
            </svg>
            </i>
            <p><?php echo Utilities::getLabel('L_24X7_Customer')?> <br>
              <?php echo Utilities::getLabel('L_Support')?> </p>
          </li>
          <li><i class="svg-icn">
            <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 422.518 422.518" style="enable-background:new 0 0 422.518 422.518;" xml:space="preserve">
              <path d="M422.512,215.424c0-0.079-0.004-0.158-0.005-0.237c-0.116-5.295-4.368-9.514-9.727-9.514h-2.554l-39.443-76.258
	c-1.664-3.22-4.983-5.225-8.647-5.226l-67.34-0.014l2.569-20.364c0.733-8.138-1.783-15.822-7.086-21.638
	c-5.293-5.804-12.683-9.001-20.81-9.001h-209c-5.255,0-9.719,4.066-10.22,9.308l-2.095,16.778h119.078
	c7.732,0,13.836,6.268,13.634,14c-0.203,7.732-6.635,14-14.367,14H126.78c0.007,0.02,0.014,0.04,0.021,0.059H10.163
	c-5.468,0-10.017,4.432-10.16,9.9c-0.143,5.468,4.173,9.9,9.641,9.9H164.06c7.168,1.104,12.523,7.303,12.326,14.808
	c-0.216,8.242-7.039,14.925-15.267,14.994H54.661c-5.523,0-10.117,4.477-10.262,10c-0.145,5.523,4.215,10,9.738,10h105.204
	c7.273,1.013,12.735,7.262,12.537,14.84c-0.217,8.284-7.109,15-15.393,15H35.792v0.011H25.651c-5.523,0-10.117,4.477-10.262,10
	c-0.145,5.523,4.214,10,9.738,10h8.752l-3.423,35.818c-0.734,8.137,1.782,15.821,7.086,21.637c5.292,5.805,12.683,9.001,20.81,9.001
	h7.55C69.5,333.8,87.3,349.345,109.073,349.345c21.773,0,40.387-15.545,45.06-36.118h94.219c7.618,0,14.83-2.913,20.486-7.682
	c5.172,4.964,12.028,7.682,19.514,7.682h1.55c3.597,20.573,21.397,36.118,43.171,36.118c21.773,0,40.387-15.545,45.06-36.118h6.219
	c16.201,0,30.569-13.171,32.029-29.36l6.094-67.506c0.008-0.091,0.004-0.181,0.01-0.273c0.01-0.139,0.029-0.275,0.033-0.415
	C422.52,215.589,422.512,215.508,422.512,215.424z M109.597,329.345c-13.785,0-24.707-11.214-24.346-24.999
	c0.361-13.786,11.87-25.001,25.655-25.001c13.785,0,24.706,11.215,24.345,25.001C134.89,318.131,123.382,329.345,109.597,329.345z
	 M333.597,329.345c-13.785,0-24.706-11.214-24.346-24.999c0.361-13.786,11.87-25.001,25.655-25.001
	c13.785,0,24.707,11.215,24.345,25.001C358.89,318.131,347.382,329.345,333.597,329.345z M396.457,282.588
	c-0.52,5.767-5.823,10.639-11.58,10.639h-6.727c-4.454-19.453-21.744-33.882-42.721-33.882c-20.977,0-39.022,14.429-44.494,33.882
	h-2.059c-2.542,0-4.81-0.953-6.389-2.685c-1.589-1.742-2.337-4.113-2.106-6.676l12.609-139.691l28.959,0.006l-4.59,50.852
	c-0.735,8.137,1.78,15.821,7.083,21.637c5.292,5.806,12.685,9.004,20.813,9.004h56.338L396.457,282.588z"/>
            </svg>
            </i>
            <p><?php echo Utilities::getLabel('L_Fast')?><br>
              <?php echo Utilities::getLabel('L_Checkout_Delivery')?></p>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
 <div id="body" class="body">
    <div id="main-area">
	<section id="content">
        <div class="checkout-sidebar"></div>
        <div class="checkout-content">
          <div class="checkout-head">
            <div class="page-name"> <?php echo Utilities::getLabel('M_Subscription_Checkout')?> </div>
            <a href="<?php echo Utilities::generateUrl('account','packages')?>" class="continue-link"> <?php echo Utilities::getLabel('M_Back_to_Packages')?></a> </div>
          <section id="checkout-summary" class="checkout-panel active clearfix">
              <div class="title-bar">
              	  <div class="box-heading"> <?php echo Utilities::getLabel('M_Summary')?></div>
              </div>
              <div class="box-content"></div>
          </section>
          <section id="payment-summary" class="checkout-panel clearfix hide">
            <div class="title-bar">
              <div class="box-heading"><?php echo Utilities::getLabel('M_Payment')?></div>
            </div>
            <div class="box-content">
            </div>
          </section>
		</div>
	</section>
	</div>
</div>
<script>
$(document).ready(function(){
	loadCartSummary();
	loadPaymentSummary();
	//reloadCheckoutSideBar();
	//loadCartSummary();
});
$(document).on('change', '#pay_from_wallet', function(event) {
	makePaymentFromWallet();
});
function makePaymentFromWallet(){
	var me=$(this);
	if ( me.data('requestRunning') ) {
		return;
	}
	me.data('requestRunning', true);
	var href=generateUrl('common', 'check_ajax_user_logged_in');
	$.ajax({url: href,async: false}).done(function(logged) {
		if (logged==true){
			if($('#pay_from_wallet').is(':checked')){
				wallet=1
			}else{
				wallet=0
			}
			var data = "pay_from_wallet="+wallet;
			data += '&outmode=json&is_ajax_request=yes';
			var href=generateUrl('subscription', 'process_wallet_subscription');
			callAjax(href, data, function(response){
				me.data('requestRunning', false);
				var json = parseJsonData(response);
				if (json["success"]){
					loadPaymentSummary();
						//loadSideBarPaymentSummary();
					/*loadSideBarPaymentSummary();
							loadPaymentSummary();*/
				}
			})
			
		} else{  login_popupbox(); }
		me.data('requestRunning', false); 
	});
	return false;					
}
function loadSideBarPaymentSummary(){
	showCssElementLoading($('#sidebar-payment-summary'));
	var href=generateUrl('subscription', 'checkout_sidebar_payment_summary');
	callAjax(href,'', function(response){
		var json = parseJsonData(response);
		if (json.cart_count==0){
			location.reload();
		}
		$('#sidebar-payment-summary').html(json.html);
	})
}
function loadCartSummary(){
	var href=generateUrl('subscription', 'checkout_summary');
		callAjax(href,'', function(response){
			$('#checkout-summary .box-content').html(response);
			//reloadCheckoutSideBar();
		});
}
function loadPaymentTab(){
	$('#checkout-summary').addClass('done');
	loadPaymentSummary();
	$("#step2").trigger('click');
	return false;
	
}	
function loadPaymentSummary(){
		var href=generateUrl('subscription', 'checkout_payment');
		callAjax(href,'', function(response){
			var json = parseJsonData(response);
			$('#payment-summary').removeClass('hide');
			$('#payment-summary .box-content').html(json.html).show();
			if (json.free_trial){
				$('#payment-summary').hide();
			}
			reloadCheckoutSideBar();
		});
		
}
function reloadCheckoutSideBar(){
		showCssElementLoading($('.checkout-sidebar'));
		var href=generateUrl('subscription', 'checkout_sidebar');
		callAjax(href,'', function(response){
			var json = parseJsonData(response);
			if (json.cart_count==0){
				location.reload();
			}
			$('.checkout-sidebar').html(json.html);
			//resetSlickSettings();
			$('.tgl-triger').click(function(e) {
				e.preventDefault();
				$(this).addClass('active');
				var $div = $(this).next('.tgl-data');
				$(".tgl-data").not($div).removeClass('open');
				$div.toggleClass('open');
			});
		
		})
	}
$(document).on('click', '#RemoveDiscountCoupon', function(event) {
	event.preventDefault();
	var href=generateUrl('common', 'check_ajax_user_logged_in');
	$.ajax({url: href,async: false}).done(function(logged) {
	if (logged==true){
		var href=generateUrl('subscription', 'remove_coupon');
		callAjax(href,'', function(response){
			var json = parseJsonData(response);
			if (json['success']==1){
				//reloadCheckoutSideBar();
				loadPaymentSummary();
			}
		})
		
	}else{  
			login_popupbox(); }
	});
			
})
$(document).on('submit', '#frmSubscriptionWalletPayment', function(event) {
				event.preventDefault();
				var me=$(this);
				if ( me.data('requestRunning') ) {
					return;
				}
				var frm=this;
				var href=generateUrl('common', 'check_ajax_user_logged_in');
					$.ajax({url: href,async: false}).done(function(html) {
							if (html==false){
								login_popupbox();
							}else{
								el = $('#ajax_wallet_message');
								me.data('requestRunning', true);
								showHtmlElementLoading(el);
						        var data = getFrmData(frm);
						        data += '&outmode=json&is_ajax_request=yes';
						        callAjax(me.attr('action'), data, function(response){ //alert(response);
									var json = parseJsonData(response);
									me.data('requestRunning', false);
									if (json['error']) {
										el.html('<div class="alert alert-danger">'+json['error']+'<div>');
									}
									if (json['redirect']) {
										$(location).attr("href",json['redirect']);
									}
			
								});
						}
					});
		
		return false;					
      
})
$(document).on('submit', '#frmSubscriptionDiscountCoupon', function(event) {
	event.preventDefault();
	var me=$(this);
	var href=generateUrl('common', 'check_ajax_user_logged_in');
	$.ajax({url: href,async: false}).done(function(logged) {
				if (logged==true){
					var data = $("#frmSubscriptionDiscountCoupon").serialize();
					data += '&outmode=json&is_ajax_request=yes';
					var href=generateUrl('subscription', 'apply_coupon');
					callAjax(href, data, function(response){
						//alert(response);
						var json = parseJsonData(response);
						if (json['success']==1){
							$('.coupons #ajax_response').html('<p class="success">' + json['message'] + '</p>').show();
							$('#RemoveDiscountCoupon').show();
							//loadSideBarPaymentSummary();
							loadPaymentSummary();
							
							
						}else{
							$('.coupons #ajax_response').html('<p class="un-success">' + json['message'] + '</p>').show();
							setTimeout(function () { $('.coupons #ajax_response').hide();}, 3000);
						}
							
					})
				}else{
					login_popupbox();
				}
	});
	return false;
});
$(document).on('submit', '#frmSubscriptionRewardPoints', function(event) {
	event.preventDefault();
	var me=$(this);
	var href=generateUrl('common', 'check_ajax_user_logged_in');
	$.ajax({url: href,async: false}).done(function(logged) {
						if (logged==true){
							var data = $("#frmSubscriptionRewardPoints").serialize();
							data += '&outmode=json&is_ajax_request=yes';
							var href=generateUrl('subscription', 'apply_reward_points');
				         	callAjax(href, data, function(response){
							var json = parseJsonData(response);
							if (json['success']==1){
								$('.reward #ajax_response').html('<p class="success">' + json['message'] + '</p>').show();
								//reloadCheckoutSideBar();
								loadPaymentSummary();
							}else{
								$('.reward #ajax_response').html('<p class="un-success">' + json['message'] + '</p>').show();
								setTimeout(function () { $('.reward #ajax_response').hide();}, 3000);
						}
					})
					}else{
					login_popupbox();
					}
			});
	return false;
});
</script>