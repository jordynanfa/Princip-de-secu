<?php
/**
 * functions.php : fonctions utilitaires partagées par tout le site.
 *
 *  - Démarrage de session (pour gérer les connexions)
 *  - Validation de l'identifiant et du mot de passe
 *  - Authentification (login / logout)
 *  - Journalisation encodée des activités, avec rotation par jour
 *  - Petites aides pour le panier et l'affichage
 */

// On démarre la session une seule fois, au tout début.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
 *  RÈGLES DE VALIDATION
 * ========================================================= */

/**
 * Règle de l'IDENTIFIANT (login client) :
 *   - longueur maximale : 6 caractères
 *   - au moins 2 chiffres
 *   - uniquement lettres et chiffres (pas d'espace ni symbole)
 *
 * Renvoie true si l'id est valide, false sinon.
 */
function login_valide(string $login): bool
{
    // Longueur 3 à 6 caractères (le max imposé est 6).
    if (strlen($login) < 3 || strlen($login) > 6) {
        return false;
    }
    // Uniquement des caractères alphanumériques.
    if (!preg_match('/^[A-Za-z0-9]+$/', $login)) {
        return false;
    }
    // Compter les chiffres : il en faut au moins 2.
    $nb_chiffres = preg_match_all('/[0-9]/', $login);
    return $nb_chiffres >= 2;
}

/**
 * Règle du MOT DE PASSE :
 *   - au moins 8 caractères
 *   - au moins 1 majuscule
 *   - au moins 1 chiffre
 */
function mot_de_passe_valide(string $mdp): bool
{
    if (strlen($mdp) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $mdp)) {   // une majuscule
        return false;
    }
    if (!preg_match('/[0-9]/', $mdp)) {   // un chiffre
        return false;
    }
    return true;
}

/* =========================================================
 *  AUTHENTIFICATION
 * ========================================================= */

/**
 * Tente de connecter quelqu'un avec un login + mot de passe.
 * On cherche d'abord dans les clients, puis dans les comptes système.
 * Renvoie true si la connexion réussit.
 */
function tenter_connexion(PDO $pdo, string $login, string $mdp): bool
{
    // 1) Chercher parmi les clients inscrits.
    $stmt = $pdo->prepare("SELECT password FROM clients WHERE login = ?");
    $stmt->execute([$login]);
    $ligne = $stmt->fetch();
    $role  = "client";

    // 2) Sinon, chercher parmi les comptes système (admin / userdb...).
    if (!$ligne) {
        $stmt = $pdo->prepare("SELECT password, role FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $ligne = $stmt->fetch();
        $role  = $ligne['role'] ?? null;
    }

    // Aucun compte trouvé.
    if (!$ligne) {
        journaliser("LOGIN_FAIL", $login);
        return false;
    }

    // Vérifier le mot de passe contre l'empreinte stockée.
    if (password_verify($mdp, $ligne['password'])) {
        $_SESSION['login'] = $login;
        $_SESSION['role']  = $role;
        journaliser("LOGIN_OK", $login);
        return true;
    }

    journaliser("LOGIN_FAIL", $login);
    return false;
}

/** Déconnecte l'utilisateur courant. */
function deconnexion(): void
{
    if (est_connecte()) {
        journaliser("LOGOUT", $_SESSION['login']);
    }
    session_unset();
    session_destroy();
}

/** Vrai si un utilisateur est connecté. */
function est_connecte(): bool
{
    return isset($_SESSION['login']);
}

/** Renvoie le login courant, ou null. */
function utilisateur_courant(): ?string
{
    return $_SESSION['login'] ?? null;
}

/* =========================================================
 *  JOURNALISATION (LOGS) ENCODÉE + ROTATION PAR JOUR
 * ========================================================= */

/**
 * Écrit une ligne dans le journal d'activité.
 *
 *  - Chaque ligne est ENCODÉE en base64 (les infos ne sont pas en clair).
 *  - Un fichier DIFFÉRENT est créé chaque jour (rotation) :
 *        logs/activite-AAAA-MM-JJ.log
 *    => on n'a jamais un seul fichier qui grossit sans fin.
 *
 * @param string $action  type d'évènement (LOGIN_OK, LOGOUT, REGISTER...)
 * @param string $qui     login concerné
 * @param string $detail  information complémentaire (optionnelle)
 */
function journaliser(string $action, string $qui, string $detail = ""): void
{
    $dossier = __DIR__ . "/../logs";
    if (!is_dir($dossier)) {
        @mkdir($dossier, 0755, true);
    }

    // Nom du fichier du jour (rotation quotidienne).
    $fichier = $dossier . "/activite-" . date("Y-m-d") . ".log";

    // Ligne lisible : date, action, qui, ip, détail.
    $ip    = $_SERVER['REMOTE_ADDR'] ?? "local";
    $ligne = sprintf("[%s] %s | user=%s | ip=%s | %s",
        date("Y-m-d H:i:s"), $action, $qui, $ip, $detail);

    // On encode la ligne (base64) avant de l'écrire.
    $ligne_encodee = base64_encode($ligne) . PHP_EOL;

    // Écriture en fin de fichier (append), avec verrou.
    @file_put_contents($fichier, $ligne_encodee, FILE_APPEND | LOCK_EX);
}

/* =========================================================
 *  PANIER (stocké en session)
 * ========================================================= */

/** Ajoute une formation au panier de la session. */
function panier_ajouter(int $formation_id): void
{
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    if (!in_array($formation_id, $_SESSION['panier'])) {
        $_SESSION['panier'][] = $formation_id;
    }
}

/** Retire une formation du panier. */
function panier_retirer(int $formation_id): void
{
    if (isset($_SESSION['panier'])) {
        $_SESSION['panier'] = array_values(
            array_filter($_SESSION['panier'], fn($id) => $id !== $formation_id)
        );
    }
}

/** Renvoie la liste des id de formations dans le panier. */
function panier_contenu(): array
{
    return $_SESSION['panier'] ?? [];
}

/** Vide le panier. */
function panier_vider(): void
{
    $_SESSION['panier'] = [];
}

/* =========================================================
 *  AFFICHAGE SÛR
 * ========================================================= */

/** Échappe le texte avant affichage (évite les soucis d'affichage HTML). */
function h(?string $texte): string
{
    return htmlspecialchars($texte ?? "", ENT_QUOTES, "UTF-8");
}
