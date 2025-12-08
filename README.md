# Steel Pipe Production Financial Analysis System

This is a simple financial analysis system for steel pipe production developed with PHP and MySQL. The system allows for collection and processing of financial-economic data, calculation of investment profitability metrics, forecasting, and sensitivity analysis.

## Features

1. **Data Management**:
   - Input production data (volumes, costs, prices)
   - Track various types of costs (raw materials, energy, logistics, etc.)
   - Record market prices by product type and region
   - Register investments

2. **Financial Calculations**:
   - ROI (Return on Investment)
   - NPV (Net Present Value)
   - IRR (Internal Rate of Return)
   - Payback Period
   - Break-even Analysis

3. **Forecasting**:
   - Revenue forecasting using linear regression
   - 12-month projection

4. **Sensitivity Analysis**:
   - Impact assessment of steel price changes
   - Impact assessment of demand fluctuations

## Files Structure

- `config.php` - Database configuration
- `finance_calculator.php` - Main business logic and calculations
- `database.sql` - Database schema
- `index.php` - Main application interface
- `README.md` - This file

## Installation

1. Make sure you have PHP and MySQL installed
2. Import the database schema:
   ```sql
   mysql -u root -p < database.sql
   ```
3. Update the database credentials in `config.php` if needed
4. Place all files in your web server directory
5. Access the application through your browser

## Usage

1. Navigate to the input tab to enter production data, costs, market prices, and investments
2. Go to the analysis tab to see calculated financial metrics
3. Check the forecasting tab for projected revenues
4. Use the sensitivity analysis tab to see how changes affect profitability

## Error Handling

The system includes error handling for:
- Missing input data
- Invalid numeric values
- Database errors
- Insufficient data for calculations
- Calculation convergence issues

## Technologies Used

- PHP 7.0+
- MySQL
- HTML/CSS/JavaScript
- PDO for database operations

## Notes

- This system uses simplified financial models for educational purposes
- For real-world applications, more complex models would be needed
- The forecasting uses basic linear regression which may not capture complex trends