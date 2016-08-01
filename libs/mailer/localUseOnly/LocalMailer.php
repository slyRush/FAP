<?php

/**
 * Created by PhpStorm.
 * User: dev
 * Date: 02/11/2015
 * Time: 18:12
 */
require __DIR__ . '/PHPMailerAutoload.php';
class LocalMailer
{
    private $mail;

    public function __construct(){
        $this->mail = new PHPMailer;
        $this->mail->isSMTP();
        //$this->mail->SMTPDebug = 2;
        //$this->mail->Debugoutput = 'html';
        $this->mail->Host = 'auth.smtp.1and1.fr';
        //$this->mail->Host = 'smtp.mail.yahoo.com';

        $this->mail->Port = 587;
        $this->mail->SMTPSecure = 'tls';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = "audaceamiel@odity.fr";
        $this->mail->Password = "M41l@4ud4c3;";
        $this->mail->setFrom('mail', '');
        $this->mail->IsHTML(true); // send as HTML
        $this->mail->addReplyTo('preparez-vos-vacances-citroen@actel.fr', 'Citroen Vacances');
    }

    public function sendMail($to, $mailSubject, $content){
        $this->mail->AddAddress($to,"");
        $this->mail->Subject = $mailSubject;
        $this->mail->Body = $content;
        return ($this->mail->send());
    }
}