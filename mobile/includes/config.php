<?php
/**
 * Paramètre sitekey et secretkey du RECAPTCHA de google
 * ( à dynamiser sur chaque environnement )
 */
//define("CAPTCHA_SECRET_KEY", "6Lc5hB8TAAAAADQ5svVcAav8OFLp87cJEf-JPdW0"); // env preprod interne ODiTY
//define("CAPTCHA_SITE_KEY", "6Lc5hB8TAAAAAPven0WESmpwi6YRYhwqeWmNkWPU"); // env preprod interne ODiTY
define("CAPTCHA_SECRET_KEY", "6LeROh8TAAAAAHI2_YJL6J7MbWWiDuiKJi8VKNua"); // env localhost
define("CAPTCHA_SITE_KEY", "6LeROh8TAAAAAJ_vMRYl4wnkmrRp6fewloMQe9Px"); // env localhost

// mail destinataire formulaire contact
define("DESTINATAIRE_CONTACT_FORM", "cguezou@odity.fr");

// mail expéditeur confirmation inscription et confirmation participation
define("EXPEDITEUR_CONFIRMATION", "cguezou@odity.fr");


/**
 * Paramètre smtp pour l'envoi de mail
 */
define("SMTP_HOST", "auth.smtp.1and1.fr"); // smtp host
define("SMTP_PORT", 587); // smtp port number
define("SMTP_USER_NAME", "audaceamiel@odity.fr"); // smtp user name
define("SMTP_USER_PWD", "M41l@4ud4c3;"); // smtp user password