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
					<h1>Affiliate Setup</h1>  
	                   <div class="tabs_nav_container responsive flat">
                            
                            <ul class="tabs_nav">
                                <li><a class="active" rel="tabs_1" href="javascript:void(0)">General</a></li>
                                <?php if ($affiliate['affiliate_id']>0) {?>
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
                                        
                                        <div id="tabs_4" class="tabs_panel">
                                        	<div id="transaction_ajax_message"></div>
                                        	<div id="transaction"></div>	
                                            <?php // echo $transactionfrm->getFormHtml(); ?>
                                        </div>
                                        
                                        <div id="tabs_5" class="tabs_panel">
                                            <?php echo $changepasswordfrm->getFormHtml(); ?>
                                        </div>
                                        <div id="tabs_6" class="tabs_panel">
                                            <?php echo $sendemailfrm->getFormHtml(); ?>
                                        </div>
										<!--tab 1 end here-->
										<!--tab 2 start here-->
                                        
										<!--tab 2 end here-->
                                  </div>      
                        </div>
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
	loadStates(document.getElementById('ua_country'), <?php echo intval($frm->getField('affiliate_state')->value); ?>);
	
	$('input[name=\'affiliate_payment\']').on('change', function() {
		$('.payment').parent().parent().hide();
		$('div [lang=\'payment-'+this.value+'\']').parent().parent().show();
	});
	$('input[name=\'affiliate_payment\']:checked').trigger('change');
	
	$('#transaction').delegate('.pagination a', 'click', function(e) {
		e.preventDefault();
		$('#transaction').load(this.href);
	});
	
	$('#transaction').load(generateUrl('affiliates','transactions',['<?php echo $affiliate['affiliate_id']?>']));
	
	 $(function(){
        $('form[name="frmAffTransaction"]').bind('submit', function(event){
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
						$("#frmAffTransaction").reset();
						el.html('<div class="alert alert_success">'+json['message']+'<div>');
						$('#transaction').load(generateUrl('affiliates','transactions',['<?php echo $affiliate['affiliate_id']?>']));
					}
				});
			return false;					
      });
    })
		
	
});
jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
}
</script> 				