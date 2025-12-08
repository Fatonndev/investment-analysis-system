-- Database schema for ROI Analysis System for Pipe Rolling Plant

-- Create database
CREATE DATABASE IF NOT EXISTS pipe_plant_roi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE pipe_plant_roi;

-- Table for production data
CREATE TABLE production_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    product_type VARCHAR(255) NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    unit VARCHAR(50) DEFAULT 'tons',
    equipment_load DECIMAL(5,2) DEFAULT 0.00, -- Equipment load percentage
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_product_type (product_type)
);

-- Table for cost data
CREATE TABLE cost_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    cost_type ENUM('raw_material', 'energy', 'labor', 'depreciation', 'logistics', 'other') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_cost_type (cost_type)
);

-- Table for price data
CREATE TABLE price_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    product_type VARCHAR(255) NOT NULL,
    size_type VARCHAR(100), -- e.g., diameter, wall thickness
    precision_class VARCHAR(50), -- e.g., ordinary, enhanced precision
    region VARCHAR(100), -- sales region
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_product_type (product_type),
    INDEX idx_region (region)
);

-- Table for investment data
CREATE TABLE investment_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investment_name VARCHAR(255) NOT NULL,
    investment_type ENUM('equipment', 'modernization', 'construction', 'other') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    investment_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_investment_date (investment_date),
    INDEX idx_investment_type (investment_type)
);

-- Table for calculation results (for caching and reporting)
CREATE TABLE calculation_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calculation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    discount_rate DECIMAL(5,4) DEFAULT 0.1000,
    forecast_years INT DEFAULT 3,
    roi DECIMAL(10,4),
    npv DECIMAL(15,2),
    irr DECIMAL(10,4),
    payback_period DECIMAL(10,2),
    total_revenue DECIMAL(15,2),
    total_costs DECIMAL(15,2),
    net_profit DECIMAL(15,2),
    total_investment DECIMAL(15,2),
    break_even_units DECIMAL(15,2),
    parameters JSON, -- Store calculation parameters
    results JSON, -- Store detailed results including forecasts
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_calculation_date (calculation_date)
);

-- Insert sample data for testing
INSERT INTO production_data (date, product_type, quantity, unit, equipment_load) VALUES
('2023-01-01', 'Seamless Pipe', 1250.50, 'tons', 85.50),
('2023-02-01', 'Seamless Pipe', 1300.25, 'tons', 87.25),
('2023-03-01', 'Seamless Pipe', 1280.75, 'tons', 86.00),
('2023-04-01', 'Welded Pipe', 980.30, 'tons', 75.25),
('2023-05-01', 'Welded Pipe', 1020.60, 'tons', 78.50),
('2023-06-01', 'Welded Pipe', 1050.40, 'tons', 80.75);

INSERT INTO cost_data (date, cost_type, amount, currency, description) VALUES
('2023-01-15', 'raw_material', 750000.00, 'USD', 'Steel raw material purchase'),
('2023-01-20', 'energy', 50000.00, 'USD', 'Energy costs'),
('2023-01-25', 'labor', 120000.00, 'USD', 'Labor costs'),
('2023-01-30', 'depreciation', 45000.00, 'USD', 'Equipment depreciation'),
('2023-02-15', 'raw_material', 780000.00, 'USD', 'Steel raw material purchase'),
('2023-02-20', 'energy', 52000.00, 'USD', 'Energy costs'),
('2023-02-25', 'labor', 122000.00, 'USD', 'Labor costs'),
('2023-03-15', 'raw_material', 768000.00, 'USD', 'Steel raw material purchase'),
('2023-03-20', 'energy', 51000.00, 'USD', 'Energy costs'),
('2023-03-25', 'labor', 121000.00, 'USD', 'Labor costs');

INSERT INTO price_data (date, product_type, size_type, precision_class, region, price, currency) VALUES
('2023-01-01', 'Seamless Pipe', '108x4mm', 'ordinary', 'Domestic', 4200.00, 'USD'),
('2023-02-01', 'Seamless Pipe', '108x4mm', 'ordinary', 'Domestic', 4250.00, 'USD'),
('2023-03-01', 'Seamless Pipe', '108x4mm', 'ordinary', 'Domestic', 4300.00, 'USD'),
('2023-01-01', 'Welded Pipe', '114x5mm', 'ordinary', 'Domestic', 3800.00, 'USD'),
('2023-02-01', 'Welded Pipe', '114x5mm', 'ordinary', 'Domestic', 3850.00, 'USD'),
('2023-03-01', 'Welded Pipe', '114x5mm', 'ordinary', 'Domestic', 3900.00, 'USD');

INSERT INTO investment_data (investment_name, investment_type, amount, currency, investment_date, description) VALUES
('New Rolling Mill', 'equipment', 5000000.00, 'USD', '2022-06-01', 'Purchase of new pipe rolling mill equipment'),
('Plant Modernization', 'modernization', 2000000.00, 'USD', '2022-08-01', 'Modernization of production lines'),
('New Warehouse', 'construction', 1500000.00, 'USD', '2022-10-01', 'Construction of new warehouse facility');