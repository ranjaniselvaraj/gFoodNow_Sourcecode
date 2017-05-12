<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $txn_status_arr; ?> 
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
												    <th width="8%">Status</th>
												</tr>
												</thead>
											<tbody>
                                            <?php
											if (!$arr_listing || !is_array($arr_listing)) {
												echo "<tr><td colspan=4>No Record Found</td></tr>";
											} else {
												?>
        
											<?php foreach ($arr_listing as $transaction) { ?>
											<tr>
												<td><?php echo Utilities::formatDate($transaction["utxn_date"])?></td>
												<td><?php echo Utilities::displayMoneyFormat($transaction["utxn_credit"])?></td>
												<td><?php echo Utilities::displayMoneyFormat($transaction["utxn_debit"])?></td>
												<td><?php echo Utilities::displayMoneyFormat($transaction['balance']); ?></td>
												<td><?php echo strip_tags(Utilities::renderHtml($transaction["formatted_comments"])) ?></td>
												<td><?php echo $txn_status_arr[$transaction["utxn_status"]]?></td>
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
											'action' => 'transactions',
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
                                        
                                        