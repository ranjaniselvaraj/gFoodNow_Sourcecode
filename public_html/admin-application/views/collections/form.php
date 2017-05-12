<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<?php echo html_entity_decode($breadcrumb); ?>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Collection Setup</h4></div>
						
                        <div class="sectionbody">                            
                             <?php echo $frm->getFormHtml(); ?>                        
						</div>	
															
					</section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				
<script>
$(document).ready(
	<?php $col_type=isset($collection_type)?$collection_type:"C"; ?>
    function() {
		setTimeout($("input[type='radio'][value=<?php echo $col_type?>]").click(), 2000);
    }
);
$("input[type='radio'][name='collection_type']").bind("change", function() {
	var col_type = this.value
	var category_elem = $(this).parent().parent().parent().parent().parent().parent().next("tr");
	var products_elem = $(this).parent().parent().parent().parent().parent().parent().next().next("tr");
	var shops_elem = $(this).parent().parent().parent().parent().parent().parent().next().next().next("tr");
	
	category_elem.find('input').attr('disabled', true);
	category_elem.find("td").css('color','#ccc');
	category_elem.find('input').css('background-color', '#ccc');
	
	products_elem.find('input').attr('disabled', true);
	products_elem.find("td").css('color','#ccc');
	products_elem.find('input').css('background-color', '#ccc');
	
	shops_elem.find('input').attr('disabled', true);
	shops_elem.find("td").css('color','#ccc');
	shops_elem.find('input').css('background-color', '#ccc');
	
		
	
	if (col_type=="C"){
		category_elem.find('input').attr('disabled', false);
		category_elem.find("td").css('color','');
		category_elem.find('input').css('background-color', '');	
	} else if (col_type=="P"){
		products_elem.find('input').attr('disabled', false);
		products_elem.find("td").css('color','');
		products_elem.find('input').css('background-color', '');	
	}
	else if (col_type=="S"){
		shops_elem.find('input').attr('disabled', false);
		shops_elem.find("td").css('color','');
		shops_elem.find('input').css('background-color', '');	
	}
	
});
$(function() {
	
	
	$('input[name=\'category\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'categories_autocomplete',[1],webroot),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) {
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'category\']').val('');
				$('#collection-category' + suggestion.data).remove();
				$('#collection-categories').append('<div id="collection-category' + suggestion.data + '"><i class="remove_filter remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' + suggestion.value + '&nbsp;&nbsp;<a href="#" class="reorder-up"><img src="'+webroot+'images/admin/Arrows-Up-icon.png"/></a> <a href="#" class="reorder-down"><img src="'+webroot+'images/admin/Arrows-Down-icon.png"/></a><input type="hidden" name="categories[]" value="' + suggestion.data + '" /></div>');
    	 }
	});
	
$('input[name=\'products\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'products_autocomplete',[],webroot),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) {
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'products\']').val('');
				$('#collection-products' + suggestion.data).remove();
				$('#collection-products').append('<div id="collection-products' + suggestion.data + '"><i class="remove_product remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' + suggestion.value + '&nbsp;&nbsp;<a href="#" class="reorder-up"><img src="'+webroot+'images/admin/Arrows-Up-icon.png"/></a> <a href="#" class="reorder-down"><img src="'+webroot+'images/admin/Arrows-Down-icon.png"/></a><input type="hidden" name="products[]" value="' + suggestion.data + '" /></div>');
    	 }
	});	
$('input[name=\'shops\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('shops', 'autocomplete'),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) {
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'shops\']').val('');
				$('#collection-shops' + suggestion.data).remove();
				$('#collection-shops').append('<div id="collection-shops' + suggestion.data + '"><i class="remove_shop remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' + suggestion.value + '&nbsp;&nbsp;<a href="#" class="reorder-up"><img src="'+webroot+'images/admin/Arrows-Up-icon.png"/></a> <a href="#" class="reorder-down"><img src="'+webroot+'images/admin/Arrows-Down-icon.png"/></a><input type="hidden" name="shops[]" value="' + suggestion.data + '" /></div>');
    	 }
	});	
$('#collection-categories').delegate('.remove_filter', 'click', function() {
	$(this).parent().remove();
});
$('#collection-products').delegate('.remove_product', 'click', function() {
	$(this).parent().remove();
});
$('#collection-shops').delegate('.remove_shop', 'click', function() {
	$(this).parent().remove();
});
$('#body').delegate('.reorder-up', 'click', function() {
		var $current = $(this).closest('div')
		var $previous = $current.prev('div');
		if($previous.length !== 0){
    		$current.insertBefore($previous);
  		}
  		return false;
});
$('#body').delegate('.reorder-down', 'click', function() {
	var $current = $(this).closest('div')
	var $next = $current.next('div');
	if($next.length !== 0){
		$current.insertAfter($next);
	}
	return false;
});
})
</script>			