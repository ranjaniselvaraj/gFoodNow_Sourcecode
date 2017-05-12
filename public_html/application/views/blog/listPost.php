<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if (!$records || !is_array($records)) {
    ?>
    <div class="no_result">
        <div class="no_result_text">            
            <h5><?php echo Utilities::getLabel('L_No_Blog_Post_Found_With_Search_Criteria!!');?></h5>
            <p><?php echo Utilities::getLabel('L_Try_To_Search_With_Different_Keyword');?></p>
			<div class="gap"></div>
			<p><a href="<?php echo Utilities::generateUrl('blog'); ?>" class="btn"> <?php echo Utilities::getLabel('L_Back_To_Blog');?></a> </p>
        </div>
    </div>
    </div>
    <?php
} else {
    echo createHiddenFormFromPost('frmPaging', '', array(), array());
    foreach ($records as $record) {
        ?>
        <div class="post">
            <div class="top">
                <div class="date">
                    <p><?php echo Utilities::dateFormat("d", $record['post_published']); ?></p> <span class="month"><?php echo Utilities::dateFormat("M", $record['post_published']); ?></span> </div>
                <div class="cmnt_area">
                    <h2 class="title"><a href="<?php echo Utilities::generateUrl('blog', 'post', array($record['post_seo_name'])); ?>"><?php echo ucfirst(Utilities::myTruncateCharacters($record['post_title'], 50)); ?> </a></h2>
                    <div class="post-meta">
                        <ul class="cmt-box">
                            <li> <i class="icon ion-eye"></i><a href="<?php echo Utilities::generateUrl('blog', 'post', array($record['post_seo_name'])); ?>"><?php echo Utilities::getLabel('L_Total_Views');?> [<?php echo $record['post_view_count']; ?>]</a></li>
                            <?php if ($record['post_comment_status'] != 0) { ?>	
                                <li><i class="icon ion-chatbubble-working"></i><a href="<?php echo Utilities::generateUrl('blog', 'post', array($record['post_seo_name'])); ?>#comment-form"><?php echo Utilities::getLabel('L_Comments');?> [<?php echo $record['comment_count']; ?>]</a> </li>
                            <?php } ?>
                            <?php if (!empty($record['post_contributor_name']) && $record['post_contributor_name'] != '') { ?>	
                                <li><i class="icon ion-person"></i><?php echo Utilities::getLabel('L_Author');?> [<?php echo $record['post_contributor_name']; ?>]</li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php if (!empty($record['post_image_file_name'])) { ?>
                <div class="post_imges">
                    <a href="<?php echo Utilities::generateUrl('blog', 'post', array($record['post_seo_name'])); ?>">
                        <img src="<?php echo Utilities::generateUrl('image', 'post', array('large', $record['post_image_file_name'])); ?>">
                    </a>
                </div>
            <?php } ?>	
            <div class="post_midlSectn editor-cms">
                <p><?php
                    if (!empty($record['post_short_description'])) {
                        echo $record['post_short_description'];
                    } else {
                        echo Utilities::myTruncateCharacters( html_entity_decode($record['post_content']), 200);
                    }
                    ?></p><a class="link" href="<?php echo Utilities::generateUrl('blog', 'post', array($record['post_seo_name'])); ?>">[<?php echo Utilities::getLabel('L_Click_to_Continue..');?>]</a>
                <!-- /.post-meta -->
            </div>
            <div class="sharewrap">
                <div class="addthis_toolbox addthis_default_style" addthis:title="<?php echo $record['post_title']; ?>" addthis:url="<?php echo Utilities::generateAbsoluteUrl('blog', 'post', array($record['post_seo_name'])); ?>" >
                    <a class="addthis_button_facebook_like" fb:like:layout="button_count" fb:like:share="true"></a>
                    <a class="addthis_button_tweet" tw:via=""></a>
                    <a class="addthis_button_linkedin_counter"></a>
                    <a class="addthis_button_pinterest_pinit"></a>
                    <a class="addthis_counter addthis_pill_style"></a>
                </div>
            </div>
        </div>
        <?php
    }
    if ($pages > 1) {
		 
        $vars = array('page' => $page, 'pages' => $pages);
        echo Utilities::renderView(CONF_THEME_PATH.'common/blog-pagination.php', $vars);
    }
}
?>
<script>
    $(document).ready(function () {
        if (window.addthis) {
            window.addthis = null;
            window._adr = null;
            window._atc = null;
            window._atd = null;
            window._ate = null;
            window._atr = null;
            window._atw = null;
        }
        return $.getScript("//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5026620141dbd841");
    });
</script>
