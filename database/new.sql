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
    grading_rules TEXT NULL,
    order_num INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User practice: saved work and attempts
CREATE TABLE user_practice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    title VARCHAR(255) NOT NULL,
    html_code TEXT NOT NULL,
    task_id INT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (task_id) REFERENCES practice_tasks(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_task (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User task progress: attempts, completion, and best score per task
CREATE TABLE user_task_progress (
    user_id INT NOT NULL,
    task_id INT NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    passes INT NOT NULL DEFAULT 0,
    best_score INT NOT NULL DEFAULT 0,
    last_attempt_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, task_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES practice_tasks(id) ON DELETE CASCADE,
    INDEX idx_task_progress_task (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User daily activity: used for streak calculations
CREATE TABLE user_daily_activity (
    user_id INT NOT NULL,
    activity_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, activity_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User lesson badges: lesson mastery completion record
CREATE TABLE user_lesson_badges (
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_badge_lesson (lesson_id)
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
-- ADDITIONAL LESSONS & PRACTICE TASKS (EXPANDED CURRICULUM)
-- ============================================================================

INSERT INTO lessons (title, description, difficulty, order_num) VALUES
('HTML Document Structure', 'Build complete HTML5 document foundations', 'beginner', 7),
('Semantic Text & Accessibility', 'Write readable, accessible text content', 'beginner', 8),
('Navigation & Site Structure', 'Create navigation menus and multi-section layouts', 'beginner', 9),
('Media Embeds', 'Embed audio, video, and external content', 'intermediate', 10),
('Advanced Tables', 'Build structured, accessible data tables', 'intermediate', 11),
('Form Validation Basics', 'Use built-in HTML validation features', 'intermediate', 12),
('Form UX Patterns', 'Improve form usability and clarity', 'intermediate', 13),
('Page Sections & Landmarks', 'Use semantic landmarks for page architecture', 'intermediate', 14),
('Metadata & SEO Basics', 'Improve discoverability with metadata', 'intermediate', 15),
('Responsive HTML Patterns', 'Create HTML structures that adapt well', 'advanced', 16),
('Accessible Components', 'Create components with better accessibility', 'advanced', 17),
('Portfolio Project Tasks', 'Combine concepts into mini project pages', 'advanced', 18);

INSERT INTO practice_tasks (lesson_id, title, instruction, hint, starter_code, order_num) VALUES
(7, 'Create a Valid HTML5 Skeleton',
 'Write a full HTML5 document with doctype, html, head, title, and body.',
 'Start with <!DOCTYPE html> and include the core structural tags.',
 '<!-- Build a full document skeleton -->\n\n',
 1),

(7, 'Add Meta Charset and Viewport',
 'Create the head section with charset and viewport meta tags plus a title.',
 'Use <meta charset="UTF-8"> and viewport content="width=device-width, initial-scale=1.0".',
 '<!-- Create your <head> metadata -->\n\n',
 2),

(7, 'Build a Multi-Section Body',
 'Inside body, add header, main, and footer with sample text.',
 'Use semantic section tags instead of generic divs.',
 '<!-- Add header, main, and footer -->\n\n',
 3),

(8, 'Use Strong and Emphasis Correctly',
 'Write a paragraph with one important word and one emphasized phrase.',
 'Use <strong> for importance and <em> for emphasis.',
 '<p><!-- Write your paragraph here --></p>\n',
 1),

(8, 'Create Accessible Abbreviation',
 'Use an abbreviation tag for HTML and provide its full meaning.',
 'Use <abbr title="HyperText Markup Language">HTML</abbr>.',
 '<!-- Add your abbreviation example -->\n\n',
 2),

(8, 'Mark Up a Quote with Citation',
 'Add a blockquote and a citation line for the source.',
 'Use <blockquote> and <cite> together.',
 '<!-- Add quote and citation -->\n\n',
 3),

(9, 'Build a Navigation Menu',
 'Create a nav section with at least 4 links: Home, About, Lessons, Contact.',
 'Wrap your links inside <nav> and keep link text clear.',
 '<!-- Build your nav menu -->\n\n',
 1),

(9, 'Create Jump Links',
 'Build a mini table of contents with links that jump to sections on the same page.',
 'Use href="#section-id" and matching id attributes.',
 '<!-- Create internal anchor links -->\n\n',
 2),

(9, 'Add Breadcrumb Navigation',
 'Create a breadcrumb trail like Home > Lessons > HTML Basics.',
 'Use nav + ordered list for accessible breadcrumbs.',
 '<!-- Build breadcrumbs -->\n\n',
 3),

(10, 'Embed an Audio Player',
 'Add an audio element with controls and a fallback message.',
 'Use <audio controls> with a nested <source>.',
 '<!-- Add audio player -->\n\n',
 1),

(10, 'Embed a Video Player',
 'Add a video element with controls and poster image placeholder.',
 'Use <video controls width="..."> and include source type.',
 '<!-- Add video player -->\n\n',
 2),

(10, 'Add an Embedded Map Frame',
 'Insert an iframe placeholder for a map and include a meaningful title attribute.',
 'Use <iframe title="..."> for accessibility.',
 '<!-- Add iframe embed -->\n\n',
 3),

(11, 'Create Table Caption and Head',
 'Build a table with caption, thead, tbody, and at least 3 columns.',
 'Use semantic table grouping elements.',
 '<!-- Build structured table -->\n\n',
 1),

(11, 'Use Rowspan and Colspan',
 'Create a small schedule table using rowspan or colspan at least once.',
 'Merge cells with rowspan/colspan where it makes sense.',
 '<!-- Create merged-cell table -->\n\n',
 2),

(11, 'Mark Header Scope',
 'Create a data table with th elements that include proper scope attributes.',
 'Use scope="col" and scope="row" where appropriate.',
 '<!-- Add scoped headers -->\n\n',
 3),

(12, 'Required Form Inputs',
 'Build a form with name and email inputs that are both required.',
 'Use required attribute on each required field.',
 '<!-- Add required inputs -->\n\n',
 1),

(12, 'Pattern Validation',
 'Add a phone input validated by a pattern attribute.',
 'Example pattern: [0-9]{3}-[0-9]{3}-[0-9]{4}',
 '<!-- Add pattern-validated input -->\n\n',
 2),

(12, 'Length Constraints',
 'Add a username input with minlength and maxlength constraints.',
 'Use minlength="4" and maxlength="16" as a starting point.',
 '<!-- Add constrained input -->\n\n',
 3),

(13, 'Label Every Input',
 'Create a form where each input is correctly associated with a label.',
 'Match label for="..." to input id="...".',
 '<!-- Build labeled form -->\n\n',
 1),

(13, 'Group Inputs with Fieldset',
 'Create a form section for contact preferences using fieldset and legend.',
 'Use radio buttons inside a fieldset.',
 '<!-- Add fieldset group -->\n\n',
 2),

(13, 'Use Helpful Placeholder and Help Text',
 'Add one input with placeholder and a small helper description below it.',
 'Use concise placeholder text and a separate hint paragraph.',
 '<!-- Build helper text pattern -->\n\n',
 3),

(14, 'Build a Semantic Landing Structure',
 'Create header, nav, main, aside, and footer sections in one page.',
 'Treat each section as a meaningful page landmark.',
 '<!-- Build semantic layout -->\n\n',
 1),

(14, 'Article with Nested Sections',
 'Create an article containing at least two nested section elements.',
 'Use section headings for structure clarity.',
 '<!-- Build article with sections -->\n\n',
 2),

(14, 'Main Content with Supporting Aside',
 'Build main content and an aside with related links.',
 'Avoid placing primary content inside aside.',
 '<!-- Build main + aside -->\n\n',
 3),

(15, 'Add SEO Meta Description',
 'Create a head block with title and meta description.',
 'Keep the description concise and specific.',
 '<!-- Add title + description meta -->\n\n',
 1),

(15, 'Add Open Graph Basics',
 'Add basic Open Graph tags: og:title, og:description, og:type.',
 'Use <meta property="og:..." content="..."> tags.',
 '<!-- Add Open Graph meta tags -->\n\n',
 2),

(15, 'Canonical Link Tag',
 'Add a canonical URL link element in the head section.',
 'Use <link rel="canonical" href="https://example.com/page">.',
 '<!-- Add canonical link -->\n\n',
 3),

(16, 'Picture Element for Responsive Images',
 'Use picture with at least one source and fallback img.',
 'Provide different source media conditions.',
 '<!-- Build picture element -->\n\n',
 1),

(16, 'Responsive Data Card Markup',
 'Create semantic card markup with image, heading, text, and action link.',
 'Use article for the card container.',
 '<!-- Build responsive card HTML -->\n\n',
 2),

(16, 'Content Priority Structure',
 'Create a page where important content appears first in the HTML order.',
 'Think mobile-first content order in markup.',
 '<!-- Build content-priority markup -->\n\n',
 3),

(17, 'Accessible Button Group',
 'Create a group of action buttons with clear accessible names.',
 'Use explicit text labels and logical grouping.',
 '<!-- Build accessible button group -->\n\n',
 1),

(17, 'Accessible Form Error Message Area',
 'Add an error summary container and link it to an input using aria-describedby.',
 'Use unique ids and readable error text.',
 '<!-- Build accessible error pattern -->\n\n',
 2),

(17, 'Keyboard-Friendly Dialog Markup',
 'Create HTML structure for a dialog with title, content, and close button.',
 'Use role="dialog" and aria-labelledby attributes.',
 '<!-- Build dialog markup -->\n\n',
 3),

(18, 'Portfolio Hero Section',
 'Build a portfolio hero section with name, tagline, and call-to-action links.',
 'Use heading hierarchy and semantic sections.',
 '<!-- Build portfolio hero -->\n\n',
 1),

(18, 'Project Showcase Grid Markup',
 'Create markup for at least 3 project cards with title, description, and link.',
 'Use article elements for each project item.',
 '<!-- Build project showcase -->\n\n',
 2),

(18, 'Contact Section with Form and Social Links',
 'Add a final contact section that includes a simple form and social links list.',
 'Include labels and accessible link text.',
 '<!-- Build contact section -->\n\n',
 3);
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

