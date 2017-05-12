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
                        <div class="sectionhead"><h4>Messages</h4></div>
                        <div class="sectionbody">                                                                          
                          <table class="table table-responsive" id="dtTable">
                                        
                                        <tbody>
                                           <?php foreach ($thread_detail as $sn=>$row) {  ?>
											<tr>
												<td><div class="avatar">
                                                <img alt="" src="<?php echo Utilities::generateUrl('image', 'user',array($row["message_sent_by_profile"],'SMALL'),CONF_WEBROOT_URL)?>"></div></td>
												<td>
                                                	<a href="<?php echo Utilities::generateUrl('users','customer_form',array($row["message_from"]));?>"><?php echo $row["message_sent_by_username"]?></a><br/>
													<span><?php echo Utilities::formatDate($row["message_date"],true)?></span>
													
													<span contenteditable="true" onBlur="saveToDatabase(this,'<?php echo $row["message_id"]?>')" onClick="showEdit(this);"><?php echo nl2br($row["message_text"])?></span>
												</td>
												<td>
                                                <ul class="actions">
                                                   <li><a href="#" onclick="ConfirmDelete('<?php echo $row['message_id']?>', $(this))"; title="Delete" ><i class="ion-trash-b icon"></i></a></li>
                                                   
</ul>	
                                                </td>
											</tr>
										<?php }?>                                           
                                        </tbody>    
                                    </table>                                
                                </div>
								<div class="gap"></div>								
                        </section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				