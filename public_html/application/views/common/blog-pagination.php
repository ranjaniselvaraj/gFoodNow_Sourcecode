<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
?>
<?php
if ($pages <= 1)
    return;
?>
<div class="pagination">
    <ul class="pagination">
        <?php
        if ($page > 1):
            echo '<li class="prv"><a href="javascript:void(0)" onclick="listPages(' . ($page - 1) . ')"><i class="ion-more"></i></a></li>';
        endif;
        ?>
        <?php
        if ($pages > 1) :
            echo getPageString('<li><a  href="javascript:void(0)" onclick="listPages(xxpagexx)">xxpagexx</a></li>', $pages, $page, ' <li ><a class="active" href="javascript:void(0)">xxpagexx</a></li>');
        endif;
        ?>
        <?php
        if ($page < $pages):
            echo '<li class="next"><a href="javascript:void(0)" onclick="listPages(' . ($page + 1) .')"><i class="ion-more"></i></a></li>';
        endif;
        ?>      
    </ul>
</div>
