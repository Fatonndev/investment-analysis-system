<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

class CSVHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Parse CSV file and return array of rows
     */
    public function parseCSV($file_path) {
        $rows = [];
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }
    
    /**
     * Import production data from CSV
     */
    public function importProductionData($project_id, $file_path) {
        $rows = $this->parseCSV($file_path);
        $errors = [];
        
        // Skip header row
        $header = array_shift($rows);
        
        foreach ($rows as $index => $row) {
            // Ensure we have enough columns
            if (count($row) < 7) {
                $errors[] = "Row " . ($index + 2) . " has insufficient columns. Expected 7 columns (period, product_type, quantity, unit, revenue, variable_costs, fixed_costs)";
                continue;
            }
            
            $data = [
                'project_id' => $project_id,
                'period' => $row[0], // period (YYYY-MM-DD or YYYY-MM format)
                'product_type' => $row[1],
                'quantity' => (float)$row[2],
                'unit' => $row[3],
                'revenue' => (float)$row[4],
                'variable_costs' => (float)$row[5],
                'fixed_costs' => (float)$row[6]
            ];
            
            // Validate period format and convert to proper date format if needed
            $period = $data['period'];
            if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                // Format is YYYY-MM, convert to YYYY-MM-01
                $data['period'] = $period . '-01';
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
                $errors[] = "Row " . ($index + 2) . " has invalid period format. Expected YYYY-MM or YYYY-MM-DD";
                continue;
            }
            
            try {
                $this->db->insert('production_data', $data);
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 2) . " failed to insert: " . $e->getMessage();
            }
        }
        
        return $errors;
    }
    
    /**
     * Import operational costs data from CSV
     */
    public function importOperationalCosts($project_id, $file_path) {
        $rows = $this->parseCSV($file_path);
        $errors = [];
        
        // Skip header row
        $header = array_shift($rows);
        
        foreach ($rows as $index => $row) {
            // Ensure we have at least the basic columns
            if (count($row) < 3) {
                $errors[] = "Row " . ($index + 2) . " has insufficient columns. Expected at least 3 columns (period, cost_type, ...)";
                continue;
            }
            
            $period = $row[0];
            $cost_type = $row[1];
            
            // Validate period format
            if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                $period = $period . '-01';
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
                $errors[] = "Row " . ($index + 2) . " has invalid period format. Expected YYYY-MM or YYYY-MM-DD";
                continue;
            }
            
            $data = [
                'project_id' => $project_id,
                'period' => $period,
                'cost_type' => $cost_type
            ];
            
            // Process different cost types based on the type
            switch ($cost_type) {
                case 'raw_material':
                    // Column mapping: period(0), cost_type(1), material_type(2), cost_per_unit(3), quantity_used(4), total_cost(5)
                    if (count($row) < 6) {
                        $errors[] = "Row " . ($index + 2) . " has insufficient columns for raw material. Expected: period, cost_type, material_type, cost_per_unit, quantity_used, total_cost";
                        continue 2;
                    }
                    $data['material_type'] = $row[2];
                    $data['cost_per_unit'] = (float)$row[3];
                    $data['quantity_used'] = (float)$row[4];
                    $data['total_cost'] = (float)$row[5];
                    break;
                    
                case 'energy':
                    // Column mapping: period(0), cost_type(1), [empty], cost_per_unit(3), quantity_used(4), total_cost(5), energy_type(6)
                    if (count($row) < 7) {
                        $errors[] = "Row " . ($index + 2) . " has insufficient columns for energy. Expected: period, cost_type, [empty], cost_per_unit, quantity_used, total_cost, energy_type";
                        continue 2;
                    }
                    $data['energy_type'] = $row[6];
                    $data['cost_per_unit'] = (float)$row[3];
                    $data['quantity_used'] = (float)$row[4];
                    $data['total_cost'] = (float)$row[5];
                    break;
                    
                case 'logistics':
                    // Column mapping: period(0), cost_type(1), [empty], [empty], [empty], total_cost(5), [empty], route(7)
                    if (count($row) < 8) {
                        $errors[] = "Row " . ($index + 2) . " has insufficient columns for logistics. Expected: period, cost_type, [empty], [empty], [empty], total_cost, [empty], route";
                        continue 2;
                    }
                    $data['route'] = $row[7];
                    $data['total_cost'] = (float)$row[5];
                    break;
                    
                case 'labor':
                    // Column mapping: period(0), cost_type(1), [empty], [empty], [empty], [empty], [empty], [empty], department(8), salary_cost(9), benefits(10)
                    if (count($row) < 11) {
                        $errors[] = "Row " . ($index + 2) . " has insufficient columns for labor. Expected: period, cost_type, [empty], [empty], [empty], [empty], [empty], [empty], department, salary_cost, benefits";
                        continue 2;
                    }
                    $data['department'] = $row[8];
                    $data['salary_cost'] = (float)$row[9];
                    $data['benefits'] = (float)$row[10];
                    $data['total_cost'] = $data['salary_cost'] + $data['benefits'];
                    break;
                    
                case 'depreciation':
                    // Column mapping: period(0), cost_type(1), [empty], [empty], [empty], [empty], [empty], [empty], [empty], [empty], [empty], asset_name(11), depreciation_amount(12)
                    if (count($row) < 13) {
                        $errors[] = "Row " . ($index + 2) . " has insufficient columns for depreciation. Expected: period, cost_type, [empty], [empty], [empty], [empty], [empty], [empty], [empty], [empty], [empty], asset_name, depreciation_amount";
                        continue 2;
                    }
                    $data['asset_name'] = $row[11];
                    $data['depreciation_amount'] = (float)$row[12];
                    break;
                    
                default:
                    $errors[] = "Row " . ($index + 2) . " has unknown cost type: " . $cost_type;
                    continue 2;
            }
            
            try {
                $this->db->insert('operational_costs', $data);
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 2) . " failed to insert: " . $e->getMessage();
            }
        }
        
        return $errors;
    }
    
    /**
     * Import product prices from CSV
     */
    public function importProductPrices($project_id, $file_path) {
        $rows = $this->parseCSV($file_path);
        $errors = [];
        
        // Skip header row
        $header = array_shift($rows);
        
        foreach ($rows as $index => $row) {
            // Ensure we have enough columns
            if (count($row) < 6) {
                $errors[] = "Row " . ($index + 2) . " has insufficient columns. Expected 6 columns (period, product_type, size_spec, precision_class, region, price_per_unit)";
                continue;
            }
            
            $period = $row[0];
            // Validate period format
            if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                $period = $period . '-01';
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
                $errors[] = "Row " . ($index + 2) . " has invalid period format. Expected YYYY-MM or YYYY-MM-DD";
                continue;
            }
            
            $data = [
                'project_id' => $project_id,
                'period' => $period,
                'product_type' => $row[1],
                'size_spec' => $row[2],
                'precision_class' => $row[3],
                'region' => $row[4],
                'price_per_unit' => (float)$row[5]
            ];
            
            try {
                $this->db->insert('product_prices', $data);
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 2) . " failed to insert: " . $e->getMessage();
            }
        }
        
        return $errors;
    }
    
    /**
     * Import investment data from CSV
     */
    public function importInvestmentData($project_id, $file_path) {
        $rows = $this->parseCSV($file_path);
        $errors = [];
        
        // Skip header row
        $header = array_shift($rows);
        
        foreach ($rows as $index => $row) {
            // Ensure we have enough columns
            if (count($row) < 4) {
                $errors[] = "Row " . ($index + 2) . " has insufficient columns. Expected 4 columns (investment_type, description, amount, investment_date)";
                continue;
            }
            
            $investment_date = $row[3];
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $investment_date)) {
                $errors[] = "Row " . ($index + 2) . " has invalid investment date format. Expected YYYY-MM-DD";
                continue;
            }
            
            $data = [
                'project_id' => $project_id,
                'investment_type' => $row[0],
                'description' => $row[1],
                'amount' => (float)$row[2],
                'investment_date' => $investment_date
            ];
            
            try {
                $this->db->insert('investment_data', $data);
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 2) . " failed to insert: " . $e->getMessage();
            }
        }
        
        return $errors;
    }
}
?>