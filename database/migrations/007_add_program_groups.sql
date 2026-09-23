-- Adds optional "program group" tagging so the admin can feature a category
-- or an individual course under one of three public homepage/menu groupings:
-- Role Based Programs, Technical Courses, Business Programs.
-- Assignment is optional at both the category and course level; a course
-- can override its category's grouping, or opt out entirely (NULL = hidden
-- from the grouped menu, course still appears normally everywhere else).

ALTER TABLE categories
    ADD COLUMN program_group ENUM('role_based', 'technical', 'business') NULL DEFAULT NULL AFTER slug;

ALTER TABLE courses
    ADD COLUMN program_group ENUM('role_based', 'technical', 'business') NULL DEFAULT NULL AFTER is_featured;
