<?php
/**
 * Tous les fonctions pour sécuriser le WS avec un API Key : clé API obligatoire avant de pouvoir consommer les ressources après authentification
 */

include_once "set_headers.php";

require_once dirname(__DIR__)  . '/db_manager/dbManager.php';

/**
 * Generate API Key unique MD5 String
 */
function generateApiKey()
{
    return md5(uniqid(rand(), true));
}

/**
 * Add authentification : verify if API Key send in Header (Key: Authorization) are valid and connected to one user
 */
function authenticate() {
    $headers = apache_request_headers(); // Get headers request

    // Check authorization in headers
    if (isset($headers['Authorization'])) {
        $db = new DBManager();

        $api_key = $headers['Authorization']; // Get API key

        $isValidApiKey = $db->entityManager->utilisateur("api_key = ?", $api_key)->fetch();

        if ($isValidApiKey == FALSE) // Validate API Key, exist into DB
        {
            global $app;
            echoResponse(401, false, "Access refused. API Key invalid", NULL);
            $app->stop();
        }
        else
        if ($isValidApiKey != FALSE)
        {
            global $userId, $userConnected;
            $userId = $isValidApiKey["id"]; // Obtenir l'ID utilisateur (clé primaire)
            $userConnected = $isValidApiKey;
        }
    }
    else
    {
        global $app;
        echoResponse(401, false, "You're not allowed to access the ressource. API Key missing", NULL); // API Key missing
        $app->stop();
    }
}