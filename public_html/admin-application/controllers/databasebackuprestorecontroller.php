<?php
class DatabasebackuprestoreController extends CommonController{
	function default_action(){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),DATABASE_BACKUP_RESTORE)) {
     	   die(Admin::getUnauthorizedMsg());
	    }
		if(Utilities::isOurDemoSystem()) {	
     	   die('This feature is disabled for security reasons. Please feel free to contact our sales team to request more details about this feature.');
	    }
		$settingsObj=new Settings();
		$backup_frm=$this->getBackupForm();
		$upload_frm=$this->getUploadForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['submit_backup'])){
			$settingsObj=new Settings();
			$settingsObj->backupDatabase(trim($post["name"]));
			Message::addMessage("Database Backup on Server created Successfully!!");
			Utilities::reloadPage();
		}elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['submit_upload'])){
			$ext=strrchr($_FILES['file']['name'], '.');
			if(strtolower($ext) != '.sql'){
				Message::addErrorMessage("File type unsupported. Please upload Sql file.");
			}else{
				if(!Utilities::saveFile($_FILES['file']['tmp_name'],$_FILES['file']['name'], $response, CONF_DB_BACKUP_DIRECTORY.'/')){
					Message::addErrorMessage($response);
				}else{
					
					$settingsObj->restoreDatabase(trim($response));
					Message::addMessage("Database Restored Successfully!!");
				}
			}
			Utilities::reloadPage();
		}
		$files_array=$settingsObj->getDatabaseDirectoryFiles();	
		$this->set('backup_frm', $backup_frm);
		$this->set('upload_frm', $upload_frm);
		$this->set('files_array',$files_array);
		$this->_template->render();
	}
	
	function download($file){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),DATABASE_BACKUP_RESTORE)) {
     	   die(Admin::getUnauthorizedMsg());
	    }
		if(isset($file) and trim($file) !=""){
			$settingsObj=new Settings();
			$settingsObj->download_file($file);
		}
	}
	
	function restore($file){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),DATABASE_BACKUP_RESTORE)) {
     	   die(Admin::getUnauthorizedMsg());
	    }
		if(isset($file) and trim($file) !=""){
			$settingsObj=new Settings();
			$settingsObj->restoreDatabase($file);
			Message::addMessage("Database restored successfully!!");
		}
		Utilities::redirectUserReferer();
	}
	
	function delete($file){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),DATABASE_BACKUP_RESTORE)) {
     	   die(Admin::getUnauthorizedMsg());
	    }
		if(isset($file) and trim($file) !=""){
			unlink(CONF_DB_BACKUP_DIRECTORY_FULL_PATH.$file);
			Message::addMessage("Record deleted Successfully!!");
		}
		Utilities::redirectUserReferer();
	}
	
	protected function getBackupForm(){
		$frm=new Form('frmdatabaseBackup','frmdatabaseBackup');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->setExtra('class="web_form"');
		$frm->setFieldsPerRow(2);
		$frm->setJsErrorDisplay('afterfield');
		$frm->captionInSameCell(true);
		 $frm->setLeftColumnProperties('width="30%"');
		$fld=$frm->addRequiredField('File Name', 'name', '', '', 'class="medium" autocomplete=off ');
		$frm->addSubmitButton('', 'submit_backup', 'Backup on Server', 'Backup on Server');  
		return $frm;
	}
	
	protected function getUploadForm(){
		$frm=new Form('frmdatabaseUpload','frmdatabaseUpload');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->setExtra('class="web_form"');
		$frm->setFieldsPerRow(2);
		$frm->setJsErrorDisplay('afterfield');
		$frm->captionInSameCell(true);
		$frm->setLeftColumnProperties('width="30%"');
		$fld=$frm->addFileUpload('DB Upload', 'file', '', '', 'class="input" autocomplete=off ');
		$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
		$fld->html_after_field='<label class="filelabel">Browse File</label></div>';
		$fld->requirements()->setRequired();
		$frm->addSubmitButton('', 'submit_upload', 'Upload on Server', 'Upload on Server');  
		return $frm;
	}
	
}