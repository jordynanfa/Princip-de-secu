<?php
/**
 * login.php : page de connexion (login + mot de passe).
 * Plusieurs utilisateurs peuvent être connectés en même temps : chaque
 * navigateur a sa propre session, donc aucun conflit entre eux.
 */

require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/functions.php";

$pageTitle = "Connexion - RichPath";
$erreur    = null;

// Si déjà connecté, on va directement à l'espace perso.
if (est_connecte()) {
    header("Location: /account.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"] ?? "");
    $mdp   = $_POST["mdp"] ?? "";

    if ($login === "" || $mdp === "") {
        $erreur = "Merci de remplir les deux champs.";
    } elseif (tenter_connexion($pdo, $login, $mdp)) {
        // Connexion réussie -> espace perso.
        setcookie('role', $_SESSION['role'] ?? 'client', ['path' => '/']);
        header("Location: /account.php");
        exit;
    } else {
        $erreur = "Identifiant ou mot de passe incorrect.";
    }
}

include __DIR__ . "/includes/header.php";
?>

<section class="page-content">
    <h1>Connexion</h1>
    <p>Accédez à votre espace personnel.</p>

    <?php if ($erreur): ?>
        <div class="erreur-box"><p>⚠️ <?= h($erreur) ?></p></div>
    <?php endif; ?>

    <form method="post" action="/login.php" class="form-vertical">
        <label for="login">Identifiant</label>
        <input type="text" id="login" name="login">

        <label for="mdp">Mot de passe</label>
        <input type="password" id="mdp" name="mdp">

        <button type="submit" class="btn">Se connecter</button>
    </form>

    <p class="small" style="margin-top:20px;">
        Pas encore de compte ? <a href="/register.php">Créez-en un ici.</a>
    </p>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
