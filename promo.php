<?php

require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/functions.php";

$pageTitle = "Code promo - RichPath";
$resultat  = null;   // ligne(s) trouvée(s)
$erreur    = null;   // message d'erreur SQL éventuel (indice)
$codeSaisi = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codeSaisi = $_POST["code"] ?? "";

    // ---- LIGNE VULNÉRABLE : concaténation directe dans la requête ----
    $sql = "SELECT code, reduction, description FROM promos WHERE code = '" . $codeSaisi . "'";

    try {
        // On exécute la requête brute (query), sans paramètre lié.
        $stmt = $pdo->query($sql);
        $resultat = $stmt->fetchAll();
        journaliser("PROMO_CHECK", utilisateur_courant() ?? "anon", "code=" . $codeSaisi);
    } catch (PDOException $e) {
        // On affiche l'erreur SQL : c'est l'indice volontaire.
        $erreur = $e->getMessage();
    }
}

include __DIR__ . "/includes/header.php";
?>

<section class="page-content">
    <h1>🎁 Vérifie ton code promo</h1>
    <p>Tu as un <strong>code de réduction</strong> ou un <strong>code parrainage</strong> ? Entre-le ici pour voir ton avantage.</p>

    <form method="post" action="/promo.php" class="form-vertical">
        <label for="code">Code promo / parrainage</label>
        <input type="text" id="code" name="code" value="<?= h($codeSaisi) ?>" placeholder="ex. BIENVENUE10">
        <button type="submit" class="btn">Vérifier</button>
    </form>

    <?php if ($erreur !== null): ?>
        <div class="erreur-box" style="margin-top:20px;">
            <p>⚠️ Erreur lors de la vérification :</p>
            <p style="font-family:monospace; font-size:13px;"><?= h($erreur) ?></p>
        </div>
    <?php elseif ($resultat !== null): ?>
        <?php if (count($resultat) > 0): ?>
            <div class="notice" style="margin-top:20px;">
                <?php foreach ($resultat as $r): ?>
                    <p>✅ <strong><?= h($r['code']) ?></strong> —
                       <?= h((string)$r['reduction']) ?> % —
                       <?= h($r['description']) ?></p>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="erreur-box" style="margin-top:20px;">
                <p>❌ Code promo invalide.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Nouveau : pensez à utiliser votre code parrainage pour cumuler les réductions ! -->
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
