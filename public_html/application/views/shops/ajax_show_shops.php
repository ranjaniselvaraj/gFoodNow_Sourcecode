<?php  $cnt=0; foreach ($shops as $sn=>$row): $sn++; ?>
            <div class="ppc-campaign"><?php include CONF_THEME_PATH . 'common/shop_thumb_view.php'; ?></div>
<?php  endforeach;?>
<div class="clear"></div>
<?php  if ($page<$pages):?>
<div class="loadmorepage">
<div class="aligncenter">
<a href="javascript:void(0)" onclick="listPagingShops('<?php echo $page+1?>');" class="loadmore btn"><?php echo Utilities::getLabel('F_Load_More')?></a>
</div>
</div>
<?php  endif;?>
<script type="text/javascript">
$(document).ready(function() { 
		ppc_track_impressions();
	})
</script>				