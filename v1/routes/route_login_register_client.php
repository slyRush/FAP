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
$app->post('/login/client', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $client_query = $db->entityManager->client("email = ?", $email);
    $client = $client_query->fetch();

    if( $client != FALSE ) //false si l'email de l'client n'est pas trouvé
    {
        if (PassHash::check_password($client['password_hash'], $password))
        {
            $user = JSON::removeNode($client, "password_hash"); //remove password_hash column from $user
            if($user["status"] == 0) //client activé
            {
                $logManager->setLog($client, (string)$client_query, false);
                echoResponse(200, true, "Connexion réussie", $client); // Mot de passe utilisateur est correcte
            }
            else
            {
                $logManager->setLog($client, (string)$client_query, true);
                echoResponse(200, true, "Connexion réussie", $client); // Mot de passe utilisateur est correcte
            }
        }
        else
        {
            $logManager->setLog($client, (string)$client_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($client, (string)$client_query, true);
        echoResponse(200, false, "Echec de la connexion. identificateurs incorrectes", NULL); // identificateurs de l'utilisateur sont erronés
    }
});

/**
* Register user
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register/client', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('name','email','password')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['name'];
    $email = $request_params['email'];
    $password = $request_params['password'];

    validateEmail($email); //valider adresse email

    $client_exist_query = $db->entityManager->client("email = ?", $email);
    $client_exist = $db->entityManager->client("email = ?", $email)->fetch();

    if($client_exist == FALSE) //email client doesn't exist
    {
        $data = array(
            "name"              => $name,
            "email"             => $email,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_client = $db->entityManager->client()->insert($data);

        if($insert_client == FALSE)
        {
            $logManager->setLog(null, (string)$client_exist_query . " / " . buildSqlQueryInsert("client", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_client != FALSE || is_array($insert_client))
            {
                $logManager->setLog(null, (string)$client_exist_query . " / " . buildSqlQueryInsert("client", $data), false);
                echoResponse(201, true, "Author inscrit avec succès", $insert_client);
            }
        }
    }
    else
    {
        if($client_exist != FALSE || count($client_exist) > 1)
        {
            $logManager->setLog(null, (string)$client_exist_query, false);
            echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});