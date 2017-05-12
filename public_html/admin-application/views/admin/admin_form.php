<?php
if (!SYSTEM_INIT) die('Invalid Access'); // avoid direct access.
/* @var $frmAdmin Form */
?>
<div class="tblheading">Admin Setup Form</div>
<div class="form"><?php echo $frmAdmin->getFormHtml(); ?></div>