<?php

// Email functions

function sendMail($to, $subject, $body, $extra_headers=''){
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

    $headers .= 'From: ' . Settings::getSetting("CONF_FROM_NAME") ."<".Settings::getSetting("CONF_FROM_EMAIL").">" . "\r\nReply-to: ".Settings::getSetting("CONF_REPLY_TO_EMAIL");

    if ($extra_headers != '') $headers .= $extra_headers;
    return mail($to, $subject, $body, $headers);
}

function sendMailTpl($to, $tpl, $vars=array(), $extra_headers='',$smtp=0,$smtp_arr=array()) {
	
    //$db = &Syspage::getdb();
	global $db;
    $rs = $db->query("SELECT tpl_subject, tpl_body FROM tbl_email_templates WHERE tpl_code=".$db->quoteVariable($tpl)." and tpl_status=1");
    $row = $db->fetch($rs);

    if (!isset($row['tpl_body']) || empty($row['tpl_body'])) {
        return false;
    }

    $subject = $row['tpl_subject'];
    $body = $row['tpl_body'];
    $vars['{current_date}']=date('M d, Y');
    foreach ($vars as $key => $val) {
        $subject = str_replace($key, $val, $subject);
        $body = str_replace($key, $val, $body);
    }
	$company_logo="<img src='".Utilities::generateAbsoluteUrl('image', 'site_email_logo',array(Settings::getSetting("CONF_EMAIL_LOGO")),CONF_WEBROOT_URL)."' alt=''  />";
	$body=str_replace('{Company_Logo}',$company_logo,$body);
	//die($body."=".$to."=".$subject);
	//$db = &Syspage::getdb();

    $db->insert_from_array('tbl_email_archives', array(
    'mailarchive_from_email'=>'',
    'mailarchive_to_email'=>$to,
    'mailarchive_tpl_name'=>$tpl,
    'mailarchive_subject'=>$subject,
    'mailarchive_message'=>$body,
    'mailarchive_sent_on'=>date('Y-m-d H:i:s'),
    ), $exec_mysql_func=true);
	
	if (empty($smtp_arr)){
			$smtp_arr=array("host"=>Settings::getSetting("CONF_SMTP_HOST"),"port"=>Settings::getSetting("CONF_SMTP_PORT"),"username"=>Settings::getSetting("CONF_SMTP_USERNAME"),"password"=>Settings::getSetting("CONF_SMTP_PASSWORD"));
	}
	
	if (Settings::getSetting("CONF_SEND_EMAIL")){
		if ((Settings::getSetting("CONF_SEND_SMTP_EMAIL")) || $smtp){
			return Utilities::sendSmtpEmail($to, $subject, $body,'',$smtp_arr);
		}
		else
		    return sendMail($to, $subject, $body);
	}else{
		return true;
	}
	//return Utilities::sendSmtpEmail($to, $subject, $body);
}

function convert_url($str)
{
	return "xx".$str;
}

function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
    $file = $path.$filename;
    $file_size = filesize($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $name = basename($file);
    $header = "From: ".$from_name." <".$from_mail.">\r\n";
    $header .= "Reply-To: ".$replyto."\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
    $header .= "This is a multi-part message in MIME format.\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-type:text/html; charset=iso-8859-1\r\n";
    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $header .= $message."\r\n\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
    $header .= "Content-Transfer-Encoding: base64\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
    $header .= $content."\r\n\r\n";
    $header .= "--".$uid."--";
    if (@mail($mailto, $subject, "", $header)) {
        return true;
    } else {
        return false;
    }
}

/*function sendSmtpEmail($toAdress,$Subject,$body,$attachment="",$smtp_arr=array())
{
	//require_once (dirname(__FILE__).'/../mailer/PHPMailerAutoload.php');
	require_once (dirname(__FILE__).'/../mail.php');
	//require_once ('mailer/PHPMailerAutoload.php');
	$host = $smtp_arr["host"]!=""?$smtp_arr["host"]:Settings::getSetting("CONF_SMTP_HOST"); // or "mail.example.com" is using without ssl
	$port = $smtp_arr["port"]!=""?$smtp_arr["port"]:Settings::getSetting("CONF_SMTP_PORT"); // only is using ssl
	$username = $smtp_arr["username"]!=""?$smtp_arr["username"]:Settings::getSetting("CONF_SMTP_USERNAME"); // only is using ssl
	$password = $smtp_arr["password"]!=""?$smtp_arr["password"]:Settings::getSetting("CONF_SMTP_PASSWORD"); // only is using ssl
	$mail = new Mail();
	$mail->protocol = "smtp";
	$mail->smtp_hostname = $host;
	$mail->smtp_username = $username;
	$mail->smtp_password = html_entity_decode($password, ENT_QUOTES, 'UTF-8');
	$mail->smtp_port = $port;
	$mail->setTo($toAdress);
	$mail->setFrom(Settings::getSetting("CONF_FROM_EMAIL"));
	$mail->setSender(html_entity_decode(Settings::getSetting("CONF_FROM_NAME"), ENT_QUOTES, 'UTF-8'));
	$mail->setSubject(html_entity_decode($Subject, ENT_QUOTES, 'UTF-8'));
	$mail->setText($body);
	$mail->send();
	return true;
} */

function sendSmtpEmail($toAdress,$Subject,$body,$attachment="",$smtp_arr=array())
{
	//require_once (dirname(__FILE__).'/../mailer/PHPMailerAutoload.php');
	//require_once ('mailer/PHPMailerAutoload.php');
	$host = $smtp_arr["host"]!=""?$smtp_arr["host"]:Settings::getSetting("CONF_SMTP_HOST"); // or "mail.example.com" is using without ssl
	$port = $smtp_arr["port"]!=""?$smtp_arr["port"]:Settings::getSetting("CONF_SMTP_PORT"); // only is using ssl
	$username = $smtp_arr["username"]!=""?$smtp_arr["username"]:Settings::getSetting("CONF_SMTP_USERNAME"); // only is using ssl
	$password = $smtp_arr["password"]!=""?$smtp_arr["password"]:Settings::getSetting("CONF_SMTP_PASSWORD"); // only is using ssl
	
	//die($host."=".$port."=".$username."=".$password);
	//Create a new PHPMailer instance
	$mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
	//Tell PHPMailer to use SMTP
	$mail->isSMTP();
	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;
	$mail->SMTPSecure = 'tls';
	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';
	//Set the hostname of the mail server
	$mail->Host = $host;
	//Set the SMTP port number - likely to be 25, 465 or 587
	$mail->Port = $port;
	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;
	//Username to use for SMTP authentication
	$mail->Username = $username;
	//Password to use for SMTP authentication
	$mail->Password = $password;
	//Set who the message is to be sent from
	$mail->setFrom(Settings::getSetting("CONF_FROM_EMAIL"), Settings::getSetting("CONF_FROM_NAME"));
	//Set an alternative reply-to address
	$mail->addReplyTo(Settings::getSetting("CONF_REPLY_TO_EMAIL"), Settings::getSetting("CONF_FROM_NAME"));
	//$mail->addReplyTo('info@dummyid.com', 'First Last');
	//Set who the message is to be sent to
	$mail->addAddress($toAdress);
	//Set the subject line
	$mail->Subject = $Subject;
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	$mail->msgHTML($body);
	//Replace the plain text body with one created manually
	$mail->AltBody = 'This is a plain-text message body';
	//Attach an image file
	//$mail->addAttachment('images/phpmailer_mini.png');
	
	//send the message, check for errors
	if (!$mail->send()) {
		//echo 'Message could not be sent.';
	    //echo 'Mailer Error: ' . $mail->ErrorInfo;
		throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
	    exit;
		//return $mail->ErrorInfo;
	} else {
		//echo 'Message sent.';
		return true;
	}
}

// Email function Ends