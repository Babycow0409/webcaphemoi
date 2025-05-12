-- Add active column to users table if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS active TINYINT(1) DEFAULT 1; 