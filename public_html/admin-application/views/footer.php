<div class="system_message" style="display:none;">
    <a class="closeMsg" href="javascript:void(0);"></a>
    <div id="common_msg"><?php echo Message::getHtml();?></div>
</div>
<!--footer start here-->
    <footer id="footer">
		<p>Note: All the times are according to server time. Current server time is <?php echo date("d M Y, H:i A")?></p>
		<p>Powered By: <a target="_blank" href="http://www.yo-kart.com">Yo-kart.com</a></p>
     
    </footer>
    <!--footer start here-->    
    
    
  
</main>
<!--wrapper end here-->
<div class="color_pallete">
    <a href="#" class="pallete_control"><i class="ion-android-settings icon"></i></a>
    <div class="controlwrap hideLayout">
        <h5>Change Layout</h5>
        <div class="btngroup">
            <a href="javascript:void(0)" data-layout='0' class="themebtn btn-sm btn-primary outline layout_switcher <?php echo ($dashboard_layout==0)?'active':''?>">Fixed</a>
            <a href="javascript:void(0)" data-layout='1' class="themebtn btn-sm btn-primary outline right layout_switcher <?php echo ($dashboard_layout==1)?'active':''?>">Fluid</a>
        </div>
    </div>
    <div class="controlwrap">
        <h5>Color Palette [Admin]</h5>
        <ul class="colorpallets admin-theme-switcher">
            <li class="red <?php if($dashboard_color=='red'){echo "active";}?>" data-color='red' ><a href="javascript:void(0)" class="color_red"></a></li>
            <li class="green <?php if($dashboard_color=='green'){echo "active";}?>" data-color='green'><a href="javascript:void(0)" class="color_green"></a></li>
            <li class="yellow <?php if($dashboard_color=='yellow'){echo "active";}?>" data-color='yellow'><a href="javascript:void(0)" class="color_yellow"></a></li>
            <li class="orange <?php if($dashboard_color=='orange'){echo "active";}?>" data-color='orange'><a href="javascript:void(0)" class="color_orange"></a></li>
            <li class="darkblue <?php if($dashboard_color=='darkblue'){echo "active";}?>" data-color='darkblue'><a href="javascript:void(0)" class="color_darkblue"></a></li>
            <li class="darkgrey <?php if($dashboard_color=='darkgrey'){echo "active";}?>" data-color='darkgrey'><a href="javascript:void(0)" class="color_darkgrey"></a></li>
            <li class="blue <?php if($dashboard_color=='blue'){echo "active";}?>" data-color='blue'><a href="javascript:void(0)" class="color_blue"></a></li>
            <li class="brown <?php if($dashboard_color=='brown'){echo "active";}?>" data-color='brown'><a href="javascript:void(0)" class="color_brown"></a></li>
        </ul>
    </div>
    
</div>   

<? if (((strpos($_SERVER['HTTP_HOST'],"yo-kart.com")!== false)  || (strpos($_SERVER['HTTP_HOST'],"localhost")!== false)) && (strpos($_SERVER['HTTP_HOST'], 'mcnation') === false)){?>
<div class="fixed-demo-btn" >
  <a id="btn-demo" href="javascript:void(0);" class="request-demo">Request a Demo</a>  
</div>
<? } ?>    
<script src="<?php echo CONF_WEBROOT_URL; ?>js/admin/common_functions.js" type="text/javascript"></script>
  
<!--circles chart-->     
<script src="<?php echo CONF_WEBROOT_URL; ?>js/admin/circles.js"></script>    
<script>
		var colors = [
				['#d02518', '#fff'], ['#FCE6A4', '#EFB917'], ['#BEE3F7', '#45AEEA'], ['#F8F9B6', '#D2D558'], ['#F4BCBF', '#D43A43']
			],
			circles = [];
		for (var i = 1; i <= 5; i++) {
			var child = document.getElementById('circles-' + i),
				percentage = 31.42 + (i * 9.84);
            
            if(child == null){
                continue;
            }
				
			var	circle = Circles.create({
					id:         child.id,
					value:      percentage,
					radius:     getWidth(),
					width:      5,
					colors:     colors[i - 1]
				});
			circles.push(circle);
		}
		window.onresize = function(e) {
			for (var i = 0; i < circles.length; i++) {
				circles[i].updateRadius(getWidth());
			}
		};
		function getWidth() {
            return $('.pieprogress').width()/2;
			return window.innerWidth / 10;
		}
		
$(document).ready(function() {
	$().piroBox_ext({
	piro_speed : 700,
		bg_alpha : 0.8,
		piro_scroll : true // pirobox always positioned at the center of the page
	});
});
</script>
</body>
</html>

