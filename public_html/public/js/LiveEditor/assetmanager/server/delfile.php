<?php
ob_start();
session_start();
$is_admin_for_file_manager = 0;
$path_for_images = '';
if (!isset($_SESSION['WYSIWYGFileManagerRequirements']) || !is_file($_SESSION['WYSIWYGFileManagerRequirements'])){
	die('File manager authentication not set.');
}
require_once $_SESSION['WYSIWYGFileManagerRequirements'];
if($is_admin_for_file_manager !== 1){
	die('File manager authentication not set.');
}
if(strpos($_POST["file"], $path_for_images) !== 0){
	exit('Not Authorized!!'); /* To stop front users from deleting files from un-authorized folders. */
}
$root=$_SERVER["DOCUMENT_ROOT"] ;
$file = $root . $_POST["file"];

if(file_exists ($file)) {
	unlink($file);
} else {

}

echo $_POST["file"];

?>