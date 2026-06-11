-- =====================================================
-- БАЗА ДАННЫХ ДЛЯ ПОРТАЛА «Банкетам.Нет»
-- Вариант №4 (полностью исправленная версия)
-- =====================================================

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS banquet_net;
USE banquet_net;

-- =====================================================
-- 1. ТАБЛИЦА users (пользователи)
-- =====================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. ТАБЛИЦА rooms (помещения для банкетов)
-- =====================================================
DROP TABLE IF EXISTS rooms;
CREATE TABLE rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    capacity INT,
    price_per_hour DECIMAL(10, 2)
);

-- =====================================================
-- 3. ТАБЛИЦА bookings (заявки на бронирование)
-- =====================================================
DROP TABLE IF EXISTS bookings;
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    event_date DATE NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('Новая', 'Банкет назначен', 'Банкет завершен') DEFAULT 'Новая',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- =====================================================
-- 4. ТАБЛИЦА reviews (отзывы)
-- =====================================================
DROP TABLE IF EXISTS reviews;
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

-- =====================================================
-- ТЕСТОВЫЕ ДАННЫЕ (помещения для банкетов)
-- =====================================================
INSERT INTO rooms (id, name, type, capacity, price_per_hour) VALUES
(1, 'Золотой зал', 'зал', 100, 5000),
(2, 'Изумрудный зал', 'зал', 50, 3000),
(3, 'Летняя веранда', 'летняя веранда', 80, 4000),
(4, 'Уютная веранда', 'закрытая веранда', 30, 2500),
(5, 'Банкетный зал "Премиум"', 'зал', 150, 8000),
(6, 'Закрытая веранда "Сакура"', 'закрытая веранда', 40, 3500);

-- =====================================================
-- АДМИНИСТРАТОР
-- Логин: Admin26
-- Пароль: Demo20
-- =====================================================
INSERT INTO users (id, login, password, full_name, phone, email, role) 
VALUES (1, 'Admin26', 'Demo20', 
        'Администратор', '+7 (000) 000-00-00', 'admin@banquet.ru', 'admin');

-- =====================================================
-- ТЕСТОВЫЙ ПОЛЬЗОВАТЕЛЬ
-- Логин: testuser
-- Пароль: test123
-- =====================================================
INSERT INTO users (id, login, password, full_name, phone, email, role) 
VALUES (2, 'testuser', 'test123', 
        'Тестовый Пользователь', '+7 (999) 999-99-99', 'test@banquet.ru', 'user');

-- =====================================================
-- КОНЕЦ ФАЙЛА
-- =====================================================
