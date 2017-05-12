<?php defined('SYSTEM_INIT') or die('Invalid Usage');?>
<div>
    <div class="body clearfix">      
      <div class="fixed-container">
	  
        <?php echo Message::getHtml(); ?>
         
        <div class="content">
             <?php include 'rightpanelblog.php'; ?>
            <div class="col_left">
            <?php if ($post_data || is_array($post_data)) { ?>
                
                    <div class="post">
                        <div class="top">
                            <div class="date">
                                <p><?php echo Utilities::dateFormat("d", $post_data['post_published']); ?></p> <span class="month"><?php echo Utilities::dateFormat("M", $post_data['post_published']); ?></span> </div>
                            <div class="cmnt_area">
                                <h2 class="title"><?php echo ucfirst($post_data['post_title']); ?></h2>
                                <div class="post-meta">
                                    <ul class="cmt-box">
                                        <li> <i class="icon ion-eye"></i><?php echo Utilities::getLabel('L_Views');?> [<?php echo $post_data['post_view_count']; ?>]</li>
                                        <?php if ($post_data['post_comment_status'] != 0) { ?>
                                            <li><i class="icon ion-chatbubble-working"></i><?php echo Utilities::getLabel('L_Comments');?> [<?php echo $comment_count; ?>] </li>
                                        <?php } ?>
                                        <?php if (!empty($post_data['post_contributor_name']) && $post_data['post_contributor_name'] != '') { ?>
                                            <li><i class="icon ion-person"></i><?php echo Utilities::getLabel('L_Author');?> [<?php echo $post_data['post_contributor_name']; ?>]</li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="post_imges">
                            <?php
                            if (!empty($slider_images) && count($slider_images) > 1) {
                                ?>
                                <div  class="areagallery">
                                    <ul class="blog_slider" >
                                        <?php foreach ($slider_images as $slide_images1) { ?>
                                            <li>
                                                <img src="<?php echo Utilities::generateUrl('image', 'post', array('large', $slide_images1['slide_images'])); ?>" />
                                            </li>
                                        <?php } ?>
                                    </ul>	
                                    <ul class="slider_nav" >
                                        <?php
                                        foreach ($slider_images as $slide_images1) {
                                            echo '<li><img src="' . Utilities::generateUrl('image', 'post', array('thumb', $slide_images1['slide_images'])) . '"></li>';
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <?php
                            } else {
                                if (!empty($slider_images[0]['slide_images'])) {
                                    echo '<div class="cell-img img-responsive"><img src="' . Utilities::generateUrl('image', 'post', array('large', $slider_images[0]['slide_images'])) . '"></div>';
                                }
                            }
                            ?>
                        </div>
                        <div class="post_midlSectn editor-cms">
                            <p><?php echo html_entity_decode($post_data['post_content'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <!-- /.post-meta -->
                        </div>
                        <div class="sharewrap">
                            <div class="addthis_toolbox addthis_default_style" addthis:title="<?php echo $post_data['post_title']; ?>" addthis:url="<?php echo Utilities::generateAbsoluteUrl('blog', 'post', array($post_data['post_seo_name'])); ?>" >
                                <a class="addthis_button_facebook_like" fb:like:layout="button_count" fb:like:share="true"></a>
                                <a class="addthis_button_tweet" tw:via=""></a>
                                <a class="addthis_button_linkedin_counter"></a>
                                <a class="addthis_button_pinterest_pinit"></a>
                                <a class="addthis_counter addthis_pill_style"></a>
                            </div>
                        </div>
                    </div>
                    <?php
					//print_r($post_data);
					
                    if ($post_data['post_comment_status'] != 0) {
                        ?>
                        <div id="comment-post-list">
                        </div>
                        <div class="add-comnt" id="comment-form">
                            <div class="add-review">
                                <h2><?php echo Utilities::getLabel('L_Add_Your_Comment');?> </h2>
                                <?php echo $frmComment->getFormTag(); ?>
                                <table class="reviewTbl">
                                    <tbody>
                                        <?php
                                        if (empty($loggedUserId)) {
                                            ?>
                                            <tr>
                                                <td><?php echo $frmComment->getFieldHTML('comment_author_name'); ?></td>
                                                <td><?php echo $frmComment->getFieldHTML('comment_author_email'); ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>	
                                        <tr>
                                            <td colspan="2"><?php echo $frmComment->getFieldHTML('comment_content'); ?></td>
                                        </tr>
                                        <tr>
                                            <td>
											<?php if (!empty(CONF_RECAPTACHA_SITEKEY)){ 
												echo $frmComment->getFieldHtml('captcha_code'); 
											} ?></td>
                                            <?php echo $frmComment->getFieldHTML('comment_post_id'); ?>
                                            <?php echo $frmComment->getFieldHTML('comment_user_id'); ?>
                                            <td><?php echo $frmComment->getFieldHTML('btn_submit'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                </form>
                                <?php echo $frmComment->getExternalJs(); ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                <?php
            } else {
                ?>
                <div class="no_result">
                    <div class="no_result_text">                        
                        <h5><?php echo Utilities::getLabel('L_No_Blog_Post_Found_With_Search_Criteria!!');?></h5>
                        <p><?php echo Utilities::getLabel('L_Try_To_Search_With_Different_Keyword');?> <br>
                            <a class="btn" href="<?php echo Utilities::generateUrl('blog'); ?>">
                        <?php echo Utilities::getLabel('L_Back_To_Blog');?></a> </p>
                    </div>
                </div>
            </div>
        <?php } ?>
             </div>
        
    </div>
      </div>
    </div>
  </div>
  <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5026620141dbd841"></script>
  <script src='https://www.google.com/recaptcha/api.js'></script>