<?php
/**
 * Created by PhpStorm.
 * User: RANERIOA
 * Date: 28/10/2015
 * Time: 21:53
 */

//TODO: remove this when on prod
include __DIR__ . "/localUseOnly/LocalMailer.php";
class CustomMailer
{
    private $mailContent = "";
    private $mailType = "";
    private $mailObject = "";
    private $valToHtmlEntities = true;

	private $mail_template_dir = "";
    /**
     * @param string|string $mailContentFileName => just the filename, put the file in template_part/mail/
     * @param array $vars => array of variables in the mail content template [xxVarTemplateName => xxVarValue]
     * @param string => this will be shown in header content (for contact form, eg: CONTACT)
     * @param string => this will be shown in header content (for contact form, eg: Demande d'information)
     */
    public function __construct($mailContentFileName, array $vars, $mailType = ' ', $mailObject = ' ', $valToHtmlEntities = true){
        $this->mail_template_dir = $this->getPath();
        $this->mailType = $mailType;
        $this->mailObject = $mailObject;
        $this->valToHtmlEntities = $valToHtmlEntities;
        $this->mailContent = $this->getMailPartContent($vars, $mailContentFileName);

        
    }

    public function sendMail($to, $mailSubject){
        //return mail($to, $mailSubject, $this->getFullMailContent() , $this->getMailHeaders());
       //echo $this->getFullMailContent();
        return (new LocalMailer())->sendMail($to, $mailSubject, $this->getFullMailContent());
    }

    public function getPath(){
       return dirname(__FILE__) ."/mail/";
    }
    /**
     * this function return the full mail content (totality => header + content + footer)
     * @return string
     */
    private function getFullMailContent(){
        //$strMail = $this->getMailHeaderContent();
        //$strMail .= $this->mailContent;
        return $this->mailContent;
    }
    /**
     * @return string the html header content
     */
   /* private function getMailHeaderContent(){
        $vars['mailType'] = $this->mailType;
        $vars['mailObject'] = $this->mailObject;

        return $this->getMailPartContent($vars, "mailHeaderContent.html" );
    }*/

    /**
     * @return string => the html footer content
     */
    /*private function getMailFooterContent(){
        $vars['qualiscoreAddress'] = get_option('ql_ab_chk_dest_address') . ' ' . get_option('ql_ab_chk_dest_town'). ' ' . get_option('ql_ab_chk_dest_cp');
        $vars['qualiscoreFixePhone'] = get_option('ql_ab_fix_phone');
        $vars['qualiscoreMobilePhone'] = get_option('ql_ab_mobile_phone');
        $vars['qualiscoreEmail'] = get_option('ql_ab_mail');
        $vars['qualiscoreContactPageLink'] = get_permalink(524);
        return $this->getMailPartContent($vars, "mailFooterContent.html" );
    }*/



    /**
     * @param array $vars
     * @param $fileName: just the file name, eg: mailHeader.html
     * @return string
     */
    private function getMailPartContent(array $vars, $fileName){
        $vars['mailDate'] =strftime('%d %b %Y', time());
        $strMailPart = file_get_contents($this->mail_template_dir . $fileName);
        $parts_to_mod = array("{{civilite}}", "{{nom}}");
        $replace_with = array("Mr", "Bagdad"); // to replace
        for ($i = 0; $i < count($parts_to_mod); $i++) {
            $strMailPart = str_replace($parts_to_mod[$i], $replace_with[$i], $strMailPart);
        }
        return $strMailPart;
    }

    /**
     * @return string|mail header mime type
     */
    private function getMailHeaders(){
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        return $headers;
    }

}