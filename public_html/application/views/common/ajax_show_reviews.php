<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php foreach ($reviews as $review) { ?>
<div class="reviewList">
              <aside class="grid_1">
                <figure class="photo"><img src="<?php echo Utilities::generateUrl('image','user',array($review["user_profile_image"],'SMALL'))?>" alt="<?php echo $review['user_username']?>"></figure>
                <span class="postedname"><span><?php echo Utilities::getLabel('L_Reviewed_by')?></span><?php echo $review['user_username']?></span> </aside>
              <aside class="grid_2">
                <div class="ratingWrap">
                   <div class="rating">
	                <ul>
                	<?php for($j=1;$j<=5;$j++){ ?>
	                  <li class="<?php echo $j<=round($review["review_rating"])?"active":"in-active" ?>"> 
    	                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="18px" height="18px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
        	            <g>
                      <path fill="<?php echo $j<=round($review["review_rating"])?"#ff3a59":"#474747" ?>" d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42"/>
                    </g>
                    </svg> </li>
                    <?php } ?> 
                </ul>
               
              </div>
                </div>
                <span class="datetext"><?php echo Utilities::formatDate($review["reviewed_on"])?></span>
                <div class="collectionreviews">
                  <div class="secionreviews">
                    <p class="reviewtxt"><?php echo nl2br($review["review_text"])?></p>
                    <div class="itemdetails">
                      <figure class="itemthumb"><img src="<?php echo Utilities::generateUrl('image','product_image',array($review["prod_id"],'THUMB'))?>" alt="<?php echo $review["prod_name"]?>"></figure>
                      <span class="item_name"><a href="<?php echo Utilities::generateUrl('products','view',array($review["prod_id"]))?>"><?php echo $review["prod_name"]?></a></span> </div>
                  </div>
                </div>
              </aside>
            </div>
<?php } ?>
