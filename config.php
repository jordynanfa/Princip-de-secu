<?php
/**
 * config.php : connexion à la base de données MariaDB (via PDO).
 *
 * On centralise ici les paramètres de connexion pour ne pas les répéter
 * dans chaque page. Toutes les pages qui ont besoin de la base font :
 *     require_once __DIR__ . "/includes/config.php";
 * et utilisent ensuite la variable $pdo.
 *
 * PDO + requêtes préparées = protection naturelle contre l'injection SQL.
 */

// ---- Paramètres de connexion (à adapter si besoin) ----
$DB_HOST = "localhost";
$DB_NAME = "richpath";
$DB_USER = "richpath_web";      // utilisateur MariaDB dédié au site
$DB_PASS = "Arnaque2026";    // mot de passe de cet utilisateur

// DSN = chaîne de connexion PDO.
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

// Options PDO : erreurs en exceptions, pas d'émulation des requêtes préparées.
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // $pdo est l'objet qu'on réutilise partout pour parler à la base.
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // En cas d'échec, on arrête proprement avec un message générique.
    http_response_code(500);
    exit("Erreur : impossible de se connecter à la base de données.");
}
