-- ============================================================================
-- CodeDojo2 Fresh SQL (Clean Bootstrap)
-- Purpose: reliable reset with minimal seed data and correct FK order.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS codedojo2
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE codedojo2;

-- Drop in dependency-safe order
DROP TABLE IF EXISTS user_lesson_badges;
DROP TABLE IF EXISTS user_daily_activity;
DROP TABLE IF EXISTS user_task_progress;
DROP TABLE IF EXISTS user_practice;
DROP TABLE IF EXISTS practice_tasks;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- AUTH
-- ============================================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_admin_username (username),
    INDEX idx_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_user_username (username),
    INDEX idx_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- LEARNING / PRACTICE
-- ============================================================================
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    order_num INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lessons_difficulty (difficulty),
    INDEX idx_lessons_order (order_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_tasks_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_practice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    title VARCHAR(255) NOT NULL,
    html_code MEDIUMTEXT NOT NULL,
    task_id INT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (task_id) REFERENCES practice_tasks(id) ON DELETE SET NULL,
    INDEX idx_user_practice_user (user_id),
    INDEX idx_user_practice_task (task_id),
    INDEX idx_user_practice_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_progress_task (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_daily_activity (
    user_id INT NOT NULL,
    activity_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, activity_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_lesson_badges (
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_badges_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- MINIMAL SEED DATA
-- ============================================================================
-- Admin: admin / codedojo123
INSERT INTO admins (username, email, password, is_active) VALUES
('admin', 'admin@codedojo.local', '$2y$10$E8.GNt8oDDJ2RxwsKxcLEOQgxl/YrvZwTyR/E4T5n2QI/p9YBc8vC', TRUE);

-- User: user / user123
INSERT INTO users (username, email, password, first_name, last_name, is_active) VALUES
('user', 'user@codedojo.local', '$2y$10$6f8JL9F6SqkVJlzEGq8rkeqQJ6Q6CQqafjNpTpZVFUQGD3cGe3K.C', 'John', 'Doe', TRUE);

INSERT INTO lessons (title, description, difficulty, order_num) VALUES
('HTML Basics', 'Core HTML structure and tags', 'beginner', 1);

INSERT INTO practice_tasks (lesson_id, title, instruction, hint, starter_code, order_num) VALUES
(1,
 'Create Your First Button',
 'Create a button element with text "Click Me!" and style it with inline CSS.',
 'Use <button> and a style attribute.',
 '<!-- Write your button here -->\n',
 1);

-- Starter example project
INSERT INTO user_practice (user_id, title, html_code, task_id, is_completed)
VALUES (
    1,
    'Welcome Project',
    '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Welcome</title></head><body><h1>Welcome to CodeDojo2</h1><p>Start coding and save your work.</p></body></html>',
    NULL,
    FALSE
);

-- ============================================================================
-- Done
-- ============================================================================
