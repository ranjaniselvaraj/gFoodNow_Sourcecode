<?

$source=CONF_INSTALLATION_PATH."restore/user-uploads";
$target=CONF_USER_UPLOADS_PATH;
Utilities::full_copy($source,$target);
echo "Uploads folder restored.<br/>";

$file=CONF_INSTALLATION_PATH."restore/database/database.sql";
$settingsObj=new Settings();
$settingsObj->restoreDatabase($file,false);
echo "Database restored successfully.<br/>";
	
?>