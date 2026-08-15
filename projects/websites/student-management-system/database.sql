-- ============================================================
-- Elevate Media College — Database Schema + Seed Data
-- For InfinityFree (phpMyAdmin): REMOVE the CREATE DATABASE / USE lines below.
-- ============================================================

-- REMOVE these lines on InfinityFree:
-- CREATE DATABASE IF NOT EXISTS elevate_media_college;
-- USE elevate_media_college;

SET NAMES utf8mb4;

-- Drop any previous partial imports (this makes re-imports clean).
-- FK checks are disabled so table creation order never matters again.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS loans, timetables, messages, students, classrooms, books, departments, admins;

-- ------------------------------------------------------------
-- Tables
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    building VARCHAR(120) NOT NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#6d5df6',
    head VARCHAR(120) NOT NULL,
    established YEAR NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS classrooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    building VARCHAR(120) NOT NULL,
    department_id INT DEFAULT NULL,
    capacity INT NOT NULL DEFAULT 30,
    floor INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_classroom_dept FOREIGN KEY (department_id)
        REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS timetables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classroom_id INT NOT NULL,
    department_id INT DEFAULT NULL,
    day VARCHAR(12) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    course VARCHAR(150) NOT NULL,
    lecturer VARCHAR(120) NOT NULL,
    CONSTRAINT fk_tt_classroom FOREIGN KEY (classroom_id)
        REFERENCES classrooms(id) ON DELETE CASCADE,
    CONSTRAINT fk_tt_dept FOREIGN KEY (department_id)
        REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    isbn VARCHAR(40) DEFAULT NULL,
    category VARCHAR(80) NOT NULL,
    year INT DEFAULT NULL,
    copies INT NOT NULL DEFAULT 1,
    available INT NOT NULL DEFAULT 1,
    shelf VARCHAR(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL,
    course VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    avatar_color VARCHAR(20) NOT NULL DEFAULT '#6d5df6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    CONSTRAINT fk_loan_book FOREIGN KEY (book_id)
        REFERENCES books(id) ON DELETE CASCADE,
    CONSTRAINT fk_loan_student FOREIGN KEY (student_id)
        REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seed: Departments
-- ------------------------------------------------------------

INSERT INTO departments (name, code, building, color, head, established, description) VALUES
('ICT & Computer Science',   'ICT', 'ICT Building',   '#6d5df6', 'Dr. Sarah Njeri',   2016, 'Software engineering, networking, data science and cyber security programmes delivered in modern computer labs.'),
('Media & Communication',    'MCM', 'Media Studio',   '#00d4ff', 'Mr. David Otieno',   2017, 'Journalism, film production, broadcast, digital content creation and public relations training with real studio experience.'),
('Business & Management',    'BMS', 'Business School', '#ffc93c', 'Ms. Grace Wanjiru',  2015, 'Entrepreneurship, accounting, marketing and business leadership programmes focused on real-world ventures.'),
('Engineering & Technology', 'ENG', 'Engineering Block', '#2dd4a7', 'Eng. Peter Mwangi', 2018, 'Electrical, mechanical and civil engineering technology with hands-on laboratory projects.'),
('General Studies',          'GST', 'Main Block',     '#ff5c7a', 'Mrs. Agnes Chebet',  2015, 'Communication skills, ethics, mathematics and general courses supporting every programme at the college.');

-- ------------------------------------------------------------
-- Seed: Classrooms
-- ------------------------------------------------------------

INSERT INTO classrooms (name, building, department_id, capacity, floor) VALUES
('ICT Lab 1',        'ICT Building',      1, 40, 1),
('ICT Lab 2',        'ICT Building',      1, 30, 2),
('Studio A',         'Media Studio',      2, 25, 1),
('Studio B',         'Media Studio',      2, 20, 2),
('Lecture Hall 1',   'Main Block',        5, 120, 1),
('Lecture Hall 2',   'Main Block',        5, 100, 2),
('Business Seminar', 'Business School',   3, 45, 1),
('Boardroom',        'Business School',   3, 30, 2),
('Engineering Lab',  'Engineering Block', 4, 35, 1),
('Innovation Hub',   'Engineering Block', 4, 28, 2);

-- ------------------------------------------------------------
-- Seed: Timetables (Monday – Friday)
-- ------------------------------------------------------------

INSERT INTO timetables (classroom_id, department_id, day, start_time, end_time, course, lecturer) VALUES
-- ICT Building
(1, 1, 'Monday',    '08:00:00', '10:00:00', 'Web Development I',        'Dr. Sarah Njeri'),
(1, 1, 'Wednesday', '08:00:00', '10:00:00', 'Database Systems',         'Mr. Kelvin Mutua'),
(1, 1, 'Friday',    '10:00:00', '12:00:00', 'Software Engineering',     'Dr. Sarah Njeri'),
(2, 1, 'Tuesday',   '10:00:00', '12:00:00', 'Computer Networks',        'Mr. Kelvin Mutua'),
(2, 1, 'Thursday',  '08:00:00', '10:00:00', 'Python Programming',       'Ms. Faith Adhiambo'),
-- Media Studio
(3, 2, 'Monday',    '10:00:00', '12:00:00', 'Introduction to Journalism','Mr. David Otieno'),
(3, 2, 'Wednesday', '12:00:00', '14:00:00', 'Film Production',          'Mr. David Otieno'),
(3, 2, 'Friday',    '08:00:00', '10:00:00', 'Digital Content Creation', 'Ms. Mercy Wairimu'),
(4, 2, 'Tuesday',   '08:00:00', '10:00:00', 'Broadcast News Writing',   'Ms. Mercy Wairimu'),
(4, 2, 'Thursday',  '12:00:00', '14:00:00', 'Public Relations',         'Mr. David Otieno'),
-- Main Block
(5, 5, 'Monday',    '14:00:00', '16:00:00', 'Communication Skills',     'Mrs. Agnes Chebet'),
(5, 5, 'Wednesday', '14:00:00', '16:00:00', 'Ethics & Integrity',       'Mrs. Agnes Chebet'),
(6, 5, 'Friday',    '14:00:00', '16:00:00', 'General Mathematics',      'Mr. George Kamau'),
-- Business School
(7, 3, 'Monday',    '08:00:00', '10:00:00', 'Entrepreneurship',         'Ms. Grace Wanjiru'),
(7, 3, 'Wednesday', '10:00:00', '12:00:00', 'Financial Accounting',     'Mr. Brian Omondi'),
(8, 3, 'Tuesday',   '14:00:00', '16:00:00', 'Marketing Management',     'Ms. Grace Wanjiru'),
-- Engineering Block
(9, 4, 'Monday',    '10:00:00', '12:00:00', 'Electrical Principles',    'Eng. Peter Mwangi'),
(9, 4, 'Thursday',  '10:00:00', '12:00:00', 'Digital Electronics',      'Eng. Peter Mwangi'),
(10, 4, 'Wednesday','08:00:00', '10:00:00','Mechanical Workshop',       'Mr. Samuel Kiprop');

-- ------------------------------------------------------------
-- Seed: Books
-- ------------------------------------------------------------

INSERT INTO books (title, author, isbn, category, year, copies, available, shelf) VALUES
('Clean Code',                        'Robert C. Martin',            '9780132350884', 'Technology',     2008, 3, 3, 'A-01'),
('JavaScript: The Good Parts',        'Douglas Crockford',           '9780596517748', 'Technology',     2008, 2, 2, 'A-02'),
('Computer Networking: A Top-Down Approach', 'James Kurose & Keith Ross', '9780133594140', 'Technology', 2017, 2, 2, 'A-03'),
('Deep Work',                         'Cal Newport',                 '9781455586691', 'Self-Improvement', 2016, 3, 2, 'B-01'),
('Atomic Habits',                     'James Clear',                 '9780735211292', 'Self-Improvement', 2018, 4, 4, 'B-02'),
('Understanding Media',               'Marshall McLuhan',            '9780416283979', 'Media Studies', 1964, 2, 2, 'C-01'),
('Broadcast News Writing',            'Ted White & Frank Barnas',    '9781452220491', 'Media Studies', 2012, 2, 1, 'C-02'),
('The Elements of Journalism',        'Bill Kovach & Tom Rosenstiel','9780804136785', 'Media Studies', 2014, 2, 2, 'C-03'),
('Decolonising the Mind',             'Ngugi wa Thiong''o',          '9780852555019', 'Literature',    1986, 3, 3, 'D-01'),
('Born a Crime',                      'Trevor Noah',                 '9780399588190', 'Literature',    2016, 3, 2, 'D-02'),
('Purple Hibiscus',                   'Chimamanda Ngozi Adichie',    '9781565123878', 'Literature',    2003, 2, 2, 'D-03'),
('The Lean Startup',                  'Eric Ries',                   '9780307887894', 'Business',      2011, 3, 3, 'E-01'),
('Marketing Management',              'Philip Kotler & Kevin Keller','9781292092621', 'Business',      2015, 2, 2, 'E-02'),
('Principles of Economics',           'N. Gregory Mankiw',           '9781305585126', 'Business',      2017, 2, 2, 'E-03'),
('Digital Fundamentals',              'Thomas L. Floyd',             '9780132737968', 'Engineering',   2014, 2, 2, 'F-01'),
('Introduction to Electronics',       'Earl Gates',                  '9781111128531', 'Engineering',   2011, 2, 2, 'F-02');

-- ------------------------------------------------------------
-- Seed: Students
-- ------------------------------------------------------------

INSERT INTO students (first_name, last_name, email, phone, course, department, avatar_color) VALUES
('John',   'Doe',     'john.doe@example.com',     '0712345678', 'Software Engineering',   'ICT',  '#6d5df6'),
('Jane',   'Smith',   'jane.smith@example.com',   '0723456789', 'Business Administration','BMS',  '#ffc93c'),
('Ali',    'Khan',    'ali.khan@example.com',     '0734567890', 'Electrical Engineering', 'ENG',  '#2dd4a7'),
('Faith',  'Njeri',   'faith.njeri@example.com',  '0745678901', 'Film Production',        'MCM',  '#00d4ff'),
('Brian',  'Omondi',  'brian.omondi@example.com', '0756789012', 'Computer Networking',    'ICT',  '#8b5cf6'),
('Mercy',  'Wairimu', 'mercy.wairimu@example.com','0767890123', 'Digital Marketing',      'BMS',  '#f59e0b'),
('Samuel', 'Kiprop',  'samuel.kiprop@example.com','0778901234', 'Mechanical Engineering', 'ENG',  '#10b981'),
('Amina',  'Hassan',  'amina.hassan@example.com', '0789012345', 'Journalism',             'MCM',  '#06b6d4'),
('Peter',  'Mwangi',  'peter.mwangi@example.com', '0790123456', 'Cyber Security',         'ICT',  '#6366f1'),
('Wanjiru','Gitau',   'wanjiru.gitau@example.com','0701234567', 'Accounting',             'BMS',  '#fbbf24');

-- ------------------------------------------------------------
-- Seed: Loans (a few active, a couple returned)
-- ------------------------------------------------------------

INSERT INTO loans (book_id, student_id, borrow_date, due_date, return_date) VALUES
(7,  4,  '2026-07-28', '2026-08-11', NULL),
(10, 8,  '2026-07-30', '2026-08-13', NULL),
(2,  1,  '2026-07-20', '2026-08-03', '2026-08-01'),
(13, 5,  '2026-08-03', '2026-08-17', NULL),
(5,  10, '2026-07-25', '2026-08-08', '2026-08-04');

-- ------------------------------------------------------------
-- Seed: Admin account (username: admin / password: admin123)
-- ------------------------------------------------------------

INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$10$YwvkwXFRBpxsNGPOkgbEm.Py3D7TqREIkSzGnTk.unSdIJEflE57e');

SET FOREIGN_KEY_CHECKS = 1;
