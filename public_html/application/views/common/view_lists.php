<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<span class="listtitle"><a href="#"><?php echo Utilities::getLabel('L_Your_Lists')?></a></span>
<div class="heightlist">
  <ul class="listselection">
    <?php foreach($user_lists as $ukey=>$uval):?>
    <li class="listtem 
    <?php if (in_array($product_id,explode(",",$uval["ulist_products"]))):?> listselect <?php endif;?>" id="<?php echo $uval["ulist_id"]?>" rel="<?php echo $product_id?>"><a href="#"><?php echo $uval["ulist_title"]?></a></li>
    <?php endforeach;?>
  </ul>
</div>
<div class="createList">
  <?php echo $frm->getFormHtml();?>
  <span id="test<?php echo $product_id?>"></span>
</div>