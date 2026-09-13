-- Add full course outline support to courses
-- Safe for reruns: checks information_schema before altering

SET @db_name = DATABASE();

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'courses'
      AND COLUMN_NAME = 'course_outline'
);

SET @ddl = IF(
    @column_exists = 0,
    'ALTER TABLE courses ADD COLUMN course_outline LONGTEXT NULL AFTER description',
    'SELECT "course_outline already exists"'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
