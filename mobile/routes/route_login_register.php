<?php
/**
 * Routes login manipulation - 'users' table concerned
 * ----------- METHOD no need authentification---------------------------------
 */

include_once dirname(__DIR__)  . '/includes/functions/set_headers.php';

require_once dirname(__DIR__)  . '/includes/config.php';
require_once dirname(__DIR__)  . '/includes/functions/utils.php';
require_once dirname(__DIR__)  . '/includes/functions/json.php';
require_once dirname(__DIR__)  . '/includes/functions/security_api.php';
require_once dirname(__DIR__)  . '/includes/db_manager/dbManager.php';
require_once dirname(__DIR__)  . '/includes/pass_hash.php';
require_once dirname(__DIR__)  . '/includes/Log.class.php';
include_once dirname(dirname(__DIR__)) . '/libs/mailer/localUseOnly/class.phpmailer.php';
include_once dirname(dirname(__DIR__)) . '/libs/mailer/localUseOnly/PHPMailerAutoload.php';

global $app, $mail_controll;
$db = new DBManager();
$logManager = new Log();

/**
 * Login User
 * url - /login
 * method - POST
 * @params email, password
 */
$app->post('/login', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('login','password')); // verify required params

    //get POST values
    $requestParams = json_decode($app->request()->getBody(), true);
    $email = $requestParams["login"];
    $password = $requestParams["password"];

    validateEmail($email); // validate email address

    $userQuery = $db->entityManager->utilisateur("email = ?", $email);
    $user = $userQuery->fetch();

    if( $user != FALSE ) //false email user was not found
    {
        //role
        $roleId = $user['role_id'];
        $role = $db->entityManager->role("niveau = ?", $roleId)->fetch();

        //address
        $userId = $user['id'];
        $address = $db->entityManager->adresse->where("utilisateur_id = ?", $userId);

        if (PassHash::check_password($user['password_hash'], $password))
        {
            switch($role)
            {
                case "1" : //Superadmin
                case "2" : //Admin
                {
                    $user = removeKeyByUser($user, $role); //remove no need column from $user

                    /** BEGIN : merge user with address information */
                    foreach ($address as $addr) {
                        if($addr['type_adresse_id'] == 1) //take just simple address (code address in address type = 1
                        {
                            $user["adresse"] = $addr['adresse_reel'];
                        }
                    }

                    /** END */

                    $logManager->setLog($user, (string)$userQuery, false);
                    echoResponseWithRecordsName(200, true, "Success connexion", $user, "user"); // OK: user connected, return user object

                    break;
                }

                case "3" : //Client
                {
                    $user = removeKeyByUser($user, $role); //remove no need column from $user

                    /** BEGIN : merge user with address information */
                    foreach ($address as $addr) {
                        if($addr['type_adresse_id'] == 3) //take just delivery address (code address in address type = 1
                        {
                            $user["adresse_facturation"] = $addr['adresse_reel']; //take just billing address (code address in address type = 3
                        }
                    }
                    /** END */

                    $logManager->setLog($user, (string)$userQuery, false);
                    echoResponseWithRecordsName(200, true, "Success connexion", $user, "user"); // OK: user connected, return user object

                    break;
                }
                case "4" : //Fournisseur
                {
                    $user = removeKeyByUser($user, $role); //remove no need column from $user

                    foreach ($address as $a) {
                        var_dump($a);
                    }

                    /** BEGIN : merge user with address information */
                    foreach ($address as $addr) {
                        if($addr['type_adresse_id'] == 2) //take just delivery address (code address in address type = 1
                        {
                            $user["adresse_livraison"] = $addr['adresse_reel']; //take just delivery address (code address in address type = 3
                        }

                        if($addr['type_adresse_id'] == 3) //take just billing address (code address in address type = 1
                        {
                            $user["adresse_facturation"] = $addr['adresse_reel']; //take just delivery address (code address in address type = 3
                        }
                    }

                    /** END */

                    $logManager->setLog($user, (string)$userQuery, false);
                    echoResponseWithRecordsName(200, true, "Success connexion", $user, "user"); // OK: user connected, return user object

                    break;
                }
                case "5" : //Afficheur
                {
                    $supplierAssociated = $db->entityManager->utilisateur("id", $user["fournisseur_id"])->fetch();

                    $user = removeKeyByUser($user, $role); //remove no need column from $user

                    $supplierAssociated = removeKeyByUser(JSON::parseNotormObjectToArray($supplierAssociated), $supplierAssociated['role_id']);
                    $supplierAssociated = replaceKeyNameInArray($supplierAssociated, "id", "idFournisseur"); //change id key to idFournisseur

                    $user = insertKeyValuePairInArray($user, 'Fournisseur', $supplierAssociated, 8); //insert supplier object after role_id
                    $user = replaceKeyNameInArray($user, "code_postal", "codePostal"); //change code_postal key to codePostal

                    /** BEGIN : merge user with department list affected by campaign */
                    /*$ctrlUserCampaign = $db->entityManager->assoc_ucfb->where("utilisateur_id = ?", $userId);

                    foreach ($ctrlUserCampaign as $userCampaign) {
                        $allDepartment = $db->entityManager->assoc_sd_assoc_ucfb->where("assoc_ucfb_id = ?", $userCampaign['id']);
                        $user["campagne"]['campagne_id'] = $userCampaign['campagne_id'];
                        $user["campagne"]['campagne_titre'] = $db->entityManager->campagne[$userCampaign['campagne_id']]['titre'];

                        foreach ($allDepartment as $depart) { //get all department affected
                            $user["campagne"]['departement_id'] = $depart['departement_id'];
                            $user["campagne"]['departement_nom'] = $db->entityManager->departement[$depart['departement_id']]['nom'];
                        }

                    }*/
                    /** END */

                    $logManager->setLog($user, (string)$userQuery, false);
                    echoResponseWithRecordsName(200, true, "Success connexion", $user, "user"); // OK: user connected, return user object

                    break;
                }
            }
        }
        else
        {
            $logManager->setLog($user, (string)$userQuery, true);
            echoResponseWithLog(400, false, "Wrong password", NULL); // Wrong password
        }
    }
    else
    {
        $logManager->setLog($user, (string)$userQuery, true);
        echoResponse(400, false, "Connexion failed. Username or Password wrong", NULL); //Username or Password wrong
    }
});

/**
* Register user
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register', function() use ($app, $db, $logManager) {
    $requestParams = json_decode($app->request()->getBody(), true); //get request params
    if(isset($requestParams['role_id']))
    {
        $roleId = $requestParams['role_id'];
    }
    else
    {
        echoResponse(400, false, 'Champ(s) requis role_id est (sont) manquant(s) ou vide(s)', NULL);
        $app->stop();
    }

    switch($roleId) //if role_id is defined in request params
    {
        case "1": //Supermadmin
        case "2": //Admin
        {
            verifyRequiredParams(array('nom', 'prenom', 'email', 'telephone', 'url_photo', 'password', 'adresse', 'role_id')); // check all params need

            // read des params de post
            $requestParams = json_decode($app->request()->getBody(), true);
            $name = $requestParams['nom'];
            $firstname = $requestParams['prenom'];
            $email = $requestParams['email'];
            $telephone = $requestParams['telephone'];
            $urlImage = $requestParams['url_photo'];
            $password = $requestParams['password'];
            $address = $requestParams['adresse'];

            validateEmail($email); //validate email address

            $userQuery = $db->entityManager->utilisateur("email = ?", $email);
            $userExist = $db->entityManager->utilisateur("email = ?", $email)->fetch();

            if($userExist == FALSE) //email utilisateur doesn't exist
            {
                /** Insert in User */
                $dataUser = array(
                    "nom"                   => $name,
                    "prenom"                => $firstname,
                    "email"                 => $email,
                    "telephone"             => $telephone,
                    "url_photo"             => $urlImage,
                    "api_key"               => generateApiKey(), // Generate PAI Key
                    "password_hash"         => PassHash::hash($password), //Generate hash password
                    "role_id"               => $roleId //must be 1 : ROLE SUPERADMIN
                );

                $insertUser = $db->entityManager->utilisateur()->insert($dataUser); // Insert in user table
                $insertUserId = $db->entityManager->utilisateur()->insert_id();

                /** Insert in Superadmin */
                $dataAddress = array(
                    "adresse_reel"      => $address,
                    "type_adresse_id"   => 1, //simple address
                    "utilisateur_id"    => $insertUserId
                );

                $insertAddress = $db->entityManager->adresse()->insert($dataAddress);

                //$data = array_merge($dataUser, $dataAddress);
                $data = array(
                    "utilisateur_id" => $insertUserId
                );

                if($insertUser == FALSE && $insertAddress == FALSE)
                {
                    $logManager->setLog(null, (string)$userQuery . " / " . buildSqlQueryInsert("utilisateur", $data), true);
                    echoResponse(400, false, "Oops! Error has occured when trying to connect", NULL);
                }
                else
                {
                    if( ($insertUser != FALSE || is_array($insertUser)) && ($insertAddress != FALSE || is_array($insertAddress)) )
                    {
                        $logManager->setLog(null, (string)$userQuery . " / " . buildSqlQueryInsert("utilisateur", $data), false);
                        echoResponse(201, true, "User inserted", JSON::removeNode($data, "password_hash"));
                    }
                }
            }
            else
            {
                if($userExist != FALSE || count($userExist) > 1)
                {
                    $logManager->setLog(null, (string)$userQuery, false);
                    echoResponse(400, false, "Sorry, E-mail already exist", NULL);
                }
            }

            break;
        }

        case "3": //Client
        {
            verifyRequiredParams(array('nom', 'prenom', 'email', 'telephone', 'url_photo', 'password', 'adresse_facturation')); // check all params need

            // read des params de post
            $requestParams = json_decode($app->request()->getBody(), true);
            $name = $requestParams['nom'];
            $firstname = $requestParams['prenom'];
            $email = $requestParams['email'];
            $telephone = $requestParams['telephone'];
            $urlImage = $requestParams['url_photo'];
            $password = $requestParams['password'];
            $address = $requestParams['adresse_facturation'];

            validateEmail($email); //validate email address

            $userQuery = $db->entityManager->utilisateur("email = ?", $email);
            $userExist = $db->entityManager->utilisateur("email = ?", $email)->fetch();

            if($userExist == FALSE) //email utilisateur doesn't exist
            {
                /** Insert in User */
                $dataUser = array(
                    "nom"                   => $name,
                    "prenom"                => $firstname,
                    "email"                 => $email,
                    "telephone"             => $telephone,
                    "url_photo"             => $urlImage,
                    "api_key"               => generateApiKey(), // Generate PAI Key
                    "password_hash"         => PassHash::hash($password), //Generate hash password
                    "role_id"               => $roleId //must be 1 : ROLE SUPERADMIN
                );

                $insertUser = $db->entityManager->utilisateur()->insert($dataUser); // Insert in user table
                $insertUserId = $db->entityManager->utilisateur()->insert_id();

                /** Insert in Superadmin */
                $dataAddress = array(
                    "adresse_reel"      => $address,
                    "type_adresse_id"   => 3, //delivery address
                    "utilisateur_id"    => $insertUserId
                );

                $insertAddress = $db->entityManager->adresse()->insert($dataAddress);

                //$data = array_merge($dataUser, $dataAddress);
                $data = array(
                    "utilisateur_id" => $insertUserId
                );

                if($insertUser == FALSE && $insertAddress == FALSE)
                {
                    $logManager->setLog(null, (string)$userQuery . " / " . buildSqlQueryInsert("utilisateur", $data), true);
                    echoResponse(400, false, "Oops! Error has occured when trying to connect", NULL);
                }
                else
                {
                    if( ($insertUser != FALSE || is_array($insertUser)) && ($insertAddress != FALSE || is_array($insertAddress)) )
                    {
                        $logManager->setLog(null, (string)$userQuery . " / " . buildSqlQueryInsert("utilisateur", $data), false);
                        echoResponse(201, true, "User inserted", JSON::removeNode($data, "password_hash"));
                    }
                }
            }
            else
            {
                if($userExist != FALSE || count($userExist) > 1)
                {
                    $logManager->setLog(null, (string)$userQuery, false);
                    echoResponse(400, false, "Sorry, E-mail already exist", NULL);
                }
            }

            break;
        }
        case "4": //Fournisseur
        {
            verifyRequiredParams(array('nom', 'prenom', 'email', 'telephone', 'url_photo', 'password', 'raison_sociale', 'siret', 'iban', 'ville', 'adresse_facturation', 'adresse_livraison', 'codepostal_facturation')); // check all params need

            //read post params
            $requestParams = json_decode($app->request()->getBody(), true);
            $name = $requestParams['nom'];
            $firstname = $requestParams['prenom'];
            $email = $requestParams['email'];
            $telephone = $requestParams['telephone'];
            $urlImage = $requestParams['url_photo'];
            $password = $requestParams['password'];

            //table fournisseur need
            $corporateName = $requestParams['raison_sociale'];
            $siret = $requestParams['siret'];
            $iban = $requestParams['iban'];
            $ville = $requestParams['ville'];
            $deliveryAddress = $requestParams['adresse_facturation'];
            $billingAddress = $requestParams['adresse_livraison'];
            $postalCodeDeliveryAddress = $requestParams['codepostal_facturation'];

            validateEmail($email); //validate email address

            $supplierExistQuery = $db->entityManager->utilisateur("email = ?", $email);
            $supplierExist = $db->entityManager->utilisateur("email = ?", $email)->fetch();

            if($supplierExist == FALSE) //email fournisseur doesn't exist
            {
                /** Insert in User */
                $dataUser = array(
                    "nom"                       => $name,
                    "prenom"                    => $firstname,
                    "email"                     => $email,
                    "telephone"                 => $telephone,
                    "url_photo"                 => $urlImage,
                    "raison_sociale"            => $corporateName,
                    "siret"                     => $siret,
                    "iban"                      => $iban,
                    "ville"                     => $ville,
                    "code_postal_facturation"    => $postalCodeDeliveryAddress,
                    "api_key"                   => generateApiKey(), // Generate API Key
                    "password_hash"             => PassHash::hash($password), //Generate hash password
                    "role_id"                   => $roleId //must be 4: ROLE FOURNISSEUR
                );

                $insertUser = $db->entityManager->utilisateur()->insert($dataUser); // Insert in user table
                $insertUserId = $db->entityManager->utilisateur()->insert_id();

                /** Insert in fournisseur */
                $dataSupplierDeliveryAddress = array(
                    "adresse_reel"              => $deliveryAddress,
                    "type_adresse_id"           => 3, //delivery address
                    "utilisateur_id"            => $insertUserId
                );

                $dataSupplierBillingAddress = array(
                    "adresse_reel"              => $billingAddress,
                    "type_adresse_id"           => 2, //billing address
                    "utilisateur_id"            => $insertUserId
                );

                /*$dataSupplierDeliveryAddress = JSON::removeNode($dataSupplierDeliveryAddress, "type_adresse_id");
                $dataSupplierDeliveryAddress = JSON::removeNode($dataSupplierDeliveryAddress, "utilisateur_id");
                $dataSupplierBillingAddress = JSON::removeNode($dataSupplierBillingAddress, "type_adresse_id");
                $dataSupplierBillingAddress = JSON::removeNode($dataSupplierBillingAddress, "utilisateur_id");*/

                $allAddress = array(
                    "adresse_facturation" => $deliveryAddress,
                    "adresse_livraison"   => $billingAddress
                );

                $insertSupplierDeliveryAddress = $db->entityManager->adresse()->insert($dataSupplierDeliveryAddress);
                $insertSupplierBillingAddress = $db->entityManager->adresse()->insert($dataSupplierBillingAddress);

                /*$data = array_merge($dataUser, $allAddress);

                $data = JSON::removeNode($data, "password_hash");

                $data = insertKeyValuePairInArray($data, "id", $insertUserId, 0);*/
                $data = array(
                    "utilisateur_id" => $insertUserId
                );

                if($insertUser == FALSE && $insertSupplierDeliveryAddress == FALSE && $insertSupplierBillingAddress == FALSE)
                {
                    $logManager->setLog(null, (string)$supplierExistQuery . " / " . buildSqlQueryInsert("fournisseur", $data), true);
                    echoResponse(400, false, "Oops! Error has occured when trying to connect", NULL);
                }
                else
                {
                    if( ($insertUser != FALSE || is_array($insertUser)) && ($insertSupplierDeliveryAddress != FALSE || is_array($insertSupplierDeliveryAddress)) && ($insertSupplierBillingAddress != FALSE || is_array($insertSupplierBillingAddress)) )
                    {
                        $logManager->setLog(null, (string)$supplierExistQuery . " / " . buildSqlQueryInsert("fournisseur", $data), false);
                        echoResponse(201, true, "User inserted", $data);
                    }
                }
            }
            else
            {
                if($supplierExist != FALSE || count($supplierExist) > 1)
                {
                    $logManager->setLog(null, (string)$supplierExistQuery, false);
                    echoResponse(400, false, "Sorry, E-mail already exist", NULL);
                }
            }

            break;
        }
        case "5": //Afficheur
        {
            verifyRequiredParams(array('nom', 'prenom', 'email', 'telephone', 'url_photo', 'password', 'num_siret_fournisseur', 'ville', 'code_postal', 'fournisseur_id'));; // check all params need

            // read post params
            $requestParams = json_decode($app->request()->getBody(), true);
            $name = $requestParams['nom'];
            $firstname = $requestParams['prenom'];
            $email = $requestParams['email'];
            $telephone = $requestParams['telephone'];
            $urlImage = $requestParams['url_photo'];
            $password = $requestParams['password'];

            //table afficheur need
            $supplierNumSiret = $requestParams['num_siret_fournisseur'];
            $postalCode = $requestParams['code_postal'];
            $supplierId = $requestParams['fournisseur_id'];

            if($db->entityManager->utilisateur("id", $supplierId)->fetch()['role_id'] != 4)
            {
                echoResponse(400, false, "User are not a supplier", NULL);
                $app->stop();
            }

            validateEmail($email); //validate email address

            $displayerExistQuery = $db->entityManager->utilisateur("email = ?", $email);
            $displayerExist = $db->entityManager->utilisateur("email = ?", $email)->fetch();

            if($displayerExist == FALSE) //email fournisseur doesn't exist
            {
                /** Insert in User */
                $dataUser = array(
                    "nom"                   => $name,
                    "prenom"                => $firstname,
                    "email"                 => $email,
                    "telephone"             => $telephone,
                    "url_photo"             => $urlImage,
                    "num_siret_fournisseur" => $supplierNumSiret,
                    "code_postal"           => $postalCode,
                    "api_key"               => generateApiKey(), // Generate API Key
                    "password_hash"         => PassHash::hash($password), //Generate hash password
                    "role_id"               => $roleId, //must be 5: ROLE AFFICHEUR
                    "fournisseur_id"        => $supplierId
                );

                $insertUser = $db->entityManager->utilisateur()->insert($dataUser); // Insert in user table
                $insertUserId = $db->entityManager->utilisateur()->insert_id();

                $data = array(
                    "utilisateur_id" => $insertUserId
                );

                if($insertUser == FALSE)
                {
                    $logManager->setLog(null, (string)$displayerExistQuery . " / " . buildSqlQueryInsert("afficheur", $data), true);
                    echoResponse(400, false, "Oops! Error has occured when trying to connect", NULL);
                }
                else
                {
                    if( ($insertUser != FALSE || is_array($insertUser)) )
                    {
                        $logManager->setLog(null, (string)$displayerExistQuery . " / " . buildSqlQueryInsert("afficheur", $data), false);
                        echoResponse(201, true, "User inserted", $data);
                    }
                }
            }
            else
            {
                if($displayerExist != FALSE || count($displayerExist) > 1)
                {
                    $logManager->setLog(null, (string)$displayerExistQuery, false);
                    echoResponse(400, false, "Sorry, E-mail already exist", NULL);
                }
            }

            break;
        }
    }
});

/**
 * Forgot password, send new password by email
 */
$app->post('/forgot-password', function() use ($app, $db, $logManager, $mail_controll) {
    verifyRequiredParams(array('login')); // verify required params

    //get POST values
    $requestParams = json_decode($app->request()->getBody(), true);
    $email = $requestParams["login"];

    validateEmail($email); // validate email address

    $userQuery = $db->entityManager->utilisateur("email = ?", $email);
    $user = $userQuery->fetch();

    if( $user != FALSE ) //false email user was not found
    {
        $email = $user['email'];
        $name = $user['nom'];
        $firstname = $user['prenom'];
        $newPassword = substr(md5(uniqid(mt_rand(), true)), 0, 8);

        /** SEND MAIL AFTER CONFIRMATION **/
        //Set who the message is to be sent from
        $mail_controll->setFrom(EXPEDITEUR_CONFIRMATION, 'FCA');
        $mail_controll->AddReplyTo(EXPEDITEUR_CONFIRMATION, 'FCA');
        //Set who the message is to be sent to
        $mail_controll->addAddress($email, mssql_escape_for_mail($name));
        //Set the subject line
        $mail_controll->Subject = '[FAP] Nouveau mot de passe';
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $filepath = require_once dirname(__DIR__)  . '/includes/mail/mail-fap-forgot-password.html';
        $content_email = setEmail($filepath);
        $parts_to_mod = array("{{nom}}", "{{prenom}}", "{{new_password}}");
        $replace_with = array(htmlentities(mssql_escape_for_mail($name)), htmlentities(mssql_escape_for_mail($firstname)), PassHash::hash($newPassword));

        for ($i = 0; $i < count($parts_to_mod); $i++) {
            $content_email = str_replace($parts_to_mod[$i], $replace_with[$i], $content_email);
        }

        $mail_controll->msgHTML($content_email);
        //Replace the plain text body with one created manually
        $mail_controll->AltBody = 'This is a plain-text message body';

        if ($mail_controll->send()) {
            echoResponse(200, true, "New password send to $email", NULL);
        }
        else
        {
            echoResponse(200, true, "Error when try to send New password to $email", NULL);
        }
    }

});