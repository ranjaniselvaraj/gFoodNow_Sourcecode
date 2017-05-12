<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
                                        	<div class="tablewrap">
											  <div id="ajax_message"></div>
			                                  <table class="dataTable table_listing">
												<thead>
												<tr>
													<th width="10%">Date</th>
												 	<th width="10%">Credit</th>
												    <th width="10%">Debit</th>
												    <th width="10%">Balance</th>
												    <th width="25%">Description</th>
												</tr>
												</thead>
											<tbody>
											<?php foreach ($arr_listing as $transaction) { ?>
											<tr>
												<td><?php echo Utilities::formatDate($transaction["atxn_date"])?></td>
												<td><?php echo Utilities::displayMoneyFormat($transaction["atxn_credit"])?></td>
												<td><?php echo Utilities::displayMoneyFormat($transaction["atxn_debit"])?></td>
												<td><?php echo Utilities::displayMoneyFormat($transaction['balance']); ?></td>
												<td><?php echo strip_tags(Utilities::renderHtml($transaction["atxn_description"])) ?></td>
											</tr>
											<?php } ?>
											</tbody>
											<tfoot>
											
											</tfoot>
											</table>
                                </div>
                                		
                                        <div class="gap"></div>
								<div class="footinfo">
                                <aside class="grid_1">
                                    <ul class="pagination">
										<?php unset($search_parameter["url"]); ?>
                                         <?php echo Utilities::renderView(Utilities::getViewsPartialPath().'pagination.php', array(
											'start_record' => $start_record,
											'end_record' => $end_record,
											'total_records' => $total_records,
											'pages' => $pages,
											'page' => $page,
											'controller' => 'advertisers',
											'action' => 'transactions',
											'url_vars' => array($criteria['advertiser']),
											'query_vars' => $search_parameter,
										)); ?>
                                    </ul>
                                </aside>  
								<?php  if ($total_records>0):?>
                                <aside class="grid_2"><span class="info">Showing <?php echo $start_record?> to  <?php echo $end_record?> of <?php echo $total_records?> entries</span></aside>
								<?php endif; ?>
                            </div>					
                                                                         
                                        </div>
                                        
                                        