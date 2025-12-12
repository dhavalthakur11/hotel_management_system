<?php
// Application Constants

// Application Settings
define('APP_NAME', 'Hotel Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/hotel_management_ignou/app/public');

// Session Settings
define('SESSION_TIMEOUT', 86400); // 24 hours in seconds

// User Roles (UPPERCASE for consistency)
define('ROLE_ADMIN', 'ADMIN');
define('ROLE_RECEPTIONIST', 'RECEPTIONIST');
define('ROLE_CUSTOMER', 'CUSTOMER');

// Room Status
define('ROOM_AVAILABLE', 'available');
define('ROOM_OCCUPIED', 'occupied');
define('ROOM_MAINTENANCE', 'maintenance');
define('ROOM_RESERVED', 'reserved');

// Booking Status
define('BOOKING_PENDING', 'pending');
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_CHECKED_IN', 'checked_in');
define('BOOKING_CHECKED_OUT', 'checked_out');
define('BOOKING_CANCELLED', 'cancelled');

// Payment Status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PAID', 'paid');
define('PAYMENT_PARTIAL', 'partial');
define('PAYMENT_REFUNDED', 'refunded');

// Payment Methods
define('PAYMENT_CASH', 'cash');
define('PAYMENT_CARD', 'card');
define('PAYMENT_UPI', 'upi');
define('PAYMENT_NET_BANKING', 'net_banking');

// Notification Types
define('NOTIF_BOOKING_CONFIRMATION', 'booking_confirmation');
define('NOTIF_CHECK_IN_REMINDER', 'check_in_reminder');
define('NOTIF_CHECK_OUT_REMINDER', 'check_out_reminder');
define('NOTIF_PAYMENT_SUCCESS', 'payment_success');

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd-m-Y');

// Pagination
define('RECORDS_PER_PAGE', 10);

// File Upload
define('UPLOAD_PATH', __DIR__ . '/../../public/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Email Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-password');
define('FROM_EMAIL', 'noreply@hotelmanagement.com');
define('FROM_NAME', 'Hotel Management System');

// SMS Settings
define('SMS_API_KEY', 'your-sms-api-key');
define('SMS_SENDER_ID', 'HOTEL');

// Tax Settings
define('GST_RATE', 0.18); // 18%
define('SERVICE_CHARGE', 0.10); // 10%

// Audit Actions (UPPERCASE)
define('ACTION_CREATE', 'CREATE');
define('ACTION_UPDATE', 'UPDATE');
define('ACTION_DELETE', 'DELETE');
define('ACTION_LOGIN', 'LOGIN');
define('ACTION_LOGOUT', 'LOGOUT');
define('ACTION_VIEW', 'VIEW');

// Error Messages
define('ERR_INVALID_CREDENTIALS', 'Invalid username or password');
define('ERR_UNAUTHORIZED', 'You are not authorized to access this resource');
define('ERR_SESSION_EXPIRED', 'Your session has expired. Please login again');
define('ERR_ROOM_NOT_AVAILABLE', 'Room is not available for selected dates');
define('ERR_INVALID_INPUT', 'Invalid input provided');

// Success Messages
define('MSG_LOGIN_SUCCESS', 'Login successful');
define('MSG_LOGOUT_SUCCESS', 'Logout successful');
define('MSG_BOOKING_SUCCESS', 'Booking created successfully');
define('MSG_SAVE_SUCCESS', 'Data saved successfully');
define('MSG_UPDATE_SUCCESS', 'Data updated successfully');
define('MSG_DELETE_SUCCESS', 'Data deleted successfully');

// Info Messages
define('MSG_PROCESSING', 'Processing your request...');
define('MSG_LOADING', 'Loading...');

// Database
define('DB_HOST', '10.147.17.170');
define('DB_PORT', '1521');
define('DB_SERVICE', 'orclpdb');
define('DB_USER', 'system');
define('DB_PASS', 'dhaval123');

// Timezone
define('APP_TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(APP_TIMEZONE);
?>