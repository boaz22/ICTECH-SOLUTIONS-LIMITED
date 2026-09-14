-- One-off data sync: brings in records that existed in a later production
-- dump (ictech_solutions_limited (2).sql, generated 2026-09-11 14:37 UTC)
-- but were missing from this local database. Foreign keys are remapped by
-- natural key (email / slug) since auto-increment ids diverged between the
-- two copies. All dump timestamps are UTC and are shifted +3h here to match
-- this database's existing +03:00 local-time convention.

-- 1. Trainer account created on the newer copy, missing here
INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
SELECT 'Kevin Abuta', 'kevitech0@gmail.com', '+254710731146',
       '$2y$10$OwxJ8mLnZYjn3fNRtUx9BeAlx15cCcrViLniQFq47UW.dKHuYGlQK',
       'trainer', 'active', '2026-09-11 09:53:38', '2026-09-11 09:53:38'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'kevitech0@gmail.com');

-- 2. Two courses added on the newer copy, missing here (category_id 1 = Web Development in both)
INSERT INTO courses (id, title, slug, description, objectives, requirements, category_id, price, duration, image, status, is_featured, created_at, updated_at)
SELECT 7, 'Test Course QA Check', 'test-course-qa-check',
       'A QA test course description to verify the admin add-course form works.',
       '', '', 1, 1000.00, '4 weeks', NULL, 'archived', 0,
       '2026-09-11 12:43:03', '2026-09-11 12:43:21'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE slug = 'test-course-qa-check');

INSERT INTO courses (id, title, slug, description, objectives, requirements, category_id, price, duration, image, status, is_featured, created_at, updated_at)
SELECT 8, 'Full stack web development', 'full-stack-web-development',
       'Grocient is indeed the best and leading website development company in Jaipur. We use smart solutions to provide a working website for your business. Having experience with over 100 different industries. We can design an industry-specific website for you and help take your business to new heights. Website :- https://www.grocient.com/website-development-company-in-jaipur/',
       'develop websites', 'laptop', 1, 10000.00, '8 weeks', 'course-a90e27c7ace8e6f1.jpg', 'published', 0,
       '2026-09-11 12:53:17', '2026-09-11 12:53:17'
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE slug = 'full-stack-web-development');

-- 3. site_settings table did not exist locally at all
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(100) NOT NULL,
  setting_value VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
('company_address', 'Nairobi, Kenya'),
('company_email', 'info@ictech.co.ke'),
('company_phone', '+254 700 000 000'),
('courses_offered', '20+'),
('satisfaction_rate', '95%'),
('students_trained', '500+');

-- 4. The abutakevin254@gmail.com / PHP Web Development enrollment progressed
--    to completed (with trainer approval) on the newer copy; bring that
--    state into the existing local row (same user_id=9, course_id=1 pair).
UPDATE enrollments e
JOIN users u ON u.email = 'abutakevin254@gmail.com'
JOIN users t ON t.email = 'kevitech0@gmail.com'
SET e.status = 'completed',
    e.trainer_id = t.id,
    e.approved_at = '2026-09-08 12:29:00',
    e.progress = 100,
    e.enrolled_at = '2026-09-08 13:26:27',
    e.updated_at = '2026-09-11 12:58:54'
WHERE e.user_id = u.id AND e.course_id = 1;

-- 5. Certificate issued for that completed enrollment, missing here
INSERT INTO certificates (id, enrollment_id, certificate_number, issued_at)
SELECT 1, e.id, 'ICTECH-2026-000014', '2026-09-11 13:03:15'
FROM enrollments e
JOIN users u ON u.email = 'abutakevin254@gmail.com'
WHERE e.user_id = u.id AND e.course_id = 1
  AND NOT EXISTS (SELECT 1 FROM certificates WHERE certificate_number = 'ICTECH-2026-000014');
