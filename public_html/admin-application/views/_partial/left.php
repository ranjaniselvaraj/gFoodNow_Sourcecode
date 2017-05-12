        <span class="leftoverlay"></span>
        <aside class="leftside">
            
            <div class="sidebar_inner">
                 <div class="profilewrap">
                        <div class="profilecover">
                            <figure class="profilepic"><img src="<?php echo Utilities::generateUrl('image','user', array($admin_image,"small"),CONF_WEBROOT_URL); ?>" alt=""></figure>
                            <span class="profileinfo">Welcome <?php echo (isset($admin_name)?$admin_name:''); ?></span>
                        </div>    
                        
                        <div class="profilelinkswrap">
                            <ul class="leftlinks">
                                <li><a href="<?php echo Utilities::generateUrl('admin', 'profile'); ?>">View Profile</a></li>												
								<li><a href="<?php echo Utilities::generateUrl('admin', 'change_password'); ?>">Change Password</a></li>
                                <li><a href="<?php echo Utilities::generateUrl('admin', 'logout'); ?>">Logout</a></li>
                            </ul>   
                        </div>    
                    </div> 
            
           
           
                    <ul class="leftmenu">
						<?php if (Admin::getAdminAccess($admin_id,DASHBOARD)):?>
                        <li <?php if ($controller=="home"):?>class="current" <?php endif; ?>><a href="<?php echo Utilities::generateUrl('home'); ?>">Dashboard</a></li>
						<?php endif;?>
						  <?php if ((Admin::getAdminAccess($admin_id,SHOPS)) || (Admin::getAdminAccess($admin_id,BRANDS)) || (Admin::getAdminAccess($admin_id,PRODUCTCATEGORIES)) || (Admin::getAdminAccess($admin_id,PRODUCTS)) || (Admin::getAdminAccess($admin_id,PRODUCTTAGS)) || (Admin::getAdminAccess($admin_id,OPTIONS))   || (Admin::getAdminAccess($admin_id,FILTERGROUPOPTIONS)) || (Admin::getAdminAccess($admin_id,ATTRIBUTES)))					
					:?>
		                    <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="shops") || ($controller=="brands") || ($controller=="categories") || ($controller=="products") || ($controller=="reviews") || ($controller=="producttags") || ($controller=="property") || ($controller=="options") || ($controller=="filters") || ($controller=="attributegroups") || ($controller=="attributes")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif; ?>
                    
                        <li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Catalog</a>
                            <ul class="<?php echo $catalog_ul_class;?>">
                                 <?php if (Admin::getAdminAccess($admin_id,SHOPS)):?>
                                   	<li class="<?php if ($controller=="shops") echo 'active'?>"  ><a href="<?php echo Utilities::generateUrl('shops'); ?>">Shops</a></li>
                                    <?php endif; ?>
                                    <?php if (Admin::getAdminAccess($admin_id,BRANDS)):?>
									<li class="<?php if ($controller=="brands") echo 'active'?>" ><a href="<?php echo Utilities::generateUrl('brands'); ?>">Product Brands</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,PRODUCTCATEGORIES)):?>
                                    <li class="<?php if ($controller=="categories") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('categories'); ?>">Product Categories</a></li>
                                    <?php endif;?>
                                    
                                    <?php if (Admin::getAdminAccess($admin_id,PRODUCTS)):?>
									<li class="<?php if ($controller=="products") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('products'); ?>">Products</a></li>
                                    <li class="<?php if ($controller=="reviews") echo 'active'?>" ><a href="<?php echo Utilities::generateUrl('reviews'); ?>">Product Reviews</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,PRODUCTTAGS)):?>
                                    <li class="<?php if ($controller=="producttags") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('producttags'); ?>">Product Tags</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,OPTIONS)):?>
									<li class="<?php if ($controller=="options" && $action=="admin") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('options','admin'); ?>">Admin Options</a></li>
                                    <li class="<?php if ($controller=="options" && $action=="suppliers") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('options','suppliers'); ?>">Seller Options</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,FILTERGROUPOPTIONS)):?>
									<li class="<?php if ($controller=="filters") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('filters'); ?>">Filters</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,ATTRIBUTES)):?>
									<li class="<?php if ($controller=="attributes") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('attributes'); ?>">Attributes/Specifications</a></li>
                                    <?php endif;?>
                            </ul>
                        </li>
						<?php endif;?>
						<?php if ((Admin::getAdminAccess($admin_id,CUSTOMERS)) || (Admin::getAdminAccess($admin_id,WITHDRAWAL_REQUESTS)) || (Admin::getAdminAccess($admin_id,CANCELLATION_REQUESTS)) || (Admin::getAdminAccess($admin_id,SUPPLIER_APPROVAL_REQUESTS)) || (Admin::getAdminAccess($admin_id,SUPPLIER_APPROVAL_FORM))  || (Admin::getAdminAccess($admin_id,SUPPLIER_REQUESTS)) ):?>
                        
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="users" && $action!="advertisers") || (($controller=="custom") && ($action=="cancellation_requests")) || (($controller=="custom") && ($action=="withdrawal_requests"))): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif; ?>
                             
                             
                      <li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Buyers/Sellers</a>
						<ul class="<?php echo $catalog_ul_class;?>">
							<?php if (Admin::getAdminAccess($admin_id,CUSTOMERS)):?>
							 <li class="<?php if ($controller=="users" && $action=='default_action') echo 'active'?>"><a href="<?php echo Utilities::generateUrl('users'); ?>">Manage Buyers/Sellers</a></li>
							<?php endif; ?>
							<?php if (Admin::getAdminAccess($admin_id,CANCELLATION_REQUESTS)):?>
							<li class="<?php if ($controller=="users" && $action=='cancellation_requests') echo 'active'?>"><a href="<?php echo Utilities::generateUrl('users','cancellation_requests'); ?>">Order Cancellation Requests</a></li>
							<?php endif;?>
                            <?php if (Admin::getAdminAccess($admin_id,WITHDRAWAL_REQUESTS)):?>
								<li class="<?php if ($controller=="custom" && $action=='withdrawal_requests') echo 'active'?>"><a href="<?php echo Utilities::generateUrl('custom','withdrawal_requests'); ?>">Funds Withdrawal Requests</a></li>
							<?php endif;?>
							<?php if (Admin::getAdminAccess($admin_id,SUPPLIER_APPROVAL_REQUESTS)):?>
							<li class="<?php if ($controller=="users" && $action=="supplier_approval_requests") echo 'active'?>" ><a href="<?php echo Utilities::generateUrl('users','supplier_approval_requests'); ?>">Seller Approval Requests</a></li>
							<?php endif;?>
							<?php if (Admin::getAdminAccess($admin_id,SUPPLIER_APPROVAL_FORM)):?>
							<li class="<?php if ($controller=="users" && $action=='supplier_form') echo 'active'?>"><a href="<?php echo Utilities::generateUrl('users','supplier_form'); ?>">Seller Approval Form</a></li>
							<?php endif;?>
                            <?php if (Admin::getAdminAccess($admin_id,SUPPLIER_REQUESTS)):?>
							<li class="<?php if ($controller=="users" && $action=='supplier_requests') echo 'active'?>"><a href="<?php echo Utilities::generateUrl('users','supplier_requests'); ?>">Seller Requests</a></li>
							<?php endif;?>
						</ul>
						<?php endif;?>
                   		
                        
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="affiliates")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>
                        <?php if (Admin::getAdminAccess($admin_id,AFFILIATES)):?>
						<li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Affiliates</a>
                            <ul class="<?php echo $catalog_ul_class?>">
								<li class="<?php if ($controller=="affiliates" && $action=='default_action') echo 'active'?>"><a href="<?php echo Utilities::generateUrl('affiliates'); ?>">Affiliate Users</a></li>
								<li class="<?php if ($controller=="affiliates" && $action=='withdrawal_requests') echo 'active'?>"><a href="<?php echo Utilities::generateUrl('affiliates','withdrawal_requests'); ?>">Funds Withdrawal Requests</a></li>
                            </ul>
                        </li>
						<?php endif; ?>
                        
                           
																	
						<?php if ((Admin::getAdminAccess($admin_id,COLLECTIONS)) || (Admin::getAdminAccess($admin_id,NAVIGATION)) || (Admin::getAdminAccess($admin_id,CONTENTPAGES))  || (Admin::getAdminAccess($admin_id,EXTRACONTENTPAGE)) || (Admin::getAdminAccess($admin_id,LANGUAGELABELS)) || (Admin::getAdminAccess($admin_id,SLIDES)) || (Admin::getAdminAccess($admin_id,BANNERS)) || (Admin::getAdminAccess($admin_id,EMPTYCARTITEMS))  || (Admin::getAdminAccess($admin_id,FAQCATEGORIES)) || (Admin::getAdminAccess($admin_id,FAQMANAGEMENT))  || (Admin::getAdminAccess($admin_id,TESTIMONIALS))  || (Admin::getAdminAccess($admin_id,REPORTREASONS)) || (Admin::getAdminAccess($admin_id,CANCELREASONS)) || (Admin::getAdminAccess($admin_id,RETURNREASONS))  || (Admin::getAdminAccess($admin_id,ORDERSTATUS)) || (Admin::getAdminAccess($admin_id,SHIPPINGCOMPANY)) || (Admin::getAdminAccess($admin_id,SHIPPINGDURATION)) || (Admin::getAdminAccess($admin_id,DISCOUNTCOUPONS)) || (Admin::getAdminAccess($admin_id,SOCIALPLATFORMS)) ):?>
                        
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="collections") || ($controller=="navigations") || ($controller=="cms") || ($controller=="extrapage") || ($controller=="banners") || ($controller=="slides") || ($controller=="emptycartitems") || ($controller=="cancelreasons") || ($controller=="labels") || ($controller=="faqcategories") || ($controller=="faqs") || ($controller=="testimonials") || ($controller=="reportreasons") || ($controller=="reportreasons") || ($controller=="returnreasons") || ($controller=="orderstatus") || ($controller=="shippingcompany") || ($controller=="shippingduration") || ($controller=="coupons") || ($controller=="socialmedia")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>                        
                        <li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">CMS</a>
                            <ul class="<?php echo $catalog_ul_class?>">
                                <?php if (Admin::getAdminAccess($admin_id,COLLECTIONS)):?>
                                    <li class="<?php if ($controller=="collections") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('collections'); ?>">Collections Management</a></li>
                                    <?php endif;?>
									<?php if (Admin::getAdminAccess($admin_id,NAVIGATION)):?>
                                    <li class="<?php if ($controller=="navigations") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('navigations'); ?>">Navigations Management</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,CONTENTPAGES)):?>
									<li class="<?php if ($controller=="cms") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('cms'); ?>">Content Pages</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,EXTRACONTENTPAGE)):?>
									<li class="<?php if ($controller=="extrapage") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('extrapage'); ?>">Content Block</a></li>
                                    <?php endif;?>
									<?php if (Admin::getAdminAccess($admin_id,LANGUAGELABELS)):?>
									<li class="<?php if ($controller=="labels") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('labels'); ?>">Language Labels</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,SLIDES)):?>
                                    <li class="<?php if ($controller=="slides") echo 'active'?>" ><a href="<?php echo Utilities::generateUrl('slides'); ?>">Slides Management</a></li>
                                    <?php endif;?>
									<?php if (Admin::getAdminAccess($admin_id,BANNERS)):?>
                                    <li class="<?php if ($controller=="banners") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('banners'); ?>">Banner Management</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,EMPTYCARTITEMS)):?>
                                    <li class="<?php if ($controller=="emptycartitems") echo 'active'?>" ><a href="<?php echo Utilities::generateUrl('emptycartitems'); ?>">Empty Cart Items Management</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,FAQCATEGORIES)):?>
									<li class="<?php if ($controller=="faqcategories") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('faqcategories'); ?>">FAQ Category Management</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,FAQMANAGEMENT)):?>
									<li class="<?php if ($controller=="faqs") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('faqs'); ?>">FAQs Management</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,TESTIMONIALS)):?>
									<li class="<?php if ($controller=="testimonials") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('testimonials'); ?>">Testimonials Management</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,REPORTREASONS)):?>
                                    <li class="<?php if ($controller=="reportreasons") echo 'active'?>" ><a href="<?php echo Utilities::generateUrl('reportreasons'); ?>">Report Reasons</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,CANCELREASONS)):?>
                                    <li class="<?php if ($controller=="cancelreasons") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('cancelreasons'); ?>">Cancel Reasons</a></li>
                                    <?php endif;?>
									<?php if (Admin::getAdminAccess($admin_id,RETURNREASONS)):?>
                                    <li class="<?php if ($controller=="returnreasons") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('returnreasons'); ?>">Return Reasons</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,ORDERSTATUS)):?>
                                    <!--<li><a href="<?php echo Utilities::generateUrl('orderstatus'); ?>">Order Statuses</a></li>-->
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,SHIPPINGCOMPANY)):?>
                                    <li class="<?php if ($controller=="shippingcompany") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('shippingcompany'); ?>">Shipping Companies</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,SHIPPINGDURATION)):?>
                                    <li class="<?php if ($controller=="shippingduration") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('shippingduration'); ?>">Shipping Duration Labels</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,DISCOUNTCOUPONS)):?>
                                    <li class="<?php if ($controller=="coupons") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('coupons'); ?>">Discount Coupons</a></li>
                                    <?php endif;?>
                                    <?php if (Admin::getAdminAccess($admin_id,SOCIALPLATFORMS)):?>
                                    <li class="<?php if ($controller=="socialmedia") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('socialmedia'); ?>">Social Platforms Management</a></li>
                                    <?php endif;?>
                            </ul>
                        </li>
						<?php endif; ?>
						 <?php if ((Admin::getAdminAccess($admin_id,ZONES)) || (Admin::getAdminAccess($admin_id,COUNTRIES)) || (Admin::getAdminAccess($admin_id,STATES)) || (Admin::getAdminAccess($admin_id,CURRENCY)) || (Admin::getAdminAccess($admin_id,GENERALSETTINGS)) || (Admin::getAdminAccess($admin_id,COMMISSIONSETTINGS)) || (Admin::getAdminAccess($admin_id,AFFILIATE_COMMISSION_SETTINGS)) || (Admin::getAdminAccess($admin_id,THEMES)) || (Admin::getAdminAccess($admin_id,PAYMENTMETHODS)) || (Admin::getAdminAccess($admin_id,EMAIL_TEMPLATES)) || (Admin::getAdminAccess($admin_id,DATABASE_BACKUP_RESTORE))  || (Admin::getAdminAccess($admin_id,SERVER_INFO))):?>
						
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="zones") || ($controller=="commissions") || ($controller=="affiliatecommissions") || ($controller=="themes") || ($controller=="paymentmethods") || ($controller=="databasebackuprestore") || ($controller=="countries") || ($controller=="states") || ($controller=="currency") || ($controller=="configurations") || ($controller=="promotions") || ($controller=="emailtemplates")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>        
                        <li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Settings</a>
                            <ul class="<?php echo $catalog_ul_class?>">
								<?php if (Admin::getAdminAccess($admin_id,COUNTRIES)):?>
								<li class="<?php if ($controller=="countries") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('countries'); ?>">Country Management</a></li>
								<?php endif;?>
                                <?php if (Admin::getAdminAccess($admin_id,ZONES)):?>
								<li class="<?php if ($controller=="zones") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('zones'); ?>">Zone Management</a></li>
								<?php endif;?>
								<?php if (Admin::getAdminAccess($admin_id,STATES)):?>
								<li class="<?php if ($controller=="states") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('states'); ?>">State Management</a></li>
								<?php endif;?>
                                <?php if (Admin::getAdminAccess($admin_id,CURRENCY)):?>
								<li class="<?php if ($controller=="currency") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('currency'); ?>">Currency Management</a></li>
								<?php endif;?>
								<?php if (Admin::getAdminAccess($admin_id,GENERALSETTINGS)):?>
								<li class="<?php if ($controller=="configurations" && $action=="default_action") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('configurations'); ?>">General Settings</a></li>
								<?php endif;?>
                                <?php if (Admin::getAdminAccess($admin_id,COMMISSIONSETTINGS)):?>
								<li class="<?php if ($controller=="commissions") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('commissions'); ?>">Commission Settings</a></li>
								<?php endif;?>
                                <?php if (Admin::getAdminAccess($admin_id,AFFILIATE_COMMISSION_SETTINGS)):?>
								<li class="<?php if ($controller=="affiliatecommissions") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('affiliatecommissions'); ?>">Affiliate Commission Settings</a></li>
								<?php endif;?>
                                <?php if (Admin::getAdminAccess($admin_id,THEMES)):?>
								<li class="<?php if ($controller=="themes") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('themes'); ?>">Themes Settings</a></li>
								<?php endif;?>
								<?php if (Admin::getAdminAccess($admin_id,PAYMENTMETHODS)):?>
								<li class="<?php if ($controller=="paymentmethods") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('paymentmethods'); ?>">Payment Methods</a></li>
								<?php endif;?>
                                
                                
								<?php if (Admin::getAdminAccess($admin_id,EMAIL_TEMPLATES)):?>
								<li class="<?php if ($controller=="emailtemplates") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('emailtemplates'); ?>">Email Templates</a></li>
								<?php endif;?>
								<?php if (Admin::getAdminAccess($admin_id,DATABASE_BACKUP_RESTORE)): ?>
								<li class="<?php if ($controller=="databasebackuprestore") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('databasebackuprestore'); ?>">Database Backup & Restore</a></li>
								<?php endif;?>
								<?php if (Admin::getAdminAccess($admin_id,SERVER_INFO)):?>
								<li class="<?php if ($controller=="configurations" && $action=="view_server_info") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('configurations','view_server_info'); ?>">Server Info</a></li>
								<?php endif;?>
                            </ul>
                        </li>
						<?php endif; ?>
						<?php if ((Admin::getAdminAccess($admin_id,ORDERS)) || (Admin::getAdminAccess($admin_id,VENDORORDERS))  || (Admin::getAdminAccess($admin_id,RETURN_REQUESTS)) || (Admin::getAdminAccess($admin_id,PAYPAL_ADAPTIVE_PAYMENTS)) ):?>
                        
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="orders") || ($controller=="vendororders") || ($controller=="paypaladaptive")  || ($controller=="returnrequests")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>  
						<li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Orders</a>
                            <ul class="<?php echo $catalog_ul_class?>">
								<?php if (Admin::getAdminAccess($admin_id,ORDERS)):?>
								 <li class="<?php if ($controller=="orders") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('orders'); ?>">Customer Orders</a></li>
								<?php endif; ?>
								<?php if (Admin::getAdminAccess($admin_id,VENDORORDERS)):?>
								<li class="<?php if ($controller=="vendororders") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('vendororders'); ?>">Vendor Orders</a></li>
								<?php endif;?>
                                <?php if (Admin::getAdminAccess($admin_id,PAYPAL_ADAPTIVE_PAYMENTS)):?>
								 <li class="<?php if ($controller=="paypaladaptive" && $action=='payments') echo 'active'?>"><a href="<?php echo Utilities::generateUrl('paypaladaptive','payments'); ?>">PayPal Adaptive Payments</a></li>
								<?php endif; ?>
								
								<?php if (Admin::getAdminAccess($admin_id,RETURN_REQUESTS)):?>
								<li class="<?php if ($controller=="returnrequests") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('returnrequests'); ?>">Return Requests</a></li>
								<?php endif;?>
                            </ul>
                        </li>
						<?php endif;?>
						<?php if (Admin::getAdminAccess($admin_id,REPORTS)):?>
                        
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="reports")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>
						<li class="haschild <?php if (($controller=="reports")):?>current<?php endif; ?>"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Reports</a>
                            <ul class="<?php echo $catalog_ul_class?>">
								<li class="<?php if ($controller=="reports" && $action=="sales") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','sales'); ?>">Sales</a></li>
								<li class="<?php if ($controller=="reports" && $action=="users") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','users'); ?>">Users</a></li>
								<li class="<?php if ($controller=="reports" && $action=="products") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','products'); ?>">Products</a></li>
								<li class="<?php if ($controller=="reports" && $action=="shops") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','shops'); ?>">Shops</a></li>
								<li class="<?php if ($controller=="reports" && $action=="tax") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','tax'); ?>">Tax</a></li>
								<li class="<?php if ($controller=="reports" && $action=="commissions") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','commissions'); ?>">Commissions</a></li>
                                <li class="<?php if ($controller=="reports" && $action=="affiliates") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','affiliates'); ?>">Affiliates</a></li>
                                <li class="<?php if ($controller=="reports" && $action=="advertisers") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','advertisers'); ?>">Advertisers</a></li>
                                <li class="<?php if ($controller=="reports" && $action=="promotions") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','promotions'); ?>">Promotions</a></li>
                                <li class="<?php if ($controller=="reports" && $action=="subscriptions") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('reports','subscriptions'); ?>">Subscriptions</a></li>
                            </ul>
                        </li>
						<?php endif; ?>
                        <?php if ((Admin::getAdminAccess($admin_id,SUBSCRIPTION_PACKAGES)) || (Admin::getAdminAccess($admin_id,SUBSRIPTIONDISCOUNTCOUPONS)) || (Admin::getAdminAccess($admin_id,SUBSCRIPTIONORDERS)) || (Admin::getAdminAccess($admin_id,SUBSCRIPTIONPAYMENTMETHODS))):?>
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="subscriptionpaymentmethods") || ($controller=="subscriptionpackages") || ($controller=="subscriptioncoupons") || ($controller=="subscriptionorders")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>
                        <li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Subscription</a>
                            <ul class="<?php echo $catalog_ul_class?>">								
								<li class="<?php if ($controller=="subscriptionpaymentmethods") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('subscriptionpaymentmethods'); ?>">Payment Methods</a></li>
								<li class="<?php if ($controller=="subscriptionpackages") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('subscriptionpackages'); ?>">Packages</a></li>			
								<li class="<?php if ($controller=="subscriptioncoupons") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('subscriptioncoupons'); ?>">Coupons</a></li>			
								<li class="<?php if ($controller=="subscriptionorders") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('subscriptionorders'); ?>">Orders</a></li>		   </ul>
                        </li>
						<? endif;?>
                        
                        <? if (Admin::getAdminAccess($admin_id,EXPORT_IMPORT)):?>
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="importexport")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>
                        <li class="haschild <? if (($controller=="export_import")):?>current<? endif; ?>"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Export / Import</a>
                            <ul class="<?php echo $catalog_ul_class?>">								
								<li class="<?php if ($controller=="importexport" && $query=="export") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('importexport','default_action',array('export')); ?>">Export</a></li>			
                                <li class="<?php if ($controller=="importexport" && $query=="import") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('importexport','default_action',array('import')); ?>">Import</a></li>			
								<li class="<?php if ($controller=="importexport" && $query=="settings") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('importexport','default_action',array('settings')); ?>">Settings</a></li>		   </ul>
                        </li>
						<? endif;?>
                        
                        <?php if ((Admin::getAdminAccess($admin_id,SMART_RECOMENDED_WEIGHTAGES)) || (Admin::getAdminAccess($admin_id,SMART_RECOMENDED_PRODUCTS))):?>
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="smartrecommendations")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>
						<li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Smart Recommendations</a>
                            <ul class="<?php echo $catalog_ul_class?>">
                            	<?php if (Admin::getAdminAccess($admin_id,SMART_RECOMENDED_WEIGHTAGES)):?>
								<li class="<?php if ($controller=="smartrecommendations" && $action=="weightages") echo 'active'?>" ><a href="<?php echo Utilities::generateUrl('smartrecommendations','weightages'); ?>">Manage Weightages</a></li>
                                <?php endif;?>
                                <?php if (Admin::getAdminAccess($admin_id,SMART_RECOMENDED_PRODUCTS)):?>
								<li class="<?php if ($controller=="smartrecommendations" && $action=="products") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('smartrecommendations','products'); ?>">Manage Recommendations</a></li>
                                
                                <li class="<?php if ($controller=="smartrecommendations" && $action=="products_browsing_history") echo 'active'?>" ><a href="<?php echo Utilities::generateUrl('smartrecommendations','products_browsing_history'); ?>">Products Browsing History</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
						<?php endif; ?> 
                        
                        <? if (Admin::getAdminAccess($admin_id,PPCFEESETTINGS) || Admin::getAdminAccess($admin_id,PPC_PROMOTIONS) || Admin::getAdminAccess($admin_id,PPCPAYMENTMETHODS)  || Admin::getAdminAccess($admin_id,ADVERTISERS)):?>
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="ppcpaymentmethods") || ($controller=="ppc" && ($action=="promotions")) || ($controller=="users" && ($action=="advertisers"))): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>
                        <li class="haschild <? if (($controller=="ppcfees")):?>current<? endif; ?>"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">PPC Management</a>
                            <ul class="<?php echo $catalog_ul_class?>">
                            	<?php if (Admin::getAdminAccess($admin_id,ADVERTISERS)):?>								
								<li class="<?php if ($controller=="users" && $action=="advertisers") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('users','advertisers'); ?>">Advertisers</a></li>
                                <?php endif;?>
                                
                                <?php if (Admin::getAdminAccess($admin_id,PPCPAYMENTMETHODS)):?>
								<li class="<?php if ($controller=="ppcpaymentmethods") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('ppcpaymentmethods'); ?>">PPC Payment Methods</a></li>
								<?php endif;?>
								<?php if (Admin::getAdminAccess($admin_id,PPC_PROMOTIONS)):?>
								<li class="<?php if ($controller=="ppc" && $action=="promotions") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('ppc','promotions'); ?>">PPC Promotions</a></li>
								<?php endif;?>
						    </ul>
                        </li>
						<? endif;?>   
                        
                        
                        <? if (Admin::getAdminAccess($admin_id,BLOG_CATEGORIES) || Admin::getAdminAccess($admin_id,BLOG_POSTS) || Admin::getAdminAccess($admin_id,BLOG_CONTRIBUTIONS)  || Admin::getAdminAccess($admin_id,BLOG_COMMENTS)):?>
                        <?php 
							$catalog_menu_class=''; $catalog_ul_class = '';
							if (($controller=="blogcategories"||$controller=="blogposts"||$controller=="blogcontributions"||$controller=="blogcomments")): 
								$catalog_menu_class = 'active';
								$catalog_ul_class = 'show';
							 endif;
					 	?>
							<li class="haschild"><a class="<?php echo $catalog_menu_class?>" href="javascript:void(0)">Blog</a>
                            <ul class="<?php echo $catalog_ul_class?>">
								<?php if (Admin::getAdminAccess($admin_id,BLOG_CATEGORIES)):?>
								<li class="<?php if ($controller=="blogcategories") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('blogcategories', ''); ?>">Categories</a></li>
								<?php endif;?>
                                <?php if (Admin::getAdminAccess($admin_id,BLOG_POSTS)):?>
								<li class="<?php if ($controller=="blogposts") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('blogposts', ''); ?>">Posts</a></li>
                                <?php endif;?>
								<?php if (Admin::getAdminAccess($admin_id,BLOG_CONTRIBUTIONS)):?>
                                <li class="<?php if ($controller=="blogcontributions") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('blogcontributions', ''); ?>">Contributions</a></li>
                                <?php endif;?>
								<?php if (Admin::getAdminAccess($admin_id,BLOG_COMMENTS)):?>
								<li class="<?php if ($controller=="blogcomments") echo 'active'?>"><a href="<?php echo Utilities::generateUrl('blogcomments', ''); ?>">Comments</a></li>
                                <?php endif;?>
                            </ul>
                        </li>
						<?php endif;?>    
                        
						<?php if (Admin::getAdminAccess($admin_id,MESSAGES)):?>
                        <li <?php if (($controller=="messages")):?>class="active" <?php endif; ?>><a href="<?php echo Utilities::generateUrl('messages'); ?>">Messages</a></li>
						<?php endif;?>
						<?php if (Admin::getAdminAccess($admin_id,SUBADMINS)):?>
                        <li <?php if (($controller=="admin")):?>class="active" <?php endif; ?>><a href="<?php echo Utilities::generateUrl('admin');?>">Admin Users</a></li>
						<?php endif;?>                        
                    </ul>               
            </div>
        </aside>        