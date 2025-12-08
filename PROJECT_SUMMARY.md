# ROI Analysis System for Pipe Rolling Plant - Project Summary

## Overview
This project is a comprehensive PHP/MySQL application designed for analyzing and forecasting return on investment (ROI) for a pipe rolling plant. The system collects, processes, and analyzes financial data to calculate key investment metrics and provide forecasting capabilities.

## Architecture

### Backend (PHP)
- **Models**: Handle database operations for production data, cost data, price data, and investment data
- **Controllers**: Manage business logic and coordinate between models and utilities
- **Utils**: Contain calculation algorithms, data importers, and other utilities
- **Database**: MySQL with PDO for secure database operations

### Frontend (HTML/CSS/JS)
- **Bootstrap**: Responsive UI framework
- **Chart.js**: Data visualization
- **AJAX**: API communication

## Key Features Implemented

### 1. Data Collection and Processing
- Production data model (volumes, product types, equipment load)
- Cost data model (raw materials, energy, labor, depreciation)
- Price data model (by product type, size, precision, region)
- Investment data model (equipment, modernization, construction)

### 2. ROI Calculations
- ROI (Return on Investment)
- NPV (Net Present Value)
- IRR (Internal Rate of Return)
- Payback period
- Break-even analysis

### 3. Forecasting
- Linear regression for trend analysis
- Moving average forecasting
- Scenario analysis (optimistic, base, pessimistic)
- Sensitivity analysis

### 4. Data Import/Export
- Excel import functionality using PhpSpreadsheet
- Data validation and error handling

### 5. Visualization
- Revenue and profit forecasts
- Cost structure analysis
- Interactive charts and graphs

## Database Schema
The system uses 5 main tables:
1. `production_data` - Production volumes and equipment metrics
2. `cost_data` - Various cost types and amounts
3. `price_data` - Product pricing by type, size, and region
4. `investment_data` - Capital investments
5. `calculation_results` - Cached calculation results

## API Endpoints
- `POST /calculate` - Calculate ROI metrics
- `POST /import` - Import data from Excel files
- `GET /status` - API status check

## Technical Implementation Details

### Calculation Algorithms
- **ROI**: (Net Profit / Investment Cost) * 100
- **NPV**: Σ [Cash Flow / (1 + discount_rate)^t] - Initial Investment
- **IRR**: Newton-Raphson method implementation
- **Payback Period**: Time to recover initial investment
- **Break-even**: Fixed Costs / (Price per unit - Variable Cost per unit)

### Error Handling
- Validation for required input data
- Handling of negative values and unrealistic inputs
- Error handling for data import operations
- Validation of calculation feasibility

### Security Measures
- Prepared statements to prevent SQL injection
- Input sanitization using htmlspecialchars
- Parameter validation

## Usage Instructions

### Setup
1. Create MySQL database using `database_schema.sql`
2. Update database credentials in `src/Database/Database.php`
3. Place files in web server directory
4. Access the application via web browser

### Operation
1. Import historical data using Excel import functionality
2. Set analysis parameters (date range, discount rate, forecast years)
3. Calculate ROI metrics
4. View results and forecasts
5. Export reports as needed

## File Structure
```
/workspace/
├── README.md
├── PROJECT_SUMMARY.md
├── composer.json
├── autoload.php
├── database_schema.sql
├── src/
│   ├── Controllers/
│   │   └── ROIController.php
│   ├── Models/
│   │   ├── ProductionData.php
│   │   ├── CostData.php
│   │   ├── PriceData.php
│   │   └── InvestmentData.php
│   ├── Database/
│   │   └── Database.php
│   └── Utils/
│       ├── ROICalculator.php
│       └── DataImporter.php
├── public/
│   ├── index.php
│   └── api.php
├── css/
├── js/
└── uploads/ (created automatically)
```

## Future Enhancements
- Advanced forecasting algorithms (ARIMA, machine learning models)
- More sophisticated visualization options
- PDF report generation
- User authentication and role management
- Real-time data integration
- More detailed sensitivity analysis options