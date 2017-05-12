<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
		<div class="adress-wrapper">
        <div class="address-bar billing_addresses">
        		<?php $i = 0; foreach($addresses as $address){ ?>
                <div class="address <?php echo (($address['ua_id']==$selected_address)?'selected':''); ?>">
                  <div class="radio-wrap">
                    <label class="radio">
                      <input type="radio" value="<?php echo $address['ua_id']; ?>" class="<?php echo (($address['ua_id']==$selected_address)?'active':''); ?>" name="billing_address" onclick="cart.short_billing_address_update('<?php echo $address['ua_id']; ?>');" >
                      <i class="input-helper"></i></label>
                  </div>
                  <strong><?php echo $address['ua_name']?> </strong><br>
                  <?php echo ((strlen($address['ua_address1']) > 0)?$address['ua_address1']:'') .((strlen($address['ua_address2']) > 0)?'<br/>'.$address['ua_address2']:'') . ((strlen($address['ua_city']) > 0)?'<br/>'.$address['ua_city'] . ', ':'') . $address['ua_zip']; ?><br><?php echo $address['state_name']; ?>, <?php echo $address['country_name']; ?><br>
                    T: <?php echo $address['ua_phone']; ?>
                  <div class="btn-action"> <a title="<?php echo Utilities::getLabel('L_Edit')?>" onclick="cart.editAddressForm('<?php echo $address["ua_id"]?>')"  href="javascript:void(0)" class="action"><i class="icon">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="528.899px" height="528.899px" viewBox="0 0 528.899 528.899" style="enable-background:new 0 0 528.899 528.899;" xml:space="preserve">
                      <g>
                        <path d="M328.883,89.125l107.59,107.589l-272.34,272.34L56.604,361.465L328.883,89.125z M518.113,63.177l-47.981-47.981
		c-18.543-18.543-48.653-18.543-67.259,0l-45.961,45.961l107.59,107.59l53.611-53.611
		C532.495,100.753,532.495,77.559,518.113,63.177z M0.3,512.69c-1.958,8.812,5.998,16.708,14.811,14.565l119.891-29.069
		L27.473,390.597L0.3,512.69z"/>
                      </g>
                    </svg>
                    </i> </a>  </div>
                </div>
                <?php } ?>
              </div>
             <?php if (count($addresses)>2):?> 
             <div class="controls"> <a class="prev1 prev_billing"><svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                  <path fill-rule="evenodd" clip-rule="evenodd"  d="M0,35L40.8,0v70L0,35z"/>
                  </svg></a> <a class="next1 next_billing"><svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M0,70L0,0l40.8,35L0,70z"/>
                  </svg> </a>
             </div>
             <?php endif; ?>
              </div> 
<script type="text/javascript">
$(document).ready(function () {
		$('.prev_billing').click(function(){
          $('.address-bar').slick('slickPrev');
        });
        $('.next_billing').click(function(){
          $('.address-bar').slick('slickNext');
        });
});
$('.billing_addresses').slick({
  infinite: true,
  slidesToShow: 2,
  slidesToScroll: 2,
  arrows: false,
  responsive: [
    {
      breakpoint: 1024,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2,
        infinite: true
        
      }
    },
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    }
  ]
  
});
</script>              
