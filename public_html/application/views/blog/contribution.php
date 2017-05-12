<?php defined('SYSTEM_INIT') or die('Invalid Usage');?>
<div>
    <div class="body clearfix">  
		<div class="white_background">
      <div class="fixed-container">
		<span class="gap"><br/></span>
        <?php echo Message::getHtml(); ?>	
        <div class="content ">
            <div class="login_wrapper">
                <div class="head_sect">
                    <h2><?php echo Utilities::getLabel('L_Contribution_Form'); ?></h2>
                    <div class="siteForm">
                        <?php echo $frmContribute->getFormTag(); ?>   
                        <table class="loggin_form">
                            <tbody>
                                <tr>
                                    <td><?php echo $frmContribute->getFieldHtml('contribution_author_first_name'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frmContribute->getFieldHtml('contribution_author_last_name'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frmContribute->getFieldHtml('contribution_author_email'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frmContribute->getFieldHtml('contribution_author_phone'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php echo $frmContribute->getFieldHtml('contribution_file_name'); ?></td>
                                </tr>
                                <?php if (!empty(CONF_RECAPTACHA_SITEKEY)){?>
                                <tr>
                                    <td><?php echo $frmContribute->getFieldHtml('captcha_code'); ?></td>
                                </tr>
                                <?php }?>
                                <tr>
                                    <td>
                                        <?php echo $frmContribute->getFieldHtml('contribution_user_id'); ?>
                                        <?php echo $frmContribute->getFieldHtml('btn_submit'); ?>
                                    </td>
                                </tr>
                            </tbody></table>
                    </form>	<?php echo $frmContribute->getExternalJs(); ?>
                    </div>
                </div>
            </div>
        </div>
      </div>
      </div>
    </div>
  </div>
  <script src='https://www.google.com/recaptcha/api.js'></script>