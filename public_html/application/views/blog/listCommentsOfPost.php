<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('frmPaging', '', array(), array());
    ?>
    <div class="comment-section">
        <h2><?php echo Utilities::getLabel('L_Comments:');?> </h2>
        <?php
        
        foreach ($records as $records1) {	 
            ?>
            <div class="comnt">
                <div class="imgbox">
                <?php 
			
                  if(!empty($records1['user_profile_image'])){
                  echo '<img width="34" src="'.Utilities::generateUrl('image', 'user', array($records1['user_profile_image'], 'thumb')).'" alt="">';
                  }else{
                  echo '<img width="34"  src="'.Utilities::generateUrl('image', 'user', array('','thumb')).'" alt="">';
                  } 
                ?>
                 </div>
                <div class="comnt-txt">
                    <h3><?php echo ucfirst($records1['comment_author_name']); ?></h3>					
                    <ul class="publsh_date">
                        <li><span><i class="icon ion-calendar"></i></span><?php echo displayDate($records1['comment_date_time']); ?></li>
                        <li><span><i class="icon ion-android-time"></i></span><?php echo date('H:i:s', strtotime($records1['comment_date_time'])); ?></li>
                    </ul>
                    <p><?php echo nl2br($records1['comment_content']); ?></p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
    if ($pages > 1) {
        $vars = array('page' => $page, 'pages' => $pages);
        echo Utilities::renderView('common/blog-pagination.php', $vars);
    }
}
?>
<script>
    $('.comnt-txt').find('p').viewMore({limit: 250});
</script>