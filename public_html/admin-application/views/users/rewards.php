<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $txn_status_arr; ?> 
                                        	<div class="tablewrap">
											  <div id="ajax_message"></div>
			                                  <table class="dataTable table_listing">
												<thead>
												<tr>
												 	<th width="10%">Points</th>
												    <th width="60%">Description</th>
                                                    <th width="15%">Added Date</th>
                                                    <th width="15%">Expiry Date</th>
												</tr>
												</thead>
											<tbody>
											<?php
											if (!$arr_listing || !is_array($arr_listing)) {
												echo "<tr><td colspan=4>No Record Found</td></tr>";
											} else {
												?>
        
											<?php foreach ($arr_listing as $rewardpoint) { ?>
											<tr>
												<td><?php echo $rewardpoint['urp_points']; ?></td>
												<td><?php echo Utilities::renderHtml($rewardpoint["urp_description"]) ?></td>
                                                <td><?php echo Utilities::formatDate($rewardpoint["urp_date_added"])?></td>
                                                <td><?php echo Utilities::displayNotApplicable(Utilities::formatDate($rewardpoint["urp_date_expiry"]))?></td>
											</tr>
											<?php } 
											}?>
											</tbody>
											<tfoot>
											
											</tfoot>
											</table>
                                </div>
                                		
                                        <div class="gap"></div>
								<div class="footinfo">
                                <aside class="grid_1">
                                    <ul class="pagination">
                                         <?php echo Utilities::renderView(Utilities::getViewsPartialPath().'pagination.php', array(
											'start_record' => $start_record,
											'end_record' => $end_record,
											'total_records' => $total_records,
											'pages' => $pages,
											'page' => $page,
											'controller' => 'users',
											'action' => 'rewards',
											'url_vars' => array($criteria['user']),
											'query_vars' => array(),
										)); ?>
                                    </ul>
                                </aside>  
								<?php  if ($total_records>0):?>
                                <aside class="grid_2"><span class="info">Showing <?php echo $start_record?> to  <?php echo $end_record?> of <?php echo $total_records?> entries</span></aside>
								<?php endif; ?>
                            </div>					
                                                                         
                                        </div>
                                        
                                        