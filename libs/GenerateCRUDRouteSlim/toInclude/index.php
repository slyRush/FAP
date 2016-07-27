<?php

require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$user_id = NULL; // ID utilisateur - variable globale
$user_connected = NULL; // user connected -- all info

/**
 * TODO : Mettre tous les require_once des routes générées ici.
 */

$app->run();