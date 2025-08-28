CREATE DATABASE IF NOT EXISTS realestate_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE realestate_db;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS property_inquiries;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create properties table
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    description TEXT,
    type ENUM('house', 'apartment', 'villa', 'land'),
    status ENUM('active', 'pending', 'sold', 'inactive') DEFAULT 'pending',
    price DECIMAL(12,2),
    location VARCHAR(255),
    city VARCHAR(100),
    address TEXT,
    bedrooms INT,
    bathrooms INT,
    area DECIMAL(10,2),
    features TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create property_images table
CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    image_url VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Create property_inquiries table
CREATE TABLE property_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    message TEXT,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Create favorites table
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, property_id)
);

-- Create contact_messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- Insert sample data
-- Insert users
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert properties
INSERT INTO properties (user_id, title, description, type, status, price, location, city, bedrooms, bathrooms) VALUES
(2, 'Modern Apartment in City Center', 'Beautiful modern apartment with great view', 'apartment', 'active', 250000, 'City Center', 'Istanbul', 2, 1),
(2, 'Luxury Villa with Pool', 'Spacious villa with private pool', 'villa', 'active', 750000, 'Beachfront', 'Antalya', 4, 3),
(3, 'Cozy House with Garden', 'Perfect family house with beautiful garden', 'house', 'active', 350000, 'Suburban', 'Ankara', 3, 2);



-- Insert property inquiries
INSERT INTO property_inquiries (property_id, name, email, phone, message, status) VALUES
(1, 'Mike Wilson', 'mike@example.com', '+90 555 123 4567', 'I am interested in this apartment. When can I view it?', 'new'),
(2, 'Sarah Johnson', 'sarah@example.com', '+90 555 987 6543', 'Please provide more details about the villa.', 'read'),
(3, 'David Brown', 'david@example.com', '+90 555 456 7890', 'Is the price negotiable?', 'replied');