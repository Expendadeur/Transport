-- ============================================================
-- Migration: Rename 'utilisateur' to 'agent', add 'chauffeur'
-- Run this in phpMyAdmin or via MySQL CLI
-- ============================================================

-- 1. Update existing 'utilisateur' rows to 'agent'
UPDATE utilisateur SET role = 'agent' WHERE role = 'utilisateur';

-- 2. Add email/password to chauffeur table for authentication
ALTER TABLE chauffeur 
    ADD COLUMN IF NOT EXISTS email VARCHAR(150) UNIQUE,
    ADD COLUMN IF NOT EXISTS password VARCHAR(255),
    ADD COLUMN IF NOT EXISTS statut ENUM('actif','inactif') DEFAULT 'actif';

-- 3. Update the role ENUM on utilisateur table
ALTER TABLE utilisateur 
    MODIFY COLUMN role ENUM('admin','gestionnaire','agent','chauffeur') DEFAULT 'agent';

-- Note: Chauffeurs will authenticate via the chauffeur table directly,
-- not through the utilisateur table. The login.php handles both cases.
