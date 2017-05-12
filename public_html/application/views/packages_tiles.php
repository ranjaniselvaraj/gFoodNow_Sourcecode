<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $duration_freq_arr;  
if(!empty($packages) && is_array($packages)) :
foreach($packages as $package):
$class ='';
if(end($packages) == $package)
{
	//$class ='three';
}
elseif($packages[0]!== $package)
{
	$class ='two';
}
?>
<div class="box <?=$class;?>">
  <div class="box-inner">
	<div class="name"><?=$package['merchantpack_name']?> <span><?=Utilities::getLabel('M_Starting_At_Only');?> </span></div>
	<div class="valid">
	<?php if(!empty($package['startsAt'])){ ?>
	<sup><?=SubscriptionHelper::getCurrencySymbol();?></sup><span><?=$package['startsAt']['merchantsubpack_actual_price']?></span> / <?=SubscriptionHelper::getFormattedInterval($package['startsAt']['merchantsubpack_subs_frequency']);?> !
	<?php } else {
	?>
	<span><?=Utilities::getLabel('M_NA');?></span>
	<?php
	} ?>
	</div>
	<div class="trial">
	  <ul>
		<li><span><?=$package['merchantpack_max_products']?></span> <?=Utilities::getLabel('M_Active_Products');?></li>
		<li><span><?=$package['merchantpack_images_per_product']?></span> <?=Utilities::getLabel('M_Images_Per_Product');?></li>
	  </ul>
	</div>
    
    
	<? if(!$is_front_user_logged )
	{
	?>
	<a href="javascript:void(0);" class="btn primary-btn large" onclick="subscription.buy('<?=$package['merchantpack_id']?>' , true);"><?=Utilities::getLabel('M_Free_Trial');?></a> 
	<div class="trial"><ul><li><span><?php echo $package['merchantpack_free_trial_days'];?> <?=Utilities::getLabel('M_Days');?></span></li></ul></div>   
	<?
	}else if($includeFreePackage){
	?>
		<a href="javascript:void(0);" class="btn primary-btn large" onclick="subscription.buy('<?=$package['merchantpack_id']?>' , true);" ><?=Utilities::getLabel('M_Free_Trial');?></a>
		<div class="trial"><ul><li><span><?php echo $package['merchantpack_free_trial_days'];?> <?=Utilities::getLabel('M_Days');?></span></li></ul></div>
	<?
	}?>
	
	
	</div>
  <div class="after-box">
	<h3><?php if ($active_subscription['mporder_merchantpack_id']==$package['merchantpack_id']) {echo Utilities::getLabel('M_Currently_Active');}else{ echo Utilities::getLabel('M_SELECT_YOUR_PACKAGE');}?></h3>
	<ul>
	<?  if(!empty($package['sub_packages']) && is_array($package['sub_packages'])) :
		foreach($package['sub_packages'] as $id => $subPack):
			$checked = '';
			if( isset($chosenPlan) && $id == $chosenPlan)
			{
				$checked = 'checked';
			}
		
	?>
	  <li>
		<label class="radio">
		  <input type="radio" class="rdbSubscribe" name="package_<?=$package['merchantpack_id']?>" value="<?=$id?>"  <?=$checked?> >
		  <i class="input-helper"></i> <?=$subPack?> </label>
	  </li>
	<?
	endforeach;
	else :
?>
<li>
	<label class="radio"> 
	<i class="input-helper"></i> <?='NA'?> </label>
</li>
	<?
	endif;
	?>
	</ul>
	<? if(is_null($isSupplier))
	{
	?>
		<a class="btn primary-btn large signUp" href="<?=Utilities::generateUrl('user' , 'account' );?>" ><?=Utilities::getLabel('M_Sign_Up');?></a>
	<?
	}
	else if($includeFreePackage){
	?>
		<a class="btn primary-btn large" href="javascript:void(0)" onclick="subscription.buy('<?=$package['merchantpack_id']?>');" ><?=Utilities::getLabel('M_Subscribe_Now');?></a>
	<?
	}else{?>
	<a class="btn primary-btn large" href="javascript:void(0)" onclick="subscription.buy('<?=$package['merchantpack_id']?>');" ><?=Utilities::getLabel('M_Change_Plan');?></a>
	<?php 
	}?>
	 </div>
</div>
<?
endforeach;
endif;
?>
<script>
/* to choose only one plan at a time */
$('.rdbSubscribe:radio').change(function(){
	$('.rdbSubscribe:radio').not(this).prop('checked' , false) ; 
});
</script>