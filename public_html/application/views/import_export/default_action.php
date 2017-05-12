<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
		 <div class="body clearfix">
          <?php include CONF_THEME_PATH . 'account/_partial/account_subheader.php'; ?>
          <div class="fixed-container">
            <div class="dashboard">
              <?php include CONF_THEME_PATH .'account/_partial/account_leftpanel.php'; ?>
               <div class="data-side">
                    <?php include CONF_THEME_PATH .'account/_partial/account_tabs.php'; ?>
          		    <div class="tabz-content">
		            <h3><?php echo Utilities::getLabel('M_Bulk_Import_Export')?></h3>
					<ul class="arrowTabs">
					 <li <? if ($tabSelected=="export") {?> class="active" <? }?>><a href="<?php echo Utilities::generateUrl('import_export','default_action',array('export')); ?>"><?php echo Utilities::getLabel('L_Export')?></a></li>
					 <li <? if ($tabSelected=="import") {?> class="active" <? }?>><a href="<?php echo Utilities::generateUrl('import_export','default_action',array('import')); ?>"><?php echo Utilities::getLabel('L_Import')?></a></li>
					 <li <? if ($tabSelected=="settings") {?> class="active" <? }?>><a href="<?php echo Utilities::generateUrl('import_export','default_action',array('settings')); ?>"><?php echo Utilities::getLabel('L_Settings')?></a></li>
					</ul>
				<div class="space-lft-right">
					<div class="wrapform">
            	        <?php echo $frm->getFormHtml(); ?>       
					</div>
				</div>
            </div>
        </div>
      </div>
    </div>
  </div>
  