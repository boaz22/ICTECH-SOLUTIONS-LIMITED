-- Remove the unused course price column now that all payment collection is
-- handled offline and no admin/public code reads or writes course pricing.
-- Safe for reruns: checks information_schema before altering.

SET @db_name = DATABASE();

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'courses'
      AND COLUMN_NAME = 'price'
);

SET @ddl = IF(
    @column_exists > 0,
    'ALTER TABLE courses DROP COLUMN price',
    'SELECT "price column already removed"'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
