<?php

require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$user_id = NULL; // ID utilisateur - variable globale
$user_connected = NULL; // user connected -- all info

require_once 'routes/route_admin.php';
require_once 'routes/route_login_register_admin.php';
require_once 'routes/route_afficheur.php';
require_once 'routes/route_login_register_afficheur.php';
require_once 'routes/route_afficheur_departement.php';
require_once 'routes/route_afficheur_zone_affichage.php';
require_once 'routes/route_arrondissement.php';
require_once 'routes/route_bon_commande.php';
require_once 'routes/route_bon_commande_departement.php';
require_once 'routes/route_campagne.php';
require_once 'routes/route_campagne.php';
require_once 'routes/route_campagne.php';
require_once 'routes/route_campagne.php';
require_once 'routes/route_campagne_client.php';
require_once 'routes/route_campagne_departement.php';
require_once 'routes/route_campagne_fournisseur.php';
require_once 'routes/route_campagne_pays.php';
require_once 'routes/route_canton.php';
require_once 'routes/route_circonscription.php';
require_once 'routes/route_client.php';
require_once 'routes/route_login_register_client.php';
require_once 'routes/route_commune.php';
require_once 'routes/route_departement.php';
require_once 'routes/route_etat_panneau.php';
require_once 'routes/route_format_panneau.php';
require_once 'routes/route_fournisseur.php';
require_once 'routes/route_login_register_fournisseur.php';
require_once 'routes/route_fournisseur_adresse_livraison.php';
require_once 'routes/route_fournisseur_departement.php';
require_once 'routes/route_panneau.php';
/*require_once 'routes/route_login_register_panneau.php';*/
require_once 'routes/route_pays.php';
require_once 'routes/route_photo_panneau.php';
require_once 'routes/route_raison_signalement.php';
require_once 'routes/route_region.php';
require_once 'routes/route_role_utilisateur.php';
require_once 'routes/route_superadmin.php';
require_once 'routes/route_login_register_superadmin.php';
require_once 'routes/route_utilisateur.php';
require_once 'routes/route_login_register_utilisateur.php';
require_once 'routes/route_ville.php';
require_once 'routes/route_zone_affichage.php';
require_once 'routes/route_login_register.php';

$app->run();