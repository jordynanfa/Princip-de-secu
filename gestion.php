<?php
/**
 * gestion.php — Panneau d'administration (clients + import d'images).
 *
 *  ⚠️ VERSION VOLONTAIREMENT VULNÉRABLE (CTF)
 *  -----------------------------------------------------------------
 *  Faille Web #2 : contrôle d'accès cassé (confiance au cookie `role`).
 *  Faille Web #3 (PALIER B) : l'upload vérifie que le fichier est une
 *      VRAIE image avec getimagesize() — mais garde le nom/extension
 *      d'origine. Un fichier à la fois image valide ET PHP, nommé .php
 *      (polyglotte), passe le contrôle puis s'exécute => RCE.
 */

require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/functions.php";

$FLAG2 = "RP{Arnaque_8384}";

// 1) Être connecté.
if (!est_connecte()) {
    header("Location: /login.php");
    exit;
}

// 2) ⚠️ Contrôle d'accès cassé : confiance au cookie `role` (faille #2).
$estAdmin = (($_COOKIE['role'] ?? '') === 'admin');

// 3) ⚠️ Upload (faille #3, palier B) — réservé aux "admins".
$msgUpload = "";
if ($estAdmin && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['document'])) {
    $f = $_FILES['document'];

    // Contrôle "fort" : on vérifie le CONTENU réel (vraie image ?).
    // Falsifier le Content-Type ne suffit plus ; il faut un vrai en-tête d'image.
    $info = @getimagesize($f['tmp_name']);

    if ($info !== false) {
        $dossier = __DIR__ . "/uploads";
        if (!is_dir($dossier)) { @mkdir($dossier, 0755, true); }

        // ⚠️ On conserve le nom (et l'extension) d'origine -> .php accepté.
        $dest = $dossier . "/" . basename($f['name']);
        if (move_uploaded_file($f['tmp_name'], $dest)) {
            $msgUpload = "✅ Image importée : /uploads/" . basename($f['name']);
            journaliser("UPLOAD", utilisateur_courant() ?? "admin", basename($f['name']));
        } else {
            $msgUpload = "❌ Échec de l'enregistrement du fichier.";
        }
    } else {
        $msgUpload = "❌ Le fichier n'est pas une image valide.";
    }
}

$pageTitle = "Administration - RichPath";
include __DIR__ . "/includes/header.php";
?>

<section class="page-content">
<?php if (!$estAdmin): ?>

    <h1>🔒 Accès réservé aux administrateurs</h1>
    <p>Votre compte n'a pas les privilèges nécessaires pour consulter cette page.</p>

<?php else: ?>

    <h1>🛠️ Panneau d'administration</h1>
    <div class="recap-box">
        <p><span class="recap-label">Jeton de supervision :</span>
           <span class="recap-value"><?= h($FLAG2) ?></span></p>
    </div>

    <h2>📎 Importer une image</h2>
    <p class="small">Logo, visuel de campagne… (images uniquement — contrôle du contenu)</p>
    <?php if ($msgUpload !== ""): ?>
        <div class="notice"><?= $msgUpload ?></div>
    <?php endif; ?>
    <form method="post" action="/gestion.php" enctype="multipart/form-data" class="form-vertical">
        <input type="file" name="document">
        <button type="submit" class="btn">Importer</button>
    </form>

    <h2>Liste des clients (prospects)</h2>
    <?php
    $clients = $pdo->query("SELECT login, nom, prenom, mail, tel, ville FROM clients ORDER BY date_creation DESC")->fetchAll();
    ?>
    <table class="infos-table" style="width:100%; border-collapse:collapse;">
        <tr>
            <th style="text-align:left;">Login</th>
            <th style="text-align:left;">Nom</th>
            <th style="text-align:left;">Prénom</th>
            <th style="text-align:left;">E-mail</th>
            <th style="text-align:left;">Téléphone</th>
            <th style="text-align:left;">Ville</th>
        </tr>
        <?php foreach ($clients as $c): ?>
            <tr>
                <td><?= h($c['login']) ?></td>
                <td><?= h($c['nom']) ?></td>
                <td><?= h($c['prenom']) ?></td>
                <td><?= h($c['mail']) ?></td>
                <td><?= h($c['tel']) ?></td>
                <td><?= h($c['ville']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($clients)): ?>
            <tr><td colspan="6">Aucun client inscrit pour le moment.</td></tr>
        <?php endif; ?>
    </table>

<?php endif; ?>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
