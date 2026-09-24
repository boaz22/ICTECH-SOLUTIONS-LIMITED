-- Allow schedule entries to optionally link to the existing courses catalogue.
ALTER TABLE training_schedule
    ADD COLUMN course_id INT NULL AFTER month_start,
    ADD INDEX idx_schedule_course (course_id),
    ADD CONSTRAINT fk_schedule_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL;
