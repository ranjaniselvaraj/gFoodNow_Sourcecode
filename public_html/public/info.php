<?php 

function getInBytes($value){
	$value = trim($value);
	$last_char = strtolower($value[strlen($value) - 1]);
	switch($last_char){
		case 'g': $value *= 1024;
		case 'm': $value *= 1024;
		case 'k': $value *= 1024;
	}
	return $value;
}
function apacheGetModules(){
	if (function_exists('apache_get_modules')) {
		$apacheGetModules = apache_get_modules();
	}else {
		$apacheGetModules = array();
	}
	return $apacheGetModules;
}
$title='Yo!kart Multivendor System Requirement check';
$text_install_php = '1. Please configure your PHP settings to match requirements listed below.';
$text_install_extension = '2. Please make sure the PHP extensions listed below are installed.';
$text_msql_version = '3. Please make sure you have Mysql version 5.6';
$text_note='Note: In some server environment, script unable to detect server settings. So if you have configured your server as per required settings, You can install our Yo!kart scripts. ';
$text_strict_trans_tables='4. sql_mode - strict_trans_tables should be disabled.';
$text_setting = 'PHP Settings';
$text_current = 'Current Settings';
$text_required = 'Required Settings';
$text_extension = 'Extension Settings';
$text_file = 'Files';
$text_directory = 'Directories';
$text_status = 'Status';
$text_on = 'On';
$text_off = 'Off';
$text_missing = 'Missing';
$text_writable = 'Writable';
$text_unwritable = 'Unwritable';
$text_memory_limit = 'Memory Limit';
$text_version = 'PHP Version';
$text_mysql_version = 'MySql Version';
$text_ioncube = 'Ioncube';
$text_global = 'Register Globals';
$text_magic = 'Magic Quotes GPC';
$text_file_upload = 'File Uploads';
$text_session = 'Session Auto Start';
$text_safe_mode = 'Safe Mode';
$text_db = 'MySQLi';
$text_mysqli = 'MySQLi';
$text_mysql = 'MySQL';
$text_mpdo = 'mPDO';
$text_gd = 'GD';
$text_curl = 'cURL';
$text_mcrypt = 'mCrypt';
$text_zlib = 'ZLIB';
$text_zip = 'ZIP';
$text_mbstring = 'mbstring';
$text_short_open_tag = 'Short Open Tag';
$text_rewrite_url = 'Url Rewriting (mod_rewrite)';


$button_continue = 'Continue';
$button_back = 'Back';
$memory_limit = getInBytes(ini_get('memory_limit'))>getInBytes('32M');
$php_version = phpversion();
//$mysql_version = mysql_get_server_info();
$register_globals = ini_get('register_globals');
$magic_quotes_gpc = ini_get('magic_quotes_gpc');
$file_uploads = ini_get('file_uploads');
$session_auto_start = ini_get('session_auto_start');
$safeModeOn=ini_get('safe_mode');
$short_open_tag = ini_get('short_open_tag');

if(in_array('mod_rewrite', apacheGetModules())){
	$rewrite_url=true;
}else{
	$rewrite_url=false;
}

if (!array_filter(array('mysqli'), 'extension_loaded')) {
	$db = false;
} else {
	$db = true;
}

$gd = extension_loaded('gd');
$curl = extension_loaded('curl');
$zlib = extension_loaded('zlib');
$zip = extension_loaded('zip');
$iconv = function_exists('iconv');
$mbstring = extension_loaded('mbstring');
$ioncube = extension_loaded('IonCube Loader');


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title><?php echo $title; ?></title>
<link href="//fonts.googleapis.com/css?family=Open+Sans:400,400i,300,700" rel="stylesheet" type="text/css" />
<style>
html {
	overflow: -moz-scrollbars-vertical;
	margin: 0;
	padding: 0;
}
*,h1,h2,h3,h4,h5,h6 {
	font-family: 'Open Sans', sans-serif;
	font-weight: 400;line-height:1.2;
}
body {
	margin: 0px;
	padding: 0px;
	line-height: 1.5;
	background: #FFF;
}
body, p, td, th, input, textarea, select, option {
	color: #777777;
	text-decoration: none;
	font-size: 13px;
}
fieldset {
	border: 1px solid #DBDBDB;
	padding: 10px;
	margin-bottom: 20px;
	-webkit-border-radius: 5px 5px 5px 5px;
	-moz-border-radius: 5px 5px 5px 5px;
	-khtml-border-radius: 5px 5px 5px 5px;
	border-radius: 5px 5px 5px 5px;
}
fieldset table {
	width: 100%;
	border-collapse: collapse;
}
fieldset table.form tr td:first-child {
	width: 250px;
}
fieldset table td {
	padding: 5px;
}
.page{margin:0 auto;max-width:1000px;}

.row { margin-right: -15px; margin-left: -15px;}
.row:before,.row:after { display: table; content: " ";}
.row:after {  clear: both;}
.row:before,.row:after { display: table; content: " ";}
.row:after {  clear: both;}
.col-sm-3,.col-sm-9{ float: left;}
.col-sm-3 {   width: 25%;  }
.col-sm-9 { width: 75%; }
.table {width: 100%; margin-bottom: 20px;}
.table > thead > tr > th,.table > tbody > tr > th,.table > tfoot > tr > th,.table > thead > tr > td,.table > tbody > tr > td,.table > tfoot > tr > td {  padding: 8px;  line-height: 1.428571429;
  vertical-align: top;  border-top: 1px solid #dddddd;}
.table > thead > tr > th {  vertical-align: bottom;  border-bottom: 2px solid #dddddd;}
.table > caption + thead > tr:first-child > th,.table > colgroup + thead > tr:first-child > th,.table > thead:first-child > tr:first-child > th,.table > caption + thead > tr:first-child > td,
.table > colgroup + thead > tr:first-child > td,.table > thead:first-child > tr:first-child > td {
  border-top: 0;}
.table > tbody + tbody {  border-top: 2px solid #dddddd;}
.table .table {  background-color: #ffffff;}
.text-center {  text-align: center;}
.text-success {  color: #468847;}
.text-danger {  color: #b94a48;}
#logo{margin:10px 0;}
h1.heading{font-size:24px;font-weight:600;line-height:1.5;}
h1.heading small{font-size:14px;font-weight:400;line-height:1;display:block;}

.svg-icn svg{width:20px; height:21px; display:block; margin:0 auto;}
.svg-icn.check svg path, .svg-icn.check svg{fill:#008000;}
.svg-icn.delete svg path, .svg-icn.delete svg{fill:#FF0000;}
#logo svg { height: auto; width: 200px;}
</style>
</head>
<body>
<svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"  style="display:none">
<defs>
	<symbol id="Check_Circle" viewBox="0 0 384.97 384.97" style="enable-background:new 0 0 384.97 384.97;" xml:space="preserve">
		<path d="M192.485,0C86.173,0,0,86.173,0,192.485S86.173,384.97,192.485,384.97c106.3,0,192.485-86.185,192.485-192.485
			C384.97,86.173,298.785,0,192.485,0z M192.485,360.909c-93.018,0-168.424-75.406-168.424-168.424S99.467,24.061,192.485,24.061
			s168.424,75.406,168.424,168.424S285.503,360.909,192.485,360.909z"/>
		<path d="M280.306,125.031L156.538,247.692l-51.502-50.479c-4.74-4.704-12.439-4.704-17.179,0c-4.752,4.704-4.752,12.319,0,17.011
			l60.139,58.936c4.932,4.343,12.307,4.824,17.179,0l132.321-131.118c4.74-4.692,4.74-12.319,0-17.011
			C292.745,120.339,285.058,120.339,280.306,125.031z"/>
	</symbol>
	 <symbol id="Check_Close" viewBox="0 0 66.915 66.915" style="enable-background:new 0 0 66.915 66.915;" xml:space="preserve">
		<path d="M46.133,20.688c-0.781-0.781-2.047-0.781-2.828,0l-9.899,9.899l-9.9-9.899c-0.781-0.781-2.047-0.781-2.828,0
			s-0.781,2.047,0,2.828l9.9,9.899l-9.9,9.899c-0.781,0.781-0.781,2.047,0,2.828c0.391,0.391,0.902,0.586,1.414,0.586
			s1.023-0.195,1.414-0.586l9.9-9.899l9.899,9.899c0.391,0.391,0.902,0.586,1.414,0.586s1.023-0.195,1.414-0.586
			c0.781-0.781,0.781-2.047,0-2.828l-9.899-9.899l9.899-9.899C46.914,22.735,46.914,21.468,46.133,20.688z"/>
		<path d="M57.107,9.8C50.788,3.481,42.386,0,33.449,0S16.112,3.48,9.792,9.8c-13.045,13.045-13.045,34.271,0,47.315
			c6.318,6.319,14.721,9.8,23.657,9.8c8.938,0,17.34-3.48,23.659-9.8c6.319-6.318,9.799-14.721,9.799-23.658
			C66.906,24.521,63.426,16.119,57.107,9.8z M54.281,54.287c-5.564,5.563-12.962,8.628-20.831,8.628
			c-7.868,0-15.266-3.064-20.829-8.628c-11.485-11.485-11.485-30.174,0-41.659C18.185,7.064,25.581,4,33.449,4
			s15.266,3.064,20.829,8.627s8.628,12.961,8.629,20.83C62.907,41.326,59.844,48.724,54.281,54.287z"/>
	</symbol>
</defs>
</svg>
<div class="page">
  <header>
    <div class="row">
      <div class="col-sm-3">       
        <div id="logo" class="">
          <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="284px" height="87.25px" viewBox="0 0 284 87.25" enable-background="new 0 0 284 87.25" xml:space="preserve">
<g>
	<path fill="#474747" d="M90.455,27.413l1.489,24.063c0.185,2.4,2.214,2.588,3.026,2.64l7.626-18.434
		c0.745-1.875,1.388-4.379,1.742-8.352h10.469c0.846,4.464-0.641,9.114-1.979,12.175l-10.332,23.812
		C94.8,80.768,84.3,82.525,79.581,83.301v-7.793c6.239-3.127,9.301-7.223,11.802-12.174c-6.358,0-9.588-3.521-10.146-9.775
		l-2.282-26.146H90.455z"/>
	<path fill="#474747" d="M134.938,27.024c12.259,0,15.841,6.351,13.646,20.199c-2.125,13.314-6.587,16.5-19.222,16.5
		c-12.38,0-15.944-6.195-13.919-19.029C117.744,30.031,122.674,27.024,134.938,27.024 M130.544,55.182
		c4.561,0,5.539-0.643,6.958-9.224c1.403-8.716,0.948-10.386-3.869-10.386c-4.881,0-5.672,1.503-7.107,10.386
		C125.175,54.182,126.221,55.182,130.544,55.182"/>
	<path fill="#474747" d="M150.719,54.943c0.139-1.014,0.814-1.688,1.895-1.688h6.757c1.017,0,1.42,0.743,1.284,1.688l-1.114,6.892
		c-0.138,1.015-0.813,1.556-1.761,1.556h-6.754c-0.944,0-1.557-0.606-1.418-1.556L150.719,54.943z M156.803,14.001h10.824
		l-6.855,36.076h-8.666L156.803,14.001z"/>
	<path fill="#474747" d="M202.193,27.515h-11.687c-1.891,6.08-6.94,10.539-11.924,12.5l4.123-25.875H171.63l-7.812,49.25h11.08
		l2.554-15.813l2.565-0.064l7.854,15.881h12.159l-10.028-19.462C193.988,41.706,199.596,36.974,202.193,27.515"/>
	<path fill="#474747" d="M218.643,26.836c5.338-0.151,13.156,0.881,16.603,4.19l-4.729,32.26h-8.646l-1.703-3.546
		c-1.711,2.55-6.254,4.018-10.239,4.018c-6.74,0-11.925-6.617-9.068-20.875C203.846,27.974,211.984,27.007,218.643,26.836
		 M214.637,54.656c3.43,0,5.219-1.285,6.521-3.445l2.244-15.319c-1.416-0.624-3.885-0.862-5.59-0.472
		c-2.568,0.556-4.998,1.503-6.133,9.139C210.803,50.436,210.789,54.656,214.637,54.656"/>
	<path fill="#474747" d="M239.678,27.515h9.592l0.744,4.527c2.301-2.416,5.908-4.882,9.982-4.882c1.4,0,2.619,0.034,3.563,0.389
		l-1.672,10.658c-1.012-0.052-3.178-0.104-5.236-0.104c-3.377,0-6.299,0.357-8.209,4.021l-3.358,21.269H234L239.678,27.515z"/>
	<path fill="#474747" d="M267.047,17.449h11.252l-1.602,9.998h7.127l-1.27,8.039h-7.125l-2.078,13.059
		c-0.813,5.271,0.086,6.098,1.756,6.855c0,0,3.227,1.369,3.602,1.369l-1.055,6.67h-7.223c-5.693,0-9.92-3.53-8.465-12.871
		L267.047,17.449z"/>
	<path fill="#FF3A59" d="M77.53,0.296c-4.346-1.333-10.712,1.97-13.357,6.921c-2.038,3.809-3.622,7.063-4.893,9.675
		c-2.548,5.23-4.087,8.388-6.259,9.977c-2.865,2.105-7.029,0.825-12.797-0.951l-31.555-9.67c-2.906-0.889-5.998,0.76-6.889,3.658
		c-0.89,2.906,0.75,5.989,3.656,6.883l13.772,4.224l14.601,4.477l3.189,0.977l-0.014,0.04c3.346,1.461,5.148,5.246,4.049,8.821
		c-0.805,2.646-2.986,4.492-5.519,5.006c1.806,1.848,2.582,4.596,1.772,7.238c-1.096,3.57-4.709,5.699-8.302,5.025l-0.011,0.038
		l-0.713-0.211c-0.025-0.004-0.055-0.021-0.084-0.024l-2.025-0.615l-14.976-4.592l-6.113-1.883
		c-2.063-0.637-4.262,0.532-4.888,2.601c-0.636,2.063,0.528,4.252,2.592,4.886l29.074,8.904
		c11.674,3.586,19.905-3.521,22.881-12.163c1.858-5.38,15.513-42.374,20.076-49.541c1.35-2.109,2.581-3.024,3.569-3.758
		c0.921-0.673,1.466-1.082,1.805-2.214C80.387,3.306,80.151,1.093,77.53,0.296"/>
	<path fill="#FF3A59" d="M12.668,75.645c-0.953,3.101-4.232,4.849-7.323,3.896c-3.104-0.955-4.844-4.24-3.897-7.34
		c0.949-3.093,4.232-4.83,7.331-3.877C11.877,69.264,13.624,72.549,12.668,75.645"/>
	<path fill="#FF3A59" d="M36.979,83.1c-0.683,2.25-2.605,3.775-4.788,4.092c-0.829,0.121-1.69,0.06-2.548-0.2
		c-3.089-0.947-4.833-4.229-3.885-7.328c0.949-3.104,4.228-4.849,7.333-3.896C36.191,76.719,37.932,80.004,36.979,83.1"/>
	<path fill="#FF3A59" d="M29.837,59.898c-0.476,0-0.945-0.068-1.4-0.209l-2.266-0.748l-0.788-0.188l-14.936-4.582
		c-1.236-0.378-2.25-1.215-2.854-2.354c-0.605-1.145-0.731-2.449-0.353-3.687c0.616-2.016,2.524-3.425,4.634-3.422
		c0.475-0.003,0.953,0.069,1.408,0.206l16.123,4.949l-0.011,0.033l1.794,0.516l0.046,0.025l0.044,0.006
		c2.541,0.781,3.966,3.486,3.186,6.033C33.843,58.495,31.944,59.898,29.837,59.898"/>
	<path fill="#FF3A59" d="M33.409,47.875c-0.46,0-0.925-0.068-1.374-0.206l-2.137-0.692l-0.711-0.185l-14.773-4.52
		c-2.5-0.767-3.905-3.426-3.142-5.916c0.604-1.978,2.467-3.354,4.535-3.354c0.464-0.002,0.93,0.07,1.379,0.209l15.851,4.856
		l-0.002,0.02l1.77,0.528c2.494,0.772,3.896,3.423,3.135,5.916C37.334,46.503,35.472,47.875,33.409,47.875"/>
	<g enable-background="new    ">
		<path fill="#414042" d="M120.962,77.452c-0.048-1.291-0.096-2.854-0.096-4.417h-0.032c-0.351,1.372-0.797,2.902-1.212,4.162
			l-1.308,4.193h-1.897l-1.148-4.146c-0.351-1.275-0.702-2.807-0.973-4.21h-0.032c-0.048,1.451-0.112,3.109-0.175,4.449
			l-0.208,4.082h-2.249l0.686-10.747h3.237l1.052,3.588c0.351,1.243,0.67,2.583,0.909,3.843h0.063
			c0.287-1.244,0.654-2.663,1.021-3.859l1.132-3.571h3.189l0.574,10.747h-2.36L120.962,77.452z"/>
		<path fill="#414042" d="M127.788,70.819v6.171c0,1.865,0.718,2.807,1.945,2.807c1.276,0,1.978-0.894,1.978-2.807v-6.171h2.423
			v6.027c0,3.316-1.674,4.896-4.48,4.896c-2.711,0-4.29-1.516-4.29-4.928v-5.995H127.788z"/>
	</g>
	<g enable-background="new    ">
		<path fill="#414042" d="M136.256,70.819h2.424v8.706h4.273v2.041h-6.697V70.819z"/>
	</g>
	<g enable-background="new    ">
		<path fill="#414042" d="M144.834,72.86h-2.901v-2.041h8.26v2.041h-2.935v8.706h-2.424V72.86z"/>
		<path fill="#414042" d="M153.924,70.819v10.747H151.5V70.819H153.924z"/>
	</g>
	<g enable-background="new    ">
	</g>
	<g enable-background="new    ">
		<path fill="#414042" d="M161.211,81.566l-3.444-10.747h2.663l1.307,4.544c0.367,1.292,0.702,2.504,0.957,3.843h0.049
			c0.271-1.291,0.605-2.566,0.973-3.795l1.371-4.592h2.582l-3.619,10.747H161.211z"/>
		<path fill="#414042" d="M175.211,77.021h-3.954v2.552h4.417v1.993h-6.841V70.819h6.617v1.993h-4.193v2.232h3.954V77.021z"/>
		<path fill="#414042" d="M177.332,81.566V70.819h2.839l2.232,3.938c0.638,1.132,1.26,2.472,1.754,3.684h0.032
			c-0.144-1.42-0.191-2.87-0.191-4.497v-3.125h2.232v10.747h-2.552l-2.296-4.146c-0.638-1.148-1.355-2.535-1.865-3.795h-0.064
			c0.08,1.435,0.111,2.949,0.111,4.703v3.237H177.332z"/>
		<path fill="#414042" d="M188.336,70.979c0.893-0.144,2.057-0.239,3.269-0.239c2.057,0,3.396,0.383,4.417,1.148
			c1.116,0.829,1.817,2.152,1.817,4.05c0,2.057-0.749,3.477-1.77,4.354c-1.132,0.94-2.87,1.387-4.976,1.387
			c-1.26,0-2.168-0.079-2.758-0.159V70.979z M190.76,79.732c0.207,0.048,0.558,0.048,0.845,0.048
			c2.216,0.017,3.667-1.195,3.667-3.763c0-2.232-1.307-3.412-3.412-3.412c-0.525,0-0.893,0.048-1.1,0.096V79.732z"/>
		<path fill="#414042" d="M209.272,76.081c0,3.54-2.12,5.661-5.278,5.661c-3.173,0-5.055-2.408-5.055-5.485
			c0-3.222,2.073-5.629,5.23-5.629C207.471,70.628,209.272,73.1,209.272,76.081z M201.508,76.209c0,2.12,0.988,3.604,2.614,3.604
			c1.643,0,2.583-1.563,2.583-3.651c0-1.945-0.908-3.604-2.583-3.604C202.464,72.558,201.508,74.12,201.508,76.209z"/>
		<path fill="#414042" d="M210.867,70.963c0.781-0.128,1.93-0.224,3.238-0.224c1.594,0,2.71,0.239,3.476,0.846
			c0.653,0.51,1.005,1.275,1.005,2.264c0,1.355-0.973,2.296-1.898,2.631v0.048c0.75,0.304,1.164,1.005,1.436,1.993
			c0.335,1.229,0.654,2.631,0.861,3.046h-2.488c-0.159-0.319-0.43-1.18-0.732-2.504c-0.304-1.354-0.766-1.706-1.771-1.722h-0.718
			v4.226h-2.408V70.963z M213.275,75.587h0.957c1.212,0,1.93-0.606,1.93-1.547c0-0.973-0.67-1.482-1.786-1.482
			c-0.591,0-0.925,0.031-1.101,0.079V75.587z"/>
		<path fill="#414042" d="M223.625,79.063c0.638,0.335,1.658,0.67,2.694,0.67c1.116,0,1.706-0.462,1.706-1.164
			c0-0.669-0.51-1.052-1.801-1.499c-1.787-0.638-2.967-1.626-2.967-3.188c0-1.834,1.547-3.221,4.066-3.221
			c1.228,0,2.104,0.238,2.742,0.542l-0.542,1.945c-0.415-0.208-1.196-0.511-2.231-0.511c-1.053,0-1.563,0.494-1.563,1.037
			c0,0.686,0.59,0.988,1.993,1.515c1.897,0.701,2.774,1.69,2.774,3.205c0,1.802-1.371,3.332-4.321,3.332
			c-1.228,0-2.439-0.335-3.045-0.669L223.625,79.063z"/>
	</g>
	<g enable-background="new    ">
		<path fill="#414042" d="M234.644,81.566v-4.401l-3.396-6.346h2.79l1.084,2.583c0.336,0.781,0.574,1.355,0.83,2.057h0.031
			c0.24-0.669,0.494-1.291,0.813-2.057l1.085-2.583h2.727l-3.541,6.267v4.48H234.644z"/>
	</g>
	<g enable-background="new    ">
		<path fill="#414042" d="M241.391,79.063c0.637,0.335,1.658,0.67,2.694,0.67c1.116,0,1.706-0.462,1.706-1.164
			c0-0.669-0.51-1.052-1.802-1.499c-1.786-0.638-2.966-1.626-2.966-3.188c0-1.834,1.547-3.221,4.066-3.221
			c1.227,0,2.104,0.238,2.742,0.542l-0.543,1.945c-0.414-0.208-1.195-0.511-2.231-0.511c-1.053,0-1.563,0.494-1.563,1.037
			c0,0.686,0.591,0.988,1.994,1.515c1.896,0.701,2.773,1.69,2.773,3.205c0,1.802-1.371,3.332-4.32,3.332
			c-1.229,0-2.439-0.335-3.046-0.669L241.391,79.063z"/>
		<path fill="#414042" d="M252.01,72.86h-2.902v-2.041h8.26v2.041h-2.934v8.706h-2.424V72.86z"/>
		<path fill="#414042" d="M265.054,77.021H261.1v2.552h4.417v1.993h-6.841V70.819h6.617v1.993H261.1v2.232h3.954V77.021z"/>
		<path fill="#414042" d="M276.264,77.452c-0.048-1.291-0.096-2.854-0.096-4.417h-0.031c-0.352,1.372-0.798,2.902-1.213,4.162
			l-1.307,4.193h-1.898l-1.147-4.146c-0.351-1.275-0.702-2.807-0.973-4.21h-0.032c-0.047,1.451-0.111,3.109-0.175,4.449
			l-0.208,4.082h-2.248l0.686-10.747h3.237l1.052,3.588c0.352,1.243,0.67,2.583,0.91,3.843h0.063
			c0.287-1.244,0.654-2.663,1.021-3.859l1.132-3.571h3.188l0.574,10.747h-2.359L276.264,77.452z"/>
	</g>
</g>
</svg>

        </div>		
      </div>
	  <div class="col-sm-9">
		<h1 class="heading">Pre-Installation<small>Check your server is set-up correctly</small></h1>
	  </div>
	  </div>
	  
  </header>
  <?php if ($error_warning) { ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php } ?>
  <div class="row">
    <div class="col-sm-12">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
        <p><?php echo $text_install_php; ?></p>
        <fieldset>
          <table class="table">
            <thead>
              <tr>
                <td width="35%"><b><?php echo $text_setting; ?></b></td>
                <td width="25%"><b><?php echo $text_current; ?></b></td>
                <td width="25%"><b><?php echo $text_required; ?></b></td>
                <td width="15%" class="text-center"><b><?php echo $text_status; ?></b></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo $text_memory_limit; ?></td>
                <td><?php echo ini_get('memory_limit'); ?></td>
                <td>32M+</td>
                <td class="text-center"><?php if ($memory_limit) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg></i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $text_version; ?></td>
                <td><?php echo $php_version; ?></td>
                <td>5.5+</td>
                <td class="text-center"><?php if ($php_version >= '5.5') { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>              
              <tr>
                <td><?php echo $text_ioncube; ?></td>
                <td><?php if ($ioncube) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?></td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($ioncube) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $text_global; ?></td>
                <td><?php if ($register_globals) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_off; ?></td>
                <td class="text-center"><?php if (!$register_globals) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $text_magic; ?></td>
                <td><?php if ($magic_quotes_gpc) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_off; ?></td>
                <td class="text-center"><?php if (!$magic_quotes_gpc) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $text_file_upload; ?></td>
                <td><?php if ($file_uploads) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($file_uploads) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $text_session; ?></td>
                <td><?php if ($session_auto_start) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_off; ?></td>
                <td class="text-center"><?php if (!$session_auto_start) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
			  <?php /*?>
			  <tr>
                <td><?php echo $text_safe_mode; ?></td>
                <td><?php if ($safeModeOn) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_off; ?></td>
                <td class="text-center"><?php if (!$safeModeOn) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr> <?php */?>
			  <tr>
                <td><?php echo $text_short_open_tag; ?></td>
                <td><?php if ($short_open_tag) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($short_open_tag) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr> <tr>
                <td><?php echo $text_rewrite_url; ?></td>
                <td><?php if ($rewrite_url) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($rewrite_url) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
            </tbody>
          </table>
        </fieldset>
        <p><?php echo $text_install_extension; ?></p>
        <fieldset>
          <table class="table">
            <thead>
              <tr>
                <td width="35%"><b><?php echo $text_extension; ?></b></td>
                <td width="25%"><b><?php echo $text_current; ?></b></td>
                <td width="25%"><b><?php echo $text_required; ?></b></td>
                <td width="15%" class="text-center"><b><?php echo $text_status; ?></b></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo $text_db; ?></td>
                <td><?php if ($db) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($db) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $text_gd; ?></td>
                <td><?php if ($gd) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($gd) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $text_curl; ?></td>
                <td><?php if ($curl) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($curl) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              
              <tr>
                <td><?php echo $text_zlib; ?></td>
                <td><?php if ($zlib) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($zlib) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <tr>
                <td><?php echo $text_zip; ?></td>
                <td><?php if ($zip) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center"><?php if ($zip) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <?php if (!$iconv) { ?>
              <tr>
                <td><?php echo $text_mbstring; ?></td>
                <td>
                  <?php if ($mbstring) { ?>
                  <?php echo $text_on; ?>
                  <?php } else { ?>
                  <?php echo $text_off; ?>
                  <?php } ?>
                </td>
                <td><?php echo $text_on; ?></td>
                <td class="text-center">
                  <?php if ($mbstring) { ?>
                  <span class="text-success"><i class="fa fa-check-circle svg-icn check"><svg class="icon icon--check"><use xlink:href="#Check_Circle" /></svg>

</i></span>
                  <?php } else { ?>
                  <span class="text-danger"><i class="fa fa-minus-circle svg-icn delete"><svg class="icon icon--check"><use xlink:href="#Check_Close" /></svg></i></span>
                  <?php } ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </fieldset>
        <p><?php echo $text_msql_version; ?></p>
        <p><?php echo $text_strict_trans_tables; ?></p>
        <p><?php echo $text_note; ?></p>
                
      </form>
    </div>    
  </div>
</div>
