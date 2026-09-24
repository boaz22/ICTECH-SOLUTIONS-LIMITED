-- Add structured date and delivery fields for schedule filtering and exports.
ALTER TABLE training_schedule
    ADD COLUMN start_date DATE NULL AFTER month_start,
    ADD COLUMN end_date DATE NULL AFTER start_date,
    ADD COLUMN delivery_mode VARCHAR(100) NULL AFTER venue,
    MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'available';

CREATE INDEX idx_schedule_dates ON training_schedule (start_date, end_date);
CREATE INDEX idx_schedule_delivery ON training_schedule (delivery_mode);
