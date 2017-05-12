<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
<?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
	<div class="fixed-container">
		<div class="dashboard">
		<?php include CONF_THEME_PATH . $controller.'/_partial/account_supplierleftpanel.php'; ?>
        <div class="data-side">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
		<div class="packages-banner">
		  <div class="fixed-container">
			<div class="banner-over-txt">
			<?php echo html_entity_decode($extra_content); ?>
			</div>
		  </div>
		</div>
		<div class="fixed-container">
			<div class="packages-box clearfix">
			<? include CONF_THEME_PATH . 'packages_tiles.php'; ?>
			</div>
		</div>
        </div>
		</div>
	</div>
</div>