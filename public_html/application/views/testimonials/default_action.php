<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
   <div>
  <div class="body clearfix">
    <div class="testimonialTop">
      <div class="fixed-container">
        <h2><?php echo Utilities::getLabel('F_testimonials_top_line')?></h2>
        <h5><span><?php echo Utilities::getLabel('F_testimonials_we_love_client')?></span> <i class="svg-icn"><svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 492.719 492.719" style="enable-background:new 0 0 492.719 492.719;" xml:space="preserve">
<g>
	<g id="Icons_18_">
		<path d="M492.719,166.008c0-73.486-59.573-133.056-133.059-133.056c-47.985,0-89.891,25.484-113.302,63.569
			c-23.408-38.085-65.332-63.569-113.316-63.569C59.556,32.952,0,92.522,0,166.008c0,40.009,17.729,75.803,45.671,100.178
			l188.545,188.553c3.22,3.22,7.587,5.029,12.142,5.029c4.555,0,8.922-1.809,12.142-5.029l188.545-188.553
			C474.988,241.811,492.719,206.017,492.719,166.008z"/>
	</g>
</g> 
</svg>
</i></h5>
      </div>
    </div>
    <div class="innerContainer">
      <div class="container">
        <div class="sectionDevides">
          	<ul class="listparts">
                    	<? foreach($testimonials as $key=>$val):?>
                    	<li  class="testimonial_record <? if ($key%2==0):?>leftLi<? else:  ?>rightLi <? endif;?>">
                        	<div class="listwrap">
                            	<span class="<? if ($key%2==0):?>arrowleft<? else:  ?>arrowright<? endif;?>"></span>
                                <div class="textcontainer">
                                	<p><?php echo nl2br($val["testimonial_text"])?></p>						
                                    <div class="userdetails">
						                <div class="photoround"><img src="<?php echo Utilities::generateUrl('image','testimonial_image',array($val["testimonial_image"]))?>" alt="<?php echo $val["testimonial_name"]?>"></div>
                                        <span class="testiname"><?php echo $val["testimonial_name"]?> <span><?php echo $val["testimonial_address"]?></span></span>
                                    </div>
                                    
                                </div>
                            </div>
                        </li>
						<? endforeach;?>
                    </ul>
        </div>
      </div>
    </div>
  </div>
</div>