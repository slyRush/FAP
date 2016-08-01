<?php
/**
 * EMAIL CONFIG, TO SEND EMAIL
 **/

include_once dirname(dirname(dirname(__DIR__))) . '/libs/mailer/localUseOnly/class.phpmailer.php';
include_once dirname(dirname(dirname(__DIR__))) . '/libs/mailer/localUseOnly/PHPMailerAutoload.php';
include_once('config.php');

global $mail_controll;

$mail_controll = new PHPMailer();
//Tell PHPMailer to use SMTP
$mail_controll->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail_controll->SMTPDebug = 0;
//Ask for HTML-friendly debug output
$mail_controll->Debugoutput = 'html';
$mail_controll->Host = SMTP_HOST;
$mail_controll->Port = SMTP_PORT;
//Set the encryption system to use - ssl (deprecated) or tls
$mail_controll->SMTPSecure = 'tls';
//Whether to use SMTP authentication
$mail_controll->SMTPAuth = true;
//Username to use for SMTP authentication - use full email address for gmail
$mail_controll->Username = SMTP_USER_NAME;
//Password to use for SMTP authentication
$mail_controll->Password = SMTP_USER_PWD;