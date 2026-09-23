-- Adds an optional short course code (e.g. "WD101") and a free-text
-- subcategory label to courses, so course search can match on either in
-- addition to title, category, and homepage grouping.

ALTER TABLE courses
    ADD COLUMN course_code VARCHAR(50) NULL AFTER slug,
    ADD COLUMN subcategory VARCHAR(255) NULL AFTER category_id,
    ADD UNIQUE KEY unique_course_code (course_code);
