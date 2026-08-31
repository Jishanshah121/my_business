-- Creates the read-only user the Python AI service uses.
-- Grants are SELECT only, on catalog tables and the analytics views. No INSERT,
-- UPDATE or DELETE grant exists anywhere, so the boundary between the AI
-- service and the source of truth is enforced by MySQL, not by discipline.
--
-- Table-level grants are applied by `php bin/console db:grants` AFTER the
-- schema exists; this file only creates the account.
CREATE USER IF NOT EXISTS 'supplykaro_ai'@'%' IDENTIFIED BY 'ai_readonly_dev';
