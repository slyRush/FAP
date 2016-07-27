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
$app->post('/login/utilisateur', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password')); // v�rifier les param�tres requises

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $utilisateur_query = $db->entityManager->utilisateur("email = ?", $email);
    $utilisateur = $utilisateur_query->fetch();

    if( $utilisateur != FALSE ) //false si l'email de l'utilisateur n'est pas trouv�
    {
        if (PassHash::check_password($utilisateur['password_hash'], $password))
        {
            $user = JSON::removeNode($utilisateur, "password_hash"); //remove password_hash column from $user
            if($user["status"] == 0) //utilisateur activ�
            {
                $logManager->setLog($utilisateur, (string)$utilisateur_query, false);
                echoResponse(200, true, "Connexion r�ussie", $utilisateur); // Mot de passe utilisateur est correcte
            }
            else
            {
                $logManager->setLog($utilisateur, (string)$utilisateur_query, true);
                echoResponse(200, true, "Connexion r�ussie", $utilisateur); // Mot de passe utilisateur est correcte
            }
        }
        else
        {
            $logManager->setLog($utilisateur, (string)$utilisateur_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($utilisateur, (string)$utilisateur_query, true);
        echoResponse(200, false, "Echec de la connexion. identificateurs incorrectes", NULL); // identificateurs de l'utilisateur sont erron�s
    }
});

/**
* Register user
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register/utilisateur', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('name','email','password')); // v�rifier les param�dtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['name'];
    $email = $request_params['email'];
    $password = $request_params['password'];

    validateEmail($email); //valider adresse email

    $utilisateur_exist_query = $db->entityManager->utilisateur("email = ?", $email);
    $utilisateur_exist = $db->entityManager->utilisateur("email = ?", $email)->fetch();

    if($utilisateur_exist == FALSE) //email utilisateur doesn't exist
    {
        $data = array(
            "name"              => $name,
            "email"             => $email,
            "api_key"           => generateApiKey(), // G�n�rer API key
            "password_hash"     => PassHash::hash($password), //G�n�rer un hash de mot de passe
        );

        $insert_utilisateur = $db->entityManager->utilisateur()->insert($data);

        if($insert_utilisateur == FALSE)
        {
            $logManager->setLog(null, (string)$utilisateur_exist_query . " / " . buildSqlQueryInsert("utilisateur", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_utilisateur != FALSE || is_array($insert_utilisateur))
            {
                $logManager->setLog(null, (string)$utilisateur_exist_query . " / " . buildSqlQueryInsert("utilisateur", $data), false);
                echoResponse(201, true, "Author inscrit avec succ�s", $insert_utilisateur);
            }
        }
    }
    else
    {
        if($utilisateur_exist != FALSE || count($utilisateur_exist) > 1)
        {
            $logManager->setLog(null, (string)$utilisateur_exist_query, false);
            echoResponse(400, false, "D�sol�, cet E-mail �xiste d�ja", NULL);
        }
    }
});