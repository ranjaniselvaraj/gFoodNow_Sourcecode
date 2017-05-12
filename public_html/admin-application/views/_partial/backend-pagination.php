
     <?php 
        if ($pages <=1)
            return false;
     
     ?>
<div class="footinfo">
   
     
    <aside class="grid_1">
        <ul class="pagination">
            <?php
            if ($pages > 1) :
                echo getPageString('<li><a href="javascript:void(0)" onclick="listPages(xxpagexx)">xxpagexx</a></li>', $pages, $page, ' <li class="selected"><a href="javascript:void(0)">xxpagexx</a></li>');
            endif;
            ?>
        </ul>
    </aside>  
    <aside class="grid_2"><span class="info">Showing <?php echo $start_record;?> to <?php echo $end_record;?> of <?php echo $total_records;?> entries</span></aside>
    
</div>