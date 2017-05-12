<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<form class="web_form">
		<div class="adress-wrapper">
		<div class="address-bar shipping_addresses">
        		<?php $i = 0; foreach($addresses as $address){ ?>
                <div class="address <?php echo (($address['ua_id']==$selected_address)?'selected':''); ?>">
                  <div class="radio-wrap">
                     <label class="radio">
                      <input type="radio" value="<?php echo $address['ua_id']; ?>" class="<?php echo (($address['ua_id']==$selected_address)?'selected':''); ?>" name="shipping_address" onclick="cart.short_shipping_address_update('<?php echo $address['ua_id']; ?>');" >
                      <i class="input-helper"></i></label>
                  </div>
                  <strong><?php echo $address['ua_name']?> </strong><br>
                  <?php echo ((strlen($address['ua_address1']) > 0)?$address['ua_address1']:'') .((strlen($address['ua_address2']) > 0)?'<br/>'.$address['ua_address2']:'') . ((strlen($address['ua_city']) > 0)?'<br/>'.$address['ua_city'] . ', ':'') . $address['ua_zip']; ?><br><?php echo $address['state_name']; ?>, <?php echo $address['country_name']; ?><br>
                    T: <?php echo $address['ua_phone']; ?>
                  <div class="btn-action"> <a title="<?php echo Utilities::getLabel('L_Edit')?>" href="<?php echo Utilities::generateUrl('account', 'address_form',array($address["ua_id"],1))?>" rel="fancy_popup_box" class="action"><i class="icon">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="528.899px" height="528.899px" viewBox="0 0 528.899 528.899" style="enable-background:new 0 0 528.899 528.899;" xml:space="preserve">
                      <g>
                        <path d="M328.883,89.125l107.59,107.589l-272.34,272.34L56.604,361.465L328.883,89.125z M518.113,63.177l-47.981-47.981
		c-18.543-18.543-48.653-18.543-67.259,0l-45.961,45.961l107.59,107.59l53.611-53.611
		C532.495,100.753,532.495,77.559,518.113,63.177z M0.3,512.69c-1.958,8.812,5.998,16.708,14.811,14.565l119.891-29.069
		L27.473,390.597L0.3,512.69z"/>
                      </g>
                    </svg>
                    </i> </a> <a title="<?php echo Utilities::getLabel('L_Delete')?>" class="action delete_address" href="<?php echo Utilities::generateUrl('account', 'delete_address',array($address["ua_id"],1))?>"><i class="icon">
                    <svg xml:space="preserve" style="enable-background:new 0 0 482.428 482.429;" viewBox="0 0 482.428 482.429" height="482.429px" width="482.428px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" version="1.1">
                      <g>
                        <g>
                          <path d="M381.163,57.799h-75.094C302.323,25.316,274.686,0,241.214,0c-33.471,0-61.104,25.315-64.85,57.799h-75.098
			c-30.39,0-55.111,24.728-55.111,55.117v2.828c0,23.223,14.46,43.1,34.83,51.199v260.369c0,30.39,24.724,55.117,55.112,55.117
			h210.236c30.389,0,55.111-24.729,55.111-55.117V166.944c20.369-8.1,34.83-27.977,34.83-51.199v-2.828
			C436.274,82.527,411.551,57.799,381.163,57.799z M241.214,26.139c19.037,0,34.927,13.645,38.443,31.66h-76.879
			C206.293,39.783,222.184,26.139,241.214,26.139z M375.305,427.312c0,15.978-13,28.979-28.973,28.979H136.096
			c-15.973,0-28.973-13.002-28.973-28.979V170.861h268.182V427.312z M410.135,115.744c0,15.978-13,28.979-28.973,28.979H101.266
			c-15.973,0-28.973-13.001-28.973-28.979v-2.828c0-15.978,13-28.979,28.973-28.979h279.897c15.973,0,28.973,13.001,28.973,28.979
			V115.744z"/>
                          <path d="M171.144,422.863c7.218,0,13.069-5.853,13.069-13.068V262.641c0-7.216-5.852-13.07-13.069-13.07
			c-7.217,0-13.069,5.854-13.069,13.07v147.154C158.074,417.012,163.926,422.863,171.144,422.863z"/>
                          <path d="M241.214,422.863c7.218,0,13.07-5.853,13.07-13.068V262.641c0-7.216-5.854-13.07-13.07-13.07
			c-7.217,0-13.069,5.854-13.069,13.07v147.154C228.145,417.012,233.996,422.863,241.214,422.863z"/>
                          <path d="M311.284,422.863c7.217,0,13.068-5.853,13.068-13.068V262.641c0-7.216-5.852-13.07-13.068-13.07
			c-7.219,0-13.07,5.854-13.07,13.07v147.154C298.213,417.012,304.067,422.863,311.284,422.863z"/>
                        </g>
                      </g>
                    </svg>
                    </i> </a> </div>
                </div>
                <?php } ?>
              </div>
              <?php if (count($addresses)>2):?>
              <div class="controls"> <a class="prev1 prev_shipping"><svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                  <path fill-rule="evenodd" clip-rule="evenodd"  d="M0,35L40.8,0v70L0,35z"/>
                  </svg></a> <a class="next1 next_shipping"><svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M0,70L0,0l40.8,35L0,70z"/>
                  </svg> </a> </div>
			<?php endif;?>	                  
              </div> 
                </form>              
<script type="text/javascript">
$(document).ready(function () {
		$('.prev_shipping').click(function(){
          $('.address-bar').slick('slickPrev');
        });
        $('.next_shipping').click(function(){
          $('.address-bar').slick('slickNext');
        });
});
$('.shipping_addresses').slick({
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
