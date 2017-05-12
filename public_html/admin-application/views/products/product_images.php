<?php if (count($prod_images)>0):?>
<br/>
<form id="dd-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="formTable" >
  <tr>
	<td><ul id="sortable-list">
		<?php foreach($prod_images as $row) {  $order[] = $row['image_id']; ?>
        	<li title="<?php echo $row['image_id']; ?>" class="ui-sortable-handle">
                 <div class="controls-wrapper" id="<?php echo $row['image_id']; ?>"> 
                   <input type="hidden" name="imgrotation" id="imgrotation<?php echo $row['image_id']; ?>" value="0" />		
                   <input type="hidden" name="rotation" id="rotation<?php echo $row['image_id']; ?>" value="0" />	
                   <button class="leftRotate" title="<?php echo Utilities::getLabel('L_Left_Rotate');?>"></button>
                   <button class="rightRotate" title="<?php echo Utilities::getLabel('L_Right_Rotate');?>"></button>
                   <button class="saveImageOrientation" title="<?php echo Utilities::getLabel('L_Save_Image');?>"></button>
                   <a href="javascript:void(0);" onclick="deleteImage(<?php echo $row['image_id']; ?>,<?php echo $row['image_prod_id']; ?>);" class="delete">
                 <i class="ion-close-round"></i></a>
                 </div>
                 	<div class="wrapphoto"> <img id="product_img<?php echo $row['image_id']; ?>" src="<?php echo Utilities::generateUrl('image','product', array("THUMB", $row['image_file']),CONF_WEBROOT_URL); ?>" alt=""> 
                 </div>
               </li>
		   <?php } ?>	
			<span class="clear"></span>
			 <?php if( count($prod_images) > 1 OR is_array($prod_images) ) { ?>
			<strong><?php echo 'Showing Total' . ' '  . count($prod_images) . ' ' . 'Photos' ; ?></strong> 
			<?php } ?></ul>
	</td>
  </tr>
</table>
<input type="hidden" name="sort_order" id="sort_order" value="<?php echo implode(',',$order); ?>" />
</form>
<br/>
<small>Your changes related to product images saved instantly, no need to click "save changes" button at the bottom.</small>
<?php endif;?>													
<script>
jQuery(document).ready(function() {
	
	/* grab important elements */
	var sortInput = jQuery('#sort_order');
	var submit = 1;
	var messageBox = jQuery('#message-box');
	var list = jQuery('#sortable-list');
		
	/* create requesting function to avoid duplicate code */
	var request = function() {
		jQuery.ajax({
			beforeSend: function() {
				$.mbsmessage('Updating the sort order in the database.');
				setTimeout(function(){ $.mbsmessage.close();}, 1000);
			},
			complete: function() {
				$.mbsmessage('Image ordering has been updated.');
				setTimeout(function(){ $.mbsmessage.close();}, 1000);
			},
			data: 'sort_order=' + sortInput[0].value + '&ajax=1&do_submit=1&byajax=1', //need [0]?
			type: 'post',
			url: generateUrl('products', 'setProductImagesOrdering', [] )
		});
	};
	/* worker function */
	var fnSubmit = function(save) {
		var sortOrder = [];
		list.children('li').each(function(){
			sortOrder.push(jQuery(this).data('id'));
		});
		sortInput.val(sortOrder.join(','));
		console.log(sortInput.val());
		if(save) {
			request();
		}
	};
	/* store values */
	list.children('li').each(function() {
		var li = jQuery(this);
		li.data('id',li.attr('title')).attr('title','');
	});
	/* sortables */
	list.sortable({
		opacity: 0.7,
		update: function() {
			fnSubmit(1);
		}
	});
	list.disableSelection();
	/* ajax form submission */
	jQuery('#dd-form').bind('submit',function(e) {
		if(e) e.preventDefault();
		//fnSubmit(true);
	});
});


$(function() {
	                                 
    var rotation = 0;                            
    $(".leftRotate").click(function() {
		var elem_id = $(this).parent().attr("id");
    	rotation =	parseInt($("#rotation"+elem_id).val());
		imgrotation =	parseInt($("#imgrotation"+elem_id).val());  
		rotation = (rotation -90) % 360;
		imgrotation = (imgrotation -90) % 360;
        $("#product_img"+elem_id).rotate(imgrotation);
		$("#imgrotation"+elem_id).val(imgrotation)
		$("#rotation"+elem_id).val(rotation)
		
    });
    $(".rightRotate").click(function() {
		var elem_id = $(this).parent().attr("id");
    	rotation =	parseInt($("#rotation"+elem_id).val());
		imgrotation =	parseInt($("#imgrotation"+elem_id).val());  
		rotation = (rotation + 90) % 360;
		imgrotation = (imgrotation + 90) % 360;
        $("#product_img"+elem_id).rotate(imgrotation);
		$("#imgrotation"+elem_id).val(imgrotation)
		$("#rotation"+elem_id).val(rotation)
		
    });
	
	
    $(".saveImageOrientation").click(function() {
		elem_id = $(this).parent().attr("id");
		jQuery.ajax({
			beforeSend: function() {
				$("#rotation"+elem_id).val(0)
				$.mbsmessage('Updating the image orientation.');
				//setTimeout(function(){ $.mbsmessage.close();}, 1000);
			},
			complete: function(r) {
				$.mbsmessage(r.responseText);
				//setTimeout(function(){ $.mbsmessage.close();}, 1000);
			},
			data: 'rotation=' + rotation + '&image=' + elem_id + '&ajax=1&do_submit=1&byajax=1', //need [0]?
			type: 'post',
			url: generateUrl('products', 'save_image_orientation', [] )
		});
		
        
    });
});

</script>											