<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<? if ($hide_header_footer) include CONF_THEME_PATH . 'payment-header.php'; ?>
<?php if ($hide_header_footer) {
	global $tpl_for_js_css;
	$pathinfo = pathinfo($tpl_for_js_css);
	echo  '<script type="text/javascript" language="javascript" src="' . Utilities::generateUrl('pagejsandcss', 'js', array($pathinfo['dirname'],'cms/view'), $use_root_url, false) . '&sid=' . time() . '"></script>' . "\n";
 }?>
<div>
    <div class="body clearfix">
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo $row['page_title'];?></h1>
        </div>
      </div>
      <div class="fixed-container">
        <div class="cmsContainer">
			<?php 
				$body=preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://'.$_SERVER['HTTP_HOST'].CONF_WEBROOT_URL.'$2$3',html_entity_decode($row["page_content"]));
				echo Utilities::renderHtml($body,true);
			?>
        </div>
      </div>
    </div>
  </div>
  