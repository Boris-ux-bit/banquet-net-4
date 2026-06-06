CREATE DATABASE banquet_net;
USE banquet_net;
-- Таблица пользователей
CREATE TABLE users (
id INT PRIMARY KEY AUTO_INCREMENT,
login VARCHAR(50) UNIQUE NOT NULL,
password VARCHAR(255) NOT NULL,
full_name VARCHAR(100) NOT NULL,
phone VARCHAR(20) NOT NULL,
email VARCHAR(100) NOT NULL,
role ENUM('user','admin') DEFAULT 'user',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Таблица помещений
CREATE TABLE rooms (
id INT PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(100) NOT NULL,
type VARCHAR(50) NOT NULL,
capacity INT,
price_per_hour DECIMAL(10,2)
);
-- Таблица заявок
CREATE TABLE bookings (
id INT PRIMARY KEY AUTO_INCREMENT,
user_id INT NOT NULL,
room_id INT NOT NULL,
event_date DATE NOT NULL,
payment_method VARCHAR(50) NOT NULL,
status ENUM('Новая','Банкет назначен','Банкет завершен') DEFAULT 'Новая',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (room_id) REFERENCES rooms(id)
);
-- Таблица отзывов
CREATE TABLE reviews (
id INT PRIMARY KEY AUTO_INCREMENT,
booking_id INT NOT NULL,
user_id INT NOT NULL,
rating INT CHECK (rating BETWEEN 1 AND 5),
comment TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
FOREIGN KEY (user_id) REFERENCES users(id)
);
-- Пароль 'Demo20' в хешированном виде (например, для PHP - password_hash)
INSERT INTO users (login, password, full_name, phone, email, role)
VALUES ('Admin26', '$2y$10$...', 'Administrator', '0000000000', 'admin@banquet.com',
'admin');
