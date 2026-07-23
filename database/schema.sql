CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    vendor VARCHAR(50) NOT NULL,
    model VARCHAR(50),
    zone VARCHAR(50),
    ssh_username VARCHAR(50) NOT NULL,
    ssh_password VARCHAR(255) NOT NULL,
    ssh_port INT DEFAULT 22,
    status ENUM('online','offline') DEFAULT 'offline',
    last_sync TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vendor_commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_name VARCHAR(50) NOT NULL,
    create_user_cmd TEXT,
    delete_user_cmd TEXT,
    verify_user_cmd TEXT,
    reset_password_cmd TEXT
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    username_target VARCHAR(50),
    device_id INT,
    action VARCHAR(50),
    result VARCHAR(50),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id),
    FOREIGN KEY (device_id) REFERENCES devices(id)
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT
);

-- টেস্টের জন্য একটা admin ইউজার (username: admin, password: admin123)
INSERT INTO admins (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');