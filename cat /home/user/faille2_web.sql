-- ============================================================
--  faille2_web.sql  —  CTF Web #2 (Broken Access Control)
--  À appliquer sur la base richpath :
--      sudo mysql richpath < faille2_web.sql
--
--  Rôle : glisser dans app_config un INDICE RÉEL vers le panneau
--  d'administration. L'attaquant le découvre en dumpant app_config
--  pendant la faille #1 -> c'est le pont entre #1 et #2.
--
--  NB : le flag #2 n'est PAS en base (il est dans gestion.php),
--  pour qu'il ne soit pas exfiltré par l'injection SQL de la #1.
-- ============================================================

USE richpath;

-- On ajoute (ou on remet) l'indice vers le panneau admin.
INSERT INTO app_config (cle, valeur) VALUES
('admin_panel', '/gestion.php')
ON DUPLICATE KEY UPDATE valeur = VALUES(valeur);
