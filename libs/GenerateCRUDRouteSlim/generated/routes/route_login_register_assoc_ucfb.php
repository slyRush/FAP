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
    verifyRequiredParams(array('email','password')); // v�rifier les param�tres requises

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $assoc_ucfb_query = $db->entityManager->assoc_ucfb("email = ?", $email);
    $assoc_ucfb = $assoc_ucfb_query->fetch();

    if( $assoc_ucfb != FALSE ) //false si l'email de l'assoc_ucfb n'est pas trouv�
    {
        if (PassHash::check_password($assoc_ucfb['password_hash'], $password))
        {
            $user = JSON::removeNode($assoc_ucfb, "password_hash"); //remove password_hash column from $user
            if($user["status"] == 0) //assoc_ucfb activ�
            {
                $logManager->setLog($assoc_ucfb, (string)$assoc_ucfb_query, false);
                echoResponse(200, true, "Connexion r�ussie", $assoc_ucfb); // Mot de passe utilisateur est correcte
            }
            else
            {
                $logManager->setLog($assoc_ucfb, (string)$assoc_ucfb_query, true);
                echoResponse(200, true, "Connexion r�ussie", $assoc_ucfb); // Mot de passe utilisateur est correcte
            }
        }
        else
        {
            $logManager->setLog($assoc_ucfb, (string)$assoc_ucfb_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($assoc_ucfb, (string)$assoc_ucfb_query, true);
        echoResponse(200, false, "Echec de la connexion. identificateurs incorrectes", NULL); // identificateurs de l'utilisateur sont erron�s
    }
});

/**
* Register user
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('name','email','password')); // v�rifier les param�dtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['name'];
    $email = $request_params['email'];
    $password = $request_params['password'];

    validateEmail($email); //valider adresse email

    $assoc_ucfb_exist_query = $db->entityManager->assoc_ucfb("email = ?", $email);
    $assoc_ucfb_exist = $db->entityManager->assoc_ucfb("email = ?", $email)->fetch();

    if($assoc_ucfb_exist == FALSE) //email assoc_ucfb doesn't exist
    {
        $data = array(
            "name"              => $name,
            "email"             => $email,
            "api_key"           => generateApiKey(), // G�n�rer API key
            "password_hash"     => PassHash::hash($password), //G�n�rer un hash de mot de passe
        );

        $insert_assoc_ucfb = $db->entityManager->assoc_ucfb()->insert($data);

        if($insert_assoc_ucfb == FALSE)
        {
            $logManager->setLog(null, (string)$assoc_ucfb_exist_query . " / " . buildSqlQueryInsert("assoc_ucfb", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_assoc_ucfb != FALSE || is_array($insert_assoc_ucfb))
            {
                $logManager->setLog(null, (string)$assoc_ucfb_exist_query . " / " . buildSqlQueryInsert("assoc_ucfb", $data), false);
                echoResponse(201, true, "Author inscrit avec succ�s", $insert_assoc_ucfb);
            }
        }
    }
    else
    {
        if($assoc_ucfb_exist != FALSE || count($assoc_ucfb_exist) > 1)
        {
            $logManager->setLog(null, (string)$assoc_ucfb_exist_query, false);
            echoResponse(400, false, "D�sol�, cet E-mail �xiste d�ja", NULL);
        }
    }
});