<?php
/**
 * Email Configuration File
 * Create this file in your root directory
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com'); // For Gmail
define('SMTP_PORT', 587); // or 465 for SSL
define('SMTP_USERNAME', 'novacore.mailer@gmail.com'); // Your email
define('SMTP_PASSWORD', 'yjwc zsaa jltv vekq'); // Gmail App Password (not regular password)
define('SMTP_ENCRYPTION', 'tls'); // or 'ssl'

// Sender Information
define('MAIL_FROM_EMAIL', 'novacore.mailer@gmail.com');
define('MAIL_FROM_NAME', 'NovaCore');

// Email Settings
define('MAIL_REPLY_TO', 'support@yourdomain.com');
define('MAIL_REPLY_TO_NAME', 'Customer Support');

/**
 * Instructions for Gmail:
 * 1. Go to your Google Account settings
 * 2. Enable 2-Step Verification
 * 3. Go to Security > App passwords
 * 4. Generate an app password for "Mail"
 * 5. Use that 16-character password as SMTP_PASSWORD
 * 
 * For other email providers:
 * - Outlook: smtp.office365.com, port 587
 * - Yahoo: smtp.mail.yahoo.com, port 587
 * - Custom domain: Check your hosting provider's SMTP settings
 */