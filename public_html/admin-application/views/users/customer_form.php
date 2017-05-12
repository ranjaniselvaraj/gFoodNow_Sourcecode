<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $conf_arr_buyer_seller_types; ?> 
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
                        <div class="sectionhead"><h4>User Setup <?php //echo $user['user_type']; print_r($conf_arr_buyer_seller_types);?></h4></div>
						
                        <div class="sectionbody">                            
                             
                             <div class="tabs_nav_container responsive flat">
                            
                            <ul class="tabs_nav">
                                <li><a class="active" rel="tabs_1" href="javascript:void(0)">General</a></li>
                                <?php if ($user['user_id']>0) {?>
                                	<?php if (in_array($user['user_type'],$conf_arr_buyer_seller_types)) {?>
                                	<li><a rel="tabs_2" href="javascript:void(0)">Addresses</a></li>
                                    <li><a rel="tabs_3" href="javascript:void(0)">Bank Info</a></li>
                                    <li><a rel="tabs_7" href="javascript:void(0)">Reward Points</a></li>
                                    <? } ?>
                                    <li><a rel="tabs_4" href="javascript:void(0)">Transactions</a></li>
                                    <li><a rel="tabs_5" href="javascript:void(0)">Change Password</a></li>
                                    <li><a rel="tabs_6" href="javascript:void(0)">Send Email</a></li>
                                <?php } ?>
                            </ul> 
								<?php echo $frm->getFormTag ();  ?>
                                <div class="tabs_panel_wrap">
                                        <!--tab 1 start here-->
										<div id="tabs_1" class="tabs_panel">
                                            <?php echo $frm->getFormHtml(); ?>
                                        </div>
                                        <div id="tabs_2" class="tabs_panel">
                                        
                                        	<section class="section">
                            <div class="sectionhead">
                                <h4>User Addresses </h4>
                                <!--<a href="" class="themebtn btn-default btn-sm">View All</a>-->
                                <ul class="actions">
                                    <li class="droplink">
                                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                                        <div class="dropwrap">
                                            <ul class="linksvertical">
                                                <li><a target="_blank" href="<?php echo Utilities::generateUrl('users','address_form',array($user['user_id']))?>">Add Address</a></li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="sectionbody">
                                <div class="tablewrap">
                                    <table class="table table-striped">
											<thead>
											<tr>
												<th width="5%">ID </th>
												<th width="70%">Address</th>
												<th width="10%">Default</th>
                                                <th width="10%"></th>
											</tr>
											</thead>
											<tbody>
											<?php
											if (!$user["addresses"] || !is_array($user["addresses"])) {
												echo "<tr><td colspan=4>No Record Found</td></tr>";
											} else {
												?>
        
											<?php foreach ($user["addresses"] as $sn=>$useraddress) { ?>
											<tr id="add-row-<?php echo $useraddress['ua_id']; ?>">
												<td><?php echo ++$sn?></td>
												<td><?php echo "<strong>".$useraddress['ua_name'].'</strong><br/>'.(((strlen($useraddress['ua_address1']) > 0)?$useraddress['ua_address1']:'') .((strlen($useraddress['ua_address2']) > 0)?', '.$useraddress['ua_address2'].', ':'') . ((strlen($useraddress['ua_city']) > 0)?''.$useraddress['ua_city'] . ', ':'')) .  $useraddress['state_name'].' - '.$useraddress['ua_zip'].','.$useraddress['country_name']
		.'<br/>T: '.$useraddress['ua_phone'];?></td>
												<td><?php echo $useraddress['ua_is_default']?"Yes":"No" ?></td>
                                                <td><ul class="actions">
                                                	<li><a target="_blank" href="<?php echo Utilities::generateUrl('users', 'address_form', array($useraddress['ua_user_id'],$useraddress['ua_id']))?>"  title="Edit Address"><i class="ion-edit icon"></i></a></li>
                                                    <li><a class="button red medium deleteAddress" href="<?php echo Utilities::generateUrl('users', 'delete_address', array($useraddress['ua_user_id'],$useraddress['ua_id']))?>" id="<?php echo $useraddress['ua_id']; ?>"  title="Remove"><i class="ion-minus icon"></i></a></li>
                                                    </ul></td>
											</tr>
											<?php }
											}?>
											</tbody>
											<tfoot>
											
											</tfoot>
											</table>
                                </div>    
                            </div>
							</section>
                            
                                        	<!--<section class="section">
	                                        	<a href="<?php echo Utilities::generateUrl('users', 'address_form'); ?>">Add Address</a>
            	                       		</div>
                            
											-->                                            
                                        </div>
                                        <div id="tabs_3" class="tabs_panel">
                                            <?php echo $bankfrm->getFormHtml(); ?>
                                        </div>
                                        
                                        <div id="tabs_4" class="tabs_panel">
                                        	<div id="transaction_ajax_message"></div>
                                        	<div id="transaction"></div>	
                                            <?php echo $transactionfrm->getFormHtml(); ?>
                                        </div>
                                        
                                        <div id="tabs_5" class="tabs_panel">
                                            <?php echo $changepasswordfrm->getFormHtml(); ?>
                                        </div>
                                        <div id="tabs_6" class="tabs_panel">
                                            <?php echo $sendemailfrm->getFormHtml(); ?>
                                        </div>
										<div id="tabs_7" class="tabs_panel">
                                        	<div id="rewards_ajax_message"></div>
                                        	<div id="rewards"></div>	
                                            <?php echo $rewardfrm->getFormHtml(); ?>
                                        </div>
                                  </div>      
                        </div>
                                                    
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
 $(document).ready(function($) {
	<?php if ($showTab=="transactions") { ?>
		setTimeout(function(){ $('a[rel=tabs_4]').click(); }, 3000);
	<?php } ?> 
	$('#check-password').strength({
				strengthClass: 'strength',
				strengthMeterClass: 'strength_meter',
				strengthButtonClass: 'button_strength',
				strengthButtonText: '<?php echo Utilities::getLabel('M_Show_Password')?>',
				strengthButtonTextToggle: '<?php echo Utilities::getLabel('M_Hide_Password')?>',
				strengthVeryWeakText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_very_weak')?></p>',
				strengthWeakText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_weak')?></p>',
				strengthMediumText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_very_medium')?></p>',
				strengthStrongText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_strong')?></p>'
			});
	});
</script> 
<script type="text/javascript">
$(document).ready(function(){
	loadStates(document.getElementById('ua_country'), <?php echo intval($frm->getField('ua_state')->value); ?>);
	
	$('#transaction').delegate('.pagination a', 'click', function(e) {
		e.preventDefault();
		$('#transaction').load(this.href);
	});
	
	$('#rewards').delegate('.pagination a', 'click', function(e) {
		e.preventDefault();
		$('#rewards').load(this.href);
	});
	
	$('#transaction').load(generateUrl('users','transactions',['<?php echo $user["user_id"]?>']));
	$('#rewards').load(generateUrl('users','rewards',['<?php echo $user["user_id"]?>']));
	
	 $(function(){
        $('form[name="frmCustomerTransaction"]').bind('submit', function(event){
				event.preventDefault();
				var me=$(this);
				if ( me.data('requestRunning') ) {
					return;
				}
				var frm=this;
				el = $('#transaction_ajax_message');
				v = me.attr('validator');
				window[v].validate();
				if (!window[v].isValid()) return;
				me.data('requestRunning', true);
				var data = getFrmData(frm);
				data += '&outmode=json&is_ajax_request=yes';
				callAjax(me.attr('action'), data, function(response){
					var json = parseJsonData(response);
					me.data('requestRunning', false);
					if (json['error']) {
						el.html('<div class="alert alert_danger">'+json['error']+'<div>');
					}
					if (json['success']) {
						$("#frmCustomerTransaction").reset();
						el.html('<div class="alert alert_success">'+json['message']+'<div>');
						$('#transaction').load(generateUrl('users','transactions',['<?php echo $user["user_id"]?>']));
					}
				});
			return false;					
      });
	  
	  
        $('form[name="frmCustomerReward"]').bind('submit', function(event){
				event.preventDefault();
				var me=$(this);
				if ( me.data('requestRunning') ) {
					return;
				}
				var frm=this;
				el = $('#rewards_ajax_message');
				v = me.attr('validator');
				window[v].validate();
				if (!window[v].isValid()) return;
				me.data('requestRunning', true);
				var data = getFrmData(frm);
				data += '&outmode=json&is_ajax_request=yes';
				callAjax(me.attr('action'), data, function(response){
					var json = parseJsonData(response);
					me.data('requestRunning', false);
					if (json['error']) {
						el.html('<div class="alert alert_danger">'+json['error']+'<div>');
					}
					if (json['success']) {
						$("#frmCustomerReward").reset();
						el.html('<div class="alert alert_success">'+json['message']+'<div>');
						$('#rewards').load(generateUrl('users','rewards',['<?php echo $user["user_id"]?>']));
					}
				});
			return false;					
      });
    
	  
    })
	
	
	$(document).ready(function() {
		$(".deleteAddress" ).click(function( event ) {
		    var me = $(this);
			event.preventDefault();
			if ( me.data('requestRunning') ) {
     		   return;
		    }
			if(confirm("Sure you want to remove this item ?")){
				me.data('requestRunning', true);
				var id = $(this).attr("id");
		       	callAjax($(this).attr("href"),'', function(response){
				    	me.data('requestRunning', false);
					    var ans = parseJsonData(response);
						if (ans.status==1){
							$("#add-row-"+id).remove();
						}else{
							me.parent().html(ans.msg);
						}
					})
				}
			});
		})
	
		
	
});
jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
}
</script> 				