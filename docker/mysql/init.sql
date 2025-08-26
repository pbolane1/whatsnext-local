-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS whatsnext_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant permissions to the user
GRANT ALL PRIVILEGES ON whatsnext_dev.* TO 'whatsnext_user'@'%';
FLUSH PRIVILEGES;

-- Use the database
USE whatsnext_dev;

