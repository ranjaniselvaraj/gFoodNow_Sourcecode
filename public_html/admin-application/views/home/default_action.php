<?php  defined('SYSTEM_INIT') or die('Invalid Usage'); global $status_arr; global $payment_status_arr; global $button_status_arr; ?> 
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<!--body start here-->
    <div id="body">               
        <!--left panel start here-->
		<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
		<!--left panel end here-->
        
        <!--right panel start here-->
        <?php include Utilities::getViewsPartialPath().'right.php'; ?>   
        <!--right panel end here-->
        
        <!--main panel start here-->
        <div class="page">
            <div class="fixed_container">
                <div class="row">
					<?php if (Admin::getAdminAccess($admin_id,DASHBOARD)):?>
                    <ul class="cellgrid">
                        <li>
                            <div class="flipbox green">
                                <div class="flipper">
                                    <div class="front">
                                        <div class="iconbox">
                                            <figure class="icon"><img src="<?=CONF_WEBROOT_URL;?>images/admin/box_icon1.png" alt=""></figure>
                                            <span class="value"><span>New Buyer/Seller</span><?php echo $dashboard_info["stats"]["total_users"]['-1']; ?></span>
                                            <div class="areaprogress">
                                                <h6><span><?php echo Utilities::getPercentValue($dashboard_info["stats"]["total_users"]['30'],$dashboard_info["stats"]["total_users"]['-1']);?></span>This month plan %</h6>
                                                <div class="progress">
                                                  <div style="width: <?php echo Utilities::getPercentValue($dashboard_info["stats"]["total_users"]['30'],$dashboard_info["stats"]["total_users"]['-1']);?>" role="progressbar" class="progress-bar"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="back">
                                        <div class="cell">
                                            <div class="group">
                                                <div class="col-sm-6"><span>Total Users</span><?php echo $dashboard_info["stats"]["total_users"]['-1']; ?></div>
                                                <div class="col-sm-6"><span>This Month</span><?php echo $dashboard_info["stats"]["total_users"]['30']; ?></div>
                                            </div>
                                            <a href="<?php echo Utilities::generateUrl('users'); ?>" class="themebtn btn-default btn-sm">View Summary</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="flipbox orange">
                                <div class="flipper">
                                    <div class="front">
                                        <div class="iconbox">
                                            <figure class="icon"><img src="<?=CONF_WEBROOT_URL;?>images/admin/box_icon2.png" alt=""></figure>
                                            <span class="value"><span>Order Sales </span><?php echo Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][4]["totalsales"]);?></span>
                                            <div class="areaprogress">
                                                <h6><span><?php echo Utilities::getPercentValue($dashboard_info["stats"]["total_sales"][2]["totalsales"],$dashboard_info["stats"]["total_sales"][4]["totalsales"]);?></span>This month plan %</h6>
                                                <div class="progress">
                                                  <div style="width: <?php echo Utilities::getPercentValue($dashboard_info["stats"]["total_sales"][2]["totalsales"],$dashboard_info["stats"]["total_sales"][4]["totalsales"]);?>" role="progressbar" class="progress-bar"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="back">
                                        <div class="cell">
                                            <div class="group">
                                                <div class="col-sm-6"><span>Total Sales</span><?php echo Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][4]["totalsales"]);?></div>
                                                <div class="col-sm-6"><span>This Month</span><?php echo Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][2]["totalsales"]);?></div>
                                            </div>
                                            <a href="<?php echo Utilities::generateUrl('reports','sales'); ?>" class="themebtn btn-default btn-sm">View Summary</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="flipbox purple">
                                <div class="flipper">
                                    <div class="front">
                                        <div class="iconbox">
                                            <figure class="icon">
                                                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                     width="512px" height="512px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                                                <g>
                                                    <path fill="#a48ad4" d="M448,80H64L0,144v16v64c0,35.344,28.688,64,64,64v160c-35.313,0-64,28.656-64,64h512c0-35.344-28.625-64-64-64V288
                                                        c35.375,0,64-28.656,64-64v-64v-16L448,80z M384,224c0,17.656-14.375,32-32,32s-32-14.344-32-32v-64h64V224z M192,224
                                                        c0,17.656-14.344,32-32,32c-17.625,0-32-14.344-32-32v-64h64V224z M224,160h64v64c0,17.656-14.344,32-32,32
                                                        c-17.625,0-32-14.344-32-32V160z M32,224v-64h64v64c0,17.656-14.344,32-32,32C46.375,256,32,241.656,32,224z M96,448V279.125
                                                        c6.063-3.531,11.438-7.938,16-13.188C123.75,279.343,140.813,288,160,288c19.25,0,36.281-8.656,48-22.062
                                                        C219.75,279.343,236.813,288,256,288c11.75,0,22.562-3.375,32-8.875V448H96z M416,448h-96V279.125c9.438,5.5,20.312,8.875,32,8.875
                                                        c19.25,0,36.312-8.656,48-22.062c4.625,5.25,9.938,9.655,16,13.188V448z M480,224c0,17.656-14.375,32-32,32s-32-14.344-32-32v-64
                                                        h64V224z M448,64H64V0h384V64z M368,352c0,8.844-7.125,16-16,16c-8.812,0-16-7.156-16-16s7.188-16,16-16
                                                        C360.875,336,368,343.156,368,352z"/>
                                                </g>
                                                </svg>
                                            </figure>
                                            <span class="value"><span>New Shops</span><?=$dashboard_info["stats"]["total_shops"]['-1']?></span>
                                            <div class="areaprogress">
                                                <h6><span><?php echo Utilities::getPercentValue($dashboard_info["stats"]["total_shops"]['30'],$dashboard_info["stats"]["total_shops"]['-1']);?></span>This month plan %</h6>
                                                <div class="progress">
                                                  <div style="width: <?php echo Utilities::getPercentValue($dashboard_info["stats"]["total_shops"]['30'],$dashboard_info["stats"]["total_shops"]['-1']);?>" role="progressbar" class="progress-bar"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="back">
                                        <div class="cell">
                                            <div class="group">
                                                <div class="col-sm-6"><span>Total Shops</span><?=$dashboard_info["stats"]["total_shops"]['-1']?></div>
                                                <div class="col-sm-6"><span>This Month</span><?=$dashboard_info["stats"]["total_shops"]['30']?></div>
                                            </div>
                                            <a href="<?php echo Utilities::generateUrl('reports','shops');?>" class="themebtn btn-default btn-sm">View Summary</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="flipbox darkgreen">
                                <div class="flipper">
                                    <div class="front">
                                        <div class="iconbox">
                                            <figure class="icon"><img src="<?=CONF_WEBROOT_URL;?>images/admin/box_icon4.png" alt=""></figure>
                                            <span class="value"><span>Sales Earnings</span><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][4]["totalcommission"])?></span>
                                            <div class="areaprogress">
                                                <h6><span><?php echo Utilities::getPercentValue($dashboard_info["stats"]["total_sales"][2]["totalcommission"],$dashboard_info["stats"]["total_sales"][4]["totalcommission"]);?></span>This month plan %</h6>
                                                <div class="progress">
                                                  <div style="width: <?php echo Utilities::getPercentValue($dashboard_info["stats"]["total_sales"][2]["totalcommission"],$dashboard_info["stats"]["total_sales"][4]["totalcommission"]);?>" role="progressbar" class="progress-bar"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="back">
                                        <div class="cell">
                                            <div class="group">
                                                <div class="col-sm-6"><span>Total Profit</span><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][4]["totalcommission"])?></div>
                                                <div class="col-sm-6"><span>This Month</span><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][2]["totalcommission"])?></div>
                                            </div>
                                            <a href="<?php echo Utilities::generateUrl('reports','sales');?>" class="themebtn btn-default btn-sm">View Summary</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                   
                    
                  <div class="col-sm-12">  
                    <section class="section graphPanel">
                        <aside class="grid_1">	
						<div class="paneltop"><h4><strong>Sales</strong> Statistics</h4></div>	
                        
                        <div class="graphcontainer">
                              <div class="tabs_nav_container responsive">
                                    <ul class="tabs_nav">
                                        <li><a class="active" rel="tabs_1" href="javascript:void(0)">Sales</a></li>
                                        <li><a rel="tabs_2" href="javascript:void(0)">Sales Earnings</a></li>
                                        <li><a rel="tabs_3" href="javascript:void(0)">Buyer/Seller Signups</a></li>
                                        <li><a rel="tabs_4" href="javascript:void(0)">Products</a></li>
                                        <li><a rel="tabs_5" href="javascript:void(0)">Affiliate Signups</a></li>
                                    </ul>
                                  
                                  <div class="tabs_panel_wrap">                                       
                                        <!--tab1 start here-->
                                        <span class="togglehead active" rel="tabs_1">Sales</span>
                                        <div id="tabs_1" class="tabs_panel">
                                            <div id="monthlysales" ></div>
                                        </div>
                                      <!--tab1 end here-->
                                      
                                      <!--tab2 start here--> 
                                        <span class="togglehead" rel="tabs_2">Sales Earnings</span>
                                        <div id="tabs_2" class="tabs_panel">											
											<div id="monthlysalesearnings"></div>
                                        </div>
                                      <!--tab2 end here-->       
                                      
                                      <!--tab3 start here-->   
                                        <span class="togglehead" rel="tabs_3">Buyer/Seller Signups</span>
                                        <div id="tabs_3" class="tabs_panel">
											<div id="monthly-signups"></div>
                                        </div>
                                      <!--tab3 end here--> 
									  <!--tab4 start here-->   
                                        <span class="togglehead" rel="tabs_4">Products</span>
                                        <div id="tabs_4" class="tabs_panel">
											<div id="products-listed"></div>
                                        </div>
                                      <!--tab4 end here-->
                                      
                                      <span class="togglehead" rel="tabs_5">Affiliate Signups</span>
                                        <div id="tabs_5" class="tabs_panel">
											<div id="affiliate-monthly-signups"></div>
                                        </div>
                                      
                                  </div>
								 								  
                              </div>
                         </div>
                   
						<!--
                                <div class="paneltop"><h4><strong>Sales</strong> Statistics</h4></div>
                                <div class="graphcontainer" id="monthlysales"></div> -->
                        </aside>
                        <aside class="grid_2">
                            <table class="table graphcontent">
                                <thead>
                                    <th></th>
                                    <th>Total Sales</th>
                                    <th>Orders</th>
                                </thead>
                                <tbody>
                                    <tr class="first">
                                        <td>Today</td>
                                        <td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][0]["totalsales"])?></td>
                                        <td><?=($dashboard_info["stats"]["total_orders"][0]["totalorders"])?></td>
                                    </tr>
                                    <tr>
                                        <td>This Week</td>
                                        <td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][1]["totalsales"])?></td>
                                        <td><?=($dashboard_info["stats"]["total_orders"][1]["totalorders"])?></td>
                                    </tr>
                                    <tr>
                                        <td>This Month</td>
                                        <td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][2]["totalsales"])?></td>
                                        <td><?=($dashboard_info["stats"]["total_orders"][2]["totalorders"])?></td>
                                    </tr>
                                    <tr>
                                        <td>Last 3 Months</td>
                                        <td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][3]["totalsales"])?></td>
                                        <td><?=($dashboard_info["stats"]["total_orders"][3]["totalorders"])?></td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][4]["totalsales"])?></td>
                                        <td><?=($dashboard_info["stats"]["total_orders"][4]["totalorders"])?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </aside>
                    </section>
                      
                  </div> 
                    <?php if($configuredAnalytics){?>
                    <div class="repeatedPanel onehalf_cols">
                        <div class="col-md-6">
                            <section class="section">
                                <div class="paneltop">
                                    <h4><strong>Visitors</strong> Statistics</h4>
                                    <ul class="actions">
                                    </ul>
                                </div>
                                
                                <div class="graphcontainer" id="visitsGraph"></div>                        			
                                <ul class="horizontal_grids">									
                                    <li><?php echo $dashboard_info['visitsCount']['today']?> <span>Today</span></li>
                                    <li><?php echo $dashboard_info['visitsCount']['weekly']?> <span>Weekly</span></li>
                                    <li><?php echo $dashboard_info['visitsCount']['lastMonth']?><span>Last Month</span></li>
                                    <li><?php echo $dashboard_info['visitsCount']['last3Month']?><span>Last 3 Months</span></li>
                                </ul>                            
                                
                            </section>
                        </div>
                        <div class="col-md-6">
                            <section class="section">
                                <div class="paneltop">
                                    <h4><strong>Traffic </strong> Sources</h4>
                                    <ul class="actions">
                                        <li class="droplink">
                                            <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                                            <div class="dropwrap">
                                                <ul class="linksvertical">
                                                    <li><a href="javascript:void(0)" onClick="traficSource('today')">Today</a></li>
                                                    <li><a href="javascript:void(0)" onClick="traficSource('Weekly')">Weekly</a></li>
                                                    <li><a href="javascript:void(0)" onClick="traficSource('Monthly')">Monthly</a></li>
                                                    <li><a href="javascript:void(0)" onClick="traficSource('Yearly')">Yearly</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                               <div class="graphcontainer" id="piechart"><img src="<?php echo CONF_WEBROOT_URL.'images/loading.gif'?>"></div>                              
                               
                            </section>
                        </div>
                    </div> 
                    
                    <div class="repeatedPanel">
                        <div class="col-sm-3">
                            <div class="socialbox fb">
                                <div class="boxtop"><i class="icon ion-social-facebook"></i> <span class="socialname">facebook</span></div>
                                <div class="boxbody">
                                    <span class="left"><?php echo isset($dashboard_info['socialVisits']['rows']['Facebook']['%age'])?$dashboard_info['socialVisits']['rows']['Facebook']['%age']:0;?>%</span>
                                    <span class="right"><?php echo isset($dashboard_info['socialVisits']['rows']['Facebook']['visit'])?$dashboard_info['socialVisits']['rows']['Facebook']['visit']:0;?> Visitors</span>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-sm-3">
                            <div class="socialbox tw">
                                <div class="boxtop"><i class="icon ion-social-twitter"></i> <span class="socialname">twitter</span></div>
                                <div class="boxbody">
                                    <span class="left"><?php echo isset($dashboard_info['socialVisits']['rows']['Twitter']['%age'])?$dashboard_info['socialVisits']['rows']['Twitter']['%age']:0;?>%</span>
                                    <span class="right"><?php echo isset($dashboard_info['socialVisits']['rows']['Twitter']['visit'])?$dashboard_info['socialVisits']['rows']['Twitter']['visit']:0;?> Visitors</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-3">
                            <div class="socialbox li">
                                <div class="boxtop"><i class="icon ion-social-instagram"></i> <span class="socialname">Instagram</span></div>
                                <div class="boxbody">
                                   <span class="left"><?php echo isset($dashboard_info['socialVisits']['rows']['Instagram']['%age'])?$dashboard_info['socialVisits']['rows']['Instagram']['%age']:0;?>%</span>
                                    <span class="right"><?php echo isset($dashboard_info['socialVisits']['rows']['Instagram']['visit'])?$dashboard_info['socialVisits']['rows']['Instagram']['visit']:0;?> Visitors</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-3">
                            <div class="socialbox g">
                                <div class="boxtop"><i class="icon ion-social-reddit"></i> <span class="socialname">Reddit</span></div>
                                <div class="boxbody">
                                   <span class="left"><?php echo isset($dashboard_info['socialVisits']['rows']['Reddit']['%age'])?$dashboard_info['socialVisits']['rows']['Reddit']['%age']:0;?>%</span>
                                    <span class="right"><?php echo isset($dashboard_info['socialVisits']['rows']['Reddit']['visit'])?$dashboard_info['socialVisits']['rows']['Reddit']['visit']:0;?> Visitors</span>
                                </div>
                            </div>
                        </div>
                        
                    </div> 
                                        
                    <div class="repeatedPanel threegrids">
                        <div class="col-sm-12">
                            <section class="section">
                                
                                <div class="v_grid">
                                    <div class="paneltop">
                                        <h4><strong>Top </strong> Referrers</h4>
                                        <ul class="actions">
                                            <li class="droplink">
                                                <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                                                <div class="dropwrap">
                                                    <ul class="linksvertical">
                                                        <li><a href="javascript:void(0)" onClick="topReferers('today')">Today</a></li>
                                                        <li><a href="javascript:void(0)" onClick="topReferers('Weekly')">Weekly</a></li>
                                                        <li><a href="javascript:void(0)" onClick="topReferers('Monthly')">Monthly</a></li>
                                                        <li><a href="javascript:void(0)" onClick="topReferers('Yearly')">Yearly</a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="panelbottom">
                                        <ul class="bulletlist purple topReferers">                                         <li><img src="<?php echo CONF_WEBROOT_URL.'images/loading.gif'?>"></li>
                                        </ul>
                                    </div>
                               </div>
                                
                              <div class="v_grid">
                                    <div class="paneltop">
                                        <h4><strong>Top </strong> Countries</h4>
                                        <ul class="actions">
                                            <li class="droplink">
                                                <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
												<div class="dropwrap">	
                                                <ul class="linksvertical">
                                                        <li><a href="javascript:void(0)" onClick="topCountries('today')">Today</a></li>
                                                        <li><a href="javascript:void(0)" onClick="topCountries('Weekly')">Weekly</a></li>
                                                        <li><a href="javascript:void(0)" onClick="topCountries('Monthly')">Monthly</a></li>
                                                        <li><a href="javascript:void(0)" onClick="topCountries('Yearly')">Yearly</a></li>
                                                    </ul>
												</div>	
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="panelbottom">
                                        <ul class="bulletlist countries topCountries">                                            <li><img src="<?php echo CONF_WEBROOT_URL.'images/loading.gif'?>"></li>
                                        </ul>
                                    </div>
                               </div>
                                
                                
                                
                                <div class="v_grid">
                                    <div class="paneltop">
                                        <h4><strong>Top </strong> Search Terms</h4>
                                        <ul class="actions">
                                            <li class="droplink">
                                                <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                                                <div class="dropwrap">
                                                    <ul class="linksvertical">
                                                        <li><a href="javascript:void(0)" onClick="getTopSearchKeyword('today')">Today</a></li>
                                                        <li><a href="javascript:void(0)" onClick="getTopSearchKeyword('Weekly')">Weekly</a></li>
                                                        <li><a href="javascript:void(0)" onClick="getTopSearchKeyword('Monthly')">Monthly</a></li>
                                                        <li><a href="javascript:void(0)" onClick="getTopSearchKeyword('Yearly')">Yearly</a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="panelbottom">
                                        <ul class="bulletlist green topSearchKeyword">
											<?php /* 
											$count=1;
											if(count($dashboard_info['topSearchKeyword'])>0){
											foreach($dashboard_info['topSearchKeyword'] as $row){ if($count>11){ break;}?>
											<li><?php echo ($row['search_item']=='')?'Blank Search':$row['search_item'];?> <span class="count"><?php echo $row['search_count'];?></span></li>
											<?php $count++;}}else{ echo "No Record Found.";} */?>                                           
                                        </ul>
                                    </div>
                               </div>
                            </section>
                        </div>    
                    </div> 
                    
                    
              
                    
                    
                    <div class="repeatedPanel onehalf_cols new">
                        <div class="col-md-6">
                            <section class="section">
                                <div class="paneltop">
                                    <h4><strong>Conversions</strong> Statistics</h4>                                   
                                </div>                                
                                <ul class="horizontal_gridsthird ">
                                    <li><span>Added to cart</span><?php echo $dashboard_info['conversionStats']['added_to_cart']['%age'];?>% </li>
                                    <li><span>Reached Checkout</span><?php echo $dashboard_info['conversionStats']['reached_checkout']['%age'];?>% </li>
                                    <li><span>Purchased</span><?php echo $dashboard_info['conversionStats']['purchased']['%age'];?>%</li>
                                    <li><span>Cancelled</span><?php echo $dashboard_info['conversionStats']['cancelled']['%age'];?>%</li>
                                </ul>
                                
                                <div class="graphcontainer" id="conversionStats"></div>
                                
                            </section>
                        </div>
                        <div class="col-md-6">
                            <section class="section">
                                <div class="paneltop">
                                    <h4><strong>top </strong> Products</h4>
                                    <ul class="actions">
                                        <li class="droplink">
                                            <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                                            <div class="dropwrap">
                                                <ul class="linksvertical">
                                                    <li><a href="javascript:void(0)" onClick="topProducts('Today')">Today</a></li>
                                                    <li><a href="javascript:void(0)" onClick="topProducts('Weekly')">Weekly</a></li>
                                                    <li><a href="javascript:void(0)" onClick="topProducts('Monthly')">Monthly</a></li>
                                                    <li><a href="javascript:void(0)" onClick="topProducts('Yearly')">Yearly</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                
                                <ul class="bulletlist org topProducts" >
									<?php 
									$count=1;
									if(count($dashboard_info['topProducts'])>0){
									foreach($dashboard_info['topProducts'] as $row){ if($count>11){ break;}?>
                                    <li><?php echo $row['opr_name'];?> <span class="count"><?php echo $row['sold'];?> sold</span></li>
									<?php $count++;}}else{ echo "No Record Found.";}?>                                    
                                </ul>
                                
                            </section>
                        </div>
                    </div> 
                    
                    <?php }?>
                    
                    <div class="col-sm-12">  
                        <section class="section">
                            <div class="sectionhead">
                                <h4>Latest 5 Orders </h4>
                                <!--<a href="" class="themebtn btn-default btn-sm">View All</a>-->
                                <ul class="actions">
                                    <li class="droplink">
                                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                                        <div class="dropwrap">
                                            <ul class="linksvertical">
                                                <li><a href="<?=Utilities::generateUrl('orders')?>">View All</a></li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="sectionbody">
                                <div class="tablewrap">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                               <th width="2%">#</th>
												  <th width="15%">INV</th>
												  <th width="15%">Customer</th>
												  <th width="15%">Date</th>
												  <th width="15%">Amount</th>
												  <th width="15%">Payment Status</th>
												  <th width="8%"></th>
                                            </tr>
                                        </thead>  
                                        <tbody>
                                            <?php $inc=0; foreach ($dashboard_info["orders"] as $sn=>$row) {  $inc++;  $order_obj = new orders($row["order_id"]); ?>
											<tr>
												<td><?=$inc?></td>
												<td><?=$row["order_invoice_number"]?></td>
												<td><?=$row["order_user_name"]?></td>
												<td><?=Utilities::formatDate($row["order_date_added"],true) ?></td>
												<td><?=Utilities::displayMoneyFormat($row["order_net_charged"]) ?></td>
												<td>
												<?php $labelInfo=$button_status_arr[$payment_status_arr[$row["order_payment_status"]]];?>
												<span class="label <?php echo ($labelInfo!='')?$labelInfo:'label-info'; ?>">
												<?=$payment_status_arr[$row["order_payment_status"]]?></span></td>
												<td>
												<ul class="actions">
													<li><a href="<?=Utilities::generateUrl('orders', 'view', array($row['order_id']))?>" title="View Order"><i class="ion-eye icon"></i></a></li>
												</ul></td>
											</tr>
										<?php }?>
                                        </tbody>    
                                    </table>
                                </div>    
                            </div>
                        </section>
                    </div> 
					 <div class="col-sm-12">  	
							<div class="tabs_nav_container responsive boxbased">
									
								<ul class="tabs_nav">
									<li><a class="active" rel="tabs_01" href="javascript:void(0)"><i class="icon ion-arrow-graph-up-right"></i> Statistics</a></li>
									<li><a rel="tabs_02" href="javascript:void(0)"><i class="icon ion-bag"></i> Products</a></li>
									<li><a rel="tabs_03" href="javascript:void(0)"><i class="icon ion-ios-cart"></i> Shops</a></li>
									<li><a rel="tabs_04" href="javascript:void(0)"><i class="icon ion-android-person"></i> Buyer/Seller Signups</a></li>
                                    <li><a rel="tabs_05" href="javascript:void(0)"><i class="icon ion-android-person"></i> Advertiser Signups</a></li>
                                    <li><a rel="tabs_06" href="javascript:void(0)"><i class="icon ion-android-contact"></i>  Affiliate Signups</a></li>
								</ul>
								
								 <div class="tabs_panel_wrap">
											<span class="togglehead active" rel="tabs_01">Statistics</span>
											<div id="tabs_01" class="tabs_panel">
												<table class="table table-striped">
													<thead>
														<tr>
														 <th width="2%"></th>
															<th>Today</th>
															<th>This Week</th>
															<th>This Month</th>
															<th>Last 3 Months</th>
															<th>Total</th>
														</tr>
													</thead>  
													<tbody>
												<?php if($dashboard_info["stats"]["total_users"]!==false): ?>
													<tr>
													<th width="20%">Buyer/Seller Registered</th>
													<td><?php echo $dashboard_info["stats"]["total_users"]['1']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_users"]['7']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_users"]['30']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_users"]['90']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_users"]['-1']; ?></td>
												   </tr>
												<?php endif; ?>
                                                <?php if($dashboard_info["stats"]["total_affiliates"]!==false): ?>
													<tr>
													<th width="25%">Advertisers Registered</th>
													<td><?php echo $dashboard_info["stats"]["total_advertisers"]['1']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_advertisers"]['7']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_advertisers"]['30']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_advertisers"]['90']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_advertisers"]['-1']; ?></td>
												   </tr>
												<?php endif; ?>
												<?php if($dashboard_info["stats"]["total_affiliates"]!==false): ?>
													<tr>
													<th width="25%">Affiliates Registered</th>
													<td><?php echo $dashboard_info["stats"]["total_affiliates"]['1']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_affiliates"]['7']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_affiliates"]['30']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_affiliates"]['90']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_affiliates"]['-1']; ?></td>
												   </tr>
												<?php endif; ?>
												<?php if($dashboard_info["stats"]["total_products"]!==false): ?>
													<tr>
													<th>Products Published</th>
													<td><?php echo $dashboard_info["stats"]["total_products"]['1']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_products"]['7']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_products"]['30']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_products"]['90']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_products"]['-1']; ?></td>
												   </tr>
												<?php endif; ?>
													<tr>
													<th>Number of Shops</th>
													<td><?php echo $dashboard_info["stats"]["total_shops"]['1']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_shops"]['7']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_shops"]['30']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_shops"]['90']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_shops"]['-1']; ?></td>
												   </tr>
												<?php if($dashboard_info["stats"]["total_orders"]!==false): ?>
												   <tr>
													<th>Orders Placed Count</th>
													<td><?=($dashboard_info["stats"]["total_orders"][0]["totalorders"])?></td>
													<td><?=($dashboard_info["stats"]["total_orders"][1]["totalorders"])?></td>
													<td><?=($dashboard_info["stats"]["total_orders"][2]["totalorders"])?></td>
													<td><?=($dashboard_info["stats"]["total_orders"][3]["totalorders"])?></td>
													<td><?=($dashboard_info["stats"]["total_orders"][4]["totalorders"])?></td>
												   </tr>
													<tr>
													<th>Orders Placed Value</th>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][0]["totalsales"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][1]["totalsales"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][2]["totalsales"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][3]["totalsales"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][4]["totalsales"])?></td>
												   </tr>
												   <tr>
													<th>Average Order Value</th>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][0]["avgorder"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][1]["avgorder"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][2]["avgorder"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][3]["avgorder"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_orders"][4]["avgorder"])?></td>
												   </tr>
												   <tr>
												   <th>Sales</th>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][0]["totalsales"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][1]["totalsales"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][2]["totalsales"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][3]["totalsales"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][4]["totalsales"])?></td>
												   </tr>
                                                   <th>Sales Earnings</th>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][0]["totalcommission"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][1]["totalcommission"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][2]["totalcommission"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][3]["totalcommission"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_sales"][4]["totalcommission"])?></td>
												   </tr>
                                                   <th>PPC Earnings</th>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_ppc"][0]["totalppcearnings"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_ppc"][1]["totalppcearnings"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_ppc"][2]["totalppcearnings"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_ppc"][3]["totalppcearnings"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_ppc"][4]["totalppcearnings"])?></td>
												   </tr>
                                                   <th>Subscription Earnings</th>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_subscription"][0]["earnings"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_subscription"][1]["earnings"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_subscription"][2]["earnings"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_subscription"][3]["earnings"])?></td>
													<td><?=Utilities::displayMoneyFormat($dashboard_info["stats"]["total_subscription"][4]["earnings"])?></td>
												   </tr>
												<?php endif; ?>
												<?php if($dashboard_info["stats"]["total_withdrawal_requests"]!==false): ?>
													<tr>
													<th>Withdrawal Requests</th>
													<td><?php echo $dashboard_info["stats"]["total_withdrawal_requests"]['1']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_withdrawal_requests"]['7']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_withdrawal_requests"]['30']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_withdrawal_requests"]['90']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_withdrawal_requests"]['-1']; ?></td>
												   </tr>
												<?php endif; ?>
                                                <?php if($dashboard_info["stats"]["total_affiliate_withdrawal_requests"]!==false): ?>
													<tr>
													<th>Affiliate Withdrawal Requests</th>
													<td><?php echo $dashboard_info["stats"]["total_affiliate_withdrawal_requests"]['1']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_affiliate_withdrawal_requests"]['7']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_affiliate_withdrawal_requests"]['30']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_affiliate_withdrawal_requests"]['90']; ?></td>
													<td><?php echo $dashboard_info["stats"]["total_affiliate_withdrawal_requests"]['-1']; ?></td>
												   </tr>
												<?php endif; ?>
												<?php if($dashboard_info["stats"]["product_reviews"]!==false): ?>
													<tr>
													<th>Product Reviews</th>
													<td><?php echo $dashboard_info["stats"]["product_reviews"]['1']; ?></td>
													<td><?php echo $dashboard_info["stats"]["product_reviews"]['7']; ?></td>
													<td><?php echo $dashboard_info["stats"]["product_reviews"]['30']; ?></td>
													<td><?php echo $dashboard_info["stats"]["product_reviews"]['90']; ?></td>
													<td><?php echo $dashboard_info["stats"]["product_reviews"]['-1']; ?></td>
												   </tr>
												<?php endif; ?>
													</tbody>    
												</table>
											</div>
											<span class="togglehead" rel="tabs_02">Products</span>
											<div id="tabs_02" class="tabs_panel">
												<table class="table table-striped">
													<thead>
														<tr>
														 <th width="2%">#</th>
														  <th width="30%">Name</th>
														  <th width="15%">Brand</th>
														  <th width="15%">Shop</th>
														  <th width="5%">Available</th>
														  <th width="20%">Price</th>
														  <th width="8%"></th>
														</tr>
													</thead>  
													<tbody>
													 <?php $incp=0; foreach ($dashboard_info["products"] as $sn=>$row) { $incp++;  ?>
														<tr>
															<td><?=$incp?></td>
															<td><?=trim($row["prod_name"]) ?></td>
															<td><?=$row["brand_name"] ?></td>
															<td><?=trim($row["shop_name"]) ?></td>
															<td><?=$row["prod_stock"] ?></td>
															<td nowrap="nowrap"><?php if ($row['special']) { ?>
															<span class="cutTxt">
															<?php echo Utilities::displayMoneyFormat($row['prod_sale_price']); ?></span> / 
											<?php echo Utilities::displayMoneyFormat($row['special']); ?>
											<?php } else { ?>
												<?php echo Utilities::displayMoneyFormat($row['prod_sale_price']); ?>
										<?php } ?></td>
															<td>
															<ul class="actions">
																<li><a href="<?=Utilities::generateUrl('products', 'form', array($row['prod_id']))?>" title="View"><i class="ion-eye icon"></i></a></li>
															</ul>															</td>
											</tr>
											<?php }?>
													</tbody>    
												</table>
											</div>
										 
										  
										 
											<span class="togglehead" rel="tabs_03">Shops</span>
											<div id="tabs_03" class="tabs_panel">
												<table class="table table-striped">
													<thead>
														<tr>
														 <th width="3%">#</th>
														  <th width="12%">Shop Owner</th>
														  <th width="12%">Name</th>
														  <th width="15%">Created On</th>
														  <th width="15%">Status</th>
														</tr>
													</thead>  
													<tbody>
														 <?php $shop=0; foreach ($dashboard_info["shops"] as $sn=>$row) { $shop++; //printArray($row); ?>
														<tr>
															<td><?=$shop?></td>
															<td><?=$row["shop_owner_username"]?></td>
															<td><?=$row["shop_name"]?></td>
															<td><?php echo $row['shop_date']; ?></td>
															<td><?=$row["shop_status"]==1?"<span class='label label-success'>Active</span>":"<span class='label label-danger'>In-active</span>"?></td>
														</tr>
														<?php }?>														</tbody>    
												</table>
											</div>
											 
										  
										 
											<span class="togglehead" rel="tabs_04">Buyer/Seller Signups</span>
											<div id="tabs_04" class="tabs_panel">
												<table class="table table-striped">
													<thead>
														<tr>
															<th width="3%">#</th>
														  <th width="12%">Name</th>
														  <th width="12%">Username</th>
														  <th width="15%">Email</th>
														  <th width="15%">Phone</th>
														  <th width="15%">Added On</th>
														</tr>
													</thead>  
													<tbody>
														 <?php $cust=0; foreach ($dashboard_info["users"] as $sn=>$row) { $cust++; ?>
															<tr>
																<td><?=$cust?></td>
																<td><?=$row["user_name"]?></td>
																<td><?=$row["user_username"]?></td>
																<td><?=$row["user_email"]?></td>
																<td><?=$row["user_phone"]?></td>
																<td><?php echo $row['user_added_on']; ?></td>
															</tr>
															<?php }?>														</tbody>    
												</table>
											</div>
                                            
                                            <span class="togglehead" rel="tabs_05">Advertiser Signups</span>
											<div id="tabs_05" class="tabs_panel">
												<table class="table table-striped">
													<thead>
														<tr>
															<th width="3%">#</th>
														  <th width="12%">Name</th>
														  <th width="12%">Username</th>
														  <th width="15%">Email</th>
														  <th width="15%">Phone</th>
														  <th width="15%">Added On</th>
														</tr>
													</thead>  
													<tbody>
														 <?php $advt=0; foreach ($dashboard_info["advertisers"] as $sn=>$row) { $advt++; ?>
															<tr>
																<td><?=$advt?></td>
																<td><?=$row["user_name"]?></td>
																<td><?=$row["user_username"]?></td>
																<td><?=$row["user_email"]?></td>
																<td><?=$row["user_phone"]?></td>
																<td><?php echo $row['user_added_on']; ?></td>
															</tr>
															<?php }?>														</tbody>    
												</table>
											</div>
                                            
                                            <span class="togglehead" rel="tabs_06">Affiliate Signups</span>
											<div id="tabs_06" class="tabs_panel">
												<table class="table table-striped">
													<thead>
														<tr>
															<th width="3%">#</th>
														  <th width="12%">Name</th>
														  <th width="12%">Username</th>
														  <th width="15%">Email</th>
														  <th width="15%">Phone</th>
														  <th width="15%">Added On</th>
														</tr>
													</thead>  
													<tbody>
														 <?php $aff=0; foreach ($dashboard_info["affiliates"] as $sn=>$row) { $aff++; ?>
															<tr>
																<td><?=$aff?></td>
																<td><?=$row["affiliate_name"]?></td>
																<td><?=$row["affiliate_username"]?></td>
																<td><?=$row["affiliate_email"]?></td>
																<td><?=$row["affiliate_phone"]?></td>
																<td><?php echo $row['affiliate_added_on']; ?></td>
															</tr>
															<?php }?>														</tbody>    
												</table>
											</div>
								  </div>      
							
							</div>
						</div>
                 <?php endif;?>
                    
                </div>
            </div>
        </div>         
        <!--main panel end here-->
<?php include Utilities::getViewsPartialPath().'sale_statistics.php'; ?>
    </div>
    <!--body end here-->
<?php if($configuredAnalytics){?>
	<script>
		$(document).ready(function(){
			traficSource('yearly');
			topReferers('yearly');
			topCountries('yearly');
			getTopSearchKeyword('yearly');
		});		
	</script>
<?php }?>
<div class="welcome_msg"> Welcome back <?php echo (isset($admin_name)?$admin_name:''); ?></div> 	