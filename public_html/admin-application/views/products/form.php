<?php defined('SYSTEM_INIT') or die('Invalid Usage'); $attributes=$data["product_attributes"]; $shipping_rates=$data["product_shipping_rates"]; $discount_rates=$data["product_discounts"]; $special_rates=$data["product_specials"]; $product_options=$data["product_options"]; $option_values=$data["option_values"]; $downloads=$data["product_downloads"]; ?>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/LiveEditor/scripts/innovaeditor.js"></script>
<script src="<?php echo CONF_WEBROOT_URL; ?>js/LiveEditor/scripts/common/webfont.js" type="text/javascript"></script>
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
					<h1>Products Setup</h1>  
                      
                   <div class="tabs_nav_container responsive flat">
                            
                            <ul class="tabs_nav detailTabs">
                                <li><a class="active" rel="tabs_1" href="javascript:void(0)" name="general">General</a></li>
                                <li><a rel="tabs_2" href="javascript:void(0)" name="data">Data</a></li>
                                <li><a rel="tabs_3" href="javascript:void(0)" name="links">Links</a></li>
                                <li><a rel="tabs_3_X" href="javascript:void(0)" name="seo">SEO</a></li>
                                <li><a rel="tabs_4" href="javascript:void(0)" name="attributes">Specifications</a></li>                            
                                <li><a rel="tabs_5" href="javascript:void(0)" name="option">Option</a></li>
                                <!--<li><a rel="tabs_6" href="javascript:void(0)" name="shipping">Shipping</a></li>-->
                                <li><a rel="tabs_7" href="javascript:void(0)" name="discount">Qty Discount</a></li>
                                <li><a rel="tabs_8" href="javascript:void(0)" name="special">Special Discount</a></li>
                                <?php if (Settings::getSetting("CONF_ENABLE_DIGITAL_PRODUCTS")) {?>
                                <li><a rel="tabs_9" href="javascript:void(0)" name="download">Downloads</a></li>
                                <?php } ?>
                            </ul> 
								<?php echo $frm->getFormTag ();  ?>
                                <div class="tabs_panel_wrap">
                                        
                                        <span class="togglehead active" rel="tabs_1">Home</span>
                                        <!--tab 1 start here-->
										<div id="tabs_1" class="tabs_panel">
                                            <h4>General</h4>
											<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"><tr>
                                            <td width="15%">Product Shop <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('shop');?></td>
                                            </tr><?php if (Settings::getSetting("CONF_ENABLE_DIGITAL_PRODUCTS")) {?><tr>
                                            <td width="15%">Type<span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('prod_type');?></td>
                                            </tr><? } else {?><input type="hidden" name="prod_type" value="1" /><?php } ?><tr>
                                            <td width="15%">Name <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('prod_name');?></td>
                                            </tr><tr>
                                            <td width="15%">URL Keywords <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('seo_url_keyword');?></td>
                                            </tr><tr>
                                            <td width="15%">Price [<?php echo CONF_CURRENCY_SYMBOL?>] <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('prod_sale_price');?><br/><div id="ajax_validation_message" class="text-danger"></div></td>
                                            </tr><tr>
                                            <td width="15%">Quantity <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('prod_stock');?></td>
                                            </tr><tr>
                                            <td width="15%">Minimum Quantity <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('prod_min_order_qty');?></td>
                                            </tr><tr>
											<td width="15%">Brand/Manufacturer</td>
											<td><?php echo $frm->getFieldHTML('brand_manufacturer');?></td>
											</tr><tr>
											<td width="15%">Product Category</td>
											<td><?php echo $frm->getFieldHTML('prod_category[]');?></td>
											</tr><tr>
                                            <td width="15%">Model <? if (Settings::getSetting("CONF_PRODUCT_MODEL_MANDATORY")){ ?><span class="mandatory">*</span><? } ?></td>
                                            <td><?php echo $frm->getFieldHTML('prod_model');?></td>
                                            </tr><tr>
                                            <td width="15%">SKU <? if (Settings::getSetting("CONF_PRODUCT_SKU_MANDATORY")){ ?><span class="mandatory">*</span><? } ?></td>
                                            <td><?php echo $frm->getFieldHTML('prod_sku');?></td>
                                            </tr><?php if (Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING")){?><tr class="type_required">
                                            <td width="15%">Condition <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('prod_condition');?></td>
                                            </tr>
                                            <?php } ?>
                                            <?php if (Settings::getSetting("CONF_SHIPSTATION_API_STATUS")):?>
                                            <tr class="type_required">
                                            <td width="15%">Dimensions (L x W x H) <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('prod_length');?><div class="clear"></div></td>
                                            </tr><tr class="type_required">
                                            <td width="15%">Length Class:</td>
                                            <td><?php echo $frm->getFieldHTML('prod_length_class');?></td>
                                            </tr><tr class="type_required">
                                            <td width="15%">Weight <span class="mandatory">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('prod_weight');?></td>
                                            </tr><tr class="type_required">
                                            <td width="15%">Weight Class:</td>
                                            <td><?php echo $frm->getFieldHTML('prod_weight_class');?></td>
                                            </tr>
                                            <?php endif;?><tr>
                                            <td width="15%">Status</td>
                                            <td><?php echo $frm->getFieldHTML('prod_status');?></td>
                                            </tr><tr>
                                            <td width="15%">Photo(s)</td>
                                            <td><?php echo $frm->getFieldHTML('prod_image');?></td>
                                            </tr><tr>
                                            <td width="15%">Description</td><td><div class="editor-bar"><?php echo $frm->getFieldHTML('prod_long_desc');?></div></td>
                                            </tr><tr>
                                            <td width="15%">Tags</td>
                                            <td><?php echo $frm->getFieldHTML('prod_tags');?></td>
                                            </tr><tr class="type_required">
                                            <td width="15%">Requires Shipping</td>
                                            <td><?php echo $frm->getFieldHTML('prod_requires_shipping');?></td>
                                            </tr><tr class="type_required shipping_required"> 
                                            <td width="15%">Shipping Country</td>
                                            <td><?php echo $frm->getFieldHTML('shipping_country');?></td>
                                            </tr><tr class="type_required shipping_required">
                                            <td width="15%">Free Shipping</td>
                                            <td><?php echo $frm->getFieldHTML('prod_ship_free');?></td>
                                            </tr>
                                            <tr class="type_required"><td colspan="2" class="shipping_required">	
                                            <div class="gap"></div>
                                            <table id="shipping" class="table tbl-responsive">
											<thead>
											<tr>
											<th width="20%">Ships To </th>
											<th width="20%">Shipping Company</th>
											<th width="20%">Processing Time</th>
											<th width="15%">Cost [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
											<th width="25%">Each Additional Item [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
											<th></th>
											</tr>
											</thead>
											<tbody>
											<?php $shipping_row = 0; ?>
											<?php foreach ($shipping_rates as $shipping) { ?>
											<tr id="shipping-row<?php echo $shipping_row; ?>">
											<td>
                                            <span class="cellcaption">Ships To</span>
                                            <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][pship_id]" value="<?php echo $shipping["pship_id"]?>" />
                                            <input type="text" autocomplete="off" name="product_shipping[<?php echo $shipping_row; ?>][country_name]" value="<?php echo $shipping["pship_country"]!="-1"?$shipping["country_name"]:"&#8594;Everywhere else"?>" placeholder="<?php echo Utilities::getLabel('M_Shipping')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][country_id]" value="<?php echo $shipping["pship_country"]?>" /></td>
											<td>
                                            <span class="cellcaption">Shipping Company</span>
                                            <input type="text" autocomplete="off" name="product_shipping[<?php echo $shipping_row; ?>][company_name]" value="<?php echo $shipping["scompany_name"]?>" placeholder="<?php echo Utilities::getLabel('M_Company')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][company_id]" value="<?php echo $shipping["scompany_id"]?>" /></td>
											<td>
                                            <span class="cellcaption">Processing Time</span>
                                            <input type="text" autocomplete="off" name="product_shipping[<?php echo $shipping_row; ?>][processing_time]" value="<?php echo $shipping["sduration_label"]?>" placeholder="<?php echo Utilities::getLabel('M_Processing_Time')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][processing_time_id]" value="<?php echo $shipping["sduration_id"]?>" /></td>
											<td>
                                            <span class="cellcaption">Cost [<?php echo CONF_CURRENCY_SYMBOL?>]</span>
                                            <input type="text" autocomplete="off" name="product_shipping[<?php echo $shipping_row; ?>][cost]" value="<?php echo $shipping["pship_charges"]?>" placeholder="<?php echo Utilities::getLabel('M_Cost')?>" /></td>
											<td>
                                            <span class="cellcaption">Each Additional Item [<?php echo CONF_CURRENCY_SYMBOL?>]</span>
                                            <input type="text" autocomplete="off" name="product_shipping[<?php echo $shipping_row; ?>][additional_cost]" value="<?php echo $shipping["pship_additional_charges"]?>" placeholder="<?php echo Utilities::getLabel('M_Each_Additional_Item')?>" /></td>
											<td><ul class="actions"><li><a class="button red medium" onclick="$('#shipping-row<?php echo $shipping_row; ?>').remove();"  title="Remove"><i class="ion-minus icon"></i></a></li></ul></td>
											</tr>
											<?php $shipping_row++; ?>
											<?php } ?>
											</tbody>
											<tfoot>
											<tr>
											<td colspan="5"></td>
											<td ><ul class="actions"><li><a onclick="addShipping();" class="button medium blue" title="Add Shipping"><i class="ion-plus-round icon"></i></a></li></ul></td>
											</tr>
											</tfoot>
											</table></td></tr></table>
                                        </div>
										<!--tab 1 end here-->
										<!--tab 2 start here-->                                   
                                     
                                        <span class="togglehead" rel="tabs_2">Data</span>
                                        <div id="tabs_2" class="tabs_panel">
                                            <h4>Data</h4>
											<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
                                            <!--<tr>
                                            <td width="15%">Shipping State</td>
                                            <td><?php echo $frm->getFieldHTML('shipping_state');?></td>
                                            </tr>--><tr>
                                            <td width="15%">Subtract Stock</td>
                                            <td><?php echo $frm->getFieldHTML('prod_subtract_stock');?></td>
                                            </tr><!----><tr>
                                            <td width="15%">Track Inventory</td>
                                            <td><?php echo $frm->getFieldHTML('prod_track_inventory');?></td>
                                            </tr><tr>
                                            <td width="15%">Alert - Stock Level</td>
                                            <td><?php echo $frm->getFieldHTML('prod_threshold_stock_level');?></td>
                                            </tr><tr>
                                            <td width="15%">Youtube Video</td>
                                            <td><?php echo $frm->getFieldHTML('prod_youtube_video');?>
                                            <?php if ($data["embed_code"]!=""):?><br/>
                                            <iframe width="400" height="250" src="//www.youtube.com/embed/<?php echo $data["embed_code"]?>" frameborder="0" allowfullscreen></iframe>
                                            <?php endif; ?></td>
                                            </tr><tr>
                                            <td width="15%">Date Available</td>
                                            <td><?php echo $frm->getFieldHTML('prod_available_date');?></td>
                                            </tr><?php if (!Settings::getSetting("CONF_SHIPSTATION_API_STATUS")):?>
                                            <tr class="type_required" >
                                            <td width="15%">Dimensions (L x W x H)</td>
                                            <td><?php echo $frm->getFieldHTML('prod_length');?><div class="clear"></div></td>
                                            </tr><tr class="type_required">
                                            <td width="15%">Length Class:</td>
                                            <td><?php echo $frm->getFieldHTML('prod_length_class');?></td>
                                            </tr><tr class="type_required">
                                            <td width="15%">Weight</td>
                                            <td><?php echo $frm->getFieldHTML('prod_weight');?></td>
                                            </tr><tr class="type_required">
                                            <td width="15%" >Weight Class:</td>
                                            <td><?php echo $frm->getFieldHTML('prod_weight_class');?></td>
                                            </tr>
                                            <?php endif;?><tr>
                                            <td width="15%">Sort Order</td>
                                            <td><?php echo $frm->getFieldHTML('prod_display_order');?></td>
                                            </tr><tr>
                                            <td width="15%">Featured Product</td>
                                            <td><?php echo $frm->getFieldHTML('prod_featuered');?></td>
                                            </tr><?php if (Settings::getSetting("CONF_ENABLE_COD_PAYMENTS")):?><tr class="type_required">
                                            <td width="15%"><?php echo Utilities::getLabel('M_Enable_COD')?></td>
                                            <td><?php echo $frm->getFieldHTML('prod_enable_cod_orders');?></td>
                                            </tr><?php endif;?></table>                                            
                                        </div>
										<!--tab 2 end here-->
										<!--tab 3 start here-->
										<span class="togglehead" rel="tabs_3">Links</span>
                                        <div id="tabs_3" class="tabs_panel">
                                            <h4>Links</h4> 
											<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
											<tr>
											<td width="15%">Product Filters</td>
											<td><?php echo $frm->getFieldHTML('filter');?></td>
											</tr><tr>
											<td width="15%">Related Products</td>
											<td><?php echo $frm->getFieldHTML('related');?></td>
											</tr><?php if ($shop>0) {?><tr>
											<td width="15%">Add-On Products</td>
											<td><?php echo $frm->getFieldHTML('addons');?></td>
											</tr><?php } ?></table>											
                                        </div>
                                        
                                        <span class="togglehead" rel="tabs_3_X">SEO</span>
                                        <div id="tabs_3_X" class="tabs_panel">
                                            <h4>SEO</h4> 
											<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
											<tr>
                                            <td width="15%">Meta Tag Title <? if (Settings::getSetting("CONF_PRODUCT_META_TITLE_MANDATORY")){ ?><span class="mandatory">*</span><? } ?></td>
                                            <td><?php echo $frm->getFieldHTML('prod_meta_title');?></td>
                                            </tr><tr>
                                            <td width="15%">Meta Tag Description</td>
                                            <td><?php echo $frm->getFieldHTML('prod_meta_description');?></td>
                                            </tr><tr>
                                            <td width="15%">Meta Tag Keywords</td>
                                            <td><?php echo $frm->getFieldHTML('prod_meta_keywords');?></td>
                                            </tr></table>											
                                        </div>
                                         
										<span class="togglehead" rel="tabs_4">Specifications</span>
                                        <div id="tabs_4" class="tabs_panel">
                                            <h4>Specification</h4>
											<table id="attribute" class="table_listing tbl-responsive">
											<thead>
											<tr>
											<th width="42%">Specification</th>
											<th width="42%">Text</th>
											<th></th>
											</tr>
											</thead>
											<tbody>
											<?php $attribute_row = 0; ?>
											<?php foreach ($attributes as $attribute) { ?>
											<tr id="attribute-row<?php echo $attribute_row; ?>">
											<td  style="width: 40%;">
                                            <span class="cellcaption">Specification</span>
                                            <input type="text" name="product_attribute[<?php echo $attribute_row; ?>][name]" value="<?php echo $attribute['name']; ?>" placeholder="Specification" class="form-control" />
											<input type="hidden" name="product_attribute[<?php echo $attribute_row; ?>][attribute_id]" value="<?php echo $attribute['id']; ?>" /></td>
											<td >
                                            <span class="cellcaption">Text</span>
											<textarea name="product_attribute[<?php echo $attribute_row; ?>][product_attribute_description]" rows="5" placeholder="Text" ><?php echo isset($attribute['text']) ? $attribute['text'] : ''; ?></textarea></td>
											<td ><ul class="actions"><li><a class="button red medium" onclick="$('#attribute-row<?php echo $attribute_row; ?>').remove();"  title="Remove"><i class="ion-minus icon"></i></a></li></ul></td>
											</tr>
											<?php $attribute_row++; ?>
											<?php } ?>
											</tbody>
											<tfoot>
											<tr>
											<td colspan="2"></td>
											<td ><ul class="actions"><li><a title="Add Attribute" onclick="addAttribute();" class="button medium blue"><i class="ion-plus-round icon"></i></a></li></ul></td></td>
											</tr>
											</tfoot>
											</table>                                            
                                        </div>
										<!--tab 4 end here-->                                
										<!--tab 5 start here-->
										<span class="togglehead" rel="tabs_5">Option</span>
                                        <div id="tabs_5" class="tabs_panel">
                                            <h4>Option</h4> 
											<table id="option" class="table tbl-responsive">
											<tr>
												<th width="20%" valign="top">
													<table width="100%" id="optionTable" class="tblBorderOptionsTop">
														<?php $option_row = 0; ?>
														<?php foreach ($product_options as $product_option) { ?>
														<tr <?php if ($option_row==count($product_options)-1):?> class="active" <?php endif;?>>
															<td width="10%"><a rel="#tab-option<?php echo $option_row?>" onclick="$('a[rel=\'#tab-option<?php echo $option_row?>\']').parent().parent().remove(); $('#tab-option<?php echo $option_row?>').remove(); $('#optionTable tr').removeClass('active'); $('#optionTable tr:first-child').addClass('active').trigger('classChange'); ">X</a></td>
														   
															<td onclick="$('#optionTable tr').removeClass('active'); $('#colTab<?php echo $option_row?>').parent().addClass('active').trigger('classChange');" id="colTab<?php echo $option_row?>" ><?php echo $product_option["name"]?></td></tr>
														<tr>
														<?php $option_row++; ?>
														<?php } ?>
														<tr><td colspan="2" >
														<input type="text" name="option" value="" placeholder="Option"  />
													   </td></tr>
													</table>   
												</th>    
												<th width="80%" valign="top" class="tab-content"><?php $option_row = 0; ?>
												<?php $option_value_row = 0; ?>
												<?php foreach ($product_options as $product_option) { ?>
												<div class="tab-pane" id="tab-option<?php echo $option_row; ?>">
												  <input type="hidden" name="product_option[<?php echo $option_row; ?>][product_option_id]" value="<?php echo $product_option['product_option_id']; ?>" />
												  <input type="hidden" name="product_option[<?php echo $option_row; ?>][name]" value="<?php echo $product_option['name']; ?>" />
												  <input type="hidden" name="product_option[<?php echo $option_row; ?>][option_id]" value="<?php echo $product_option['option_id']; ?>" />
												  <input type="hidden" name="product_option[<?php echo $option_row; ?>][type]" value="<?php echo $product_option['type']; ?>" />
												  <table width="100%" class="table tbl-responsive" id="optionValue">
													<tr>
													<td width="20%" for="input-required<?php echo $option_row; ?>">Required</td>
													<td>
													  <select name="product_option[<?php echo $option_row; ?>][required]" id="input-required<?php echo $option_row; ?>">
														<?php if ($product_option['required']) { ?>
														<option value="1" selected="selected">Yes</option>
														<option value="0">No</option>
														<?php } else { ?>
														<option value="1">Yes</option>
														<option value="0" selected="selected">No</option>
														<?php } ?>
													  </select>
													</td>
												  </tr>
												  <?php if ($product_option['type'] == 'text') { ?>
												  <tr>
													<td for="input-value<?php echo $option_row; ?>">Option Value</td>
													<td>
													  <input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="Option Value" id="input-value<?php echo $option_row; ?>" />
													</td>
												  </tr>
												  <?php } ?>
												  <?php if ($product_option['type'] == 'textarea') { ?>
												  <tr>
													<td for="input-value<?php echo $option_row; ?>">Option Value</td>
													<td>
													  <textarea name="product_option[<?php echo $option_row; ?>][value]" rows="5" placeholder="<?php echo $entry_option_value; ?>" id="input-value<?php echo $option_row; ?>"><?php echo $product_option['value']; ?></textarea>
													</td>
												  </tr>
												  <?php } ?>
												  <?php if ($product_option['type'] == 'file') { ?>
												  <tr>
													<td for="input-value<?php echo $option_row; ?>">Option Value</td>
													<td>
													  <input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="Option Value" id="input-value<?php echo $option_row; ?>" />
													</td>
												  </tr>
												  <?php } ?>
												  <?php if ($product_option['type'] == 'date') { ?>
												  <tr>
													<td for="input-value<?php echo $option_row; ?>">Option Value</td>
													<td>
														<input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="Option Value" id="input-value<?php echo $option_row; ?>"  />
													  </td>
												  </tr>
												  <?php } ?>
												  <?php if ($product_option['type'] == 'time') { ?>
												  <tr>
													<td for="input-value<?php echo $option_row; ?>">Option Value</td>
													<td>
														<input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="Option Value" id="input-value<?php echo $option_row; ?>"/>
													</td>
												  </tr>
												  <?php } ?>
												  <?php if ($product_option['type'] == 'datetime') { ?>
												  <tr>
													<td for="input-value<?php echo $option_row; ?>">Option Value</td>
													<td>
														<input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="Option Value" id="input-value<?php echo $option_row; ?>" />
													</td>
												  </tr>
												  <?php } ?>
                                                  <?php if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') { ?>
                                                  <tr><td colspan="2">
												  <div class="table-responsive">
													<table id="option-value<?php echo $option_row; ?>" class="table table-striped table-bordered table-hover tbl-responsive">
													  <thead>
														<tr>
														  <td width="20%">Option Value</td>
														  <td width="15%">Quantity</td>
														  <td width="15%">Subtract</td>
														  <td width="20%">Price</td>
														  <td width="20%">Weight</td>
														  <td></td>
														</tr>
													  </thead>
													  <tbody>
														<?php 
														foreach ($product_option['product_option_value'] as $product_option_value) { ?>
														<tr id="option-value-row<?php echo $option_value_row; ?>">
														  <td >
                                                          <span class="cellcaption">Option Value</span>
                                                          <select name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][option_value_id]" class="form-control">
															  <?php if (isset($option_values[$product_option['option_id']])) { ?>
															  <?php foreach ($option_values[$product_option['option_id']] as $option_value) { ?>
															  <?php if ($option_value['option_value_id'] == $product_option_value['option_value_id']) { ?>
															  <option value="<?php echo $option_value['option_value_id']; ?>" selected="selected"><?php echo $option_value['option_value_name']; ?></option>
															  <?php } else { ?>
															  <option value="<?php echo $option_value['option_value_id']; ?>"><?php echo $option_value['option_value_name']; ?></option>
															  <?php } ?>
															  <?php } ?>
															  <?php } ?>
															</select>
															<input type="hidden" name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][product_option_value_id]" value="<?php echo $product_option_value['product_option_value_id']; ?>" /></td>
														  <td class="text-right">
                                                          <span class="cellcaption">Quantity</span>
                                                          <input type="text" name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][quantity]" value="<?php echo $product_option_value['quantity']; ?>" placeholder="<?php echo $entry_quantity; ?>" class="form-control" /></td>
														  <td >
                                                          <span class="cellcaption">Subtract</span>
                                                          <select name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][subtract]" class="form-control">
															  <?php if ($product_option_value['subtract']) { ?>
															  <option value="1" selected="selected">Yes</option>
															  <option value="0">No</option>
															  <?php } else { ?>
															  <option value="1">Yes</option>
															  <option value="0" selected="selected">No</option>
															  <?php } ?>
															</select></td>
														  <td class="text-right">
                                                          <span class="cellcaption">Price</span>
                                                          <select name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][price_prefix]" class="fieldSmall">
															  <?php if ($product_option_value['price_prefix'] == '+') { ?>
															  <option value="+" selected="selected">+</option>
															  <?php } else { ?>
															  <option value="+">+</option>
															  <?php } ?>
															  <?php if ($product_option_value['price_prefix'] == '-') { ?>
															  <option value="-" selected="selected">-</option>
															  <?php } else { ?>
															  <option value="-">-</option>
															  <?php } ?>
															</select>
															<input type="text" name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][price]" value="<?php echo $product_option_value['price']; ?>" placeholder="<?php echo $entry_price; ?>" class="fieldNormal" /></td>
														  
														  <td class="text-right">
                                                          <span class="cellcaption">Weight</span>
                                                          <select name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][weight_prefix]" class="fieldSmall">
															  <?php if ($product_option_value['weight_prefix'] == '+') { ?>
															  <option value="+" selected="selected">+</option>
															  <?php } else { ?>
															  <option value="+">+</option>
															  <?php } ?>
															  <?php if ($product_option_value['weight_prefix'] == '-') { ?>
															  <option value="-" selected="selected">-</option>
															  <?php } else { ?>
															  <option value="-">-</option>
															  <?php } ?>
															</select>
															<input type="text" name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][weight]" value="<?php echo $product_option_value['weight']; ?>" placeholder="<?php echo $entry_weight; ?>" class="fieldNormal" /></td>
														  <td ><ul class="actions"><li><a onclick="$('#option-value-row<?php echo $option_value_row; ?>').remove();" class="button small red" title="Remove"><i class="ion-minus icon"></i></a></li></ul>
														  <!--$(\'#option-value-row' + option_value_row + '\').remove();-->
															</td>
														</tr>
														<?php $option_value_row++; ?>
														<?php } ?>
													  </tbody>
													  <tfoot>
														<tr>
														  <td colspan="5"></td>
														  <td><ul class="actions"><li><a onclick="addOptionValue('<?php echo $option_row?>');" class="button small green"><i class="ion-plus-round icon"></i></a></li></ul></td>
														</tr>
													  </tfoot>
													</table>
												  </div>
												  <select id="option-values<?php echo $option_row; ?>" style="display: none;">
													<?php if (isset($option_values[$product_option['option_id']])) { ?>
													<?php foreach ($option_values[$product_option['option_id']] as $option_value) { ?>
													<option value="<?php echo $option_value['option_value_id']; ?>"><?php echo $option_value['option_value_name']; ?></option>
													<?php } ?>
													<?php } ?>
												  </select>
												  <?php } ?>
                                                  </td></tr>
                                                  
												  </table>
												  
												</div>
																					<?php $option_row++; ?>
																					<?php } ?>
																	</th>
																</tr>
																</thead>
																
																
																</table>	
                                        </div>
										<!--tab 5 end here-->
										
										 <!--tab 7 start here-->
										<span class="togglehead" rel="tabs_7">Discount</span>
                                        <div id="tabs_7" class="tabs_panel">
                                            <h4>Discount</h4>
											<table id="discount" class="table tbl-responsive">
											<thead>
											<tr>
											<th width="16%">Quantity </th>
											<th width="16%">Priority</th>
											<th width="16%">Discounted Price [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
											<th width="16%">Date Start</th>
											<th width="17%">Date End</th>
											<th></th>
											</tr>
											</thead>
											<tbody>
											<?php $discount_row = 0; ?>
											<?php foreach ($discount_rates as $discount) { ?>
											<tr id="discount-row<?php echo $discount_row; ?>">
											<td>
                                            <span class="cellcaption">Quantity</span>
                                            <input data-fld="quantity" type="text" name="product_discount[<?php echo $discount_row; ?>][quantity]" value="<?php echo $discount["pdiscount_qty"]?>" placeholder="Quantity" title="Quantity" /></td>
											<td>
                                            <span class="cellcaption">Priority</span>
                                            <input type="text" name="product_discount[<?php echo $discount_row; ?>][priority]" value="<?php echo $discount["pdiscount_priority"]?>" placeholder="Priority" /></td>
											<td>
                                            <span class="cellcaption">Discounted Price [<?php echo CONF_CURRENCY_SYMBOL?>]</span>
                                            <input type="text" name="product_discount[<?php echo $discount_row; ?>][price]" value="<?php echo $discount["pdiscount_price"]?>" placeholder="Discounted Price" /></td>
											<td>
                                            <span class="cellcaption">Date Start</span>
                                            <input type="text" name="product_discount[<?php echo $discount_row; ?>][start_date]" class="date-pick" value="<?php echo $discount["pdiscount_start_date"]?>" placeholder="Start Date" readonly="readonly" /></td>
											<td>
                                            <span class="cellcaption">Date End</span>
                                            <input type="text" name="product_discount[<?php echo $discount_row; ?>][end_date]" class="date-pick" value="<?php echo $discount["pdiscount_end_date"]?>" placeholder="End Date" readonly="readonly" /></td>
											<td><ul class="actions"><li><a class="button red medium" onclick="deleteDiscountRow('<?php echo $discount_row; ?>');" title="Remove"><i class="ion-minus icon"></i></a></li></ul></td>
											</tr>
											<?php $discount_row++; ?>
											<?php } ?>
											</tbody>
											<tfoot>
											<tr>
											<td colspan="5"></td>
											<td ><ul class="actions"><li><a onclick="addDiscount();" class="button medium blue" title="Discount"><i class="ion-plus-round icon"></i></a></li></ul></td>
											</tr>
											</tfoot>
											</table>                                            
                                        </div>
										<!--tab 7 end here-->
										<!--tab 8 start here-->
										<span class="togglehead" rel="tabs_8">Special Discount</span>
                                        <div id="tabs_8" class="tabs_panel">
                                            <h4>Special Discount</h4> 
											<table id="special" class="table tbl-responsive">
											<thead>
											<tr>
											<th width="20%">Priority</th>
											<th width="20%">Special Price [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
											<th width="20%">Date Start</th>
											<th width="20%">Date End</th>
											<th></th>
											</tr>
											</thead>
											<tbody>
											<?php $special_row = 0; ?>
											<?php foreach ($special_rates as $special) { ?>
											<tr id="special-row<?php echo $special_row; ?>">
											<td>
                                            <span class="cellcaption">Priority</span>
                                            <input type="text" name="product_special[<?php echo $special_row; ?>][priority]" value="<?php echo $special["pspecial_priority"]?>" placeholder="Priority" /></td>
											<td>
                                            <span class="cellcaption">Special Price [<?php echo CONF_CURRENCY_SYMBOL?>]</span>
                                            <input type="text" name="product_special[<?php echo $special_row; ?>][price]" value="<?php echo $special["pspecial_price"]?>" placeholder="Special Price" /></td>
											<td>
                                            <span class="cellcaption">Date Start</span>
                                            <input type="text" name="product_special[<?php echo $special_row; ?>][start_date]" class="date-pick" value="<?php echo $special["pspecial_start_date"]?>" placeholder="Start Date" readonly="readonly" /></td>
											<td>
                                            <span class="cellcaption">Date End</span>
                                            <input type="text" name="product_special[<?php echo $special_row; ?>][end_date]" class="date-pick" value="<?php echo $special["pspecial_end_date"]?>" placeholder="End Date" readonly="readonly" /></td>
											<td><ul class="actions"><li><a class="button red medium" onclick="$('#special-row<?php echo $special_row; ?>').remove();"  title="Remove"><i class="ion-minus icon"></i></a></li></ul></td>
											</tr>
											<?php $special_row++; ?>
											<?php } ?>
											</tbody>
											<tfoot>
											<tr>
											<td colspan="4"></td>
											<td ><ul class="actions"><li><a onclick="addSpecial();" class="button medium blue" title="Special"><i class="ion-plus-round icon"></i></a></li></ul></td>
											</tr>
											</tfoot>
											</table>											
                                        </div>
                                        <?php if (Settings::getSetting("CONF_ENABLE_DIGITAL_PRODUCTS")) {?>
                                        <span class="togglehead" rel="tabs_9">Downloads</span>
                                        <div id="tabs_9" class="tabs_panel">
                                            <h4>Downloads</h4>
											<table id="download" class="table_listing tbl-responsive">
											<thead>
											<tr>
											<th width="25%">Download Name</th>
											<th width="15%">Filename</th>
                                            <th width="25%">Max Download Times</th>
                                            <th width="25%">Validity (days)</th>
											<th></th>
											</tr>
											</thead>
											<tbody>
											<?php $download_row = 0; ?>
											<?php foreach ($downloads as $download) { $download_row = $download['pfile_id']; ?>
											<tr id="download-row<?php echo $download_row; ?>">
											<td>
                                            <span class="cellcaption">Download Name</span>
                                            <input type="hidden" name="product_download[<?php echo $download_row; ?>][pfile_id]" value="<?php echo $download['pfile_id']; ?>"  /><input type="text" name="product_download[<?php echo $download_row; ?>][download_name]" value="<?php echo $download['pfile_download_name']; ?>" placeholder="Download Name" class="form-control" data-fld="required" />
											</td>
											<td >
                                            <span class="cellcaption">Filename</span>
											<?php echo $download['pfile_name']; ?></td>
                                            <td >
                                            <span class="cellcaption">Max Download Times</span>
											<input type="text" name="product_download[<?php echo $download_row; ?>][max_download_times]" value="<?php echo $download['pfile_max_download_times']; ?>" placeholder="Max Download Times" class="form-control" data-fld="requiredint" /><small>-1 for Unlimited</small></td>
                                            
                                            <td >
                                            <span class="cellcaption">Validity (days)</span>
											<input type="text" name="product_download[<?php echo $download_row; ?>][validity]" value="<?php echo $download['pfile_can_be_downloaded_within_days']; ?>" placeholder="Validity (days)" class="form-control" data-fld="requiredint" /><small>-1 for Unlimited</small></td>
                                            
											<td ><ul class="actions"><li><a class="button red medium" onclick="deleteDownloadRow('<?php echo $download_row; ?>');" title="Remove"><i class="ion-minus icon"></i></a></li></ul></td>
											</tr>
											<?php $download_row++; ?>
											<?php } ?>
											</tbody>
											<tfoot>
											<tr>
											<td colspan="4"></td>
											<td ><ul class="actions"><li><a title="Add Download" onclick="addDownload();" class="button medium blue"><i class="ion-plus-round icon"></i></a></li></ul></td></td>
											</tr>
											</tfoot>
											</table>                                            
                                        </div>
                                        <?php } ?>
										<!--tab 9 end here-->
										<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
											<tr>
												<td width="15%">&nbsp;</td>
												<td><?php echo $frm->getFieldHTML('btn_submit');?></td>
												</tr><tr>
										</table> 
										<?php echo $frm->getFieldHTML('prod_id');?><?php echo $frm->getFieldHTML('prod_brand');?>
										<?php echo $frm->getFieldHTML('prod_shop');?><?php //echo $frm->getFieldHTML('prod_category');?>
										<?php echo $frm->getFieldHTML('prod_shipping_country');?>
                                        <?php echo $frm->getFieldHTML('prod_tab');?>
										<?php echo $frm->getExternalJS();?>
                                  </div>      
								</form>
                        </div>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				
<script type="text/javascript">
$(document).ready(function(){
		$(document).trigger('classChange');
});
</script>    
   
<script type="text/javascript">
$("#prod_requires_shipping").bind("change", function() { 
	var elem=$(".shipping_required");
	if (this.value==1){		
		elem.find('input').attr('disabled', false);
		elem.find("td,th").css('color','');
		elem.find('input').css('background-color', '');
		elem.find('a').removeClass('disabled');
	}
	else{
		elem.find('input').attr('disabled', true);
		elem.find("td,th").css('color','#ccc');
		elem.find('input').css('background-color', '#ccc');
		elem.find('a').addClass('disabled');
	}
});
$("#prod_requires_shipping").trigger("change");

$("#prod_track_inventory").bind("change", function() {
var elem=$(this).parent().parent().next("tr");
if (this.value==1){	
	elem.find('input').attr('disabled', false);
	elem.find("td").css('color','');
	elem.find('input').css('background-color', '');
}
else{
	elem.find('input').attr('disabled', true);
	elem.find("td").css('color','#ccc');
	elem.find('input').css('background-color', '#ccc');
	
}
});
$("#prod_track_inventory").trigger("change");
$("#prod_ship_free").bind("change", function() {
	var elem=$(this).parent().parent().next("tr");
	if ($(this).is(":checked")){
			//elem.find('input').attr('disabled', true);
			/*elem.find("td").css('color','#ccc');
			elem.find('input').css('background-color', '#ccc');*/
	}else{
			//elem.find('input').attr('disabled', false);
			/*elem.find("td").css('color','');
			elem.find('input').css('background-color', '');*/
	}
});
$("#prod_ship_free").trigger("change");


$("#prod_type").bind("change", function() {
	var elem=$(".type_required");
	
	if (this.value==1){
		elem.show();
		<?php  if (Settings::getSetting("CONF_SHIPSTATION_API_STATUS")) {?>		
		ProductfrmValidator_requirements['prod_length'] = {"required":true};
		ProductfrmValidator_requirements['prod_width'] = {"required":true};
		ProductfrmValidator_requirements['prod_height'] = {"required":true};
		ProductfrmValidator_requirements['prod_weight'] = {"required":true};
		<?php } ?>
		ProductfrmValidator.resetFields();
	}else{
		elem.hide();
		ProductfrmValidator_requirements['prod_length'] = {"required":false};
		ProductfrmValidator_requirements['prod_width'] = {"required":false};
		ProductfrmValidator_requirements['prod_height'] = {"required":false};
		ProductfrmValidator_requirements['prod_weight'] = {"required":false};
	}
	ProductfrmValidator.resetFields();
	
	
});
$("#prod_type").trigger("change");

</script>
<script type="text/javascript"><!--
var attribute_row = <?php echo $attribute_row; ?>;
function addAttribute() {
    html  = '<tr id="attribute-row' + attribute_row + '">';
	html += '  <td><span class="cellcaption">Specification</span><input type="text" name="product_attribute[' + attribute_row + '][name]" value="" placeholder="Specification" /><input type="hidden" name="product_attribute[' + attribute_row + '][attribute_id]" value="" /></td>';
	html += '  <td><span class="cellcaption">Text</span>';
	html += '<textarea name="product_attribute[' + attribute_row + '][product_attribute_description]" rows="5" placeholder="Text"></textarea>';
	html += '  </td>';
	html += '  <td><ul class="actions"><li><a class="button medium red" onclick="$(\'#attribute-row' + attribute_row + '\').remove();" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	$('#attribute tbody').append(html);
	attributeautocomplete(attribute_row);
	attribute_row++;
}
function attributeautocomplete(attribute_row) {
	$('input[name=\'product_attribute[' + attribute_row + '][name]\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: generateUrl('attributes', 'autocomplete'),
				data: {keyword: encodeURIComponent(request) },
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							category: item.attribute_group,
							label: item.name,
							value: item.attribute_id
						}
					}));
				}
			});
		},
		'select': function(item) { 
			$('input[name=\'product_attribute[' + attribute_row + '][name]\']').val(item['label']);
			$('input[name=\'product_attribute[' + attribute_row + '][attribute_id]\']').val(item['value']);
		}
	});
}
$('#attribute tbody tr').each(function(index, element) {
	attributeautocomplete(index);
});
</script> 
<script type="text/javascript"><!--
var shipping_row = <?php echo $shipping_row; ?>;
function addShipping() {
	//alert($("#prod_requires_shipping").val());
	if (($("#prod_requires_shipping").val()==0)) return;
	
    html  = '<tr id="shipping-row' + shipping_row + '">';
	html += '  <td><span class="cellcaption">Ship to</span><input type="text" name="product_shipping[' + shipping_row + '][country_name]" value="" placeholder="Shipping" autocomplete="off" /><input type="hidden" name="product_shipping[' + shipping_row + '][country_id]" value="" /></td>';
	html += '  <td><span class="cellcaption">Company</span><input type="text" name="product_shipping[' + shipping_row + '][company_name]" value="" placeholder="Company" autocomplete="off" /><input type="hidden" name="product_shipping[' + shipping_row + '][company_id]" value="" /></td>';
	html += '  <td><span class="cellcaption">Processing Time</span><input type="text" name="product_shipping[' + shipping_row + '][processing_time]" value="" placeholder="Processing Time" autocomplete="off" /><input type="hidden" name="product_shipping[' + shipping_row + '][processing_time_id]" value="" /></td>';
	html += '  <td><span class="cellcaption">Cost</span>';
	html += '<input type="text" name="product_shipping[' + shipping_row + '][cost]" value="" placeholder="Cost" />';
	html += '</td>';
	html += '<td><span class="cellcaption">Additional Unit Cost</span>';
	html += '<input type="text" name="product_shipping[' + shipping_row + '][additional_cost]" value="" placeholder="Additional Unit Cost" />';
	html += '</td>';
	html += '  <td><ul class="actions"><li><a class="button medium red" onclick="$(\'#shipping-row' + shipping_row + '\').remove();" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	$('#shipping tbody').append(html);
	shippingautocomplete(shipping_row);
	shipping_row++;
}
function shippingautocomplete(shipping_row) {
	
	$('input[name=\'product_shipping[' + shipping_row + '][country_name]\']').focusout(function() {
			setTimeout(function(){ $('.autocomplete-suggestions').hide(); }, 1000); 
	});
	
	$('input[name=\'product_shipping[' + shipping_row + '][company_name]\']').focusout(function() {
		    setTimeout(function(){ $('.autocomplete-suggestions').hide(); }, 1000); 
	});
	
	$('input[name=\'product_shipping[' + shipping_row + '][processing_time]\']').focusout(function() {
		    setTimeout(function(){ $('.autocomplete-suggestions').hide(); }, 1000); 
	});
	
	$('input[name=\'product_shipping[' + shipping_row + '][country_name]\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'countries_autocomplete','',webroot),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { 
						json.suggestions.unshift({
							data: -1,
							value: '<?php echo Utilities::getLabel('L_Everywhere_Else')?>'
						});
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'product_shipping[' + shipping_row + '][country_name]\']').val(suggestion.value);
				$('input[name=\'product_shipping[' + shipping_row + '][country_id]\']').val(suggestion.data);
    	 }
	});
	
	$('input[name=\'product_shipping[' + shipping_row + '][company_name]\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'shipping_autocomplete','',webroot),
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
				$('input[name=\'product_shipping[' + shipping_row + '][company_name]\']').val(suggestion.value);
				$('input[name=\'product_shipping[' + shipping_row + '][company_id]\']').val(suggestion.data);
    	 }
	});
	
	$('input[name=\'product_shipping[' + shipping_row + '][processing_time]\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'shippingduration_autocomplete','',webroot),
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
				$('input[name=\'product_shipping[' + shipping_row + '][processing_time]\']').val(suggestion.value);
				$('input[name=\'product_shipping[' + shipping_row + '][processing_time_id]\']').val(suggestion.data);
    	 }
	});
	
}
$('#shipping tbody tr').each(function(index, element) {
	shippingautocomplete(index);
});
</script> 
<script type="text/javascript"><!--
var discount_row = <?php echo $discount_row; ?>;
function addDiscount() {
    html  = '<tr id="discount-row' + discount_row + '">';
	html += '<td><span class="cellcaption">Quantity</span><input data-fld="quantity" type="text" name="product_discount[' + discount_row + '][quantity]" value="" placeholder="Quantity" title="Quantity" /></td>';
	html += '<td><span class="cellcaption">Priority</span><input type="text" name="product_discount[' + discount_row + '][priority]" value="" placeholder="Priority" /></td>';
	html += '<td><span class="cellcaption">Discounted Price</span><input type="text" name="product_discount[' + discount_row + '][price]" value="" placeholder="Discounted Price" /></td>';
	html += '<td><span class="cellcaption">Start Date</span><input data-fld="startdate" type="text" name="product_discount[' + discount_row + '][start_date]" value="" readonly="readonly" class="date-pick" placeholder="Start Date" title="Start Date" /></td>';
	html += '<td><span class="cellcaption">End Date</span><input data-fld="enddate" type="text" name="product_discount[' + discount_row + '][end_date]" value="" readonly="readonly" class="date-pick" placeholder="End Date" title="End Date" /></td>';
	html += '  <td><ul class="actions"><li><a class="button medium red" title="Remove" onclick="deleteDiscountRow('+discount_row+');" ><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	//ProductfrmValidator_requirements['product_discount[' + discount_row + '][end_date]'] = {"required":false,"comparewith":[{"fldname":'product_discount[' + discount_row + '][end_date]',"operator":"ge","dateFormat":"%Y-%m-%d"}]};
			
	var appended = $('#discount tbody').append(html);
		$(appended).find(':input').each(function(){	
			var name = $(this).attr('name');
			if($(this).attr('data-fld')=="quantity" && $(this).attr('data-fld')!=''){
					ProductfrmValidator_requirements[name] = {"required":true,"integer":true,"range":{"minval":2,"maxval":999,"numeric":true}};
			} 
		});
	ProductfrmValidator.resetFields();
	discount_row++;
	ResetDateCalendar();
}

$(document).ready(function(){
	$("#discount").find(':input').each(function(){
			var name = $(this).attr('name');
			if($(this).attr('data-fld')!=undefined && $(this).attr('data-fld')!=''){
					ProductfrmValidator_requirements[name] = {"required":true,"integer":true,"range":{"minval":2,"maxval":999,"numeric":true}};
			} 
			
		});
	ProductfrmValidator.resetFields();
});


function deleteDiscountRow(discount_row){
	$('#discount-row' + discount_row ).remove();
	delete ProductfrmValidator_requirements['product_discount[' + discount_row + '][quantity]'];
	ProductfrmValidator.resetFields();
}

var download_row = <?php echo $download_row; ?>;
function addDownload() {
    html  = '<tr id="download-row' + download_row + '">';
	html += '<td><span class="cellcaption">Download Name</span><input data-fld="required" type="text" name="product_download[' + download_row + '][download_name]" value="" placeholder="Download Name" title="Download Name" /></td>';
	html += '<td><span class="cellcaption">Filename</span><input data-fld="required" lang="' + download_row + '" id="file_upload' + download_row + '" type="file" name="product_download[' + download_row + '][file]" value="" title="Filename" class="downloadFile" /><input id="file_selected' + download_row + '" type="hidden" name="product_download[' + download_row + '][filename]"  /><div id="progress-wrp' + download_row + '" class="progress-wrapper hide"><div class="progress-bar"></div ><div class="status"></div></div><div id="progress-output' + download_row + '"></div></td>';
	html += '<td><span class="cellcaption">Max Download Times</span><input data-fld="requiredint"  type="text" name="product_download[' + download_row + '][max_download_times]" value="" placeholder="Max Download Times" title="Max Download Times" /><small>-1 for Unlimited</small></td>';
	html += '<td><span class="cellcaption">Validity (Days)</span><input data-fld="requiredint"  type="text" name="product_download[' + download_row + '][validity]" value="" placeholder="Validity (Days)" title="Validity (Days)" /><small>-1 for Unlimited</small></td>';
	html += '  <td><ul class="actions"><li><a class="button medium red" title="Remove" onclick="deleteDownloadRow('+download_row+');" ><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	var appended = $('#download tbody').append(html);
		$(appended).find(':input').each(function(){	
			var name = $(this).attr('name');
			if($(this).attr('data-fld')=="required"){
					ProductfrmValidator_requirements[name] = {"required":true};
			}else if($(this).attr('data-fld')=="requiredint"){
					ProductfrmValidator_requirements[name] = {"required":true,"integer":true};
			} 
		});
	ProductfrmValidator.resetFields();
	
	
	download_row++;
	
}

$(document).ready(function(){
	$("#download").find(':input').each(function(){
			var name = $(this).attr('name');
			if($(this).attr('data-fld')=="required"){
					ProductfrmValidator_requirements[name] = {"required":true};
			}else if($(this).attr('data-fld')=="requiredint"){
					ProductfrmValidator_requirements[name] = {"required":true,"integer":true};
			} 
			
		});
	ProductfrmValidator.resetFields();
});


function deleteDownloadRow(download_row){
			if(confirm("Sure you want to remove this item ?")){
			
			var data = "id="+download_row;
			var href=generateUrl('products', 'remove_download_record',[]);
	       	callAjax(href, data, function(response){ 
			    var ans = parseJsonData(response);
				if (ans.status==1){
					$('#download-row' + download_row ).remove();
					delete ProductfrmValidator_requirements['product_download[' + download_row + '][name]'];
					delete ProductfrmValidator_requirements['product_download[' + download_row + '][filename]'];
					delete ProductfrmValidator_requirements['product_download[' + download_row + '][download_name]'];
					delete ProductfrmValidator_requirements['product_download[' + download_row + '][validity]'];
					ProductfrmValidator.resetFields();
				}else{
					ShowJsSystemMessage(ans.msg);
				}
			})
		}
	
		
}



$(document).on('change', 'input.downloadFile[type=file]', function(event){
	$('#progress-wrp'+$(this).attr('lang')).removeClass('hide');  
	submitDownloadFileForm(event,$(this).attr('lang'));
})

<?php 
$valid_extensions='';
$valid_mime_types = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_DIGITAL_FILE_EXT_ALLOWED"));
$valid_arr = explode("\n", $valid_mime_types);
foreach($valid_arr as $vkey=>$vval){
	$valid_extensions .= "'".$vval."',";
}
?>
//configuration

function submitDownloadFileForm(e,download_row){
	
	var max_file_size 			= <?php echo Settings::getSetting("CONF_DIGITAL_MAX_FILE_SIZE")?>; //allowed file size. (1 MB = 1048576)
	var allowed_file_types 		= [<?php echo $valid_extensions;?>]; //allowed file types
	var progress_bar_id 		= '#progress-wrp'+download_row; //ID of an element for response output
	var hidden_file_id 			= '#file_selected'+download_row; //ID of an element for response output
	
	
	var files = e.target.files;
	
	var data = new FormData();
	$.each(files, function(key, value){
		data.append(key, value);
	});
	
	var proceed = true; //set proceed flag
	var error = [];	//errors
	var total_files_size = 0;
	
	$(progress_bar_id +" .progress-bar").css("width", "0%");
	$(progress_bar_id + " .status").text("0%");
	
	$(progress_bar_id).parent().find('ul.errorlist').remove();
	
	if(!window.File && window.FileReader && window.FileList && window.Blob){ //if browser doesn't supports File API
		error.push("Your browser does not support new File API! Please upgrade."); //push error text
	}else{
		//var total_selected_files = $('#file_upload' + download_row).files.length; //number of files
		var total_selected_files = document.getElementById("file_upload"+download_row).files.length
		
		
		 //iterate files in file input field
		$(document.getElementById("file_upload"+download_row).files).each(function(i, ifile){
			var ext = ifile.name.split('.').pop();
			if(ifile.value !== ""){ //continue only if file(s) are selected
				if(allowed_file_types.toString().toLowerCase().indexOf(ext.toString().toLowerCase()) === -1){ //check unsupported file
					error.push( "<b>"+ ifile.name + "</b> is unsupported file type! Please upload file with "+allowed_file_types+ ' Extension(s).'); //push error text
					proceed = false; //set proceed flag to false
					$("#file_upload"+download_row).val(null);
				}
				total_files_size = total_files_size + ifile.size; //add file size to total size
			}
		});
		
		
		//if total file size is greater than max file size
		if(total_files_size > max_file_size){
			max_file_size = (max_file_size / (1024*1024)).toFixed(2);
			total_files_size = (total_files_size / (1024*1024)).toFixed(2);
			error.push( "You have selected "+total_selected_files+" file(s) with total size "+total_files_size+" MB, Allowed size is " + max_file_size +" MB, Try smaller file!"); //push error text
			proceed = false; //set proceed flag to false
			$("#file_upload"+download_row).val(null);
		}
		
		
		
		//if everything looks good, proceed with jQuery Ajax
		if(proceed){
			
			
			var post_url = generateUrl('products', 'uploadProductDownloads',[],webroot)+'?files'; //get action URL of form
			//jQuery Ajax to Post form data
			$.ajax({
				url : post_url,
				type: "POST",
				data : data,
				contentType: false,
				cache: false,
				processData:false,
				xhr: function(){
					//upload Progress
					var xhr = $.ajaxSettings.xhr();
					if (xhr.upload) {
						xhr.upload.addEventListener('progress', function(event) {
							var percent = 0;
							var position = event.loaded || event.position;
							var total = event.total;
							if (event.lengthComputable) {
								percent = Math.ceil(position / total * 100);
							}
							//update progressbar
							$(progress_bar_id +" .progress-bar").css("width", + percent +"%");
							$(progress_bar_id + " .status").text(percent +"%");
						}, true);
					}
					return xhr;
				},
				mimeType:"multipart/form-data"
			}).done(function(res){
				var ans = parseJsonData(res);
				ShowJsSystemMessage(ans.msg);
				$(hidden_file_id).val(ans.file);
			});
			
		
			
		}else{
			
		}
		result_output = '#progress-output'+download_row
		$(result_output).html(""); //reset output 
		$(error).each(function(i){ //output any error to output element
			$(result_output).append('<div class="error">'+error[i]+"</div>");
		});
		
	}

	//alert(total_files_size);
}

function ResetDateCalendar(){
	$('.date-pick').datetimepicker({timepicker:false,format:'Y-m-d',formatDate:'Y-m-d',closeOnDateSelect:true,scrollMonth:false,scrollInput: false});
}
ResetDateCalendar();
</script> 
<script type="text/javascript"><!--
var special_row = <?php echo $special_row; ?>;
function addSpecial() {
    html  = '<tr id="special-row' + special_row + '">';
	html += '<td><span class="cellcaption">Priority</span><input type="text" name="product_special[' + special_row + '][priority]" value="" placeholder="Priority" /></td>';
	html += '<td><span class="cellcaption">Special Price</span><input type="text" name="product_special[' + special_row + '][price]" value="" placeholder="Special Price" /></td>';
	html += '<td><span class="cellcaption">Start Date</span><input type="text" name="product_special[' + special_row + '][start_date]" value="" class="date-pick" readonly="readonly" placeholder="Start Date" /></td>';
	html += '<td><span class="cellcaption">End Date</span><input type="text" name="product_special[' + special_row + '][end_date]" value="" class="date-pick" readonly="readonly" placeholder="End Date" /></td>';
	html += '  <td><ul class="actions"><li><a class="button medium red" onclick="$(\'#special-row' + special_row + '\').remove();" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	$('#special tbody').append(html);
	special_row++;
	ResetDateCalendar();
}
</script> 
<script type="text/javascript"><!--	
var option_row = <?php echo $option_row; ?>;
$('input[name=\'option\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: generateUrl('options', 'autocomplete'),
			data: {keyword: encodeURIComponent(request) },
			dataType: 'json',
			type: 'post',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						category: item['category'],
						label: item['name'],
						value: item['option_id'],
						type: item['type'],
						option_value: item['option_value']
					}
				}));
			}
		});
	},
	'select': function(item) {
		html  = '<div class="tab-pane" id="tab-option' + option_row + '">';
		html += '	<input type="hidden" name="product_option[' + option_row + '][product_option_id]" value="" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][name]" value="' + item['label'] + '" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][option_id]" value="' + item['value'] + '" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][type]" value="' + item['type'] + '" />';
		
		html += '	<table width="100%" class="table tbl-responsive" id="optionValue">';
		html += '	  <td width="20%" for="input-required' + option_row + '">Required</td>';
		html += '	  <td><select name="product_option[' + option_row + '][required]" id="input-required' + option_row + '">';
		html += '	      <option value="1">Yes</option>';
		html += '	      <option value="0">No</option>';
		html += '	  </select></td>';
		html += '	</tr>';
		if (item['type'] == 'text') {
			html += '<tr>';
			html += '  <td for="input-value' + option_row + '">Option Value</td>';
			html += '  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="Option Value" id="input-value' + option_row + '"  /></td>';
			html += '</tr>';
		}
		
		if (item['type'] == 'textarea') {
			html += '	<tr>';
			html += '	  <td for="input-value' + option_row + '">Option Value</td>';
			html += '	  <td><textarea name="product_option[' + option_row + '][value]" rows="5" placeholder="Option Value" id="input-value' + option_row + '"></textarea></td>';
			html += '	</tr>';			
		}
		 
		if (item['type'] == 'file') {
			/*html += '	<tr>';
			html += '	  <td for="input-value' + option_row + '">Option Value</td>';
			html += '	  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="Option Value" id="input-value' + option_row + '" /></td>';
			html += '	</tr>';*/
		}
						
		if (item['type'] == 'date') {
			html += '	<tr>';
			html += '	  <td for="input-value' + option_row + '">Option Value</td>';
			html += '	  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="Option Value" id="input-value' + option_row + '" class="date"/></td>';
			html += '	</tr>';
		}
		
		if (item['type'] == 'time') {
			html += '	<tr>';
			html += '	  <td for="input-value' + option_row + '">Option Value</td>';
			html += '	  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="Option Value"  id="input-value' + option_row + '" class="time"  /></td>';
			html += '	</tr>';
		}
				
		if (item['type'] == 'datetime') {
			html += '	<tr>';
			html += '	  <td for="input-value' + option_row + '">Option Value</td>';
			html += '	  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="Option Value"  id="input-value' + option_row + '" class="datetime" /></td>';
			html += '	</tr>';
		}
			
		if (item['type'] == 'select' || item['type'] == 'radio' || item['type'] == 'checkbox' || item['type'] == 'image') {
			html += '<tr><td colspan="2">';
			html += '  <table id="option-value' + option_row + '" class="table table-striped table-bordered table-hover tbl-responsive">';
			html += '  	 <thead>'; 
			html += '      <tr>';
			html += '        <td width="20%">Option Value</td>';
			html += '        <td width="15%">Quantity</td>';
			html += '        <td width="15%">Subtract</td>';
			html += '        <td width="20%">Price</td>';
			html += '        <td width="20%">Weight</td>';
			html += '        <td></td>';
			html += '      </tr>';
			html += '  	 </thead>';
			html += '  	 <tbody>';
			html += '    </tbody>';
			html += '    <tfoot>';
			html += '      <tr>';
			html += '        <td colspan="5"></td>';
			html += '   <td><ul class="actions"><li><a onclick="addOptionValue(' + option_row + ');" class="button small green" title="Add"><i class="ion-plus-round icon"></i></a></li></ul></td>'
			html += '      </tr>';
			html += '    </tfoot>';
			html += '  </table>';
			html += '</td></tr>';
			
            html += '  <select id="option-values' + option_row + '" style="display: none;">';
            for (i = 0; i < item['option_value'].length; i++) {
				html += '  <option value="' + item['option_value'][i]['option_value_id'] + '">' + item['option_value'][i]['name'] + '</option>';
            }
            html += '  </select>';	
			html += '</div>';	
		}
		html += '	</table>';
		
		$('#option .tab-content:first').append(html);
	
		
		$('#optionTable tr').removeClass('active');
		$('#optionTable tr:last-child').before('<tr class="active"><td width="10%"><a rel="#tab-option' + option_row + '" onclick="$(\'a[rel=\\\'#tab-option' + option_row + '\\\']\').parent().parent().remove(); $(\'#tab-option' + option_row + '\').remove(); $(\'#optionTable tr\').removeClass(\'active\'); $(\'#optionTable tr:first-child\').addClass(\'active\').trigger(\'classChange\');">X</a></td><td onclick="$(\'#optionTable tr\').removeClass(\'active\'); $(\'#colTab'+option_row+'\').parent().addClass(\'active\').trigger(\'classChange\');" id="colTab' + option_row + '" >' + item['label'] + '</td></tr>');
		$(document).trigger('classChange');
		
		$('.date').datetimepicker({
			timepicker: false,
			format:'Y-m-d',
			formatDate:'Y-m-d',
			step: 10
		});
		
		$('.time').datetimepicker({
			datepicker: false,
			format:'H:i',
			step: 10
		});
		
		$('.datetime').datetimepicker({
			datepicker: true,
			timepicker: true,
			format:'Y-m-d H:i',
			step:10
		});
				
		option_row++;
	}	
});
$(document).on('classChange', function() {
	$("#optionTable tr").each(function(){
			if ( $(this).hasClass("active")) {
				var elemAnchor=$(this).find(':first-child').find(':first-child').attr("rel");
				$('#option .tab-content .tab-pane').hide();
				$(elemAnchor).show();
			}
	})
	var rowCount = $('#optionTable tr').length;
	if (rowCount==1)
		$("#optionTable tr").removeClass('active').addClass('none');
});
</script> 
<script type="text/javascript"><!--		
var option_value_row = <?php echo $option_value_row; ?>;
function addOptionValue(option_row) {
	html  = '<tr id="option-value-row' + option_value_row + '">';
	html += '  <td ><span class="cellcaption">Option Value</span><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][option_value_id]">';
	html += $('#option-values' + option_row).html();
	html += '  </select><input type="hidden" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][product_option_value_id]" value="" /></td>';
	html += '  <td ><span class="cellcaption">Quantity</span><input type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][quantity]" value="" placeholder="Quantity" /></td>'; 
	html += '  <td ><span class="cellcaption">Subtract</span><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][subtract]">';
	html += '    <option value="1">Yes</option>';
	html += '    <option value="0">No</option>';
	html += '  </select></td>';
	html += '  <td class="text-right"><span class="cellcaption">Price</span><select class="fieldSmall" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][price_prefix]">';
	html += '    <option value="+">+</option>';
	html += '    <option value="-">-</option>';
	html += '  </select>';
	html += '  <input class="fieldNormal" type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][price]" value="" placeholder="Price" /></td>';
	html += '  <td class="text-right"><span class="cellcaption">Weight</span><select class="fieldSmall" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][weight_prefix]">';
	html += '    <option value="+">+</option>';
	html += '    <option value="-">-</option>';
	html += '  </select>';
	html += '  <input class="fieldNormal" type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][weight]" value="" placeholder="Weight" /></td>';
	html += ' <td ><ul class="actions"><li><a class="button small red" onclick="$(\'#option-value-row' + option_value_row + '\').remove();" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td>'
	html += '</tr>';
	$('#option-value' + option_row + ' tbody').append(html);
	option_value_row++;
}
var max_addons=<?php echo Settings::getSetting("CONF_MAX_NUMBER_PRODUCT_ADDONS") ?>;
	
	$('input[name=\'addons\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'products_autocomplete',[],webroot),
				data: {keyword: encodeURIComponent(query),shop: '<?php echo $shop?>' },
				dataType: 'json',
				type: 'post',
				success: function(json) { //alert(json);
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				var n = $("#product-addon div").length; 
				if (n<max_addons){
				$('input[name=\'addons\']').val('');
				$('#product-addon' + suggestion.data).remove();
				$('#product-addon').append('<div id="product-addon' + suggestion.data + '"><i class="remove_addon remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' +suggestion.value + '<input type="hidden" name="product_addon[]" value="' + suggestion.data + '" /></div>');
				}
    	 }
	});
	
	$('#product-addon').delegate('.remove_addon', 'click', function() {
		$(this).parent().remove();
	});
	
</script>
<script type="text/javascript">
<?php if (!empty($tab)) {?>
	  setTimeout(function(){ $("a[name='<?php echo $tab?>']").parents('.tabs_nav_container:first').find(".tabs_panel").hide();
	  var activeTab = $("a[name='<?php echo $tab?>']").attr("rel");
	  $("#"+activeTab).fadeIn();		
	  $("a[name='<?php echo $tab?>']").parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
	  $("a[name='<?php echo $tab?>']").addClass("active");
	  $(".togglehead").removeClass("active");
	  $(".togglehead[rel^='"+activeTab+"']").addClass("active"); }, 500);
	  
<?php } else {?>
	$(".tabs_panel").hide();
	$('.tabs_panel_wrap').find(".tabs_panel:first").show();
<?php }?>
</script>

