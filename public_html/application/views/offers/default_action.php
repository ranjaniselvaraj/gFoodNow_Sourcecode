<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
   <div>
   <div class="body clearfix">
      <div class="page-title"><?php echo Utilities::getLabel('M_Coupon_Codes')?> <span><?php echo Utilities::getLabel('M_Find_all_discount_codes')?> </span></div>
      <div class="fixed-container">
        <div class="coupon-page">
          <?php  if (!empty($offers) && count($offers)>0){?>
          <div class="coupon-list clearfix">
          	<?php  foreach($offers as $key=>$val) {?>
            <div class="coupon-item">
              <div class="cpn-box">
                <div class="offer-pic"><img src="<?php echo Utilities::generateUrl('image','coupon',array('FRONT',$val["coupon_image"]))?>" alt="<?php echo $val["coupon_title"];?>"/></div>
                <div class="right-content">
                  <div class="name"><?php echo strtoupper($val["coupon_title"])?> </div>
                  <div class="desc"><span class="short"><?php echo $val["coupon_description"]?></span></div>
                  <div class="coupon-code"><?php echo $val["coupon_code"]?></div>
                  <div class="after-code">
                    <p class="expires"><strong><?php echo Utilities::getLabel('M_Expires')?>: </strong> <?php echo Utilities::formatDate($val["coupon_end_date"])?></p><br/>
                    <p class="expires"><strong><?php echo Utilities::getLabel('M_Min_order_value')?>: </strong> <?php echo Utilities::displayMoneyFormat($val["coupon_min_order_value"])?></p>
                  </div>
                  <div class="clear"></div>
                </div>
              </div>
            </div>
            <?php  } ?>
          </div>
          <?php  } else{?>
          	<div class="alert alert-info aligncenter">
            	<?php echo Utilities::getLabel('L_We_do_not_have_any_offer')?></p>
            </div>
          <?php  } ?>
        </div>
      </div>
    </div>
  </div>
<script type="text/javascript">
  $(document).ready(function() {
    // Configure/customize these variables.
    var showChar = 200;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "<?php echo Utilities::getLabel('M_Show_more')?>";
    var lesstext = "<?php echo Utilities::getLabel('M_Show_less')?>";
    
    $('.short').each(function() {
        var content = $(this).html();
 
        if(content.length > showChar) {
 
            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);
 
            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';
 
            $(this).html(html);
        }
 
    });
 
    $(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
			$(this).parent().parent().parent().addClass("less");
			$(this).parent().parent().parent().removeClass("more");

            $(this).html(moretext);
        } else {
            $(this).addClass("less");
			$(this).parent().parent().parent().addClass("more");
			$(this).parent().parent().parent().removeClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });
});
</script>