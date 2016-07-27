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

    $admin_query = $db->entityManager->admin("email = ?", $email);
    $admin = $admin_query->fetch();

    if( $admin != FALSE ) //false si l'email de l'admin n'est pas trouvé
    {
        if (PassHash::check_password($admin['password_hash'], $password))
        {
            $user = JSON::removeNode($admin, "password_hash"); //remove password_hash column from $user
            if($user["status"] == 0) //admin activé
            {
                $logManager->setLog($admin, (string)$admin_query, false);
                echoResponse(200, true, "Connexion réussie", $admin); // Mot de passe utilisateur est correcte
            }
            else
            {
                $logManager->setLog($admin, (string)$admin_query, true);
                echoResponse(200, true, "Connexion réussie", $admin); // Mot de passe utilisateur est correcte
            }
        }
        else
        {
            $logManager->setLog($admin, (string)$admin_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($admin, (string)$admin_query, true);
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

    $admin_exist_query = $db->entityManager->admin("email = ?", $email);
    $admin_exist = $db->entityManager->admin("email = ?", $email)->fetch();

    if($admin_exist == FALSE) //email admin doesn't exist
    {
        $data = array(
            "name"              => $name,
            "email"             => $email,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_admin = $db->entityManager->admin()->insert($data);

        if($insert_admin == FALSE)
        {
            $logManager->setLog(null, (string)$admin_exist_query . " / " . buildSqlQueryInsert("admin", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_admin != FALSE || is_array($insert_admin))
            {
                $logManager->setLog(null, (string)$admin_exist_query . " / " . buildSqlQueryInsert("admin", $data), false);
                echoResponse(201, true, "Author inscrit avec succès", $insert_admin);
            }
        }
    }
    else
    {
        if($admin_exist != FALSE || count($admin_exist) > 1)
        {
            $logManager->setLog(null, (string)$admin_exist_query, false);
            echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});