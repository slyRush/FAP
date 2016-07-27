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
$app->post('/login', function() use ($app, $db, $logManager) {
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
$app->post('/register', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('name','email','password')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['name'];
    $email = $request_params['email'];
    $password = $request_params['password'];

    validateEmail($email); //valider adresse email

    $superadmin_exist_query = $db->entityManager->superadmin("email = ?", $email);
    $superadmin_exist = $db->entityManager->superadmin("email = ?", $email)->fetch();

    if($superadmin_exist == FALSE) //email superadmin doesn't exist
    {
        $data = array(
            "name"              => $name,
            "email"             => $email,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_superadmin = $db->entityManager->superadmin()->insert($data);

        if($insert_superadmin == FALSE)
        {
            $logManager->setLog(null, (string)$superadmin_exist_query . " / " . buildSqlQueryInsert("superadmin", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_superadmin != FALSE || is_array($insert_superadmin))
            {
                $logManager->setLog(null, (string)$superadmin_exist_query . " / " . buildSqlQueryInsert("superadmin", $data), false);
                echoResponse(201, true, "Author inscrit avec succès", $insert_superadmin);
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