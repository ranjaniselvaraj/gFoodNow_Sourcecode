<?php
class Files {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getError() {
        return $this->error;
    }

	public function isUploadedFileValidImage($files) {
		if (!empty($files['name']) && is_file($files['tmp_name'])) {
				
				// Sanitize the filename
				$filename = basename(html_entity_decode($files['name'], ENT_QUOTES, 'UTF-8'));
				if ((Utilities::utf8_strlen($filename) < 3) || (Utilities::utf8_strlen($filename) > 128)) {
					$this->error = Utilities::getLabel('L_Filename_must_between_3_and_255');
					return false;
				}
				// Allowed file extension types
				$allowed = array();
				$extension_allowed = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_IMAGE_MIME_ALLOWED"));
				$filetypes = explode("\n", $extension_allowed);
				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}
				if (!in_array($files['type'], $allowed)) {
					$this->error = Utilities::getLabel('L_Incorrect_Filetype');
					return false;
				}
				// Check to see if any PHP files are trying to be uploaded
				$content = file_get_contents($files['tmp_name']);
				if (preg_match('/\<\?php/i', $content)) {
					$this->error = Utilities::getLabel('L_Incorrect_Filetype');
					return false;
				}
				// Return any upload error
				if ($files['error'] != UPLOAD_ERR_OK) {
					$this->error = Utilities::getLabel('L_error_upload_'.$files['error']);
					return false;
				}
				return true;
		}else {
				$this->error = Utilities::getLabel('L_File_could_not_uploaded_unknown_reason');
		}
		return false;
		
	}
	
}
