-- ICTECH Solutions Limited - Database Schema
-- MySQL Database for Training Management Platform

-- Users table (students and admins)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description LONGTEXT,
    objectives LONGTEXT,
    requirements LONGTEXT,
    category_id INT NOT NULL,
    price DECIMAL(10, 2) DEFAULT 0,
    duration VARCHAR(100),
    image VARCHAR(255),
    status ENUM('published', 'draft', 'archived') DEFAULT 'draft',
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enrollments table
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    INDEX idx_user_id (user_id),
    INDEX idx_course_id (course_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    enrollment_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    method ENUM('mpesa', 'card', 'bank') DEFAULT 'mpesa',
    status ENUM('pending', 'paid', 'failed', 'cancelled') DEFAULT 'pending',
    reference VARCHAR(255),
    checkout_request_id VARCHAR(255),
    merchant_request_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_reference (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials table
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(255),
    message LONGTEXT NOT NULL,
    photo VARCHAR(255),
    type ENUM('student', 'trainer') DEFAULT 'student',
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training Partners table
CREATE TABLE partners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    logo VARCHAR(255),
    website VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact Messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message LONGTEXT NOT NULL,
    status ENUM('new', 'read', 'archived') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA FOR DEVELOPMENT
-- ============================================

-- Insert sample admin account
-- Username: admin@ictech.local | Password: Admin@123
INSERT INTO users (name, email, phone, password, role, status) VALUES
('Admin User', 'admin@ictech.local', '+254712345678', '$2y$10$vI7.Ej4gLlZ1qV9ZY5S0euK0kZQTIFbF.YzKqY9oKfK5pK5KmY5mS', 'admin', 'active');

-- Insert sample student accounts
INSERT INTO users (name, email, phone, password, role, status) VALUES
('John Kamau', 'john.kamau@email.com', '+254723456789', '$2y$10$vI7.Ej4gLlZ1qV9ZY5S0euK0kZQTIFbF.YzKqY9oKfK5pK5KmY5mS', 'student', 'active'),
('Mary Kipchoge', 'mary.kipchoge@email.com', '+254734567890', '$2y$10$vI7.Ej4gLlZ1qV9ZY5S0euK0kZQTIFbF.YzKqY9oKfK5pK5KmY5mS', 'student', 'active'),
('Peter Ochieng', 'peter.ochieng@email.com', '+254745678901', '$2y$10$vI7.Ej4gLlZ1qV9ZY5S0euK0kZQTIFbF.YzKqY9oKfK5pK5KmY5mS', 'student', 'active');

-- Insert categories
INSERT INTO categories (name, slug) VALUES
('Web Development', 'web-development'),
('Mobile Development', 'mobile-development'),
('Data Science', 'data-science'),
('Cloud Computing', 'cloud-computing'),
('Cybersecurity', 'cybersecurity'),
('Business & Management', 'business-management');

-- Insert sample courses
INSERT INTO courses (title, slug, description, objectives, requirements, category_id, price, duration, status, is_featured) VALUES
('PHP Web Development Fundamentals', 'php-web-development', 
'Learn to build dynamic websites using PHP. This comprehensive course covers PHP basics, database integration, and best practices for web development.',
'Understand PHP syntax; Build database-driven websites; Implement secure authentication; Work with forms and sessions',
'Basic HTML/CSS knowledge; Computer with PHP installed; Text editor or IDE',
1, 4500, '8 weeks', 'published', TRUE),

('Advanced MySQL Database Design', 'advanced-mysql',
'Master database design, optimization, and management using MySQL. Perfect for aspiring database administrators and developers.',
'Design normalized databases; Write efficient queries; Implement indexing; Backup and recovery strategies',
'Basic SQL knowledge; Understanding of data structures',
1, 3500, '6 weeks', 'published', TRUE),

('JavaScript ES6+ Modern JavaScript', 'javascript-es6',
'Comprehensive guide to modern JavaScript with ES6+, async/await, and best practices. Build interactive web applications.',
'Master ES6 syntax; Understand async programming; Work with APIs; Build responsive interfaces',
'Basic programming knowledge; Familiarity with HTML/CSS',
1, 4000, '8 weeks', 'published', TRUE),

('React.js for Frontend Development', 'reactjs-frontend',
'Build modern, scalable user interfaces with React. Learn components, hooks, state management, and deployment.',
'Build React applications; Manage state effectively; Create reusable components; Deploy React apps',
'JavaScript fundamentals; Understanding of HTML/CSS',
1, 5000, '10 weeks', 'published', TRUE),

('Flutter Mobile App Development', 'flutter-mobile-dev',
'Create beautiful, natively compiled applications for mobile, web, and desktop using Flutter and Dart.',
'Create Flutter applications; Design mobile UIs; Integrate APIs; Deploy to Play Store and App Store',
'Dart programming basics; Understanding of mobile development concepts',
2, 5500, '12 weeks', 'published', TRUE),

('Data Science with Python', 'data-science-python',
'Learn data analysis, visualization, and machine learning using Python. Industry-ready skills for data professionals.',
'Analyze data using pandas; Create visualizations; Build ML models; Work with real datasets',
'Python basics; Mathematical foundation; Statistics knowledge helpful',
3, 6000, '12 weeks', 'published', TRUE);

-- Insert testimonials
INSERT INTO testimonials (name, role, message, type, is_featured) VALUES
('James Kariuki', 'Software Developer', 'ICTECH training transformed my career. The practical approach and experienced instructors made learning enjoyable and effective.', 'student', TRUE),
('Sarah Mwangi', 'Full Stack Developer', 'The best investment I made was in ICTECH courses. I went from beginner to professional developer in 6 months.', 'student', TRUE),
('Michael Ouma', 'IT Consultant', 'Excellent curriculum, professional instructors, and real-world projects. Highly recommended for anyone serious about tech careers.', 'student', TRUE);

-- Insert training partners
INSERT INTO partners (name, logo, status) VALUES
('Microsoft Azure', 'microsoft-azure.png', 'active'),
('Google Cloud', 'google-cloud.png', 'active'),
('Amazon AWS', 'amazon-aws.png', 'active'),
('Safaricom', 'safaricom.png', 'active'),
('Airtel Kenya', 'airtel.png', 'active');
