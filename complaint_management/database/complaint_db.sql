-- ============================================================
-- ComplainX - Complaint Management System
-- Database: complaint_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS complaint_db;
USE complaint_db;

-- ----------------------------------------------------------
-- TABLE: users
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('user','admin','agent') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------------------------------------
-- TABLE: categories
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- ----------------------------------------------------------
-- TABLE: complaints
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_no VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    description TEXT NOT NULL,
    attachment VARCHAR(255) DEFAULT NULL,
    priority ENUM('Low','Medium','High') DEFAULT 'Medium',
    status ENUM('Pending','In Progress','Resolved','Closed') DEFAULT 'Pending',
    agent_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ----------------------------------------------------------
-- TABLE: remarks
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS remarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ----------------------------------------------------------
-- SEED DATA
-- ----------------------------------------------------------

-- Default Categories
INSERT INTO categories (name) VALUES
('Technical'),
('Billing'),
('Infrastructure'),
('General'),
('Academic'),
('Hostel');

-- Default Admin user (password: admin123)
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin User', 'admin@complainx.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9999999999', 'admin');

-- Default Agent (password: agent123)
INSERT INTO users (name, email, password, phone, role) VALUES
('Ravi Kumar', 'ravi@complainx.com', '$2y$10$TKh8H1.PjcXqa48XzTHQCOQVQLW1oFx2GSMbexDGRuV3m3Fz2P2b2', '8888888888', 'agent'),
('Priya Singh', 'priya@complainx.com', '$2y$10$TKh8H1.PjcXqa48XzTHQCOQVQLW1oFx2GSMbexDGRuV3m3Fz2P2b2', '7777777777', 'agent');

-- Default Student (password: user123)
INSERT INTO users (name, email, password, phone, role) VALUES
('Arjun Joshi', 'arjun@student.com', '$2y$10$BeXMDcHKizHNaBjTlD9Zve8EXJoXeVp.p00WcmQXD5FD3dEjRg5LS', '6666666666', 'user');

-- Sample Complaints
INSERT INTO complaints (complaint_no, user_id, title, category_id, description, priority, status, agent_id, created_at) VALUES
('CMP-001', 4, 'Internet not working in lab', 1, 'The internet has been down in Computer Lab 2 for 3 days. Students are unable to complete assignments.', 'High', 'Resolved', 2, '2025-02-10 09:00:00'),
('CMP-002', 4, 'Fee receipt not generated', 2, 'After paying the semester fee online, I did not receive the receipt. The portal shows payment done.', 'Medium', 'In Progress', 3, '2025-02-14 10:30:00'),
('CMP-003', 4, 'Hostel room light issue', 3, 'The light in Room 204 has been flickering and now completely stopped working. Facing issues at night.', 'Low', 'Pending', NULL, '2025-02-18 11:00:00'),
('CMP-004', 4, 'Library book not returned properly', 4, 'A book I returned last week is still showing as issued to me in the library system.', 'Medium', 'Pending', NULL, '2025-02-20 14:00:00'),
('CMP-005', 4, 'Projector broken in Room 3B', 1, 'The projector in Room 3B is not working. It shows no signal. Teachers are not able to conduct proper classes.', 'High', 'In Progress', 2, '2025-02-22 09:30:00'),
('CMP-006', 4, 'Canteen food quality complaint', 4, 'The food quality in the canteen has deteriorated significantly. Multiple students have complained about this.', 'Low', 'Closed', 3, '2025-02-05 08:00:00');

-- Sample Remarks
INSERT INTO remarks (complaint_id, user_id, comment) VALUES
(1, 1, 'Assigned to Ravi Kumar. Network team is investigating.'),
(1, 2, 'Issue identified — faulty router. Replacement ordered.'),
(1, 1, 'Router replaced. Internet is now functional. Marking as Resolved.'),
(2, 1, 'Assigned to Priya Singh from Finance team.'),
(2, 3, 'Looking into the payment gateway records.'),
(5, 2, 'Projector sent for repair. Expected back in 2 days.');
