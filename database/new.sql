-- ============================================================================
-- CodeDojo Complete Database Setup
-- Drop old database and create new one with all tables and data
-- ============================================================================

-- Drop existing database if it exists
DROP DATABASE IF EXISTS codedojo;

-- Create fresh database
CREATE DATABASE codedojo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE codedojo;

-- ============================================================================
-- AUTHENTICATION TABLES
-- ============================================================================

-- Admins table: stores admin accounts with secure passwords
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table: stores regular user accounts with secure passwords
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- LEARNING CONTENT TABLES
-- ============================================================================

-- Lessons table: stores HTML learning modules
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    order_num INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_difficulty (difficulty),
    INDEX idx_order (order_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Practice tasks: specific coding challenges
CREATE TABLE practice_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT,
    title VARCHAR(255) NOT NULL,
    instruction TEXT NOT NULL,
    hint TEXT,
    starter_code TEXT,
    solution_code TEXT,
    order_num INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User practice: saved work and attempts
CREATE TABLE user_practice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    html_code TEXT NOT NULL,
    task_id INT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES practice_tasks(id) ON DELETE SET NULL,
    INDEX idx_created (created_at),
    INDEX idx_task (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT DEFAULT AUTHENTICATION ACCOUNTS
-- ============================================================================

-- Default admin account
-- Username: admin, Password: codedojo123 (bcrypt hash)
INSERT INTO admins (username, email, password, is_active) VALUES
('admin', 'admin@codedojo.local', '$2y$10$E8.GNt8oDDJ2RxwsKxcLEOQgxl/YrvZwTyR/E4T5n2QI/p9YBc8vC', TRUE);

-- Default user account
-- Username: user, Password: user123 (bcrypt hash)
INSERT INTO users (username, email, password, first_name, last_name, is_active) VALUES
('user', 'user@codedojo.local', '$2y$10$6f8JL9F6SqkVJlzEGq8rkeqQJ6Q6CQqafjNpTpZVFUQGD3cGe3K.C', 'John', 'Doe', TRUE);

-- ============================================================================
-- INSERT SAMPLE LESSONS
-- ============================================================================

INSERT INTO lessons (title, description, difficulty, order_num) VALUES
('HTML Basics', 'Learn the fundamental building blocks of HTML', 'beginner', 1),
('Text Formatting', 'Master headings, paragraphs, and text styling', 'beginner', 2),
('Links & Images', 'Work with hyperlinks and image elements', 'beginner', 3),
('Lists & Tables', 'Create organized content with lists and tables', 'intermediate', 4),
('Forms & Inputs', 'Build interactive forms and input fields', 'intermediate', 5),
('Semantic HTML', 'Use modern semantic elements for better structure', 'advanced', 6);

-- ============================================================================
-- INSERT SAMPLE PRACTICE TASKS
-- ============================================================================

INSERT INTO practice_tasks (lesson_id, title, instruction, hint, starter_code, order_num) VALUES
(1, 'Create Your First Button', 
 'Create a button element with the text "Click Me!" and style it with inline CSS to have a blue background and white text.',
 'Use the <button> tag and the style attribute. Try: style="background-color: blue; color: white;"',
 '<!-- Write your button here -->\n\n',
 1),

(1, 'Build a Simple Card',
 'Create a div with a border, padding, and rounded corners. Inside, add a heading and a paragraph.',
 'Use a <div> with inline styles. Try border, padding, and border-radius properties.',
 '<!-- Create your card here -->\n\n',
 2),

(2, 'Heading Hierarchy',
 'Create a page structure using h1, h2, and h3 headings showing "Main Title", "Section", and "Subsection".',
 'Use <h1>, <h2>, and <h3> tags in order. Each heading should be on its own line.',
 '<!-- Create heading hierarchy -->\n\n',
 1),

(2, 'Format a Paragraph',
 'Write a paragraph about your favorite hobby. Make one word bold and one word italic.',
 'Use <strong> for bold and <em> for italic inside a <p> tag.',
 '<p>\n  <!-- Write your paragraph here -->\n</p>\n',
 2),

(3, 'Create a Hyperlink',
 'Create a link to "https://www.google.com" with the text "Search on Google" that opens in a new tab.',
 'Use <a> tag with href and target="_blank" attributes.',
 '<!-- Create your link here -->\n\n',
 1),

(3, 'Add an Image',
 'Add an image with src="https://via.placeholder.com/300x200" and alt text "Placeholder image". Center it with inline CSS.',
 'Use <img> tag with src, alt attributes. Add style="display: block; margin: 0 auto;"',
 '<!-- Add your image here -->\n\n',
 2),

(4, 'Build an Unordered List',
 'Create a shopping list with at least 5 items using an unordered list.',
 'Use <ul> for the list and <li> for each item.',
 '<!-- Create your shopping list -->\n\n',
 1),

(4, 'Create a Simple Table',
 'Build a 3x3 table showing Name, Age, City for three people with a header row.',
 'Use <table>, <thead>, <tbody>, <tr>, <th>, and <td> tags.',
 '<!-- Create your table here -->\n\n',
 2),

(5, 'Build a Contact Form',
 'Create a form with fields for Name (text), Email (email), and a Submit button.',
 'Use <form>, <input> with type attributes, and <label> for accessibility.',
 '<!-- Create your form here -->\n\n',
 1),

(6, 'Semantic Article Structure',
 'Create an article with header, main, and footer sections using semantic HTML5 tags.',
 'Use <article>, <header>, <main>, and <footer> tags.',
 '<!-- Build semantic structure -->\n\n',
 1);

-- ============================================================================
-- INSERT SAMPLE USER PRACTICES
-- ============================================================================

INSERT INTO user_practice (title, html_code, is_completed) VALUES
('My First Button', '<button style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Click Me!</button>', TRUE),
('Practice Card', '<div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; max-width: 300px;">\n  <h2>Card Title</h2>\n  <p>This is my practice card with some content.</p>\n</div>', FALSE);

-- ============================================================================
-- SETUP COMPLETE
-- ============================================================================
-- Default Credentials:
-- Admin:  username: admin    password: codedojo123
-- User:   username: user     password: user123
--
-- Tables Created:
-- 1. admins - Admin user accounts
-- 2. users - Regular user accounts
-- 3. lessons - Learning modules
-- 4. practice_tasks - Coding challenges
-- 5. user_practice - User's saved work
--
-- You can now access your CodeDojo application!
-- ============================================================================
