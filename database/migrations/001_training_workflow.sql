-- Apply once to an existing ICTECH database.
-- Fresh installations should use database/schema.sql instead.

ALTER TABLE users
    MODIFY role ENUM('student', 'trainer', 'admin') DEFAULT 'student';

ALTER TABLE enrollments
    ADD COLUMN trainer_id INT NULL AFTER status,
    ADD COLUMN approved_by INT NULL AFTER trainer_id,
    ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by,
    ADD COLUMN progress TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER approved_at,
    ADD COLUMN student_completed_at TIMESTAMP NULL AFTER progress,
    ADD COLUMN trainer_approved_at TIMESTAMP NULL AFTER student_completed_at,
    ADD COLUMN admin_approved_at TIMESTAMP NULL AFTER trainer_approved_at,
    ADD CONSTRAINT fk_enrollment_trainer FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_enrollment_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;

CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT NOT NULL UNIQUE,
    certificate_number VARCHAR(64) NOT NULL UNIQUE,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reset_user (user_id),
    INDEX idx_reset_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
