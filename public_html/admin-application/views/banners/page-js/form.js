$( document ).ready(function() {
    CallFunc();
});
function CallFunc() {
		var st=$('#banner_type').val();
		if (st=="1"){ setTimeout(function(){toggleDisabled(document.getElementById("banner_image"),true);
					toggleDisabled(document.getElementById("banner_html"),false);},500);	
			} else { 
				setTimeout(function(){toggleDisabled(document.getElementById("banner_image"),false);
					toggleDisabled(document.getElementById("banner_html"),true);},500);	
			}
	}
function toggleDisabled(el,enable){
	try {
		if (enable==false){
				el.style.color='';
		}else{
				el.style.color='#ccc';
		}
		el.disabled =  enable ;
	}catch(E){}
	try {
			if (el.childNodes && el.childNodes.length > 0){	
					for (var x = 0; x < el.childNodes.length; x++) {
						toggleDisabled(el.childNodes[x],enable);
				}
			}
		}
	catch(E){}
}
