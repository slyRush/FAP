<?php

require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$userId = NULL; // ID utilisateur - variable globale
$userConnected = NULL; // user connected -- all info

require_once 'routes/route_adresse.php';
require_once 'routes/route_arrondissement.php';
require_once 'routes/route_assoc_ppr_assoc_ucfb.php';
require_once 'routes/route_assoc_sd_assoc_ucfb.php';
require_once 'routes/route_assoc_ucfb.php';
require_once 'routes/route_bcommande.php';
require_once 'routes/route_campagne.php';
require_once 'routes/route_canton.php';
require_once 'routes/route_circonscription.php';
require_once 'routes/route_commune.php';
require_once 'routes/route_departement.php';
require_once 'routes/route_etat_panneau.php';
require_once 'routes/route_facture.php';
require_once 'routes/route_format_panneau.php';
require_once 'routes/route_panneau.php';
require_once 'routes/route_pays.php';
require_once 'routes/route_photo_panneau.php';
require_once 'routes/route_raison_signalement.php';
require_once 'routes/route_region.php';
require_once 'routes/route_role.php';
require_once 'routes/route_type_adresse.php';
require_once 'routes/route_utilisateur.php';
require_once 'routes/route_login_register.php';

$app->run();