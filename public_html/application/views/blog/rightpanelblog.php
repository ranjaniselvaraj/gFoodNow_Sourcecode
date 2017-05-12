<div class="col_right">
    <div class="searchform">
        <?php echo $frmSearchForm->getFormHtml(); ?>
    </div>
    <a class="themeBtn" href="<?php echo Utilities::generateUrl('blog', 'contribution'); ?>" class="grydtn-btn"><?php echo Utilities::getLabel('L_Contribute')?></a>
    <?php 
     function getList($categories1, $level) {
         $str='';
        $limit = 10; /* level limit */
        $str.='<li><a href="' . Utilities::generateUrl('blog', 'category', array($categories1['category_seo_name'])) . '">' . ucfirst($categories1['category_title']) . '<span class="countxt">(' . $categories1['count_post'] . ')</span> </a>';
        if (isset($categories1['children'])) {
            $level++;
            if ($level < $limit) {
                $str.='<span class="accordion_icon "></span>';
            }
            $str.='<ul class="sub_cat">';
            foreach ($categories1['children'] as $children) {
                if ($level == $limit) {
                    continue;
                }
                $str.=getList($children, $level);
                //  $level++;
            }
            $str.='</ul>';
        }
        $str.='</li>';
        return $str;
    }
    
    
    
    if ($categories || is_array($categories)) { ?>
        <div class="round_sectn">
            <div class="sectnTop">
                <h4><?php echo Utilities::getLabel('L_Categories');?> </h4>
            </div>
            <div class="sectnMiddle">  
                <ul class="blog_lnks accordion">
                    <?php
                    foreach ($categories as $categories1) {
                         $level = 0;
                    echo getList($categories1, $level);
//                        echo '<li><a href="' . Utilities::generateUrl('blog', 'category', array($categories1['category_seo_name'])) . '">' . ucfirst($categories1['category_title']) . '<span class="countxt">' . $categories1['count_post'] . '</span> </a>';
//                        if (isset($categories1['children'])) {
//                            echo '<ul class="sub_cat">';
//                            foreach ($categories1['children'] as $children) {
//                                echo '<li><a href="' . Utilities::generateUrl('blog', 'category', array($children['category_seo_name'])) . '">' . ucfirst($children['category_title']) . ' <span class="countxt">' . $children['count_post'] . '</span></a> </li>';
//                            }
//                            echo '</ul>';
//                        }
//                        echo '</li>';
                    }
                    ?>	
                </ul>
            </div> 
        </div>
    <?php } ?>        
    <?php if ($archives || is_array($archives)) { ?>
        <div class="round_sectn">
            <div class="sectnTop">
                <h4><?php echo Utilities::getLabel('L_Archives');?></h4></div>
            <div class="sectnMiddle">
                <ul class="blog_lnks">
                    <?php
                    foreach ($archives as $archives1) {
                        $month = Utilities::dateFormat("m", $archives1['created_month']);
                        $year = Utilities::dateFormat("Y", $archives1['created_month']);
                        echo '<li><a href="' . Utilities::generateUrl('blog', 'archives', array($year, $month)) . '">' . $archives1['created_month'] . '</a> </li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="round_sectn">
        <?php if ($recent_post || is_array($recent_post)) { ?>
            <div class="sectnTop">
                <h4><?php echo Utilities::getLabel('L_Recent_Posts');?></h4>
            </div>
            <div class="sectnMiddle">
                <ul class="post-links">
                    <?php
                    foreach ($recent_post as $records) {
                        ?>
                        <li> <a href="<?php echo Utilities::generateUrl('blog', 'post', array($records['post_seo_name'])); ?>"><?php echo ucfirst(Utilities::myTruncateCharacters($records['post_title'], 30)); ?></a>
                            <ul class="comnt_date">
                            
                                <li><a href="javascript:void(0);"><span><i class="icon ion-calendar"></i></span><?php echo displayDate($records['post_published']); ?></a> </li>
                        </li>
                      <?php if( $records['post_comment_status']!=0 && $records['comment_count'] > 0) {?>
                        <li><a href="<?php echo Utilities::generateUrl('blog', 'post', array($records['post_seo_name'])); ?>#comment-form"><span><i class="icon ion-chatbox"></i></span><?php echo $records['comment_count']; ?> <?php echo Utilities::getLabel('L_Comments');?></a></li>
                      <?php } ?>    
                </ul>
                    </li>
                    <?php
                }
                ?>
                </ul></div>
            <?php
        }
        ?>	
    </div>
</div>