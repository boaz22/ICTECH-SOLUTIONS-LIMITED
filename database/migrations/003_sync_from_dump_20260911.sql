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

-- 6. M-Pesa retry history for that same enrollment attempt, missing here
--    (local payment id=2 is an unrelated earlier attempt from 2026-09-05, kept as-is)
INSERT INTO payments (id, user_id, enrollment_id, amount, method, status, reference, checkout_request_id, merchant_request_id, created_at, updated_at)
SELECT v.id, u.id, v.enrollment_id, v.amount, 'mpesa', v.status, v.reference, v.checkout_request_id, v.merchant_request_id, v.created_at, v.updated_at
FROM (
    SELECT 3  AS id, NULL AS enrollment_id, 4500.00 AS amount, 'pending' AS status, 'ICTECH-1' AS reference, 'ws_0D00DB509523' AS checkout_request_id, 'mr_3519995C7D61' AS merchant_request_id, '2026-09-07 14:55:07' AS created_at, '2026-09-07 14:55:07' AS updated_at
    UNION ALL SELECT 4,  NULL, 4500.00, 'pending', 'ICTECH-1', 'ws_1121C76FBBD7', 'mr_BFB4970C88B9', '2026-09-07 14:55:47', '2026-09-07 14:55:47'
    UNION ALL SELECT 5,  NULL, 3500.00, 'failed',  'ICTECH-2', 'ws_E3393CAE8203', 'mr_78B298484A14', '2026-09-07 15:16:12', '2026-09-07 15:16:13'
    UNION ALL SELECT 6,  NULL, 3500.00, 'failed',  'ICTECH-2', 'ws_6F9AC99A3467', 'mr_2EC3E9D099E3', '2026-09-07 15:17:49', '2026-09-07 15:17:51'
    UNION ALL SELECT 7,  NULL, 3500.00, 'pending', 'ICTECH-2', 'ws_B15FCAC6240F', 'mr_8DD8FE69B60C', '2026-09-07 15:27:08', '2026-09-07 15:27:08'
    UNION ALL SELECT 8,  NULL, 1.00,    'pending', 'ICTECH-1', 'ws_13020F08A359', 'mr_220D3507C923', '2026-09-07 15:32:45', '2026-09-07 15:32:45'
    UNION ALL SELECT 9,  NULL, 1.00,    'paid',    'ICTECH-1', 'ws_CO_070920261537444758819156', '26c8-4725-862c-fe8026d25cb6151592', '2026-09-07 15:37:40', '2026-09-07 15:40:37'
    UNION ALL SELECT 10, NULL, 1.00,    'paid',    'ICTECH-1', 'ws_CO_070920261542130758819156', '26c8-4725-862c-fe8026d25cb6151938', '2026-09-07 15:42:11', '2026-09-07 15:42:20'
    UNION ALL SELECT 11, NULL, 1.00,    'paid',    'ICTECH-1', 'ws_CO_070920261553050758819156', 'ac7b-470b-8df7-23ab3cef3827162187', '2026-09-07 15:53:02', '2026-09-07 15:53:11'
    UNION ALL SELECT 12, 8,    1.00,    'pending', 'ICTECH-1', 'ws_CO_080920261326300758819156', 'acb1-49f3-a5a1-a6a35275487889432', '2026-09-08 13:26:27', '2026-09-08 13:26:29'
    UNION ALL SELECT 13, 8,    1.00,    'failed',  'ICTECH-1', 'ws_81DEF744305B', 'mr_625B671DEAC4', '2026-09-08 13:27:18', '2026-09-08 13:27:18'
    UNION ALL SELECT 14, 8,    1.00,    'failed',  'ICTECH-1', 'ws_52A87B7AEAE0', 'mr_5BF6ACB11289', '2026-09-08 13:27:22', '2026-09-08 13:27:23'
    UNION ALL SELECT 15, 8,    1.00,    'failed',  'ICTECH-1', 'ws_35EB597597F2', 'mr_ED40E2BC3173', '2026-09-08 13:27:27', '2026-09-08 13:27:27'
    UNION ALL SELECT 16, 8,    1.00,    'failed',  'ICTECH-1', 'ws_DE4B4A34DA66', 'mr_5F167D58E90A', '2026-09-08 13:27:36', '2026-09-08 13:27:36'
    UNION ALL SELECT 17, 8,    1.00,    'failed',  'ICTECH-1', 'ws_0F66D3744311', 'mr_9AB376EFC91B', '2026-09-08 13:27:39', '2026-09-08 13:27:39'
    UNION ALL SELECT 18, 8,    1.00,    'failed',  'ICTECH-1', 'ws_BB7F257D7FAE', 'mr_EB8FF51968AB', '2026-09-08 13:27:41', '2026-09-08 13:27:41'
    UNION ALL SELECT 19, 8,    1.00,    'paid',    'ICTECH-1', 'ws_CO_080920261328546758819156', '4e7d-425c-aa3e-9b62f1dcf8a2187442', '2026-09-08 13:28:52', '2026-09-08 13:29:00'
) v
JOIN users u ON u.email = 'abutakevin254@gmail.com'
WHERE NOT EXISTS (SELECT 1 FROM payments p WHERE p.id = v.id);
