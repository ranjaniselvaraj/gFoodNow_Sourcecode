<?php
class SystemrestoreController extends CommonController{
	
	
	
	function default_action(){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),DATABASEBACKUPRESTORE)) {
     	   die(Admin::getUnauthorizedMsg());
	    }
		$settingsObj=new Settings();
		$restore_point_frm=$this->getRestorePointForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['submit_restore_point'])){
			if ($settingsObj->compress(CONF_INSTALLATION_PATH."restore",CONF_INSTALLATION_PATH."restore-backups/")){
				$settingsObj->findandDeleteOldestFile(CONF_INSTALLATION_PATH."restore-backups/");
				$target=CONF_INSTALLATION_PATH."restore/user-uploads";
				$source=CONF_USER_UPLOADS_PATH;
				Utilities::full_copy($source,$target);
				$settingsObj->backupDatabase("database",false,false,CONF_INSTALLATION_PATH."restore/database");
				Message::addMessage("Restore Point Updated Successfully!!");
				Utilities::reloadPage();
			}
		}
		$this->set('restore_point_frm', $restore_point_frm);
		$this->_template->render();
	}
	
	function update_setting($val){
		$settingsObj=new Settings();
		if ($settingsObj->update(array("CONF_AUTO_RESTORE_ON"=>$val))){
			die("Setting Updated");
		}
	}
	
	
	protected function getRestorePointForm(){
		$frm=new Form('frmdatabaseBackup','frmdatabaseBackup');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->setExtra('class="web_form"');
		$frm->setFieldsPerRow(4);
		$frm->setJsErrorDisplay('afterfield');
		$frm->captionInSameCell(false);
		$frm->setLeftColumnProperties('width="10%"');
		$fld=$frm->addSubmitButton('', 'submit_restore_point', 'Create Restore Point');  
		$fld->html_after_field='<p><strong>Notes</strong>: On clicking the above button, system restore point will change to current database & uploads folder and current restore folder will be moved to backup folder with current date attached to it.</p>';
		$fld->merge_caption=true;
		$fld->merge_cells=4;
		$isChecked=Settings::getSetting("CONF_AUTO_RESTORE_ON")?"checked":"";
		$frm->addHtml('<strong>Auto Restore</strong>','','&nbsp;<div class="onoffswitch">
    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" '.$isChecked.'>
    <label class="onoffswitch-label" for="myonoffswitch">
        <span class="onoffswitch-inner"></span>
        <span class="onoffswitch-switch"></span>
    </label>
</div>');
		return $frm;
	}
	
	
}