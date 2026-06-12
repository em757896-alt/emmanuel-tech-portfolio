-- REMOVE these lines on InfinityFree:
-- CREATE DATABASE IF NOT EXISTS student_management;
-- USE student_management;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL,
    course VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO students (first_name, last_name, email, phone, course, department) VALUES
('John', 'Doe', 'john.doe@example.com', '1234567890', 'Computer Science', 'ICT'),
('Jane', 'Smith', 'jane.smith@example.com', '0987654321', 'Business Administration', 'Business'),
('Ali', 'Khan', 'ali.khan@example.com', '5551234567', 'Electrical Engineering', 'Engineering');