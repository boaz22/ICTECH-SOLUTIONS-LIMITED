-- Remove online payment gateway support.
-- The platform no longer processes online payments (M-Pesa/card); enrollment
-- is created directly by an admin once a training agreement is reached.
DROP TABLE IF EXISTS payments;