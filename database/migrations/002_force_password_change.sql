-- Adds a flag so users whose password was set/reset directly by an admin
-- (new trainer accounts, admin "Reset Password" action) are forced to
-- choose their own new password on next login.
ALTER TABLE users
    ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER status;
