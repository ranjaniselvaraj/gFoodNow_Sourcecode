{{#products}}
<div data-attr-id="{{promotion_id}}" class="{{#prod_promotion_id}}thumb_impression  thumb_click {{/prod_promotion_id}} shop-item {{#prod_out_of_stock}} <?php  echo 'out-of-stock'; ?> {{/prod_out_of_stock}}" itemscope itemtype="http://schema.org/Product" >
				{{#prod_promotion_id}}
                <div class="lable"><?php echo Utilities::getLabel('M_Sponsored')?></div>
				{{/prod_promotion_id}}
                {{#prod_cod}}
                <!--<div class="lable"><?php echo Utilities::getLabel('M_COD_Enabled')?></div>-->
				{{/prod_cod}}
                {{#prod_out_of_stock}}					
                     <div class="out-stock-text">
                     	<a class="ppc_promotion_click" href="{{prod_url}}"></a>
		                 <span class="txt"><?php echo Utilities::getLabel('M_Out_of_Stock')?></span>
	                 </div>
                {{/prod_out_of_stock}}
                    
                <div class="image"><a itemprop="url" class="ppc_promotion_click" href="{{prod_url}}">
                <img class="img-responsive" src="{{prod_image_url}}" alt="{{prod_name}}"></a></div>
                
                <div class="caption"> <span class="name product-name-red"> 
                	<a itemprop="url" class="ppc_promotion_click" href="{{prod_url}}">
                	   <div itemprop="name">
						{{prod_short_name}}
                       </div>
                    </a></span>
                    <div itemprop="manufacturer" itemscope itemtype="http://schema.org/Organization"> 
	                  	<span class="brand-name">
	                        <div itemprop="name">
    	            			<a itemprop="url" href="{{prod_shop_url}}">
                            		{{shop_name}}
	                            </a>
                            </div>
	    	            </span> 
                    </div>
                </div>
                
                {{#prod_sale_price}}	
					<div class="price" itemprop="offers" itemscope itemtype="http://schema.org/Offer"> 
                        {{#special}}
							<span class="price-new" itemprop="price">{{prod_special}}</span> <span class="price-old">{{prod_price}}</span>
						{{/special}}
                        {{^special}}
                        <span class="price-new" itemprop="price">{{prod_price}}</span> 
						{{/special}}	
					</div>
				{{/prod_sale_price}}
                 
                 
                 <div class="overlay"> <a href="{{prod_list_url}}" id="product_{{prod_id}}" class="link listButton listView"> <i class="svg-icn">
                <svg width="64" version="1.1" xmlns="http://www.w3.org/2000/svg" height="64" viewBox="0 0 64 64" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 64 64">
                  <g>
                    <g>
                      <path d="m15.068,10.225h45.851c1.128,0 2.041-0.913 2.041-2.041 0-1.126-0.913-2.04-2.041-2.04h-45.851c-1.128,0-2.04,0.914-2.04,2.04 1.77636e-15,1.128 0.912,2.041 2.04,2.041z"/>
                      <path d="m60.919,30.03h-45.851c-1.128,0-2.04,0.914-2.04,2.04s0.912,2.041 2.04,2.041h45.851c1.128,0 2.041-0.915 2.041-2.041s-0.913-2.04-2.041-2.04z"/>
                      <path d="m60.919,53.965h-45.851c-1.128,0-2.04,0.912-2.04,2.04 0,1.126 0.912,2.041 2.04,2.041h45.851c1.128,0 2.041-0.915 2.041-2.041 7.10543e-15-1.128-0.913-2.04-2.041-2.04z"/>
                      <path d="m4.577,12.468c2.413,0 4.366-1.956 4.366-4.369 0-2.414-1.953-4.366-4.366-4.366-2.415,0-4.368,1.952-4.368,4.366 3.60822e-16,2.413 1.953,4.369 4.368,4.369z"/>
                      <path d="m4.577,36.329c2.413,0 4.366-1.955 4.366-4.368s-1.953-4.368-4.366-4.368c-2.415,0-4.368,1.955-4.368,4.368s1.953,4.368 4.368,4.368z"/>
                      <path d="m4.577,51.633c-2.417,0-4.37,1.957-4.37,4.37 0,2.416 1.953,4.37 4.37,4.37 2.413,0 4.368-1.954 4.368-4.37 0-2.413-1.955-4.37-4.368-4.37z"/>
                    </g>
                  </g>
                </svg>
                </i> </a>
                <div class="product_lists listcontainer display-div{{prod_id}}" id="display-div{{prod_id}}" data-href="{{prod_list_url}}"></div>
                <a id="item_{{prod_id}}" class="favourite itemfav link {{#prod_favorite}} active {{/prod_favorite}}" 
                title="
                		{{#prod_favorite}}
							<?php echo Utilities::getLabel('L_Un-Favorite');?>
						{{/prod_favorite}}
                        {{^prod_favorite}}
                        	<?php echo Utilities::getLabel('L_Favorite'); ?>
						{{/prod_favorite}}
                        
                " href="javascript:void(0)"> <i class="svg-icn">
                <svg  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
width="511.626px" height="511.627px" viewBox="0 0 511.626 511.627" style="enable-background:new 0 0 511.626 511.627;"
xml:space="preserve">
 <g>
   <path d="M475.366,71.951c-24.175-23.606-57.575-35.404-100.215-35.404c-11.8,0-23.843,2.046-36.117,6.136
c-12.279,4.093-23.702,9.615-34.256,16.562c-10.568,6.945-19.65,13.467-27.269,19.556c-7.61,6.091-14.845,12.564-21.696,19.414
c-6.854-6.85-14.087-13.323-21.698-19.414c-7.616-6.089-16.702-12.607-27.268-19.556c-10.564-6.95-21.985-12.468-34.261-16.562
c-12.275-4.089-24.316-6.136-36.116-6.136c-42.637,0-76.039,11.801-100.211,35.404C12.087,95.552,0,128.288,0,170.162
c0,12.753,2.24,25.889,6.711,39.398c4.471,13.514,9.566,25.031,15.275,34.546c5.708,9.514,12.181,18.796,19.414,27.837
c7.233,9.042,12.519,15.27,15.846,18.699c3.33,3.422,5.948,5.899,7.851,7.419L243.25,469.937c3.427,3.429,7.614,5.144,12.562,5.144
s9.138-1.715,12.563-5.137l177.87-171.307c43.588-43.583,65.38-86.41,65.38-128.475C511.626,128.288,499.537,95.552,475.366,71.951
z"/>
 </g></svg>
                </i> </a> </div>
                   
                 
                <div class="over-btn-grp"> <a href="javascript:void(0);" onclick="cart.add('{{prod_id}}', '1');" class="btn secondary-btn" title="<?php echo Utilities::getLabel('L_Add_to_cart')?>">
                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                  <path fill="#474747" d="M70.1,11.7h-7l-7,21H25.7l-7-23.3c0,0-2.8-9.2-7-9.3h-7c0,0-5.5,1.9-4.7,4.7c0,0,0.3,6.7,9.3,2.3l11.7,35
	h39.6L70.1,11.7z"/>
                  <path fill="#474747" d="M29.2,46.3c3.2,0,5.8,2.6,5.8,5.8s-2.6,5.8-5.8,5.8s-5.8-2.6-5.8-5.8C23.4,49,26,46.3,29.2,46.3z"/>
                  <path fill="#474747" d="M52.4,46.3c3.2,0,5.8,2.6,5.8,5.8s-2.6,5.8-5.8,5.8s-5.8-2.6-5.8-5.8C46.5,49,49.2,46.3,52.4,46.3z"/>
                  <rect x="24.4" y="10.5" fill="#474747" width="28" height="7"/>
                  <rect x="34.9" y="0" fill="#474747" width="7" height="28"/>
                </svg>
                </a> <a href="{{prod_url}}" class="ppc_promotion_click btn secondary-btn" title="<?php echo Utilities::getLabel('L_Product_View')?>">
                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="-89 80 70 70" enable-background="new -89 91 70 70" xml:space="preserve">
                  <g>
                    <g>
                      <path fill="#ffffff" d="M-54,91c-21.8,0-34.9,21.7-34.9,21.7s13.1,22,34.9,22s34.9-21.8,34.9-21.8S-32.2,91-54,91z M-54,130.3
			c-19.1,0-29.5-17.5-29.5-17.5S-73.1,95.4-54,95.4s29.5,17.5,29.5,17.5S-34.9,130.3-54,130.3z"/>
                      <circle fill="#ffffff" cx="-54" cy="112.8" r="13.1"/>
                    </g>
                  </g>
                </svg>
                </a></div>
                
              </div>
{{/products}}
