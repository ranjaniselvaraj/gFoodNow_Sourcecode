<?php
/*print_r($_POST);
exit;*/
require_once('settings.php');
require_once 'application-top.php';
require_once 'includes/check-unique.php';
exit;

$post=getPostedData();
$expr='/^[a-zA-Z_]+$/';
if(!preg_match($expr, $post['tbl']) || !preg_match($expr, $post['tbl_fld']) || !preg_match($expr, $post['tbl_key'])) dieJsonError('Invalid Request');
$srch=new SearchBase($post['tbl']);
$srch->addCondition($post['tbl_fld'], '=', $post['val']);
$srch->addCondition($post['tbl_key'], '!=', $post['key_val']);
$rs=$srch->getResultSet();
if($db->total_records($rs)>0){
	$arr=array('status'=>0, 'existing_value'=>'');
	if(trim($post['key_val'])!='' && $post['key_val']!='0'){
		$srch=new SearchBase($post['tbl']);
		$srch->addCondition($post['tbl_key'], '=', $post['key_val']);
		$srch->addFld($post['tbl_fld']);
		$rs=$srch->getResultSet();
		if($row=$db->fetch($rs)) $arr['existing_value']=$row[$post['tbl_fld']];
	}
	die(json_encode($arr));
}
dieJsonSuccess('Available');
print_r($post);
?>