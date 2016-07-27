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
    verifyRequiredParams(array('{$required_params_login}')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    ${$table_name}_query = $db->entityManager->{$table_name}("email = ?", $email);
    ${$table_name} = ${$table_name}_query->fetch();

    if( ${$table_name} != FALSE ) //false si l'email de l'{$table_name} n'est pas trouvé
    {
        if (PassHash::check_password(${$table_name}['password_hash'], $password))
        {
            $user = JSON::removeNode(${$table_name}, "password_hash"); //remove password_hash column from $user
            if($user["status"] == 0) //{$table_name} activé
            {
                $logManager->setLog(${$table_name}, (string)${$table_name}_query, false);
                echoResponse(200, true, "Connexion réussie", ${$table_name}); // Mot de passe utilisateur est correcte
            }
            else
            {
                $logManager->setLog(${$table_name}, (string)${$table_name}_query, true);
                echoResponse(200, true, "Connexion réussie", ${$table_name}); // Mot de passe utilisateur est correcte
            }
        }
        else
        {
            $logManager->setLog(${$table_name}, (string)${$table_name}_query, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog(${$table_name}, (string)${$table_name}_query, true);
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
    verifyRequiredParams(array('{$required_params_register}')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['name'];
    $email = $request_params['email'];
    $password = $request_params['password'];

    validateEmail($email); //valider adresse email

    ${$table_name}_exist_query = $db->entityManager->{$table_name}("email = ?", $email);
    ${$table_name}_exist = $db->entityManager->{$table_name}("email = ?", $email)->fetch();

    if(${$table_name}_exist == FALSE) //email {$table_name} doesn't exist
    {
        $data = array(
            "name"              => $name,
            "email"             => $email,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_{$table_name} = $db->entityManager->{$table_name}()->insert($data);

        if($insert_{$table_name} == FALSE)
        {
            $logManager->setLog(null, (string)${$table_name}_exist_query . " / " . buildSqlQueryInsert("{$table_name}", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_{$table_name} != FALSE || is_array($insert_{$table_name}))
            {
                $logManager->setLog(null, (string)${$table_name}_exist_query . " / " . buildSqlQueryInsert("{$table_name}", $data), false);
                echoResponse(201, true, "Author inscrit avec succès", $insert_{$table_name});
            }
        }
    }
    else
    {
        if(${$table_name}_exist != FALSE || count(${$table_name}_exist) > 1)
        {
            $logManager->setLog(null, (string)${$table_name}_exist_query, false);
            echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});