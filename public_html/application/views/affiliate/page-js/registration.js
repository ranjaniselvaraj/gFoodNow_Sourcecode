$(document).on('blur', '.check_affiliate_username', function(event){		
			var me = $(this);
			event.preventDefault();
			if ( me.data('requestRunning') ) {
     		   return;
		    }
			el = $('#ajax_availability_username');
			el.removeClass('availables').removeClass('not-availables');
		    showHtmlElementLoadingSmall(el);
	
			me.data('requestRunning', true);
			var strVal = me.val();
			var data = "username="+encodeURIComponent(strVal);
			data += '&outmode=json&is_ajax_request=yes';
			var href=generateUrl('affiliate', 'check_username_availability',[],webroot);
         	callAjax(href, data, function(response){
				me.data('requestRunning', false);
				var ans = parseJsonData(response);
				if (ans.check==1){
					el.removeClass('not-availables').addClass('availables');
				}else if (ans.check==2){
					el.removeClass('availables').addClass('not-availables');
				}else{
					el.removeClass('availables').removeClass('not-availables');
				}
				el.html(ans.message);
			})
	})
	
	$(document).on('blur', '.check_affiliate_email', function(event){		
			var me = $(this);
			event.preventDefault();
			if ( me.data('requestRunning') ) {
     		   return;
		    }
			el = $('#ajax_availability_email');
			el.removeClass('availables').removeClass('not-availables');
		    showHtmlElementLoadingSmall(el);
	
			me.data('requestRunning', true);
			var strVal = me.val();
			var data = "email="+encodeURIComponent(strVal);
			data += '&outmode=json&is_ajax_request=yes';
			var href=generateUrl('affiliate', 'check_email_availability',[],webroot);
         	callAjax(href, data, function(response){
				//alert(response);
				me.data('requestRunning', false);
				var ans = parseJsonData(response);
				if (ans.check==1){
					el.removeClass('not-availables').addClass('availables');
				}else if (ans.check==2){
					el.removeClass('availables').addClass('not-availables');
				}else{
					el.removeClass('availables').removeClass('not-availables');
				}
				el.html(ans.message);
			})
	})