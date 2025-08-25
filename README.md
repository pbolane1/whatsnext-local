# WhatsNext Real Estate Application

A comprehensive real estate transaction management system built with PHP.

## Setup Instructions

### 1. Configuration
Before running the application, you need to configure the following in `include/common.php`:

- **Stripe API Keys**: Replace `YOUR_STRIPE_PUBLIC_KEY_HERE` and `YOUR_STRIPE_PRIVATE_KEY_HERE`
- **Twilio Credentials**: Replace `YOUR_TWILLIO_SID_HERE`, `YOUR_TWILLIO_KEY_HERE`, and `YOUR_TWILLIO_NUMBER_HERE`
- **Database Connection**: Replace `YOUR_DB_USER`, `YOUR_DB_PASSWORD`, `YOUR_DB_NAME_DEV`, and `YOUR_DB_NAME`

### 2. Database Setup
Import the SQL files from the `sql/` directory to set up your database.

### 3. File Permissions
Ensure the following directories are writable:
- `dynamic/images/`
- `dynamic/files/`
- `uploads/`
- `temp/`

### 4. Dependencies
The application uses:
- PHP 7.4+
- MySQL/MariaDB
- Apache/Nginx
- Stripe API
- Twilio API

## Features
- User management system
- Agent and coordinator portals
- Timeline management
- File uploads
- Email notifications
- SMS integration
- Payment processing

## Security Notes
- This repository contains template configuration files
- Never commit actual API keys or database credentials
- Use environment variables for production deployments
