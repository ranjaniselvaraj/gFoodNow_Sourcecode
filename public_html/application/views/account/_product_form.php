<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/LiveEditor/scripts/innovaeditor.js"></script>
<script src="<?php echo CONF_WEBROOT_URL; ?>js/LiveEditor/scripts/common/webfont.js" type="text/javascript"></script>
<?php $attributes=$data["product_attributes"]; $shipping_rates=$data["product_shipping_rates"]; $discount_rates=$data["product_discounts"]; $special_rates=$data["product_specials"]; $product_options=$data["product_options"]; $option_values=$data["option_values"];?>	
    <div class="body clearfix">
      <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
      <div class="fixed-container">
        <div class="dashboard">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
          <div class="data-side">
          	<?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
            <!--<h3>Products Setup</h3>-->
             <div class="box-head">
            	<h3><?php echo Utilities::getLabel('L_Products_Setup')?> <a href="<?php echo Utilities::generateUrl('account', 'product_setup_info')?>" rel="fancy_popup_box"> <i class="svg-icn">
              <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 245.334 245.334" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 245.334 245.334">
                <g>
                  <path d="M122.667,0C55.028,0,0,55.028,0,122.667s55.027,122.667,122.666,122.667s122.667-55.028,122.667-122.667   S190.305,0,122.667,0z M122.667,215.334C71.57,215.334,30,173.764,30,122.667S71.57,30,122.667,30s92.667,41.57,92.667,92.667   S173.763,215.334,122.667,215.334z"/>
                  <rect width="30" x="107.667" y="109.167" height="79"/>
                  <rect width="30" x="107.667" y="57.167" height="29"/>
                </g>
              </svg>
              </i></a></h3>
	            <div class="padding20 fr"> <a href="<?php echo Utilities::generateUrl('account', 'publications')?>" class="btn small ">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_my_products')?></a> </div>
          </div>
          	
            <div class="tabz-setup">
                <ul class="detailTabs">
                    <li class="active"><a rel="tabs_1" name="general" ><?php echo Utilities::getLabel('L_General')?></a></li>
                    <li><a rel="tabs_2" name="data" ><?php echo Utilities::getLabel('L_Data')?></a></li>
                    <li><a rel="tabs_3" name="seo" ><?php echo Utilities::getLabel('L_SEO')?></a></li>
                    <!--<li><a rel="tabs_3" ><?php echo Utilities::getLabel('L_Links')?></a></li>-->
                    <li><a rel="tabs_4" name="attribute" id="attribute" ><?php echo Utilities::getLabel('L_Specifications')?></a></li>
                    <li><a rel="tabs_5" name="option" ><?php echo Utilities::getLabel('L_OPTIONS')?></a></li>
                    <!--<li><a rel="tabs_6" name="shipping" ><?php echo Utilities::getLabel('L_Shipping')?></a></li>-->
                    <li><a rel="tabs_7" name="discount" ><?php echo Utilities::getLabel('L_Discount')?></a></li>
                    <li><a rel="tabs_8" name="special" ><?php echo Utilities::getLabel('L_Special')?></a></li>
                </ul>
            </div>
			
            <?php echo $frm->getFormTag ();  ?>
            <div>
                	<!--tab 1 start here-->
    		                        <div id="tabs_1" class="tabs_content border_cover">
                                            <table id="tab_general"><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Product_Title')?></label> <span class="spn_must_field">*</span></td>
                                            <td><?php echo Utilities::stripRequiredStar($frm->getFieldHTML('prod_name'));?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_URL_Keywords')?></label> <span class="spn_must_field">*</span></td>
                                            <td><?php echo $frm->getFieldHTML('seo_url_keyword');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Selling_Price')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</label> <span class="spn_must_field">*</span></td>
                                            <td><?php echo Utilities::stripRequiredStar($frm->getFieldHTML('prod_sale_price'));?>
                                            <div id="ajax_validation_message" class="text-danger"></div> </td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Quantity')?></label> <span class="spn_must_field">*</span></td>
                                            <td><?php echo Utilities::stripRequiredStar($frm->getFieldHTML('prod_stock'));?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Minimum_Quantity')?></label> <span class="spn_must_field">*</span></td>
                                            <td><?php echo Utilities::stripRequiredStar($frm->getFieldHTML('prod_min_order_qty'));?></td>
                                            </tr><tr>
                                            <tr>
                                        	<td width="25%"><label><?php echo Utilities::getLabel('M_Brand_Manufacturer')?></label></td>
	                                        <td><?php echo $frm->getFieldHTML('brand_manufacturer');?> &nbsp;<a href="<?php echo Utilities::generateUrl('account', 'brand_request')?>" rel="fancy_popup_box" ><?php echo Utilities::getLabel('M_Request_for_new_brand')?></a></td>
    	                                    </tr><tr>
        	                                <td width="25%"><label><?php echo Utilities::getLabel('M_Product_Category')?></label></td>
            	                            <td><?php echo $frm->getFieldHTML('prod_category[]');?></td>
                	                        </tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Model')?></label> <? if (Settings::getSetting("CONF_PRODUCT_MODEL_MANDATORY")){ ?><span class="spn_must_field">*</span><? } ?></td>
                                            <td><?php echo Utilities::stripRequiredStar($frm->getFieldHTML('prod_model'));?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_SKU')?></label> <? if (Settings::getSetting("CONF_PRODUCT_SKU_MANDATORY")){ ?><span class="spn_must_field">*</span><? } ?></td>
                                            <td><?php echo Utilities::stripRequiredStar($frm->getFieldHTML('prod_sku'));?></td>
                                            </tr>
                                            <?php if (Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING")){?><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Product_Condition')?></label> <span class="spn_must_field">*</span></td>
                                            <td><?php echo Utilities::stripRequiredStar($frm->getFieldHTML('prod_condition'));?></td>
                                            </tr>
                                            <?php } ?><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Photos')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_image');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Product_Description')?></label></td><td><div class="editor-bar"><?php echo $frm->getFieldHTML('prod_long_desc');?></div></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Tags')?></label></td>
                                            <td><div class="custom-width"><?php echo $frm->getFieldHTML('prod_tags');?></div></td>
                                            </tr><tr>
                                            <td colspan="2"><h4 class="heading"><?php echo Utilities::getLabel('M_Shipping_Info_Charges')?></h4></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Shipping_Country')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('shipping_country');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Free_Shipping')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_ship_free');?></td>
                                            </tr><tr>
                                            <td colspan="2"><table id="tab_shipping">
                                    <thead><!--<tr>
                                            <td width="25%"><?php echo Utilities::getLabel('M_Default_Shipping_Charges')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</td>
                                            <td><?php echo $frm->getFieldHTML('prod_shipping');?></td>
                                            </tr>-->
                                            <tr>
                                            <td colspan="2" class="nopadding"><table id="shipping" class="tbl-responsive">
                                    <thead>
                                    <tr>
                                    <th width="17%"><?php echo Utilities::getLabel('M_Ships_To')?></th>
                                    <th width="17%"><?php echo Utilities::getLabel('M_Shipping_Company')?></th>
                                    <th width="17%"><?php echo Utilities::getLabel('M_Processing_Time')?></th>
                                    <th width="25%"><?php echo Utilities::getLabel('M_Cost')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
                                    <th width="20%"><?php echo Utilities::getLabel('M_Each_Additional_Item')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
                                    <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $shipping_row = 0; ?>
                                    <?php foreach ($shipping_rates as $shipping) { ?>
                                     <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][pship_id]" value="<?php echo $shipping['pship_id']; ?>" />
                                    <tr id="shipping-row<?php echo $shipping_row; ?>">
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Ships_To')?></span>
                                    <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][country_name]" value="<?php echo $shipping["pship_country"]!="-1"?$shipping["country_name"]:"&#8594;Everywhere else"?>" placeholder="<?php echo Utilities::getLabel('M_Shipping')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][country_id]" value="<?php echo $shipping["pship_country"]?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Shipping_Company')?></span>
                                    <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][company_name]" value="<?php echo $shipping["scompany_name"]?>" placeholder="<?php echo Utilities::getLabel('M_Company')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][company_id]" value="<?php echo $shipping["scompany_id"]?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Processing_Time')?></span>
                                    <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][processing_time]" value="<?php echo $shipping["sduration_label"]?>" placeholder="<?php echo Utilities::getLabel('M_Processing_Time')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][processing_time_id]" value="<?php echo $shipping["sduration_id"]?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Cost')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</span><input type="text" name="product_shipping[<?php echo $shipping_row; ?>][cost]" value="<?php echo $shipping["pship_charges"]?>" placeholder="<?php echo Utilities::getLabel('M_Cost')?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Each_Additional_Item')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</span>
                                    <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][additional_cost]" value="<?php echo $shipping["pship_additional_charges"]?>" placeholder="<?php echo Utilities::getLabel('M_Each_Additional_Item')?>" /></td>
                                    <td><button type="button" onclick="$('#shipping-row<?php echo $shipping_row; ?>').remove();" class="btn red " title="<?php echo Utilities::getLabel('M_Remove')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/minus-white.png" alt=""/></i></button>
                                    <!--<a class="button red medium" onclick="$('#shipping-row<?php echo $shipping_row; ?>').remove();"  title="Remove">Remove</a>--></td>
                                    </tr>
                                    <?php $shipping_row++; ?>
                                    <?php } ?>
									</tbody>
                                    <tfoot>
                                    <tr>
                                    <td colspan="5"></td>
                                    <td ><!--<a onclick="addShipping();" class="button medium blue">Add Shipping</a>--><button type="button" class="btn blue " title="<?php echo Utilities::getLabel('M_Shipping')?>" onclick="addShipping();" ><i><img src="<?php echo CONF_WEBROOT_URL?>images/plus-white.png" alt=""/></i></button></td>
                                    </tr>
                                    </tfoot>
                                    </table></td>
                                            </tr></thead></table>
                                            </td></tr>
                                            
                                            </table>
                    		        </div>
                            	    <!--tab 1 end here-->
	                                <!--tab 2 start here-->
    	                            <div id="tabs_2" class="tabs_content border_cover">
                                            <table id="tab_data">
                                            <tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Subtract_Stock')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_subtract_stock');?></td>
                                            </tr><!--<tr>
                                            <td width="25%"><?php echo Utilities::getLabel('M_Requires_Shipping')?></td>
                                            <td><?php echo $frm->getFieldHTML('prod_requires_shipping');?></td>
                                            </tr>--><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Track_Inventory')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_track_inventory');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Alert_Stock_Level')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_threshold_stock_level');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Youtube_Video')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_youtube_video');?>
                                            <?php if ($data["embed_code"]!=""):?><br/>
                                            <iframe width="400" height="250" src="//www.youtube.com/embed/<?php echo $data["embed_code"]?>" frameborder="0" allowfullscreen></iframe>
                                            <?php endif; ?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Date_Available')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_available_date');?></td>
                                            </tr><tr>
                       								<td><label><?php echo Utilities::getLabel('M_Dimensions_LWH')?></label></td>
							                        <td><div class="row">
                            								<div class="col-sm-4"><?php echo $frm->getFieldHTML('prod_length');?></div>
								                            <div class="col-sm-4"><?php echo $frm->getFieldHTML('prod_width');?></div>
								                            <div class="col-sm-4"><?php echo $frm->getFieldHTML('prod_height');?></div>
								                          </div>
														  
                                                     </td>
                      						</tr><!--<tr>
                                            <td width="25%">Dimensions (L x W x H)</td>
                                            <td></td>
                                            </tr>--><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Length_Class')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_length_class');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Weight')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_weight');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Weight_Class')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_weight_class');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Status')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_status');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Display_Order')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_display_order');?></td>
                                            </tr><tr>
                                       		<td width="25%"><label><?php echo Utilities::getLabel('M_Product_Filters')?></label></td>
                                        	<td><?php echo $frm->getFieldHTML('filter');?></td>
                                        	</tr><tr>
                                       		<td width="25%"><label><?php echo Utilities::getLabel('M_Product_Addons')?></label></td>
                                        	<td><?php echo $frm->getFieldHTML('addons');?></td>
                                        	</tr>
                                            </table>
            	                    </div>
                                <!--tab 2 end here-->
                                
                                
                                <!--tab 3 start here-->
                                	<div id="tabs_3" class="tabs_content border_cover">
                                        <table id="tab_links">
                                        <tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Meta_Tag_Title')?></label> <? if (Settings::getSetting("CONF_PRODUCT_META_TITLE_MANDATORY")){ ?><span class="spn_must_field">*</span><? } ?></td>
                                            <td><?php echo Utilities::stripRequiredStar($frm->getFieldHTML('prod_meta_title'));?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Meta_Tag_Description')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_meta_description');?></textarea></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Meta_Tag_Keywords')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_meta_keywords');?></td>
                                            </tr></table>
            	                    </div>
                                
    	                            <!--<div id="tabs_3" class="tabs_content border_cover">
                                        <table id="tab_links">
                                        <tr>
                                        <td width="25%"><label><?php echo Utilities::getLabel('M_Brand_Manufacturer')?></label></td>
                                        <td><?php echo $frm->getFieldHTML('brand_manufacturer');?> &nbsp;<a href="<?php echo Utilities::generateUrl('account', 'brand_request')?>" rel="fancy_popup_box" ><?php echo Utilities::getLabel('M_Request_for_new_brand')?></a></td>
                                        </tr><tr>
                                        <td width="25%"><label><?php echo Utilities::getLabel('M_Product_Category')?></label></td>
                                        <td><?php echo $frm->getFieldHTML('category');?></td>
                                        </tr><tr>
                                        <td width="25%"><label><?php echo Utilities::getLabel('M_Product_Filters')?></label></td>
                                        <td><?php echo $frm->getFieldHTML('filter');?></td>
                                        </tr><tr>
                                        <td width="25%">Related Products</td>
                                        <td><?php echo $frm->getFieldHTML('related');?></td>
                                        </tr></table>
            	                    </div>-->
                                <!--tab 3 end here-->
                                
                                <!--tab 4 start here-->
    	                            <div id="tabs_4" class="tabs_content border_cover">
                                    <table id="attribute" class="tbl-responsive">
                                    <thead>
                                    <tr>
                                    <th width="42%"><label><?php echo Utilities::getLabel('M_Specification')?></label></th>
                                    <th width="42%"><label><?php echo Utilities::getLabel('M_Text')?></label></th>
                                    <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $attribute_row = 0; ?>
                                    <?php foreach ($attributes as $attribute) { ?>
                                    <tr id="attribute-row<?php echo $attribute_row; ?>">
                                    <td  style="width: 40%;">
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Specification')?></span>
                                    <input type="text" name="product_attribute[<?php echo $attribute_row; ?>][name]" value="<?php echo $attribute['name']; ?>" placeholder="<?php echo Utilities::getLabel('M_Specification')?>" class="form-control" />
                                    <input type="hidden" name="product_attribute[<?php echo $attribute_row; ?>][attribute_id]" value="<?php echo $attribute['id']; ?>" /></td>
                                    <td >
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Text')?></span>
                                    <textarea name="product_attribute[<?php echo $attribute_row; ?>][product_attribute_description]" rows="5" placeholder="<?php echo Utilities::getLabel('M_Text')?>" ><?php echo isset($attribute['text']) ? $attribute['text'] : ''; ?></textarea></td>
                                    <td ><button type="button" onclick="$('#attribute-row<?php echo $attribute_row; ?>').remove();" class="btn red " title="<?php echo Utilities::getLabel('M_Remove')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/minus-white.png" alt=""/></i></button>
                                    <!--<a class="button red medium" onclick="$('#attribute-row<?php echo $attribute_row; ?>').remove();"  title="Remove">Remove</a>--></td>
                                    </tr>
                                    <?php $attribute_row++; ?>
                                    <?php } ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                    <td colspan="2"></td>
                                    <td ><button type="button" onclick="addAttribute();" class="btn blue" title="<?php echo Utilities::getLabel('M_Add_Attribute')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/plus-white.png" alt=""/></i></button>
                                    </td></tr>
                                    </tfoot>
                                    </table>
                                    
            	                    </div>
                                <!--tab 4 end here-->
                                
                                <!--tab 5 start here-->
    	                            <div id="tabs_5" class="tabs_content border_cover">
                                        <div class="pro-options-content" id="option">
	                                        <div class="step-1">
    							                <div  class="heading"><strong><?php echo Utilities::getLabel('L_Step_1')?>:</strong> <?php echo Utilities::getLabel('M_Search_Option_Groups')?> </div>
                    	                      <div id="left_options_group">
						                      <?php if (count($product_options)>0) :?>
						                      <div class="pro-options-list">	
						                      <ul id="optionTable">
                      							<?php $option_row = 0; ?>
												<?php foreach ($product_options as $product_option) { ?>
                				                <li <?php if ($option_row==count($product_options)-1):?> class="active" <?php endif;?> >
												<a href="#" onclick="$('#optionTable li').removeClass('active'); $('#colTab<?php echo $option_row?>').parent().addClass('active').trigger('classChange');" id="colTab<?php echo $option_row?>"><?php echo $product_option["name"]?></a> <a rel="#tab-option<?php echo $option_row?>" onclick="$('a[rel=\'#tab-option<?php echo $option_row?>\']').parent().remove(); $('#tab-option<?php echo $option_row?>').remove(); $('#optionTable li').removeClass('active'); $('#optionTable li:first-child').addClass('active'); $('#optionTable').trigger('classChange'); " class="remove"><i class="svg-icn">
                          <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 174.239 174.239" style="enable-background:new 0 0 174.239 174.239;" xml:space="preserve">
                            <g>
                              <path d="M87.12,0C39.082,0,0,39.082,0,87.12s39.082,87.12,87.12,87.12s87.12-39.082,87.12-87.12S135.157,0,87.12,0z M87.12,159.305
		c-39.802,0-72.185-32.383-72.185-72.185S47.318,14.935,87.12,14.935s72.185,32.383,72.185,72.185S126.921,159.305,87.12,159.305z"
		/>
                              <path d="M120.83,53.414c-2.917-2.917-7.647-2.917-10.559,0L87.12,76.568L63.969,53.414c-2.917-2.917-7.642-2.917-10.559,0
		s-2.917,7.642,0,10.559l23.151,23.153L53.409,110.28c-2.917,2.917-2.917,7.642,0,10.559c1.458,1.458,3.369,2.188,5.28,2.188
		c1.911,0,3.824-0.729,5.28-2.188L87.12,97.686l23.151,23.153c1.458,1.458,3.369,2.188,5.28,2.188c1.911,0,3.821-0.729,5.28-2.188
		c2.917-2.917,2.917-7.642,0-10.559L97.679,87.127l23.151-23.153C123.747,61.057,123.747,56.331,120.83,53.414z"/>
                            </g>
                          </svg>
                         		 </i></a></li>
		                              <?php $option_row++; ?>
					                 <?php } ?>
			    	          </ul>
                    	   </div>
	                    <?php endif;?> 
                    </div>
                    
                    <div class="option-search">
                      <input type="text" name="option" value="" placeholder="<?php echo Utilities::getLabel('M_Search_Option_Groups')?>"  />
                      <a href="<?php echo Utilities::generateUrl('account', 'option_form',array(0,1))?>" rel="fancy_popup_box" ><?php echo Utilities::getLabel('M_Create_new_option_variant')?></a>
                      </div>
                  </div>
                                        
                                        
                  <div class="step-2 tab-content">
                    <div class="heading"><strong><?php echo Utilities::getLabel('L_Step_2')?>:</strong> <?php echo Utilities::getLabel('L_Select_Options')?></div>
                    
                    <div valign="top" class="tab-content rgt-blks">
                      <?php $option_row = 0; ?>
                    									<?php $option_value_row = 0; ?>
									                    <?php foreach ($product_options as $product_option) { ?>
                                                        <div class="tab-pane-option" id="tab-option<?php echo $option_row; ?>">
                      <input type="hidden" name="product_option[<?php echo $option_row; ?>][product_option_id]" value="<?php echo $product_option['product_option_id']; ?>" />
                      <input type="hidden" name="product_option[<?php echo $option_row; ?>][name]" value="<?php echo $product_option['name']; ?>" />
                      <input type="hidden" name="product_option[<?php echo $option_row; ?>][option_id]" value="<?php echo $product_option['option_id']; ?>" />
                      <input type="hidden" name="product_option[<?php echo $option_row; ?>][type]" value="<?php echo $product_option['type']; ?>" />
                      <table class="optionValue" id="optionValue">
                      	<tr>
                        <td colspan="2" for="input-required<?php echo $option_row; ?>"><div class="side-element"><label class="checkbox">
                        <input type="checkbox" value="1" name="product_option[<?php echo $option_row; ?>][required]" id="input-required<?php echo $option_row; ?>" <?php if ($product_option['required']) { ?> checked="checked" <?php } ?>/><i class="input-helper"></i><?php echo Utilities::getLabel('M_Is_Required')?></label><i class="svg-icn tooltip"  title="<?php echo Utilities::getLabel('M_Is_Required_Info')?>">
                      <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 245.334 245.334" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 245.334 245.334">
                        <g>
                          <path d="M122.667,0C55.028,0,0,55.028,0,122.667s55.027,122.667,122.666,122.667s122.667-55.028,122.667-122.667   S190.305,0,122.667,0z M122.667,215.334C71.57,215.334,30,173.764,30,122.667S71.57,30,122.667,30s92.667,41.57,92.667,92.667   S173.763,215.334,122.667,215.334z"/>
                          <rect width="30" x="107.667" y="109.167" height="79"/>
                          <rect width="30" x="107.667" y="57.167" height="29"/>
                        </g>
                      </svg>
                      </i></div></td>
                        
                      </tr>
                      <?php if ($product_option['type'] == 'text') { ?>
                      <tr>
                        <td for="input-value<?php echo $option_row; ?>"><?php echo Utilities::getLabel('M_Option_Value')?></td>
                        <td>
                          <input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value<?php echo $option_row; ?>" />
                        </td>
                      </tr>
                      <?php } ?>
                      <?php if ($product_option['type'] == 'textarea') { ?>
                      <tr>
                        <td for="input-value<?php echo $option_row; ?>"><?php echo Utilities::getLabel('M_Option_Value')?></td>
                        <td>
                          <textarea name="product_option[<?php echo $option_row; ?>][value]" rows="5" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value<?php echo $option_row; ?>"><?php echo $product_option['value']; ?></textarea>
                        </td>
                      </tr>
                      <?php } ?>
                      <?php if ($product_option['type'] == 'file') { ?>
                      <tr>
                        <td for="input-value<?php echo $option_row; ?>"><?php echo Utilities::getLabel('M_Option_Value')?></td>
                        <td>
                          <input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value<?php echo $option_row; ?>" />
                        </td>
                      </tr>
                      <?php } ?>
                      <?php if ($product_option['type'] == 'date') { ?>
                      <tr>
                        <td for="input-value<?php echo $option_row; ?>"><?php echo Utilities::getLabel('M_Option_Value')?></td>
                        <td>
                            <input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value<?php echo $option_row; ?>"  />
                          </td>
                      </tr>
                      <?php } ?>
                      <?php if ($product_option['type'] == 'time') { ?>
                      <tr>
                        <td for="input-value<?php echo $option_row; ?>"><?php echo Utilities::getLabel('M_Option_Value')?></td>
                        <td>
                            <input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value<?php echo $option_row; ?>"/>
                        </td>
                      </tr>
                      <?php } ?>
                      <?php if ($product_option['type'] == 'datetime') { ?>
                      <tr>
                        <td for="input-value<?php echo $option_row; ?>"><?php echo Utilities::getLabel('M_Option_Value')?></td>
                        <td>
                            <input type="text" name="product_option[<?php echo $option_row; ?>][value]" value="<?php echo $product_option['value']; ?>" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value<?php echo $option_row; ?>" />
                        </td>
                      </tr>
                      <?php } ?>
                      </table>
                      <?php if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') { ?>
                      <div class="gap"></div>
                      <div class="table-responsive">
                        <table id="option-value<?php echo $option_row; ?>" class="optionValue tbl-responsive">
                          <thead>
                            <tr>
                              <th width="25%"><label><?php echo Utilities::getLabel('M_Option_Value')?></label></th>
                              <th width="15%"><label><?php echo Utilities::getLabel('M_Quantity')?></label></th>
                              <th width="15%"><label><?php echo Utilities::getLabel('M_Subtract')?></label></th>
                              <th width="20%"><label><?php echo Utilities::getLabel('M_Discounted_Price')?></label></th>
                              <th width="15%"><label><?php echo Utilities::getLabel('M_Weight')?></label></th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
							foreach ($product_option['product_option_value'] as $product_option_value) { ?>
                            <tr id="option-value-row<?php echo $option_value_row; ?>">
                              <td >
                              <span class="cellcaption"><?php echo Utilities::getLabel('M_Option_Value')?></span>
                              
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
                              <span class="cellcaption"><?php echo Utilities::getLabel('M_Quantity')?></span>
                              
                              <input type="text" name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][quantity]" value="<?php echo $product_option_value['quantity']; ?>" placeholder="<?php echo $entry_quantity; ?>" class="form-control" /></td>
                              <td >
                              <span class="cellcaption"><?php echo Utilities::getLabel('M_Subtract')?></span>
                              <select name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][subtract]" class="form-control">
                                  <?php if ($product_option_value['subtract']) { ?>
                                  <option value="1" selected="selected"><?php echo Utilities::getLabel('M_Yes')?></option>
                                  <option value="0"><?php echo Utilities::getLabel('M_No')?></option>
                                  <?php } else { ?>
                                  <option value="1"><?php echo Utilities::getLabel('M_Yes')?></option>
                                  <option value="0" selected="selected"><?php echo Utilities::getLabel('M_No')?></option>
                                  <?php } ?>
                                </select></td>
                              <td class="text-right">
                              <span class="cellcaption"><?php echo Utilities::getLabel('M_Discounted_Price')?></span>
                              
                              <select name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][price_prefix]" class="form-control">
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
                                <input type="text" name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][price]" value="<?php echo $product_option_value['price']; ?>" placeholder="<?php echo $entry_price; ?>" class="form-control" /></td>
                              
                              <td class="text-right">
                              <span class="cellcaption"><?php echo Utilities::getLabel('M_Weight')?></span>
                              
                              <select name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][weight_prefix]" class="form-control">
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
                                <input type="text" name="product_option[<?php echo $option_row; ?>][product_option_value][<?php echo $option_value_row; ?>][weight]" value="<?php echo $product_option_value['weight']; ?>" placeholder="<?php echo $entry_weight; ?>" class="form-control" /></td>
                              <td >
                              <button type="button" onclick="$('#option-value-row<?php echo $option_value_row; ?>').remove();" class="btn red " title="<?php echo Utilities::getLabel('M_Remove')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/minus-white.png" alt=""/></i></button>
                              
                              <!--<a onclick="$('#option-value-row<?php echo $option_value_row; ?>').remove();" class="button small red">Remove</a>-->
								</td>
                            </tr>
                            <?php $option_value_row++; ?>
                            <?php } ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="5" class="note"><?php echo Utilities::getLabel('M_Click_plus_to_add_more_options')?></td>
                              <td ><button type="button" onclick="addProductOptionValue('<?php echo $option_row?>');" class="btn blue " title="<?php echo Utilities::getLabel('M_Add')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/plus-white.png" alt=""/></i></button>
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
                    </div>
                                                        <?php $option_row++; ?>
									                    <?php } ?>
                    </div>
                  </div>
                </div>
                                        
                                    </div>    
                                <!--tab 5 end here-->
                                
                                
                                <!--tab 6 start here-->
    	                            <!--<div id="tabs_6" class="tabs_content border_cover">
                                    <table id="tab_shipping">
                                    <thead><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Shipping_Country')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('shipping_country');?></td>
                                            </tr><tr>
                                            <td width="25%"><label><?php echo Utilities::getLabel('M_Free_Shipping')?></label></td>
                                            <td><?php echo $frm->getFieldHTML('prod_ship_free');?></td>
                                            </tr>
                                            <tr>
                                            <td colspan="2" class="nopadding"><table id="shipping">
                                    <thead>
                                    <tr>
                                    <th width="17%"><?php echo Utilities::getLabel('M_Ships_To')?></th>
                                    <th width="17%"><?php echo Utilities::getLabel('M_Shipping_Company')?></th>
                                    <th width="17%"><?php echo Utilities::getLabel('M_Processing_Time')?></th>
                                    <th width="25%"><?php echo Utilities::getLabel('M_Cost')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
                                    <th width="20%"><?php echo Utilities::getLabel('M_Each_Additional_Item')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
                                    <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $shipping_row = 0; ?>
                                    <?php foreach ($shipping_rates as $shipping) { ?>
                                     <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][pship_id]" value="<?php echo $shipping['pship_id']; ?>" />
                                    <tr id="shipping-row<?php echo $shipping_row; ?>">
                                    <td><input type="text" name="product_shipping[<?php echo $shipping_row; ?>][country_name]" value="<?php echo $shipping["pship_country"]!="-1"?$shipping["country_name"]:"&#8594;Everywhere else"?>" placeholder="<?php echo Utilities::getLabel('M_Shipping')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][country_id]" value="<?php echo $shipping["pship_country"]?>" /></td>
                                    <td><input type="text" name="product_shipping[<?php echo $shipping_row; ?>][company_name]" value="<?php echo $shipping["scompany_name"]?>" placeholder="<?php echo Utilities::getLabel('M_Company')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][company_id]" value="<?php echo $shipping["scompany_id"]?>" /></td>
                                    <td><input type="text" name="product_shipping[<?php echo $shipping_row; ?>][processing_time]" value="<?php echo $shipping["sduration_label"]?>" placeholder="<?php echo Utilities::getLabel('M_Processing_Time')?>" /><input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][processing_time_id]" value="<?php echo $shipping["sduration_id"]?>" /></td>
                                    <td><input type="text" name="product_shipping[<?php echo $shipping_row; ?>][cost]" value="<?php echo $shipping["pship_charges"]?>" placeholder="<?php echo Utilities::getLabel('M_Cost')?>" /></td>
                                    <td><input type="text" name="product_shipping[<?php echo $shipping_row; ?>][additional_cost]" value="<?php echo $shipping["pship_additional_charges"]?>" placeholder="<?php echo Utilities::getLabel('M_Each_Additional_Item')?>" /></td>
                                    <td><button type="button" onclick="$('#shipping-row<?php echo $shipping_row; ?>').remove();" class="btn red " title="<?php echo Utilities::getLabel('M_Remove')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/minus-white.png" alt=""/></i></button>
                                    </td>
                                    </tr>
                                    <?php $shipping_row++; ?>
                                    <?php } ?>
									</tbody>
                                    <tfoot>
                                    <tr>
                                    <td colspan="5"></td>
                                    <td ><button type="button" class="btn blue " title="<?php echo Utilities::getLabel('M_Shipping')?>" onclick="addShipping();" ><i><img src="<?php echo CONF_WEBROOT_URL?>images/plus-white.png" alt=""/></i></button></td>
                                    </tr>
                                    </tfoot>
                                    </table></td>
                                            </tr></thead></table>
                                            
            	                    </div>-->
                                <!--tab 6 end here-->
                                
                                <!--tab 7 start here-->
    	                        <div id="tabs_7" class="tabs_content border_cover">
                                    <table id="discount" class="tbl-responsive">
                                    <thead>
                                    <tr>
                                    <th width="16%"><?php echo Utilities::getLabel('M_Quantity')?></th>
                                    <th width="16%"><?php echo Utilities::getLabel('M_Priority')?></th>
                                    <th width="16%"><?php echo Utilities::getLabel('M_Special_Price')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
                                    <th width="16%"><?php echo Utilities::getLabel('M_Date_Start')?></th>
                                    <th width="17%"><?php echo Utilities::getLabel('M_Date_End')?></th>
                                    <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $discount_row = 0; ?>
                                    <?php foreach ($discount_rates as $discount) { ?>
                                    <tr id="discount-row<?php echo $discount_row; ?>">
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Quantity')?></span>
                                    <input type="text" name="product_discount[<?php echo $discount_row; ?>][quantity]" value="<?php echo $discount["pdiscount_qty"]?>" placeholder="<?php echo Utilities::getLabel('M_Quantity')?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Priority')?></span>
                                    <input type="text" name="product_discount[<?php echo $discount_row; ?>][priority]" value="<?php echo $discount["pdiscount_priority"]?>" placeholder="<?php echo Utilities::getLabel('M_Priority')?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Special_Price')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</span>
                                    <input type="text" name="product_discount[<?php echo $discount_row; ?>][price]" value="<?php echo $discount["pdiscount_price"]?>" placeholder="<?php echo Utilities::getLabel('M_Discounted_Price')?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Date_Start')?></span>
                                    <input type="text" name="product_discount[<?php echo $discount_row; ?>][start_date]" class="date-pick" value="<?php echo $discount["pdiscount_start_date"]?>" placeholder="<?php echo Utilities::getLabel('M_Date_Start')?>" readonly="readonly" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Date_End')?></span>
                                    <input type="text" name="product_discount[<?php echo $discount_row; ?>][end_date]" class="date-pick" value="<?php echo $discount["pdiscount_end_date"]?>" placeholder="<?php echo Utilities::getLabel('M_Date_End')?>" readonly="readonly" /></td>
                                    <td><button type="button" onclick="$('#discount-row<?php echo $discount_row; ?>').remove();" class="btn red " title="<?php echo Utilities::getLabel('M_Remove')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/minus-white.png" alt=""/></i></button>
                                    <!--<a class="button red medium" onclick="$('#discount-row<?php echo $discount_row; ?>').remove();"  title="Remove">Remove</a>--></td>
                                    </tr>
                                    <?php $discount_row++; ?>
                                    <?php } ?>
                                   	</tbody>
                                    <tfoot>
                                    <tr>
                                    <td colspan="5"></td>
                                    <td ><button type="button" onclick="addDiscount();" class="btn blue " title="<?php echo Utilities::getLabel('M_Add_Discount')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/plus-white.png" alt=""/></i></button></td>
                                    </tr>
                                    </tfoot>
                                    </table>
                                    
            	                    </div>
                                <!--tab 7 end here-->
                                
                                <!--tab 7 start here-->
    	                        <div id="tabs_8" class="tabs_content border_cover">
                                    <table id="special" class="tbl-responsive">
                                    <thead>
                                    <tr>
                                    <th width="20%"><?php echo Utilities::getLabel('M_Priority')?></th>
                                    <th width="20%"><?php echo Utilities::getLabel('M_Price')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</th>
                                    <th width="20%"><?php echo Utilities::getLabel('M_Date_Start')?></th>
                                    <th width="20%"><?php echo Utilities::getLabel('M_Date_End')?></th>
                                    <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $special_row = 0; ?>
                                    <?php foreach ($special_rates as $special) { ?>
                                    <tr id="special-row<?php echo $special_row; ?>">
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Priority')?></span>
                                    <input type="text" name="product_special[<?php echo $special_row; ?>][priority]" value="<?php echo $special["pspecial_priority"]?>" placeholder="<?php echo Utilities::getLabel('M_Priority')?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Price')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</span>
                                    <input type="text" name="product_special[<?php echo $special_row; ?>][price]" value="<?php echo $special["pspecial_price"]?>" placeholder="<?php echo Utilities::getLabel('M_Special_Price')?>" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Date_Start')?></span>
                                    <input type="text" name="product_special[<?php echo $special_row; ?>][start_date]" class="date-pick" value="<?php echo $special["pspecial_start_date"]?>" placeholder="<?php echo Utilities::getLabel('M_Date_Start')?>" readonly="readonly" /></td>
                                    <td>
                                    <span class="cellcaption"><?php echo Utilities::getLabel('M_Date_End')?></span>
                                    <input type="text" name="product_special[<?php echo $special_row; ?>][end_date]" class="date-pick" value="<?php echo $special["pspecial_end_date"]?>" placeholder="<?php echo Utilities::getLabel('M_Date_End')?>" readonly="readonly" /></td>
                                    <td><button type="button" onclick="$('#special-row<?php echo $special_row; ?>').remove();" class="btn red " title="<?php echo Utilities::getLabel('M_Remove')?>"  ><i><img src="<?php echo CONF_WEBROOT_URL?>images/minus-white.png" alt=""/></i></button>
                                    <!--<a class="button red medium" onclick="$('#special-row<?php echo $special_row; ?>').remove();"  title="Remove">Remove</a>--></td>
                                    </tr>
                                    <?php $special_row++; ?>
                                    <?php } ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                    <td colspan="4"></td>
                                    <td ><button type="button" onclick="addSpecial();" class="btn blue " title="<?php echo Utilities::getLabel('M_Add_Special')?>" ><i><img src="<?php echo CONF_WEBROOT_URL?>images/plus-white.png" alt=""/></i></button></td>
                                    </tr>
                                    </tfoot>
                                    </table>
                                    
            	                    </div>
                                <!--tab 7 end here-->
                               	  <div class="gap"></div>
                                  <div class="product_btn_submit border_cover">
	                                  <?php echo $frm->getFieldHTML('btn_submit');?>
                                  </div>
          	</div>
            	<?php echo $frm->getFieldHTML('prod_id');?><?php echo $frm->getFieldHTML('prod_brand');?>
				<?php //echo $frm->getFieldHTML('prod_category');?>
				<?php echo $frm->getFieldHTML('prod_shipping_country');?>
                <?php echo $frm->getFieldHTML('prod_tab');?>
	            <?php echo $frm->getExternalJS();?>
                </form>
                
          </div>
        </div>
      </div>
    </div>
<script type="text/javascript">
$(document).ready(function(){
		$(document).trigger('classChange');
		$('.tooltip').tooltipster();
	});
</script>    
   
<script type="text/javascript">
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
			//elem.find("td").css('color','#ccc');
			//elem.find('input').css('background-color', '#ccc');
	}else{
			//elem.find('input').attr('disabled', false);
			//elem.find("td").css('color','');
			//elem.find('input').css('background-color', '');
	}
});
$("#prod_ship_free").trigger("change");
</script>
<script type="text/javascript"><!--
var attribute_row = <?php echo $attribute_row; ?>;
function addAttribute() {
    html  = '<tr id="attribute-row' + attribute_row + '">';
	html += '  <td><span class="cellcaption"><?php echo Utilities::getLabel('M_Specification')?></span><input type="text" name="product_attribute[' + attribute_row + '][name]" value="" placeholder="<?php echo Utilities::getLabel('M_Specification')?>" /><input type="hidden" name="product_attribute[' + attribute_row + '][attribute_id]" value="" /></td>';
	html += '  <td><span class="cellcaption"><?php echo Utilities::getLabel('M_Text')?></span>';
	html += '<textarea name="product_attribute[' + attribute_row + '][product_attribute_description]" rows="5" placeholder="<?php echo Utilities::getLabel('M_Text')?>"></textarea>';
	html += '  </td>';
	//html += '  <td><a class="button medium red" onclick="$(\'#attribute-row' + attribute_row + '\').remove();" title="Remove" >Remove</a></td>';
	html += '  <td><button type="button" class="btn red" title="<?php echo Utilities::getLabel('M_Remove')?>" onclick="$(\'#attribute-row' + attribute_row + '\').remove();" ><i><img src="'+webroot+'images/minus-white.png" alt=""/></i></button></td>';
    html += '</tr>';
	$('#attribute tbody').append(html);
	attributeautocomplete(attribute_row);
	attribute_row++;
}
function attributeautocomplete(attribute_row) {
	$('input[name=\'product_attribute[' + attribute_row + '][name]\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: generateUrl('common', 'attributes_autocomplete'),
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
    html  = '<tr id="shipping-row' + shipping_row + '">';
	html += '  <td><span class="cellcaption"><?php echo Utilities::getLabel('M_Ships_To')?></span><input type="text" name="product_shipping[' + shipping_row + '][country_name]" value="" placeholder="<?php echo Utilities::getLabel('M_Ships_To')?>" /><input type="hidden" name="product_shipping[' + shipping_row + '][country_id]" value="" /></td>';
	html += '  <td><span class="cellcaption"><?php echo Utilities::getLabel('M_Shipping_Company')?></span><input type="text" name="product_shipping[' + shipping_row + '][company_name]" value="" placeholder="<?php echo Utilities::getLabel('M_Shipping_Company')?>" /><input type="hidden" name="product_shipping[' + shipping_row + '][company_id]" value="" /></td>';
	html += '  <td><span class="cellcaption"><?php echo Utilities::getLabel('M_Processing_Time')?></span><input type="text" name="product_shipping[' + shipping_row + '][processing_time]" value="" placeholder="<?php echo Utilities::getLabel('M_Processing_Time')?>" /><input type="hidden" name="product_shipping[' + shipping_row + '][processing_time_id]" value="" /></td>';
	html += '  <td><span class="cellcaption"><?php echo Utilities::getLabel('M_Cost')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</span>';
	html += '<input type="text" name="product_shipping[' + shipping_row + '][cost]" value="" placeholder="<?php echo Utilities::getLabel('M_Cost')?>" />';
	html += '</td>';
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Each_Additional_Item')?> [<?php echo CONF_CURRENCY_SYMBOL?>]</span>';
	html += '<input type="text" name="product_shipping[' + shipping_row + '][additional_cost]" value="" placeholder="<?php echo Utilities::getLabel('M_Each_Additional_Item')?>" />';
	html += '</td>';
	//html += '  <td><a class="button medium red" onclick="$(\'#shipping-row' + shipping_row + '\').remove();" title="Remove" >Remove</a></td>';
	html += '  <td><button type="button" class="btn red" title="<?php echo Utilities::getLabel('M_Remove')?>" onclick="$(\'#shipping-row' + shipping_row + '\').remove();" ><i><img src="'+webroot+'images/minus-white.png" alt=""/></i></button></td>';
    html += '</tr>';
	$('#shipping tbody').append(html);
	shippingautocomplete(shipping_row);
	shipping_row++;
}
function shippingautocomplete(shipping_row) {
	
	/*$('input[name=\'product_shipping[' + shipping_row + '][country_name]\']').focusout(function() {
		    $('.suggestions').hide(); 
	});
	
	$('input[name=\'product_shipping[' + shipping_row + '][company_name]\']').focusout(function() {
		    $('.suggestions').hide(); 
	});
	
	$('input[name=\'product_shipping[' + shipping_row + '][processing_time]\']').focusout(function() {
		    $('.suggestions').hide(); 
	});*/

	$('input[name=\'product_shipping[' + shipping_row + '][country_name]\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'countries_autocomplete'),
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
				url: generateUrl('common', 'shipping_autocomplete'),
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
				url: generateUrl('common', 'shippingduration_autocomplete'),
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
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Quantity')?></span><input type="text" name="product_discount[' + discount_row + '][quantity]" value="" placeholder="<?php echo Utilities::getLabel('M_Quantity')?>" /></td>';
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Priority')?></span><input type="text" name="product_discount[' + discount_row + '][priority]" value="" placeholder="<?php echo Utilities::getLabel('M_Priority')?>" /></td>';
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Discounted_Price')?></span><input type="text" name="product_discount[' + discount_row + '][price]" value="" placeholder="<?php echo Utilities::getLabel('M_Discounted_Price')?>" /></td>';
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Date_Start')?></span><input type="text" name="product_discount[' + discount_row + '][start_date]" value="" readonly="readonly" class="date-pick" placeholder="<?php echo Utilities::getLabel('M_Date_Start')?>" /></td>';
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Date_End')?></span><input type="text" name="product_discount[' + discount_row + '][end_date]" value="" readonly="readonly" class="date-pick" placeholder="<?php echo Utilities::getLabel('M_Date_End')?>" /></td>';
	html += '  <td><button type="button" class="btn red" title="<?php echo Utilities::getLabel('M_Remove')?>" onclick="$(\'#discount-row' + discount_row + '\').remove();" ><i><img src="'+webroot+'images/minus-white.png" alt=""/></i></button></td>';
    html += '</tr>';
	$('#discount tbody').append(html);
	discount_row++;
	ResetDateCalendar();
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
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Priority')?></span><input type="text" name="product_special[' + special_row + '][priority]" value="" placeholder="<?php echo Utilities::getLabel('M_Priority')?>" /></td>';
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Special_Price')?></span><input type="text" name="product_special[' + special_row + '][price]" value="" placeholder="<?php echo Utilities::getLabel('M_Special_Price')?>" /></td>';
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Date_Start')?></span><input type="text" name="product_special[' + special_row + '][start_date]" value="" class="date-pick" readonly="readonly" placeholder="<?php echo Utilities::getLabel('M_Date_Start')?>" /></td>';
	html += '<td><span class="cellcaption"><?php echo Utilities::getLabel('M_Date_End')?></span><input type="text" name="product_special[' + special_row + '][end_date]" value="" class="date-pick" readonly="readonly" placeholder="<?php echo Utilities::getLabel('M_Date_End')?>" /></td>';
	html += '  <td><button type="button" class="btn red" onclick="$(\'#special-row' + special_row + '\').remove();" title="<?php echo Utilities::getLabel('M_Remove')?>" ><i><img src="'+webroot+'images/minus-white.png" alt=""/></i></button></td>';
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
			url: generateUrl('common', 'options_autocomplete'),
			data: {keyword: encodeURIComponent(request),owner: '<?php echo $user_id?>' },
			dataType: 'json',
			type: 'post',
			success: function(json) {
				//alert(json);
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
		
		html  = '<div class="tab-pane-option" id="tab-option' + option_row + '" >';
		html += '	<input type="hidden" name="product_option[' + option_row + '][product_option_id]" value="" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][name]" value="' + item['label'] + '" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][option_id]" value="' + item['value'] + '" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][type]" value="' + item['type'] + '" />';
		
		html += '	<table class="optionValue" id="optionValue" >';
		html += '	<td colspan="2" for="input-required' + option_row + '"><div class="side-element"><label class="checkbox"><input type="checkbox" name="product_option[' + option_row + '][required]" checked id="input-required' + option_row + '" value="1" /><i class="input-helper"></i><?php echo Utilities::getLabel('M_Is_Required')?></label> <i class="svg-icn tooltip"  title="<?php echo Utilities::getLabel('M_Is_Required_Info')?>"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 245.334 245.334" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 245.334 245.334"><g><path d="M122.667,0C55.028,0,0,55.028,0,122.667s55.027,122.667,122.666,122.667s122.667-55.028,122.667-122.667   S190.305,0,122.667,0z M122.667,215.334C71.57,215.334,30,173.764,30,122.667S71.57,30,122.667,30s92.667,41.57,92.667,92.667   S173.763,215.334,122.667,215.334z"/><rect width="30" x="107.667" y="109.167" height="79"/><rect width="30" x="107.667" y="57.167" height="29"/></g></svg></i>';
		//html +=  '<td align="left"></td>';
		
		
		html += '	</tr>';
		if (item['type'] == 'text') {
			html += '<tr>';
			html += '  <td for="input-value' + option_row + '"><?php echo Utilities::getLabel('M_Option_Value')?></td>';
			html += '  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value' + option_row + '"  /></td>';
			html += '</tr>';
		}
		
		if (item['type'] == 'textarea') {
			html += '	<tr>';
			html += '	  <td for="input-value' + option_row + '"><?php echo Utilities::getLabel('M_Option_Value')?></td>';
			html += '	  <td><textarea name="product_option[' + option_row + '][value]" rows="5" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value' + option_row + '"></textarea></td>';
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
			html += '	  <td for="input-value' + option_row + '"><?php echo Utilities::getLabel('M_Option_Value')?></td>';
			html += '	  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>" id="input-value' + option_row + '" class="date"/></td>';
			html += '	</tr>';
		}
		
		if (item['type'] == 'time') {
			html += '	<tr>';
			html += '	  <td for="input-value' + option_row + '"><?php echo Utilities::getLabel('M_Option_Value')?></td>';
			html += '	  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>"  id="input-value' + option_row + '" class="time"  /></td>';
			html += '	</tr>';
		}
				
		if (item['type'] == 'datetime') {
			html += '	<tr>';
			html += '	  <td for="input-value' + option_row + '"><?php echo Utilities::getLabel('M_Option_Value')?></td>';
			html += '	  <td><input type="text" name="product_option[' + option_row + '][value]" value="" placeholder="<?php echo Utilities::getLabel('M_Option_Value')?>"  id="input-value' + option_row + '" class="datetime" /></td>';
			html += '	</tr>';
		}
			
		if (item['type'] == 'select' || item['type'] == 'radio' || item['type'] == 'checkbox' || item['type'] == 'image') {
			html += '<tr><td colspan="2">';
			html += '  <table id="option-value' + option_row + '" class="optionValue">';
			html += '  	 <thead>'; 
			html += '      <tr>';
			html += '        <th width="25%"><?php echo Utilities::getLabel('M_Option_Value')?></th>';
			html += '        <th width="15%"><?php echo Utilities::getLabel('M_Quantity')?></th>';
			html += '        <th width="15%"><?php echo Utilities::getLabel('M_Subtract')?></th>';
			html += '        <th width="20%"><?php echo Utilities::getLabel('M_Price')?></th>';
			html += '        <th width="15%"><?php echo Utilities::getLabel('M_Weight')?></th>';
			html += '        <th></th>';
			html += '      </tr>';
			html += '  	 </thead>';
			html += '  	 <tbody>';
			html += '    </tbody>';
			html += '    <tfoot>';
			html += '      <tr>';
			html += '        <td colspan="5" class="note"><?php echo Utilities::getLabel('M_Click_plus_to_add_more_options')?></td>';
			html += '   <td><button type="button" class="btn blue " title="" onclick="addProductOptionValue(' + option_row + ');" ><i><img src="'+webroot+'images/plus-white.png" alt=""/></i></button></td>'
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
		html += '	</table></div>';
		//alert(html);
		$('#option .tab-content').append(html);
	
		
		$('#optionTable li').removeClass('active');
		
		//$('#optionTable li:last-child').before('<tr class="active"><td width="10%"><a rel="#tab-option' + option_row + '" onclick="$(\'a[rel=\\\'#tab-option' + option_row + '\\\']\').parent().parent().remove(); $(\'#tab-option' + option_row + '\').remove(); $(\'#optionTable li\').removeClass(\'active\'); $(\'#optionTable li:first-child\').addClass(\'active\').trigger(\'classChange\');">X</a></td><td onclick="$(\'#optionTable li\').removeClass(\'active\'); $(\'#colTab'+option_row+'\').parent().addClass(\'active\').trigger(\'classChange\');" id="colTab' + option_row + '" >' + item['label'] + '</td></tr>');
		
		var optstr = '<li class="active"><a onclick="$(\'#optionTable li\').removeClass(\'active\'); $(\'#colTab'+option_row+'\').parent().addClass(\'active\').trigger(\'classChange\');" id="colTab' + option_row + '">' + item['label'] + '</a><a href="#" rel="#tab-option' + option_row + '" onclick="$(\'a[rel=\\\'#tab-option' + option_row + '\\\']\').parent().remove(); $(\'#tab-option' + option_row + '\').remove(); $(\'#optionTable li\').removeClass(\'active\'); $(\'#optionTable li:first-child\').addClass(\'active\'); $(\'#optionTable\').trigger(\'classChange\');" class="remove"><i class="svg-icn"><svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 174.239 174.239" style="enable-background:new 0 0 174.239 174.239;" xml:space="preserve"><g><path  d="M87.12,0C39.082,0,0,39.082,0,87.12s39.082,87.12,87.12,87.12s87.12-39.082,87.12-87.12S135.157,0,87.12,0z M87.12,159.305 c-39.802,0-72.185-32.383-72.185-72.185S47.318,14.935,87.12,14.935s72.185,32.383,72.185,72.185S126.921,159.305,87.12,159.305z"/><path d="M120.83,53.414c-2.917-2.917-7.647-2.917-10.559,0L87.12,76.568L63.969,53.414c-2.917-2.917-7.642-2.917-10.559,0 s-2.917,7.642,0,10.559l23.151,23.153L53.409,110.28c-2.917,2.917-2.917,7.642,0,10.559c1.458,1.458,3.369,2.188,5.28,2.188 c1.911,0,3.824-0.729,5.28-2.188L87.12,97.686l23.151,23.153c1.458,1.458,3.369,2.188,5.28,2.188c1.911,0,3.821-0.729,5.28-2.188 c2.917-2.917,2.917-7.642,0-10.559L97.679,87.127l23.151-23.153C123.747,61.057,123.747,56.331,120.83,53.414z"/></g></svg></i></a></li>';
		if ($('#optionTable').length==0){
			$("#left_options_group").html('<div class="pro-options-list"><ul id="optionTable">'+optstr+'</ul></div>');
		}
		else{
			$('#optionTable li:last-child').after(optstr);
		}
		
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
		$('.tooltip').tooltipster();
	}	
});
$(document).on('classChange', function() {
	$("#optionTable li").each(function(){
			if ( $(this).hasClass("active")) {
				var elemAnchor=$(this).find(':nth-child(2)').attr("rel");
				$('#option .tab-content .tab-pane-option').hide();
				$(elemAnchor).show();
			}
	})
	var rowCount = $('#optionTable li').length;
	if (rowCount==1){
		$("#optionTable li").removeClass('active').addClass('none');
	}
	if (rowCount==0){
		$("#left_options_group").html('');
	}
		
});
</script> 
<script type="text/javascript"><!--		
var option_value_row = <?php echo $option_value_row; ?>;
function addProductOptionValue(option_row) {	
	html  = '<tr id="option-value-row' + option_value_row + '">';
	html += '  <td ><span class="cellcaption"><?php echo Utilities::getLabel('M_Option_Value')?></span><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][option_value_id]">';
	html += $('#option-values' + option_row).html();
	html += '  </select><input type="hidden" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][product_option_value_id]" value="" /></td>';
	html += '  <td ><span class="cellcaption"><?php echo Utilities::getLabel('M_Quantity')?></span><input type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][quantity]" value="" placeholder="<?php echo Utilities::getLabel('M_Quantity')?>" /></td>'; 
	html += '  <td ><span class="cellcaption"><?php echo Utilities::getLabel('M_Subtract')?></span><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][subtract]">';
	html += '    <option value="1"><?php echo Utilities::getLabel('M_Yes')?></option>';
	html += '    <option value="0"><?php echo Utilities::getLabel('M_No')?></option>';
	html += '  </select></td>';
	html += '  <td ><span class="cellcaption"><?php echo Utilities::getLabel('M_Discounted_Price')?></span><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][price_prefix]">';
	html += '    <option value="+">+</option>';
	html += '    <option value="-">-</option>';
	html += '  </select>';
	html += '  <input type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][price]" value="" placeholder="<?php echo Utilities::getLabel('M_Price')?>" /></td>';
	html += '  <td ><span class="cellcaption"><?php echo Utilities::getLabel('M_Weight')?></span><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][weight_prefix]">';
	html += '    <option value="+">+</option>';
	html += '    <option value="-">-</option>';
	html += '  </select>';
	html += '  <input type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][weight]" value="" placeholder="<?php echo Utilities::getLabel('M_Weight')?>" /></td>';
	html += ' <td ><button type="button" class="btn red" onclick="$(\'#option-value-row' + option_value_row + '\').remove();" title="<?php echo Utilities::getLabel('M_Remove')?>" ><i><img src="'+webroot+'images/minus-white.png" alt=""/></i></button></td>'
	html += '</tr>';
	
	$('#option-value' + option_row + ' tbody').append(html);
       
	option_value_row++;
}
<?php if (!empty($tab)) {?>
	$(".tabs_content").hide();
    var activeTab = $("a[name='<?php echo $tab?>']").attr("rel"); 
    $("#"+activeTab).fadeIn();		
    $(".detailTabs li").removeClass("active");
    $("a[name='<?php echo $tab?>']").parent().addClass("active");
<?php } else {?>
	$(".tabs_content").hide();
    $(".tabs_content:first").show();
<?php }?>
	var max_addons=<?php echo Settings::getSetting("CONF_MAX_NUMBER_PRODUCT_ADDONS") ?>;
	$('input[name=\'addons\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'products_autocomplete'),
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
