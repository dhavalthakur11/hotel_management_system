-- =====================================================
-- HOTEL MANAGEMENT SYSTEM - ORACLE DATABASE SCHEMA
-- Version: 1.0
-- Date: 2024
-- =====================================================

-- =====================================================
-- SECTION 1: DROP EXISTING OBJECTS
-- =====================================================

-- Drop tables in reverse order of dependencies
DROP TABLE audit_logs CASCADE CONSTRAINTS;
DROP TABLE feedback CASCADE CONSTRAINTS;
DROP TABLE notification_templates CASCADE CONSTRAINTS;
DROP TABLE notifications CASCADE CONSTRAINTS;
DROP TABLE invoices CASCADE CONSTRAINTS;
DROP TABLE billing CASCADE CONSTRAINTS;
DROP TABLE bookings CASCADE CONSTRAINTS;
DROP TABLE rooms CASCADE CONSTRAINTS;
DROP TABLE tariffs CASCADE CONSTRAINTS;
DROP TABLE employees CASCADE CONSTRAINTS;
DROP TABLE customers CASCADE CONSTRAINTS;
DROP TABLE users CASCADE CONSTRAINTS;

-- Drop sequences
DROP SEQUENCE user_seq;
DROP SEQUENCE customer_seq;
DROP SEQUENCE employee_seq;
DROP SEQUENCE room_seq;
DROP SEQUENCE tariff_seq;
DROP SEQUENCE booking_seq;
DROP SEQUENCE billing_seq;
DROP SEQUENCE invoice_seq;
DROP SEQUENCE feedback_seq;
DROP SEQUENCE notification_seq;
DROP SEQUENCE notification_template_seq;
DROP SEQUENCE audit_seq;

-- =====================================================
-- SECTION 2: CREATE SEQUENCES
-- =====================================================

CREATE SEQUENCE user_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE customer_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE employee_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE room_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE tariff_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE booking_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE billing_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE invoice_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE feedback_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE notification_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE notification_template_seq START WITH 1 INCREMENT BY 1 NOCACHE;
CREATE SEQUENCE audit_seq START WITH 1 INCREMENT BY 1 NOCACHE;

-- =====================================================
-- SECTION 3: CREATE TABLES
-- =====================================================

-- Users Table
CREATE TABLE users (
    user_id NUMBER PRIMARY KEY,
    username VARCHAR2(50) UNIQUE NOT NULL,
    password VARCHAR2(255) NOT NULL,
    email VARCHAR2(100) UNIQUE NOT NULL,
    full_name VARCHAR2(100) NOT NULL,
    phone VARCHAR2(15),
    role VARCHAR2(20) NOT NULL CHECK (role IN ('admin', 'receptionist', 'customer')),
    is_active NUMBER(1) DEFAULT 1,
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE
);

-- Add comments
COMMENT ON TABLE users IS 'Stores user authentication and profile information';
COMMENT ON COLUMN users.role IS 'User role: admin, receptionist, or customer';
COMMENT ON COLUMN users.is_active IS '1 = active, 0 = inactive';

-- Create indexes
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

-- Customers Table
CREATE TABLE customers (
    customer_id NUMBER PRIMARY KEY,
    user_id NUMBER,
    full_name VARCHAR2(100) NOT NULL,
    email VARCHAR2(100) NOT NULL,
    phone VARCHAR2(15) NOT NULL,
    address VARCHAR2(255),
    city VARCHAR2(50),
    state VARCHAR2(50),
    country VARCHAR2(50) DEFAULT 'India',
    id_proof_type VARCHAR2(50),
    id_proof_number VARCHAR2(50),
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE,
    CONSTRAINT fk_customer_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

COMMENT ON TABLE customers IS 'Stores customer information';

CREATE INDEX idx_customers_email ON customers(email);
CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_customers_user_id ON customers(user_id);

-- Employees Table
CREATE TABLE employees (
    employee_id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    full_name VARCHAR2(100) NOT NULL,
    phone VARCHAR2(15) NOT NULL,
    department VARCHAR2(50),
    designation VARCHAR2(50),
    salary NUMBER(10,2),
    joining_date DATE,
    shift_timing VARCHAR2(50),
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE,
    CONSTRAINT fk_employee_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

COMMENT ON TABLE employees IS 'Stores employee information';

CREATE INDEX idx_employees_user_id ON employees(user_id);
CREATE INDEX idx_employees_department ON employees(department);

-- Tariffs Table
CREATE TABLE tariffs (
    tariff_id NUMBER PRIMARY KEY,
    tariff_name VARCHAR2(50) UNIQUE NOT NULL,
    base_price NUMBER(10,2) NOT NULL,
    description VARCHAR2(255),
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE,
    CONSTRAINT chk_tariff_price CHECK (base_price > 0)
);

COMMENT ON TABLE tariffs IS 'Stores room pricing information';

CREATE INDEX idx_tariffs_name ON tariffs(tariff_name);

-- Rooms Table
CREATE TABLE rooms (
    room_id NUMBER PRIMARY KEY,
    room_number VARCHAR2(10) UNIQUE NOT NULL,
    room_type VARCHAR2(50) NOT NULL,
    floor_number NUMBER NOT NULL,
    max_occupancy NUMBER NOT NULL,
    tariff_id NUMBER NOT NULL,
    status VARCHAR2(20) DEFAULT 'available' CHECK (status IN ('available', 'occupied', 'maintenance', 'reserved')),
    amenities CLOB,
    description CLOB,
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE,
    CONSTRAINT fk_room_tariff FOREIGN KEY (tariff_id) REFERENCES tariffs(tariff_id) ON DELETE RESTRICT,
    CONSTRAINT chk_room_floor CHECK (floor_number > 0),
    CONSTRAINT chk_room_occupancy CHECK (max_occupancy > 0)
);

COMMENT ON TABLE rooms IS 'Stores room inventory information';

CREATE INDEX idx_rooms_number ON rooms(room_number);
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_rooms_type ON rooms(room_type);
CREATE INDEX idx_rooms_floor ON rooms(floor_number);

-- Bookings Table
CREATE TABLE bookings (
    booking_id NUMBER PRIMARY KEY,
    customer_id NUMBER NOT NULL,
    room_id NUMBER NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    num_guests NUMBER NOT NULL,
    total_amount NUMBER(10,2) NOT NULL,
    advance_paid NUMBER(10,2) DEFAULT 0,
    status VARCHAR2(20) DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled')),
    special_requests CLOB,
    actual_check_in DATE,
    actual_check_out DATE,
    cancellation_reason VARCHAR2(255),
    cancelled_at DATE,
    created_by NUMBER NOT NULL,
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE,
    CONSTRAINT fk_booking_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT,
    CONSTRAINT fk_booking_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE RESTRICT,
    CONSTRAINT fk_booking_creator FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    CONSTRAINT chk_booking_dates CHECK (check_out_date > check_in_date),
    CONSTRAINT chk_booking_guests CHECK (num_guests > 0),
    CONSTRAINT chk_booking_amount CHECK (total_amount >= 0)
);

COMMENT ON TABLE bookings IS 'Stores room booking information';

CREATE INDEX idx_bookings_customer ON bookings(customer_id);
CREATE INDEX idx_bookings_room ON bookings(room_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_checkin ON bookings(check_in_date);
CREATE INDEX idx_bookings_checkout ON bookings(check_out_date);

-- Billing Table
CREATE TABLE billing (
    bill_id NUMBER PRIMARY KEY,
    booking_id NUMBER NOT NULL,
    bill_number VARCHAR2(50) UNIQUE NOT NULL,
    bill_date DATE DEFAULT SYSDATE,
    room_charges NUMBER(10,2) NOT NULL,
    additional_charges NUMBER(10,2) DEFAULT 0,
    tax_amount NUMBER(10,2) NOT NULL,
    discount NUMBER(10,2) DEFAULT 0,
    total_amount NUMBER(10,2) NOT NULL,
    payment_status VARCHAR2(20) DEFAULT 'pending' CHECK (payment_status IN ('pending', 'paid', 'partial', 'refunded')),
    payment_method VARCHAR2(20) CHECK (payment_method IN ('cash', 'card', 'upi', 'net_banking')),
    payment_date DATE,
    transaction_id VARCHAR2(100),
    generated_by NUMBER NOT NULL,
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE,
    CONSTRAINT fk_billing_booking FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE RESTRICT,
    CONSTRAINT fk_billing_generator FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    CONSTRAINT chk_billing_amounts CHECK (total_amount >= 0)
);

COMMENT ON TABLE billing IS 'Stores billing and payment information';

CREATE INDEX idx_billing_booking ON billing(booking_id);
CREATE INDEX idx_billing_status ON billing(payment_status);
CREATE INDEX idx_billing_date ON billing(bill_date);
CREATE INDEX idx_billing_number ON billing(bill_number);

-- Invoices Table
CREATE TABLE invoices (
    invoice_id NUMBER PRIMARY KEY,
    billing_id NUMBER NOT NULL,
    invoice_number VARCHAR2(50) UNIQUE NOT NULL,
    generated_date DATE DEFAULT SYSDATE,
    created_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_invoice_billing FOREIGN KEY (billing_id) REFERENCES billing(bill_id) ON DELETE CASCADE
);

COMMENT ON TABLE invoices IS 'Stores invoice information';

CREATE INDEX idx_invoices_billing ON invoices(billing_id);
CREATE INDEX idx_invoices_number ON invoices(invoice_number);

-- Feedback Table
CREATE TABLE feedback (
    feedback_id NUMBER PRIMARY KEY,
    booking_id NUMBER NOT NULL,
    customer_id NUMBER NOT NULL,
    rating NUMBER CHECK (rating BETWEEN 1 AND 5),
    comments CLOB,
    created_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_feedback_booking FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

COMMENT ON TABLE feedback IS 'Stores customer feedback';

CREATE INDEX idx_feedback_booking ON feedback(booking_id);
CREATE INDEX idx_feedback_customer ON feedback(customer_id);
CREATE INDEX idx_feedback_rating ON feedback(rating);

-- Notifications Table
CREATE TABLE notifications (
    notification_id NUMBER PRIMARY KEY,
    user_id NUMBER,
    type VARCHAR2(50) NOT NULL,
    subject VARCHAR2(200) NOT NULL,
    message CLOB NOT NULL,
    sent_at DATE DEFAULT SYSDATE,
    status VARCHAR2(20) DEFAULT 'sent',
    created_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

COMMENT ON TABLE notifications IS 'Stores notification history';

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_status ON notifications(status);

-- Notification Templates Table
CREATE TABLE notification_templates (
    template_id NUMBER PRIMARY KEY,
    template_name VARCHAR2(100) UNIQUE NOT NULL,
    template_type VARCHAR2(50) NOT NULL,
    subject VARCHAR2(200),
    message_body CLOB,
    created_at DATE DEFAULT SYSDATE,
    updated_at DATE
);

COMMENT ON TABLE notification_templates IS 'Stores notification templates';

CREATE INDEX idx_notif_templates_name ON notification_templates(template_name);
CREATE INDEX idx_notif_templates_type ON notification_templates(template_type);

-- Audit Logs Table
CREATE TABLE audit_logs (
    audit_id NUMBER PRIMARY KEY,
    user_id NUMBER,
    action VARCHAR2(50) NOT NULL,
    table_name VARCHAR2(50),
    record_id NUMBER,
    description VARCHAR2(500),
    ip_address VARCHAR2(50),
    created_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

COMMENT ON TABLE audit_logs IS 'Stores audit trail of all system activities';

CREATE INDEX idx_audit_user ON audit_logs(user_id);
CREATE INDEX idx_audit_action ON audit_logs(action);
CREATE INDEX idx_audit_table ON audit_logs(table_name);
CREATE INDEX idx_audit_date ON audit_logs(created_at);

-- =====================================================
-- SECTION 4: INSERT SAMPLE DATA
-- =====================================================

-- Note: Passwords are hashed with PASSWORD_DEFAULT
-- You need to generate proper hashes using PHP's password_hash() function
-- For demo purposes, using placeholder hashes

-- Insert Sample Tariffs
INSERT INTO tariffs (tariff_id, tariff_name, base_price, description) 
VALUES (tariff_seq.NEXTVAL, 'Standard Room', 2000, 'Basic accommodation with essential amenities');

INSERT INTO tariffs (tariff_id, tariff_name, base_price, description) 
VALUES (tariff_seq.NEXTVAL, 'Deluxe Room', 3500, 'Spacious room with premium amenities');

INSERT INTO tariffs (tariff_id, tariff_name, base_price, description) 
VALUES (tariff_seq.NEXTVAL, 'Suite', 6000, 'Luxury suite with living area and premium services');

INSERT INTO tariffs (tariff_id, tariff_name, base_price, description) 
VALUES (tariff_seq.NEXTVAL, 'Executive Suite', 8500, 'Premium executive suite with exclusive facilities');

-- Insert Sample Users
-- Password: admin123 (you need to replace with actual hash)
INSERT INTO users (user_id, username, password, email, full_name, phone, role, is_active) 
VALUES (user_seq.NEXTVAL, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'admin@hotel.com', 'Administrator', '9999999999', 'admin', 1);

-- Password: rec123
INSERT INTO users (user_id, username, password, email, full_name, phone, role, is_active) 
VALUES (user_seq.NEXTVAL, 'receptionist', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'receptionist@hotel.com', 'John Receptionist', '9999999998', 'receptionist', 1);

-- Password: cust123
INSERT INTO users (user_id, username, password, email, full_name, phone, role, is_active) 
VALUES (user_seq.NEXTVAL, 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'customer@gmail.com', 'John Doe', '9999999997', 'customer', 1);

-- Insert Employee (linked to receptionist user)
INSERT INTO employees (employee_id, user_id, full_name, phone, department, designation, salary, joining_date, shift_timing) 
VALUES (employee_seq.NEXTVAL, 2, 'John Receptionist', '9999999998', 'Front Desk', 'Receptionist', 
        25000, SYSDATE, 'Morning (6 AM - 2 PM)');

-- Insert Customer (linked to customer user)
INSERT INTO customers (customer_id, user_id, full_name, email, phone, address, city, state, country) 
VALUES (customer_seq.NEXTVAL, 3, 'John Doe', 'customer@gmail.com', '9999999997', 
        '123 Main Street', 'Mumbai', 'Maharashtra', 'India');

-- Insert Sample Rooms
INSERT INTO rooms (room_id, room_number, room_type, floor_number, max_occupancy, tariff_id, status, amenities, description) 
VALUES (room_seq.NEXTVAL, '101', 'Standard', 1, 2, 1, 'available', 
        'AC, TV, WiFi, Hot Water', 'Comfortable standard room on ground floor');

INSERT INTO rooms (room_id, room_number, room_type, floor_number, max_occupancy, tariff_id, status, amenities) 
VALUES (room_seq.NEXTVAL, '102', 'Standard', 1, 2, 1, 'available', 'AC, TV, WiFi, Hot Water');

INSERT INTO rooms (room_id, room_number, room_type, floor_number, max_occupancy, tariff_id, status, amenities) 
VALUES (room_seq.NEXTVAL, '103', 'Standard', 1, 3, 1, 'available', 'AC, TV, WiFi, Hot Water');

INSERT INTO rooms (room_id, room_number, room_type, floor_number, max_occupancy, tariff_id, status, amenities) 
VALUES (room_seq.NEXTVAL, '201', 'Deluxe', 2, 3, 2, 'available', 'AC, TV, WiFi, Mini Bar, Room Service');

INSERT INTO rooms (room_id, room_number, room_type, floor_number, max_occupancy, tariff_id, status, amenities) 
VALUES (room_seq.NEXTVAL, '202', 'Deluxe', 2, 3, 2, 'available', 'AC, TV, WiFi, Mini Bar, Room Service');

INSERT INTO rooms (room_id, room_number, room_type, floor_number, max_occupancy, tariff_id, status, amenities) 
VALUES (room_seq.NEXTVAL, '301', 'Suite', 3, 4, 3, 'available', 'AC, TV, WiFi, Mini Bar, Kitchen, Living Room');

INSERT INTO rooms (room_id, room_number, room_type, floor_number, max_occupancy, tariff_id, status, amenities) 
VALUES (room_seq.NEXTVAL, '302', 'Suite', 3, 4, 3, 'available', 'AC, TV, WiFi, Mini Bar, Kitchen, Living Room');

-- Insert Notification Templates
INSERT INTO notification_templates (template_id, template_name, template_type, subject, message_body)
VALUES (notification_template_seq.NEXTVAL, 'Booking Confirmation', 'email', 'Booking Confirmed - {booking_id}',
'Dear {customer_name},

Your booking has been confirmed!

Booking Details:
- Booking ID: {booking_id}
- Room: {room_number} ({room_type})
- Check-in: {check_in_date}
- Check-out: {check_out_date}
- Total Amount: ₹{total_amount}

Thank you for choosing us!

Best regards,
Hotel Management Team');

-- Commit all changes
COMMIT;

-- =====================================================
-- SECTION 5: VERIFICATION QUERIES
-- =====================================================

-- Verify table creation
SELECT COUNT(*) AS table_count FROM user_tables;

-- Verify data insertion
SELECT 'Users' AS table_name, COUNT(*) AS record_count FROM users
UNION ALL
SELECT 'Tariffs', COUNT(*) FROM tariffs
UNION ALL
SELECT 'Rooms', COUNT(*) FROM rooms
UNION ALL
SELECT 'Employees', COUNT(*) FROM employees
UNION ALL
SELECT 'Customers', COUNT(*) FROM customers;

-- =====================================================
-- SCRIPT COMPLETED SUCCESSFULLY
-- =====================================================
-- Run the verification queries above to confirm
-- All tables and sample data have been created
-- =====================================================