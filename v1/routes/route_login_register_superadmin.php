<?php
/**
 * Routes login manipulation - 'users' table concerned
 * ----------- METHOD no need authentification---------------------------------
 */

include_once dirname(__DIR__)  . '/includes/functions/set_headers.php';

require_once dirname(__DIR__)  . '/includes/functions/utils.php';
require_once dirname(__DIR__)  . '/includes/functions/json.php';
require_once dirname(__DIR__)  . '/includes/functions/security_api.php';
require_once dirname(__DIR__)  . '/includes/db_manager/dbManager.php';
require_once dirname(__DIR__)  . '/includes/pass_hash.php';
require_once dirname(__DIR__)  . '/includes/Log.class.php';

global $app;
$db = new DBManager();
$logManager = new Log();

/**
 * Login User
 * url - /login
 * method - POST
 * @params email, password
 */
$app->post('/login/superadmin', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $superadmin_query = $db->entityManager->superadmin("email = ?", $email);
    $superadmin = $superadmin_query->fetch();

    if( $superadmin != FALSE ) //false si l'email de l'superadmin n'est pas trouvé
    {
        if (PassHash::check_password($superadmin['password_hash'], $password))
        {
            $user = JSON::removeNode($superadmin, "password_hash"); //remove password_hash column from $user
            if($user["status"] == 0) //superadmin activé
            {
                $logManager->setLog($superadmin, (string)$superadmin_query, false);
                echoResponse(200, true, "Connexion réussie", $superadmin); // Mot de passe utilisateur est correcte
            }
            else
            {
                $logManager->setLog($superadmin, (string)$superadmin_query, true);
                echoResponse(200, true, "Connexion réussie", $superadmin); // Mot de passe utilisateur est correcte
            }
        }
        else
        {
            $logManager->setLog($superadmin, (string)$superadmin_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($superadmin, (string)$superadmin_query, true);
        echoResponse(200, false, "Echec de la connexion. identificateurs incorrectes", NULL); // identificateurs de l'utilisateur sont erronés
    }
});

/**
* Register user
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register/superadmin', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom', 'prenom', 'email', 'telephone', 'url_photo', 'password', SUPERADMIN_PARAMS)); // check all params need

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['nom'];
    $firstname = $request_params['prenom'];
    $email = $request_params['email'];
    $telephone = $request_params['telephone'];
    $urlImage = $request_params['url_photo'];
    $password = $request_params['password'];
    $address = $request_params['adresse'];

    validateEmail($email); //validate email address

    $superadmin_exist_query = $db->entityManager->utilisateur("email = ?", $email);
    $superadmin_exist = $db->entityManager->utilisateur("email = ?", $email)->fetch();

    if($superadmin_exist == FALSE) //email superadmin doesn't exist
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
            "role_utilisateur_id"   => 1
        );

        $insert_utilisateur = $db->entityManager->utilisateur()->insert($dataUser); // Insert in user table
        $insert_user_id = $db->entityManager->utilisateur()->insert_id();

        /** Insert in Superadmin */
        $dataSuperadmin = array(
            "adresse"           => $address,
            "utilisateur_id"    => $insert_user_id
        );

        $insert_superadmin = $db->entityManager->superadmin()->insert($dataSuperadmin);

        $data = array_merge($dataUser, $dataSuperadmin);

        if($insert_utilisateur == FALSE && $insert_superadmin == FALSE)
        {
            $logManager->setLog(null, (string)$superadmin_exist_query . " / " . buildSqlQueryInsert("superadmin", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if( ($insert_utilisateur != FALSE || is_array($insert_utilisateur)) && ($insert_superadmin != FALSE || is_array($insert_superadmin)) )
            {
                $logManager->setLog(null, (string)$superadmin_exist_query . " / " . buildSqlQueryInsert("superadmin", $data), false);
                echoResponseWithRecordsName(201, true, "Utilisateur inscrit avec succès en tant que super admin", JSON::removeNode($data, "password_hash"), "user");
            }
        }
    }
    else
    {
        if($superadmin_exist != FALSE || count($superadmin_exist) > 1)
        {
            $logManager->setLog(null, (string)$superadmin_exist_query, false);
            echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});