-- Splendid Caterery Platform Database Schema
-- MySQL 8.0+

CREATE DATABASE IF NOT EXISTS splendid_caterery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE splendid_caterery;

-- Users & Authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer','admin','event_manager','chef','delivery','finance') NOT NULL DEFAULT 'customer',
    avatar_url VARCHAR(500),
    address TEXT,
    city VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    loyalty_points INT DEFAULT 0,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
);

-- Service Types
CREATE TABLE service_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    image_url VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Catering Packages
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    tier ENUM('basic','silver','gold','platinum','vip') NOT NULL,
    description TEXT,
    price_per_guest DECIMAL(10,2) NOT NULL,
    min_guests INT DEFAULT 20,
    max_guests INT DEFAULT 500,
    features JSON,
    image_url VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Categories
CREATE TABLE menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);

-- Menu Items
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500),
    is_vegetarian TINYINT(1) DEFAULT 0,
    is_vegan TINYINT(1) DEFAULT 0,
    is_halal TINYINT(1) DEFAULT 0,
    is_gluten_free TINYINT(1) DEFAULT 0,
    allergens JSON,
    calories INT,
    prep_time_minutes INT,
    is_active TINYINT(1) DEFAULT 1,
    popularity_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
);

-- Package-Menu mapping
CREATE TABLE package_menu_items (
    package_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    PRIMARY KEY (package_id, menu_item_id),
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Promotions
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage','fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_uses INT,
    used_count INT DEFAULT 0,
    valid_from DATE,
    valid_until DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quotations
CREATE TABLE quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_no VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    service_type_id INT,
    package_id INT,
    event_type VARCHAR(100),
    event_date DATE NOT NULL,
    event_time TIME,
    venue TEXT NOT NULL,
    guest_count INT NOT NULL,
    dietary_preferences JSON,
    special_requests TEXT,
    subtotal DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending','approved','rejected','converted','expired') DEFAULT 'pending',
    valid_until DATE,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (service_type_id) REFERENCES service_types(id),
    FOREIGN KEY (package_id) REFERENCES packages(id)
);

-- Bookings
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_no VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    quotation_id INT,
    service_type_id INT,
    package_id INT,
    event_manager_id INT,
    event_type VARCHAR(100),
    event_date DATE NOT NULL,
    event_time TIME,
    venue TEXT NOT NULL,
    venue_lat DECIMAL(10,8),
    venue_lng DECIMAL(11,8),
    guest_count INT NOT NULL,
    dietary_preferences JSON,
    special_requests TEXT,
    subtotal DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    amount_paid DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending','confirmed','in_preparation','ready','in_transit','delivered','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','partial','paid','refunded') DEFAULT 'unpaid',
    qr_code VARCHAR(255),
    promo_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (quotation_id) REFERENCES quotations(id),
    FOREIGN KEY (service_type_id) REFERENCES service_types(id),
    FOREIGN KEY (package_id) REFERENCES packages(id),
    FOREIGN KEY (event_manager_id) REFERENCES users(id),
    INDEX idx_event_date (event_date),
    INDEX idx_status (status)
);

-- Booking Menu Items (customizations)
CREATE TABLE booking_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    notes TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- Payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    reference_no VARCHAR(30) NOT NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    method ENUM('mtn_momo','telecel_cash','airteltigo','visa','mastercard','bank_transfer','cash') NOT NULL,
    status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    verified_by INT,
    verified_at DATETIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

-- Invoices
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(20) NOT NULL UNIQUE,
    booking_id INT NOT NULL,
    customer_id INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    amount_paid DECIMAL(12,2) DEFAULT 0,
    status ENUM('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
    due_date DATE,
    pdf_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (customer_id) REFERENCES users(id)
);

-- Reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(200),
    comment TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (customer_id) REFERENCES users(id)
);

-- Testimonials (website)
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    event_type VARCHAR(100),
    quote TEXT NOT NULL,
    rating TINYINT DEFAULT 5,
    image_url VARCHAR(500),
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gallery
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_url VARCHAR(500) NOT NULL,
    category VARCHAR(100),
    event_type VARCHAR(100),
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Website Content (CMS)
CREATE TABLE website_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255),
    content TEXT,
    meta_description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Event Management
CREATE TABLE event_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    event_manager_id INT NOT NULL,
    plan_notes TEXT,
    timeline JSON,
    status ENUM('draft','active','completed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (event_manager_id) REFERENCES users(id)
);

CREATE TABLE event_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    completed_by INT,
    completed_at DATETIME,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (completed_by) REFERENCES users(id)
);

CREATE TABLE event_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    photo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE staff_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    staff_id INT NOT NULL,
    role ENUM('event_manager','chef','delivery','setup') NOT NULL,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (staff_id) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

-- Kitchen Operations
CREATE TABLE kitchen_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    chef_id INT,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    status ENUM('pending','preparing','ready','served') DEFAULT 'pending',
    prep_started_at DATETIME,
    ready_at DATETIME,
    notes TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (chef_id) REFERENCES users(id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_item_id INT NOT NULL,
    instructions TEXT NOT NULL,
    prep_time_minutes INT,
    cook_time_minutes INT,
    servings INT DEFAULT 1,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

CREATE TABLE recipe_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    ingredient_name VARCHAR(200) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Inventory
CREATE TABLE inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(100),
    unit VARCHAR(50) NOT NULL,
    current_stock DECIMAL(10,2) DEFAULT 0,
    min_stock_level DECIMAL(10,2) DEFAULT 0,
    unit_cost DECIMAL(10,2),
    supplier VARCHAR(200),
    last_restocked DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_item_id INT NOT NULL,
    type ENUM('in','out','adjustment') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Delivery & Setup
CREATE TABLE delivery_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    driver_id INT,
    pickup_time DATETIME,
    delivery_time DATETIME,
    status ENUM('scheduled','picked_up','in_transit','delivered','setup_complete') DEFAULT 'scheduled',
    proof_photo_url VARCHAR(500),
    setup_photo_url VARCHAR(500),
    signature_data TEXT,
    delay_reason TEXT,
    gps_lat DECIMAL(10,8),
    gps_lng DECIMAL(11,8),
    notes TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (driver_id) REFERENCES users(id)
);

-- Live Chat
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(50) NOT NULL,
    sender_id INT,
    sender_type ENUM('customer','support','ai') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
);

-- Availability Calendar
CREATE TABLE availability_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    block_date DATE NOT NULL,
    reason VARCHAR(255),
    max_bookings INT DEFAULT 3,
    current_bookings INT DEFAULT 0,
    UNIQUE KEY unique_date (block_date)
);

-- Loyalty Rewards
CREATE TABLE loyalty_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT NOT NULL,
    type ENUM('earn','redeem') NOT NULL,
    description VARCHAR(255),
    booking_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- Financial Reports Cache
CREATE TABLE financial_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    snapshot_date DATE NOT NULL,
    daily_revenue DECIMAL(12,2) DEFAULT 0,
    monthly_revenue DECIMAL(12,2) DEFAULT 0,
    bookings_count INT DEFAULT 0,
    outstanding_balance DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_snapshot (snapshot_date)
);
