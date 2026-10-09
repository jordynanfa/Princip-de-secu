-- ============================================================
--  faille1_web_sqli.sql  —  CTF Web #1 (SQLi basique)  — v2
--  À appliquer SUR la base richpath :
--      sudo mysql richpath < faille1_web_sqli.sql
--
--  Change par rapport à la v1 :
--   - flag au format RP{Arnaque_XXXX}
--   - le flag est noyé dans une table "app_config" d'apparence réaliste
--     => le joueur doit ÉNUMÉRER puis DUMPER la table et RECONNAÎTRE le
--        flag à son format RP{...}. Il n'a pas besoin (et ne peut pas)
--        deviner d'avance le nom exact de la ligne.
--   - deux fausses pistes glissées dans le dump (rabbit holes).
-- ============================================================

USE richpath;

-- Ancienne table de la v1 : on la supprime (plus de table "secrets").
DROP TABLE IF EXISTS secrets;

-- ------------------------------------------------------------
-- Codes promo (cible de l'injection, inchangé)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS promos;
CREATE TABLE promos (
    code        VARCHAR(50) PRIMARY KEY,
    reduction   INT NOT NULL,
    description VARCHAR(150) NOT NULL
);

INSERT INTO promos (code, reduction, description) VALUES
('BIENVENUE10', 10, 'Offre de bienvenue pour les nouveaux membres'),
('RICH2024',    20, 'Promo de lancement RichPath'),
('PARRAIN15',   15, 'Code parrainage : 15 % sur ta prochaine formation');

-- ------------------------------------------------------------
-- Configuration applicative : le flag est UNE ligne parmi d'autres,
-- d'apparence banale. Le joueur ne peut pas cibler "WHERE cle='flag'"
-- sans avoir d'abord tout dumpé : il découvre le flag à son format.
-- ------------------------------------------------------------
DROP TABLE IF EXISTS app_config;
CREATE TABLE app_config (
    cle    VARCHAR(50)  PRIMARY KEY,
    valeur VARCHAR(200) NOT NULL
);

INSERT INTO app_config (cle, valeur) VALUES
('site_version',   '2.3.1'),
('maintenance',    'false'),
('promo_engine',   'active'),
('currency',       'EUR'),
-- >>> LE FLAG (format RP{Arnaque_XXXX}) <<<
('reward_token',   'RP{Arnaque_1135}'),
-- >>> FAUSSES PISTES (rabbit holes) : rien d'exploitable derrière <<<
('backup_path',    '/var/www/backup/clients_2024.sql'),   -- ce fichier n'existe pas
('legacy_account', 'superadmin');                          -- ce compte n'existe pas
