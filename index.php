<?php
/**
 * index.php : page d'accueil. Vitrine vivante avec hero, arguments,
 * aperçu des formations et de nombreux commentaires clients (depuis la BD).
 */

require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/functions.php";

// Quelques formations à mettre en avant + tous les avis.
$formations   = $pdo->query("SELECT * FROM formations ORDER BY prix LIMIT 3")->fetchAll();
$commentaires = $pdo->query("SELECT * FROM commentaires ORDER BY id")->fetchAll();

$pageTitle = "RichPath - Gagne 500k par mois depuis chez toi";
include __DIR__ . "/includes/header.php";

// Petite aide pour afficher des étoiles selon la note.
function etoiles(int $n): string {
    return str_repeat("★", $n) . str_repeat("☆", 5 - $n);
}
?>

<section class="hero">
    <h1>Gagne <span class="highlight">500 000 € / mois</span> depuis ton canapé 🚀</h1>
    <p class="subtitle">
        Rejoins les milliers d'entrepreneurs qui ont changé de vie grâce à nos méthodes.
        Pas de diplôme. Pas d'expérience. Juste de la motivation.
    </p>
    <a href="/register.php" class="btn btn-big">Je veux devenir riche →</a>
</section>

<section class="features">
    <div class="feature-card">
        <h3>⚡ Résultats rapides</h3>
        <p>Nos élèves voient leurs premiers revenus en moins de 7 jours.</p>
    </div>
    <div class="feature-card">
        <h3>📈 Méthode éprouvée</h3>
        <p>Un système testé sur plus de 10 000 personnes à travers le monde.</p>
    </div>
    <div class="feature-card">
        <h3>🔥 Accès à vie</h3>
        <p>Paye une fois, profite de tout le contenu pour toujours.</p>
    </div>
</section>

<!-- Aperçu des formations -->
<section>
    <h2 class="section-title">Nos packs les plus populaires</h2>
    <div class="formations-grid">
        <?php foreach ($formations as $f): ?>
            <div class="formation-card">
                <img src="/img/<?= h($f['image']) ?>" alt="" class="formation-img">
                <h3><?= h($f['titre']) ?></h3>
                <p class="prix"><?= number_format((float)$f['prix'], 2, ',', ' ') ?> €</p>
                <p class="desc"><?= h($f['description']) ?></p>
                <a href="/formations.php" class="btn">En savoir plus</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Beaucoup de commentaires pour rendre le site vivant -->
<section class="testimonials">
    <h2 class="section-title">Ils ont changé de vie grâce à RichPath</h2>
    <div class="avis-grid">
        <?php foreach ($commentaires as $c): ?>
            <div class="avis-card">
                <div class="avis-stars"><?= etoiles((int)$c['note']) ?></div>
                <p class="avis-texte">« <?= h($c['texte']) ?> »</p>
                <span class="avis-auteur">— <?= h($c['auteur']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="cta">
    <h2>Prêt à changer de vie ?</h2>
    <a href="/register.php" class="btn btn-big">Créer mon compte gratuitement</a>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
