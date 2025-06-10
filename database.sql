CREATE DATABASE IF NOT EXISTS course_management;
USE course_management;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE communities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    zone VARCHAR(100) NOT NULL
);

CREATE TABLE owners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    rg VARCHAR(20),
    birth_date DATE,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100)
);

CREATE TABLE properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    total_area DECIMAL(10,2),
    mechanized_area DECIMAL(10,2),
    capoeira_area DECIMAL(10,2),
    legal_reserve_area DECIMAL(10,2),
    permanent_preservation_area DECIMAL(10,2),
    cib_itr VARCHAR(50),
    car_receipt VARCHAR(50),
    FOREIGN KEY (owner_id) REFERENCES owners(id)
);

CREATE TABLE operational_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT,
    culture_type ENUM('horticultura', 'fruticultura', 'culturas_anuais', 'sistemas_agroflorestais', 'outros'),
    product VARCHAR(100),
    planted_area DECIMAL(10,2),
    productivity DECIMAL(10,2),
    production DECIMAL(10,2),
    FOREIGN KEY (property_id) REFERENCES properties(id)
);