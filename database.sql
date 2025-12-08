-- Database for Steel Pipe Production Financial Analysis
CREATE DATABASE IF NOT EXISTS steel_pipe_finance;
USE steel_pipe_finance;

-- Table for production data
CREATE TABLE production_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period DATE,
    product_type VARCHAR(100),
    quantity DECIMAL(15,2),
    unit_cost DECIMAL(15,2),
    selling_price DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for cost data
CREATE TABLE costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cost_type ENUM('raw_material', 'energy', 'logistics', 'salary', 'depreciation', 'other'),
    description VARCHAR(255),
    amount DECIMAL(15,2),
    period DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for market prices
CREATE TABLE market_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_type VARCHAR(100),
    size_spec VARCHAR(100),
    precision_class VARCHAR(50),
    region VARCHAR(100),
    price DECIMAL(15,2),
    period DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for investments
CREATE TABLE investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    amount DECIMAL(15,2),
    investment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for calculation results
CREATE TABLE calculations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calculation_type VARCHAR(50),
    result DECIMAL(15,4),
    period VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for forecast scenarios
CREATE TABLE forecast_scenarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scenario_type ENUM('optimistic', 'base', 'pessimistic'),
    period DATE,
    revenue DECIMAL(15,2),
    cost DECIMAL(15,2),
    profit DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);