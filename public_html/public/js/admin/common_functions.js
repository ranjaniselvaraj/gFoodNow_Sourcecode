$(document).ready(function(){ 
		
		// For Date field img
		$('.dateTimeFld').next('img').addClass('iconcalender');
		
		$('.filefield :input').change(function(){			
			$(this).prev('.filename').html(this.value);
		});
		
        /* for left side */    
		$('.menutrigger').click(function() {
            $(this).toggleClass("active");
			var el = $("body");
			if(el.hasClass('toggled-left')) el.removeClass("toggled-left");
			else el.addClass('toggled-left');
            return false; 
        });
        $('body').click(function(){
            if($('body').hasClass('toggled-left')){
                $('.menutrigger').removeClass("active");
                $('body').removeClass('toggled-left');
            }
        });
    	
        $('.leftoverlay').click(function(){
            if($('body').hasClass('toggled-left')){
                $('.menutrigger').removeClass("active");
                $('body').removeClass('toggled-left');
            }
        });
       
        /* for right side */    
		$('.sidetoggle a').click(function() {
            $(this).toggleClass("active");
			var el = $("body");
			if(el.hasClass('toggled-right')) el.removeClass("toggled-right");
			else el.addClass('toggled-right');
            return false; 
        });
        
        $('body').click(function(){
            if($('body').hasClass('toggled-right')){
                $('body').removeClass('toggled-right');
            }
        });
        $('.rightoverlay').click(function(){
            if($('body').hasClass('toggled-right')){
                $('body').removeClass('toggled-right');
            }
        });
        $('.leftside, .rightside').click(function(e){
            e.stopPropagation();
            //return false;
        });
    
	    	
        /* for top right menu */         
        $('.searchtoggle').click(function() {
            $(this).toggleClass("active");
                $('.searchwrap').slideToggle();
        });
    
        $('.language').click(function() {
            $(this).toggleClass("active");
        });
    
      
        /* for left links */	
        $('.leftmenu > li  > a').click(function(){
			  /*$( ".leftmenu li ul" ).each( function( index, element ){
				  $(this ).slideDown();
			  });	*/
			  //$('.leftmenu li ul').slideUp();
			  //$('.leftmenu > li ul').slideUp(); 	
              if($(this).hasClass('active')){
				  //alert('A');
                  $(this).removeClass('active');
				  $(this).siblings('.leftmenu > li ul').addClass('hide').removeClass('show');
                  //$(this).siblings('.leftmenu > li ul').slideUp();
              }else{
				  //$(this).siblings('.leftmenu > li ul')
                  $('.leftmenu > li > a').removeClass('active');
				  $('.leftmenu > li ul').addClass('hide');
                  $(this).addClass("active");
                  //$('.leftmenu > li ul').addClass('a')
                  $(this).siblings('.leftmenu > li ul').addClass('show').removeClass('hide');
              }
        });
    
    
        /* for profile links */         
        $('.profileinfo').click(function() {
            $(this).toggleClass("active");
            $('.profilelinkswrap').slideToggle("600");
        });
    
        /* for selection table */         
         $('.table-select tr').click(function() {
            $(this).toggleClass("active");
         });
    
        
    
        /* for sort icon */         
         $('.iconsort').click(function() {
            $(this).toggleClass("active");
         });
    
    
    
        /* notifications */         
        $('.alertlink').click(function() {
            $(this).toggleClass("");
            var align = $(this).attr('data-align');
            var type = $(this).attr('data-type');
            if(align.length < 1) return false;
            if(typeof type != 'undefined' && type.length){
                var el = $(".alert_position." + align + "." + type + ":first");
            }else{
                var el = $(".alert_position." + align + ":first");
            }
			if(el.hasClass('animated fadeInDown')){
                hideAlertBox(el);
            }else{
                el.removeClass('fadeOutUp');
                el.addClass('animated fadeInDown');
                setTimeout(function(){ if(el.hasClass('fadeInDown')) hideAlertBox(el); }, 10000);
            }
            return false; 
        });
        var hideAlertBox = function(el){
            el.addClass('animated fadeOutUp');
            el.removeClass('fadeInDown');
            setTimeout(function(){ el.removeClass('animated fadeOutUp'); }, 1000);
       };
        $('.alert_close').click(function(){
            var el = $(this).parents('.animated.fadeInDown:first');
            if(el.length < 1) return false;
            hideAlertBox(el);
            return false;
        });
        $('body').click(function(){
            if($('.alert_position').hasClass('animated fadeInDown')){
               $('.alert_position').removeClass('animated fadeInDown');
            }
        });
    
		$(document).on('click', '.droplink', function(event){		
			//event.preventDefault();
			$(this).toggleClass("active");
		});
		
		$(document).on('click', '.html', function(){		
			if($('.droplink').hasClass('active')){
				$('.droplink').removeClass('active');
			}
		});
  
        /* for sidetabs */ 
        $(".tab_content").hide(); //Hide all content
        $(".normaltabs li:first").addClass("active").show(); //Activate first tab
        $(".tab_content:first").show(); //Show first tab content

        //On Click Event
        $(".normaltabs li").click(function() {

        $(".normaltabs li").removeClass("active"); //Remove any "active" class
        $(this).addClass("active"); //Add "active" class to selected tab
        $(".tab_content").hide(); //Hide all tab content

        var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
        $(activeTab).fadeIn(); //Fade in the active ID content
        return false;
        });
    
        /* wave ripple effect */ 
        var parent, ink, d, x, y;
        $(".themebtn, .leftmenu > li > a, .actions > li > a, .leftlinks > li > a, .profilecover .profileinfo, .pagination li a, .circlebutton, .columlist li a").click(function(e){
            parent = $(this);
            //create .ink element if it doesn't exist
            if(parent.find(".ink").length == 0)
                parent.prepend("<span class='ink'></span>");

            ink = parent.find(".ink");
            //incase of quick double clicks stop the previous animation
            ink.removeClass("animate");

            //set size of .ink
            if(!ink.height() && !ink.width())
            {
                //use parent's width or height whichever is larger for the diameter to make a circle which can cover the entire element.
                d = Math.max(parent.outerWidth(), parent.outerHeight());
                ink.css({height: d, width: d});
            }

            //get click coordinates
            //logic = click coordinates relative to page - parent's position relative to page - half of self height/width to make it controllable from the center;
            x = e.pageX - parent.offset().left - ink.width()/2;
            y = e.pageY - parent.offset().top - ink.height()/2;

            //set the position and add class .animate
            ink.css({top: y+'px', left: x+'px'}).addClass("animate");
        })
    
        
        
        /* for common dialog box */
        /*$('.dialog_open').click(function() {
			var el = $(".dialog_box_wrap");
			if(el.hasClass('active')) el.removeClass("active");
			else el.addClass('active');
		});*/ 
       
    
        
          /* for forms elements */         
           function floatLabel(inputType){
           $(inputType).each(function(){
           var $this = $(this);
           var text_value = $(this).val();

            // on focus add class "active" to label
            $this.focus(function(){

            $this.closest('.field_control').addClass("active");
            });

            // on blur check field and remove class if needed
            $this.blur(function(){
            if($this.val() === '' || $this.val() === 'blank'){
            $this.closest('.field_control').removeClass('active');
            }
            });

            // Check input values on postback and add class "active" if value exists
            if(text_value!=''){
            $this.closest('.field_control').addClass("active");
            }

            // Automatically remove floatLabel class from select input on load
              /* $('select').closest('.field_control').removeClass('active');*/
            });

            }
            // Add a class of "floatLabel" to the input field
            floatLabel(".web_form input[type='text'], .web_form input[type='password'], .web_form input[type='email'], .web_form select, .web_form textarea, .web_form input[type='file']");

    
    
    
    
            /* for common tabs */ 

            $(".tabs_panel").hide();
            $('.tabs_panel_wrap').find(".tabs_panel:first").show();

          /* if in tab mode */
            $(".tabs_nav li a").click(function() {
              $(this).parents('.tabs_nav_container:first').find(".tabs_panel").hide();
              var activeTab = $(this).attr("rel"); 
              $("#"+activeTab).fadeIn();		

              $(this).parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
              $(this).addClass("active");

              $(".togglehead").removeClass("active");
              $(".togglehead[rel^='"+activeTab+"']").addClass("active");

            });
            /* if in drawer mode */
            $(".togglehead").click(function() {

              $(this).parents('.tabs_panel_wrap:first').find(".tabs_panel").hide();
              var d_activeTab = $(this).attr("rel");
                console.log($(this).parents('.tabs_panel_wrap:first').offset().top);
              $(window).scrollTop($(this).parents('.tabs_panel_wrap:first').offset().top-50);
              if($(this).hasClass("active")){
                $(".togglehead").removeClass("active");
                $(this).parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
                return false;
              }else{
                $("#"+d_activeTab).fadeIn();
              }

              $(".togglehead").removeClass("active");
              $(this).addClass("active");

              $(this).parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
              $(".tabs_nav li a[rel^='"+d_activeTab+"']").addClass("active");
              return;
            });
    
    

            /* for Accordian */
            //Set default open/close settings
            $('.accordiancontent').hide(); //Hide/close all containers
            $('.accordians_container').find('.accordianhead:first').addClass('active').next().show(); //Add "active" class to first trigger, then show/open the immediate next container

            //On Click
            $('.accordianhead').click(function(){
                if( $(this).next().is(':hidden') ) { //If immediate next container is closed...
                    $(this).parents('.accordians_container:first').find('.accordianhead').removeClass('active').next().slideUp(); //Remove all .acc_trigger classes and slide up the immediate next container
                    $(this).toggleClass('active').next().slideDown(); //Add .acc_trigger class to clicked trigger and slide down the immediate next container
                }else{
                    $(this).toggleClass('active').next().slideUp()
                }
                return false; //Prevent the browser jump to the link anchor
            });


        /* for inbox table */         
        /* $('.medialist > li').change(function() {
            $(this).toggleClass("selected");
        });
    */

	/* for right side */    
	$('.medialist > li').change(function() {
		$(this).toggleClass("selected");
		var el = $("body");
		if(el.hasClass('selected')) el.removeClass("selected");
		else el.addClass('selected');
		return false; 
	});

	$('body').click(function(){
		if($('body').hasClass('selected')){
			$('body').removeClass('selected');
		}
	});
	$('.containerwhite').click(function(e){
		e.stopPropagation();
		//return false;
	});


   $('.backarrow').click(function() {
		$(this).removeClass("selected");
   });


	/* for reply container */         
	$('.openreply').click(function() {
		$(this).toggleClass("active");
			$('.boxcontainer').slideToggle();
	});


	/* for expand all messages on message details page */    
	$('.expandlink').click(function() {
		$(this).toggleClass("active");
		var el = $(".medialist > li");
		if(el.hasClass('bodycollapsed')) el.removeClass("bodycollapsed");
		else el.addClass('bodycollapsed');
		return false; 
	});

	$('body').click(function(){
		if($('.containerwhite').hasClass('bodycollapsed')){
			$('.containerwhite').removeClass('bodycollapsed');
		}
	});
	$('.containerwhite').click(function(e){
		e.stopPropagation();
		//return false;
	});


	/* for fixed/fluid layout */    
	$('.iconmenus .switch').click(function() {
		$(this).toggleClass("active");
		var el = $("body");
		if(el.hasClass('switch_layout')) el.removeClass("switch_layout");
		else el.addClass('switch_layout');
	}); 

	$('.layout_switcher').click(function() {
		$('.layout_switcher').removeClass('active');
		$(this).addClass('active');
		var el = $("body");
		if(el.hasClass('switch_layout')) el.removeClass("switch_layout");
		else el.addClass('switch_layout');
	}); 
    


	/* for welcome message */    
	function showContent(){
		setTimeout(function(){
			$('.welcome_msg').fadeIn(1000).addClass("animated bounceIn");
			setTimeout(function(){
				$('.welcome_msg').fadeOut(1000).removeClass("bounceIn").addClass('bounceOut');
			},5000);
		},1500);
	}
	window.onload = showContent;
   
	/* for search form toggle */		 			 
	$('.togglelink a, .section.searchform_filter .sectionhead').click(function(){
		$(this).toggleClass("active");
		$('.togglewrap').slideToggle();
	});
    
    
	/* for active/inactive switcher */         
	 $('.statustab').click(function() {
		$(this).toggleClass("active");
	 });
		
	// swith theme dynamically
	 var themeLayoutSwitcher = function(val,type){
			if(type=='layout'){
				var data ='layout=' + val;
			}else if(type=='admin-color'){
				var data ='admin_color=' + val;
			}else if(type=='front-color'){
				var data ='front_color=' + val;
			}
		$.ajax({
			url: generateUrl('common', 'setting_update'),
			type: 'post',
			data: data,
			dataType: 'json',
			success: function(json) {
				if (json['status']==1) {
					$('.div_msg').remove();
					$('.system_message').append('<div class="div_msg"><ul><li>'+json['msg']+'</li></ul></div>');
				}else{
					$('.div_error').remove();
					$('.system_message').append('<div class="div_error"><ul><li>'+json['msg']+'</li></ul></div>');
				}
				$('.system_message').show();				
			}
		});
	}
	
    
	// Color switcher
	var theme_link_el_obj = null;
	var themeSwitcher = function(theme){
		if(typeof window.theme_link_el_obj != 'undefined' || theme_link_el_obj != null){
			window.theme_link_el_obj.remove();   
		}
		var theme_link_el = document.createElement('link');				
		$(theme_link_el).attr({'rel':'stylesheet', 'type':'text/css', 'href':webroot+'public/css/admin/'+theme.trim()+'.css'});
		window.theme_link_el_obj = $('head').append(theme_link_el).find('link:last');
		return;
	};
        
	themeSwitcher('default_color');
	$('.admin-theme-switcher li').each(function(){			
		if($(this).hasClass('active')){ 
			//themeSwitcher($(this).attr('data-color'));
		}
	});
        
	$('.admin-theme-switcher li').click(function(){
		themeSwitcher(this.className);
		var color=$(this).attr('data-color');
		themeLayoutSwitcher(color,'admin-color');
		return false;
	}); 
	
	$('.front-end-theme-switcher li').click(function(){
		var color=$(this).attr('data-theme');
		themeLayoutSwitcher(color,'front-color');
		return false;
	});       
    
	/* for  color pallete control */    
	$('.pallete_control').click(function() {
		$(this).toggleClass("active");
		var el = $("body");
		if(el.hasClass('switchtoggled')) el.removeClass("switchtoggled");
		else el.addClass('switchtoggled');
		return false; 
	});
	
	$('body').click(function(){
		if($('body').hasClass('switchtoggled')){
			$('.pallete_control').removeClass("active");
			$('body').removeClass('switchtoggled');
		}
	});
    
	$('.admin-theme-switcher,.front-end-theme-switcher').click(function(e){
		e.stopPropagation();
		//return false;
	});
    
	$('.layout_switcher').click(function(){
		var layout=$(this).attr('data-layout');
		themeLayoutSwitcher(layout,'layout');
	});
		
	$('.layoutToggle .switch-label').click(function(){if($("body").hasClass('switch_layout')){themeLayoutSwitcher(0,'layout');}else{themeLayoutSwitcher(1,'layout');}})	
	$('.layoutToggle .switch-handle').click(function(){if($("body").hasClass('switch_layout')){themeLayoutSwitcher(0,'layout');}else{themeLayoutSwitcher(1,'layout');}})			
});

function BrowseFileValue(val,id)
{
	document.getElementById(id).innerHTML = val;
}
function submitProfileImageUploadForm(){
	if ($.browser.msie && parseInt($.browser.version, 10) === 8 || $.browser.msie && parseInt($.browser.version, 10) === 9) {
		$('#imagefrm').removeAttr('onsubmit');	 
		$('#imagefrm').submit(); return true; 
	}
	var inputHtml=$('#userProfileImg_div').html();
	var data = new FormData();	 
	var $inputs = $('#frmProfileImg input[type=text],#frmProfileImg select,#frmProfileImg input[type=hidden]');
	$inputs.each(function() { data.append( this.name,$(this).val());}); 
	$.each($('#admin_image')[0].files, function(i, file) {
		var max_file_size = 1048576;
		var sizeinbytes = file.size;
		if (sizeinbytes > max_file_size){
			ShowJsSystemMessage(js_error_file_size.replace('{file_name}',file.name).replace('{file_size}',formatSizeUnits(max_file_size)),true,true);
			return;
		}
		var _validFileExtensions = [".jpg", ".jpeg", ".gif", ".png"];
		var sFileName = file.name;
		if (sFileName.length > 0) {
			var blnValid = false;
			for (var j = 0; j < _validFileExtensions.length; j++) {
				var sCurExtension = _validFileExtensions[j];
				
				if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
					blnValid = true;
					break;
				}
			}
			
			if (!blnValid) {
				ShowJsSystemMessage(js_error_file_extensions.replace('{file_name}',file.name).replace('{allowed_extensions}',_validFileExtensions.join(", ")),true,true);
				return false;
			}
		}
	
		
		$('#userProfileImg_div').html('<img src="'+webroot+'images/personal/ajax-loader.gif" />');
		data.append('admin_image', file);
		data.append( 'upload_mode' , 'ajax'); 
		$.ajax({ url: generateUrl('admin', 'uploadAdminImage' ), 
				data: data, 
				cache: false, 
				contentType: false, 
				processData: false, 
				type: 'POST', success: function(data){
					var t = jQuery.parseJSON(data);
					if (t.status==1){
						setTimeout(function(){reloadAdminImageTab()}, 2000);
					}else{
						$('#userProfileImg_div').html(inputHtml);
						alert(t.msg);
					}
				} 
		}); 
	});
}

function reloadAdminImageTab()
{
	callAjax(generateUrl('admin', 'getImageUploadTab'), '&outmode=json', function(t){	 
		$('#userProfileImg_div').html(t);
	}); 
}

function setFileInputName(obj) {
    var file_name = $(obj).val()
    $(obj).parent().find('.file_input_name').text(file_name);
}

function addMoreImagesTr() {
    $("#clone_image_div").find('.fieldadd').clone().appendTo("#elem_main_image_div");
}

function removeImageInput(obj) {
    $(obj).parents('.fieldadd').remove();
}

function confirmDelete(obj) {
    confirmBox("Are you sure you want to delete", function () {
        window.location = $(obj).attr('data-href');
    });
    return false;
}


function confirmBox(text, Success, Failure) {
    var text2 = text.length > 0 ? text : "";
    callAjax(generateUrl('common', 'confirm_box', '', ''),
            'mode=ajax&text=' + text2,
            function (t) {
                $('#body').append(t);
                $('#ConfirmBoxYes,#ConfirmBoxNo').on('click', function () {
                    $('#areyousure').remove();
                });
                $('#ConfirmBoxYes').on('click', Success);
                $('#ConfirmBoxNo').on('click', Failure);
            }
    );
}

function addMoreImages() {
    $("#clone_image_div").find('.fieldadd').clone().appendTo("#elem_main_image_div");
}