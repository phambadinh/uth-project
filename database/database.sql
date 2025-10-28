-- =============================================
-- UTH Learning Management System Database
-- Version: 2.0 - Complete Edition
-- Author: UTH Team
-- Date: 2025-10-16
-- 
-- Bao gồm:
-- - Core tables (users, courses, lessons, etc.)
-- - Video Lessons features
-- - Live Quiz System
-- - Payment System (VNPay, Momo)
-- - Email Notifications
-- - Real-time Chat
-- - Certificate Generation
-- - Progress Tracking & Recommendation
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS uth_learning CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uth_learning;


-- =============================================
-- CORE TABLES
-- =============================================

-- TABLE: users
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'student', 'instructor') DEFAULT 'student',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User accounts';


-- TABLE: courses
DROP TABLE IF EXISTS courses;
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    instructor_id INT,
    duration VARCHAR(50),
    level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    category VARCHAR(50),
    students INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_level (level),
    INDEX idx_status (status),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Course information';


-- TABLE: lessons
DROP TABLE IF EXISTS lessons;
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    content TEXT,
    video_url VARCHAR(500),
    video_type ENUM('youtube', 'vimeo', 'upload', 'external') DEFAULT 'youtube',
    video_duration INT DEFAULT 0 COMMENT 'Duration in seconds',
    watched BOOLEAN DEFAULT FALSE,
    order_num INT DEFAULT 0,
    duration INT DEFAULT 0,
    is_free BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course_id (course_id),
    INDEX idx_order (order_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Course lessons';


-- TABLE: exercises
DROP TABLE IF EXISTS exercises;
CREATE TABLE exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    lesson_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    hint TEXT,
    answer TEXT,
    points INT DEFAULT 0,
    type ENUM('MCQ', 'Code', 'Essay', 'TrueFalse') DEFAULT 'Essay',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    INDEX idx_course_id (course_id),
    INDEX idx_lesson_id (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Course exercises';


-- TABLE: enrollments
DROP TABLE IF EXISTS enrollments;
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    progress DECIMAL(5,2) DEFAULT 0.00,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_accessed TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    INDEX idx_user_id (user_id),
    INDEX idx_course_id (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User course enrollments';


-- TABLE: submissions
DROP TABLE IF EXISTS submissions;
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercise_id INT NOT NULL,
    answer TEXT,
    score INT DEFAULT 0,
    feedback TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_exercise_id (exercise_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Exercise submissions';


-- TABLE: certificates
DROP TABLE IF EXISTS certificates;
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_code VARCHAR(50) UNIQUE NOT NULL,
    issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pdf_url VARCHAR(500),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_certificate (user_id, course_id),
    INDEX idx_code (certificate_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Course completion certificates';


-- =============================================
-- VIDEO LESSONS FEATURES
-- =============================================

-- TABLE: video_progress
DROP TABLE IF EXISTS video_progress;
CREATE TABLE video_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    progress_seconds INT DEFAULT 0,
    total_duration INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (user_id, lesson_id),
    INDEX idx_user (user_id),
    INDEX idx_lesson (lesson_id),
    INDEX idx_completed (completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Video watching progress';


-- =============================================
-- LIVE QUIZ SYSTEM
-- =============================================

-- TABLE: quizzes
DROP TABLE IF EXISTS quizzes;
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    lesson_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    duration INT DEFAULT 30 COMMENT 'Duration in minutes',
    pass_score INT DEFAULT 70 COMMENT 'Passing score percentage',
    max_attempts INT DEFAULT 3,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    INDEX idx_course (course_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Quiz definitions';


-- TABLE: quiz_questions
DROP TABLE IF EXISTS quiz_questions;
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'fill_blank') DEFAULT 'multiple_choice',
    points INT DEFAULT 1,
    order_num INT DEFAULT 0,
    explanation TEXT,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id),
    INDEX idx_order (order_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Quiz questions';


-- TABLE: quiz_options
DROP TABLE IF EXISTS quiz_options;
CREATE TABLE quiz_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Quiz answer options';


-- TABLE: quiz_attempts
DROP TABLE IF EXISTS quiz_attempts;
CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score DECIMAL(5,2),
    total_questions INT,
    correct_answers INT,
    time_spent INT COMMENT 'Time in seconds',
    passed BOOLEAN DEFAULT FALSE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_quiz (quiz_id),
    INDEX idx_passed (passed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User quiz attempts';


-- TABLE: quiz_answers
DROP TABLE IF EXISTS quiz_answers;
CREATE TABLE quiz_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT,
    answer_text TEXT,
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id) REFERENCES quiz_options(id) ON DELETE SET NULL,
    INDEX idx_attempt (attempt_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User quiz answers';


-- =============================================
-- PAYMENT SYSTEM
-- =============================================

-- TABLE: payments
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    payment_method ENUM('vnpay', 'momo', 'bank_transfer', 'free') DEFAULT 'vnpay',
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_data TEXT COMMENT 'JSON data from payment gateway',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_transaction (transaction_id),
    INDEX idx_status (status),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Payment transactions';


-- TABLE: orders
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_code VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_order_code (order_code),
    INDEX idx_status (status),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Payment orders';


-- TABLE: order_items
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    course_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Order line items';


-- =============================================
-- EMAIL NOTIFICATIONS
-- =============================================

-- TABLE: email_queue
DROP TABLE IF EXISTS email_queue;
CREATE TABLE email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(100) NOT NULL,
    to_name VARCHAR(100),
    subject VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    template VARCHAR(50),
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_email (to_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Email notification queue';


-- =============================================
-- REAL-TIME CHAT
-- =============================================

-- TABLE: chat_conversations
DROP TABLE IF EXISTS chat_conversations;
CREATE TABLE chat_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation (user1_id, user2_id),
    INDEX idx_user1 (user1_id),
    INDEX idx_user2 (user2_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Chat conversations';


-- TABLE: chat_messages
DROP TABLE IF EXISTS chat_messages;
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Chat messages';


-- TABLE: support_tickets
DROP TABLE IF EXISTS support_tickets;
CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    category ENUM('technical', 'billing', 'course', 'other') DEFAULT 'other',
    status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Support tickets';


-- TABLE: ticket_replies
DROP TABLE IF EXISTS ticket_replies;
CREATE TABLE ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_staff BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Support ticket replies';


-- =============================================
-- INSERT SAMPLE DATA
-- =============================================

-- Users (Password: 123456 for all accounts)
INSERT INTO users (username, password, fullname, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@uth.edu.vn', 'admin'),
('VANLONG123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Long', 'vanlong@student.uth.edu.vn', 'student'),
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Mai', 'mai@student.uth.edu.vn', 'student'),
('instructor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', 'instructor@uth.edu.vn', 'instructor');


-- Courses
INSERT INTO courses (title, slug, description, instructor_id, duration, level, category, students, rating, total_reviews, price, status) VALUES
('HTML & CSS Fundamentals', 'html-css-fundamentals', 'Học HTML & CSS từ cơ bản đến nâng cao. Xây dựng website responsive đẹp mắt.', 4, '4 tuần', 'Beginner', 'HTML', 1234, 4.8, 230, 0, 'published'),
('JavaScript Complete Guide', 'javascript-complete', 'Khóa học JavaScript toàn diện. Từ cơ bản đến ES6+ và các framework.', 4, '6 tuần', 'Intermediate', 'JavaScript', 2341, 4.9, 450, 299000, 'published'),
('Python for Beginners', 'python-beginners', 'Học Python từ đầu. Phù hợp cho người mới bắt đầu lập trình.', 4, '8 tuần', 'Beginner', 'Python', 3452, 4.9, 520, 0, 'published'),
('PHP Web Development', 'php-web-development', 'Phát triển website với PHP & MySQL. Xây dựng ứng dụng web động.', 4, '6 tuần', 'Intermediate', 'PHP', 1876, 4.6, 180, 399000, 'published'),
('React.js Complete Course', 'reactjs-complete', 'Học React.js từ cơ bản đến nâng cao. Xây dựng Single Page Application.', 4, '10 tuần', 'Advanced', 'React', 2156, 4.7, 320, 499000, 'published'),
('Node.js Backend Development', 'nodejs-backend', 'Xây dựng RESTful API với Node.js & Express. MongoDB & JWT.', 4, '8 tuần', 'Advanced', 'Node.js', 1543, 4.8, 290, 549000, 'published');


-- Lessons
INSERT INTO lessons (course_id, title, slug, content, video_url, video_type, video_duration, order_num, duration, is_free) VALUES
(1, 'Giới thiệu về HTML', 'intro-html', 'HTML là ngôn ngữ đánh dấu để tạo cấu trúc trang web...', 'https://www.youtube.com/watch?v=pQN-pnXPaVg', 'youtube', 1800, 1, 30, TRUE),
(1, 'Các thẻ HTML cơ bản', 'basic-html-tags', 'Học về các thẻ như div, p, h1-h6, a, img...', 'https://www.youtube.com/watch?v=MDLn5-zSQQI', 'youtube', 2700, 2, 45, TRUE),
(1, 'HTML Forms', 'html-forms', 'Tạo form nhập liệu với các thẻ input, select, textarea...', 'https://www.youtube.com/watch?v=fNcJuPIZ2WE', 'youtube', 3600, 3, 60, FALSE),
(2, 'JavaScript Basics', 'js-basics', 'Biến, kiểu dữ liệu, toán tử trong JavaScript...', 'https://www.youtube.com/watch?v=W6NZfCO5SIk', 'youtube', 3000, 1, 50, TRUE),
(2, 'Functions và Scope', 'functions-scope', 'Khai báo và sử dụng functions trong JavaScript...', 'https://www.youtube.com/watch?v=N8ap4k_1QEQ', 'youtube', 3300, 2, 55, FALSE);


-- Exercises
INSERT INTO exercises (course_id, title, description, hint, answer, points, type) VALUES
(1, 'Tạo trang HTML đầu tiên', 'Tạo một trang HTML với tiêu đề "Hello World"', 'Sử dụng thẻ <h1> và <title>', '<!DOCTYPE html><html><head><title>Hello World</title></head><body><h1>Hello World</h1></body></html>', 10, 'Code'),
(1, 'HTML Quiz', 'Thẻ nào dùng để tạo liên kết?', 'Có 4 đáp án', '<a>', 5, 'MCQ'),
(2, 'JavaScript Variables', 'Khai báo biến và gán giá trị', 'Sử dụng let hoặc const', 'let x = 10;', 10, 'Code');


-- Enrollments
INSERT INTO enrollments (user_id, course_id, progress, last_accessed) VALUES
(2, 1, 45.5, NOW()),
(2, 2, 20.0, NOW()),
(3, 1, 80.0, NOW());


-- Submissions
INSERT INTO submissions (user_id, exercise_id, answer, score) VALUES
(2, 1, '<!DOCTYPE html><html><head><title>My Page</title></head><body><h1>Hello</h1></body></html>', 8),
(2, 2, '<a>', 5);


-- Sample Quiz Data
INSERT INTO quizzes (course_id, title, description, duration, pass_score, max_attempts, is_active) VALUES
(1, 'HTML Basics Quiz', 'Test your HTML knowledge with this beginner quiz', 15, 70, 3, TRUE);

SET @quiz_id = LAST_INSERT_ID();

-- Quiz Questions
INSERT INTO quiz_questions (quiz_id, question, question_type, points, order_num) VALUES
(@quiz_id, 'HTML stands for?', 'multiple_choice', 1, 1),
(@quiz_id, 'Is HTML a programming language?', 'true_false', 1, 2),
(@quiz_id, 'What is the correct HTML tag for the largest heading?', 'multiple_choice', 1, 3);

-- Get question IDs
SET @q1_id = (SELECT id FROM quiz_questions WHERE quiz_id = @quiz_id AND order_num = 1);
SET @q2_id = (SELECT id FROM quiz_questions WHERE quiz_id = @quiz_id AND order_num = 2);
SET @q3_id = (SELECT id FROM quiz_questions WHERE quiz_id = @quiz_id AND order_num = 3);

-- Quiz Options
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(@q1_id, 'Hyper Text Markup Language', TRUE),
(@q1_id, 'High Tech Modern Language', FALSE),
(@q1_id, 'Home Tool Markup Language', FALSE),
(@q1_id, 'Hyperlinks and Text Markup Language', FALSE),
(@q2_id, 'false', TRUE),
(@q2_id, 'true', FALSE),
(@q3_id, '<h1>', TRUE),
(@q3_id, '<h6>', FALSE),
(@q3_id, '<heading>', FALSE),
(@q3_id, '<head>', FALSE);


-- =============================================
-- VIEWS & STORED PROCEDURES
-- =============================================

-- View: Course Statistics
CREATE OR REPLACE VIEW course_stats AS
SELECT 
    c.id,
    c.title,
    c.category,
    c.level,
    c.price,
    COUNT(DISTINCT e.user_id) as total_enrollments,
    COUNT(DISTINCT l.id) as total_lessons,
    COUNT(DISTINCT ex.id) as total_exercises,
    COUNT(DISTINCT q.id) as total_quizzes,
    c.rating as avg_rating,
    c.students
FROM courses c
LEFT JOIN enrollments e ON c.id = e.course_id
LEFT JOIN lessons l ON c.id = l.course_id
LEFT JOIN exercises ex ON c.id = ex.course_id
LEFT JOIN quizzes q ON c.id = q.course_id
WHERE c.status = 'published'
GROUP BY c.id;


-- Stored Procedure: Get User Progress
DROP PROCEDURE IF EXISTS GetUserProgress;
DELIMITER //
CREATE PROCEDURE GetUserProgress(IN userId INT)
BEGIN
    SELECT 
        c.id as course_id,
        c.title,
        c.category,
        c.thumbnail,
        e.progress,
        e.enrolled_at,
        e.last_accessed,
        e.completed_at,
        CASE 
            WHEN e.progress >= 100 THEN 'completed'
            WHEN e.progress > 0 THEN 'in_progress'
            ELSE 'not_started'
        END as status
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = userId
    ORDER BY e.last_accessed DESC;
END //
DELIMITER ;


-- Stored Procedure: Get Course Details
DROP PROCEDURE IF EXISTS GetCourseDetails;
DELIMITER //
CREATE PROCEDURE GetCourseDetails(IN courseId INT)
BEGIN
    -- Course info
    SELECT 
        c.*,
        u.fullname as instructor_name,
        u.avatar as instructor_avatar,
        COUNT(DISTINCT e.user_id) as enrolled_count,
        COUNT(DISTINCT l.id) as lesson_count,
        COUNT(DISTINCT ex.id) as exercise_count
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN exercises ex ON c.id = ex.course_id
    WHERE c.id = courseId
    GROUP BY c.id;
    
    -- Course lessons
    SELECT * FROM lessons WHERE course_id = courseId ORDER BY order_num ASC;
    
    -- Course exercises
    SELECT * FROM exercises WHERE course_id = courseId ORDER BY id ASC;
    
    -- Course quizzes
    SELECT * FROM quizzes WHERE course_id = courseId AND is_active = TRUE ORDER BY id ASC;
END //
DELIMITER ;


-- =============================================
-- INDEXES FOR PERFORMANCE
-- =============================================

-- Additional indexes for better query performance
CREATE INDEX idx_enrollments_progress ON enrollments(progress);
CREATE INDEX idx_lessons_video_type ON lessons(video_type);
CREATE INDEX idx_courses_price ON courses(price);
CREATE INDEX idx_courses_rating ON courses(rating);
CREATE INDEX idx_video_progress_completed ON video_progress(completed);
CREATE INDEX idx_quiz_attempts_score ON quiz_attempts(score);


-- =============================================
-- TRIGGERS
-- =============================================

-- Trigger: Update course students count
DROP TRIGGER IF EXISTS after_enrollment_insert;
DELIMITER //
CREATE TRIGGER after_enrollment_insert
AFTER INSERT ON enrollments
FOR EACH ROW
BEGIN
    UPDATE courses 
    SET students = students + 1 
    WHERE id = NEW.course_id;
END //
DELIMITER ;


-- Trigger: Update last_accessed on enrollment
DROP TRIGGER IF EXISTS before_enrollment_update;
DELIMITER //
CREATE TRIGGER before_enrollment_update
BEFORE UPDATE ON enrollments
FOR EACH ROW
BEGIN
    IF NEW.progress > OLD.progress THEN
        SET NEW.last_accessed = NOW();
    END IF;
    
    IF NEW.progress >= 100 AND OLD.progress < 100 THEN
        SET NEW.completed_at = NOW();
    END IF;
END //
DELIMITER ;


-- =============================================
-- FINISH
-- =============================================

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- Summary
SELECT 'Database setup completed successfully!' as Status;
SELECT COUNT(*) as TotalTables FROM information_schema.tables WHERE table_schema = 'uth_learning';
SELECT 
    (SELECT COUNT(*) FROM users) as Users,
    (SELECT COUNT(*) FROM courses) as Courses,
    (SELECT COUNT(*) FROM lessons) as Lessons,
    (SELECT COUNT(*) FROM enrollments) as Enrollments,
    (SELECT COUNT(*) FROM quizzes) as Quizzes;

-- End of SQL file
-- ================================================
-- UTH LEARNING DATABASE - FULL SCHEMA
-- ================================================

CREATE DATABASE IF NOT EXISTS uth_learning 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE uth_learning;

-- ================================================
-- TABLE: users
-- ================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    role ENUM('student', 'instructor', 'admin') DEFAULT 'student',
    avatar VARCHAR(255) DEFAULT '/assets/images/avatars/default.png',
    bio TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: courses
-- ================================================
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255) DEFAULT '/assets/images/courses/default.jpg',
    instructor_id INT,
    category VARCHAR(50) NOT NULL,
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    duration VARCHAR(50),
    price DECIMAL(10,2) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    total_reviews INT DEFAULT 0,
    students INT DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: lessons
-- ================================================
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_url VARCHAR(500),
    video_type ENUM('youtube', 'vimeo', 'local') DEFAULT 'youtube',
    duration INT DEFAULT 0 COMMENT 'Duration in minutes',
    order_num INT DEFAULT 0,
    is_free BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_order (order_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: enrollments
-- ================================================
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    progress DECIMAL(5,2) DEFAULT 0,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_accessed TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    INDEX idx_user (user_id),
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: video_progress
-- ================================================
CREATE TABLE video_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    progress_seconds INT DEFAULT 0,
    total_duration INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (user_id, lesson_id),
    INDEX idx_user (user_id),
    INDEX idx_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: quizzes
-- ================================================
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT DEFAULT 30 COMMENT 'Duration in minutes',
    pass_score INT DEFAULT 70,
    max_attempts INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: quiz_questions
-- ================================================
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    points INT DEFAULT 1,
    order_num INT DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: quiz_options
-- ================================================
CREATE TABLE quiz_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: quiz_attempts
-- ================================================
CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score DECIMAL(5,2) DEFAULT 0,
    total_questions INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    time_spent INT DEFAULT 0 COMMENT 'Time in seconds',
    passed BOOLEAN DEFAULT FALSE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_quiz (quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: certificates
-- ================================================
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_code VARCHAR(50) UNIQUE NOT NULL,
    issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_certificate (user_id, course_id),
    INDEX idx_code (certificate_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: payments
-- ================================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('vnpay', 'momo', 'bank_transfer') NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: course_reviews
-- ================================================
CREATE TABLE course_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, course_id),
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: chat_conversations
-- ================================================
CREATE TABLE chat_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_message_at TIMESTAMP NULL,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation (user1_id, user2_id),
    INDEX idx_last_message (last_message_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: chat_messages
-- ================================================
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABLE: lesson_notes
-- ================================================
CREATE TABLE lesson_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_note (user_id, lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- INSERT SAMPLE DATA
-- ================================================

-- Admin user (password: admin123)
INSERT INTO users (username, email, password, fullname, role) VALUES
('admin', 'admin@uth.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Student users (password: 123456)
INSERT INTO users (username, email, password, fullname, role) VALUES
('nguyenvana', 'nguyenvana@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', 'student'),
('tranthib', 'tranthib@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', 'student');

-- Instructor (password: 123456)
INSERT INTO users (username, email, password, fullname, role, bio) VALUES
('giangvien1', 'giangvien@uth.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn Giảng', 'instructor', 'Giảng viên lập trình với 10 năm kinh nghiệm');

-- Sample courses
INSERT INTO courses (title, slug, description, thumbnail, instructor_id, category, level, duration, price, rating, students, status) VALUES
('HTML Cơ Bản - Xây Dựng Website Từ Đầu', 'html-co-ban', 'Học HTML từ cơ bản đến nâng cao, xây dựng website hoàn chỉnh', '/assets/images/courses/html-course.jpg', 4, 'Programming', 'beginner', '8 tuần', 0, 4.8, 1250, 'published'),
('CSS Master - Thiết Kế Web Chuyên Nghiệp', 'css-master', 'Làm chủ CSS, Flexbox, Grid và các kỹ thuật thiết kế hiện đại', '/assets/images/courses/css-course.jpg', 4, 'Programming', 'intermediate', '10 tuần', 299000, 4.9, 980, 'published'),
('JavaScript Từ Cơ Bản Đến Nâng Cao', 'javascript-co-ban', 'Học JavaScript ES6+, DOM, Event Handling và Async Programming', '/assets/images/courses/javascript-course.jpg', 4, 'Programming', 'beginner', '12 tuần', 499000, 4.7, 1520, 'published'),
('PHP & MySQL - Lập Trình Web Backend', 'php-mysql', 'Xây dựng ứng dụng web động với PHP và MySQL', '/assets/images/courses/php-course.jpg', 4, 'Programming', 'intermediate', '14 tuần', 599000, 4.6, 850, 'published'),
('Python Cho Người Mới Bắt Đầu', 'python-beginner', 'Học Python từ zero, cú pháp cơ bản và lập trình hướng đối tượng', '/assets/images/courses/python-course.jpg', 4, 'Programming', 'beginner', '10 tuần', 0, 4.9, 2100, 'published'),
('C++ Lập Trình Hệ Thống', 'cpp-programming', 'Học C++ và các kỹ thuật lập trình hệ thống chuyên sâu', '/assets/images/courses/cpp-course.jpg', 4, 'Programming', 'advanced', '16 tuần', 799000, 4.5, 450, 'published');

-- Sample lessons for HTML course
INSERT INTO lessons (course_id, title, description, video_url, video_type, duration, order_num, is_free) VALUES
(1, 'Giới thiệu về HTML', 'Tìm hiểu HTML là gì và vai trò của nó trong web development', 'dQw4w9WgXcQ', 'youtube', 15, 1, TRUE),
(1, 'Cấu trúc cơ bản của HTML', 'Học về cấu trúc document HTML và các thẻ cơ bản', 'dQw4w9WgXcQ', 'youtube', 20, 2, TRUE),
(1, 'Làm việc với Text và Links', 'Định dạng text và tạo liên kết trong HTML', 'dQw4w9WgXcQ', 'youtube', 25, 3, FALSE),
(1, 'HTML Forms và Input', 'Tạo form thu thập dữ liệu người dùng', 'dQw4w9WgXcQ', 'youtube', 30, 4, FALSE);

-- Sample lessons for CSS course
INSERT INTO lessons (course_id, title, description, video_url, video_type, duration, order_num, is_free) VALUES
(2, 'CSS Basics', 'Giới thiệu CSS và cách sử dụng', 'dQw4w9WgXcQ', 'youtube', 18, 1, TRUE),
(2, 'CSS Selectors', 'Các loại selector trong CSS', 'dQw4w9WgXcQ', 'youtube', 22, 2, FALSE),
(2, 'CSS Flexbox', 'Layout hiện đại với Flexbox', 'dQw4w9WgXcQ', 'youtube', 35, 3, FALSE);

-- Sample enrollment
INSERT INTO enrollments (user_id, course_id, progress, last_accessed) VALUES
(2, 1, 25, NOW()),
(2, 5, 60, NOW()),
(3, 1, 10, NOW());

-- Sample quiz
INSERT INTO quizzes (course_id, title, description, duration, pass_score) VALUES
(1, 'Kiểm tra HTML Cơ Bản', 'Bài kiểm tra kiến thức HTML cơ bản', 30, 70);

-- Sample quiz questions
INSERT INTO quiz_questions (quiz_id, question, points, order_num) VALUES
(1, 'HTML là viết tắt của gì?', 1, 1),
(1, 'Thẻ nào dùng để tạo tiêu đề lớn nhất?', 1, 2);

-- Sample quiz options
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(1, 'HyperText Markup Language', TRUE),
(1, 'High Tech Modern Language', FALSE),
(1, 'Home Tool Markup Language', FALSE),
(2, '<h1>', TRUE),
(2, '<heading>', FALSE),
(2, '<h6>', FALSE);

-- ================================================
-- DONE!
-- ================================================
