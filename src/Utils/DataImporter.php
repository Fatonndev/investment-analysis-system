<?php

namespace App\Utils;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\ProductionData;
use App\Models\CostData;
use App\Models\PriceData;
use App\Models\InvestmentData;

class DataImporter
{
    /**
     * Import production data from Excel file
     */
    public static function importProductionData($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip header row
            array_shift($rows);
            
            $productionModel = new ProductionData();
            $importedCount = 0;
            
            foreach ($rows as $row) {
                if (empty($row[0])) continue; // Skip empty rows
                
                $productionModel->date = $row[0] ?? '';
                $productionModel->product_type = $row[1] ?? '';
                $productionModel->quantity = $row[2] ?? 0;
                $productionModel->unit = $row[3] ?? 'tons';
                $productionModel->equipment_load = $row[4] ?? 0;
                
                if ($productionModel->create()) {
                    $importedCount++;
                }
            }
            
            return ['success' => true, 'count' => $importedCount];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Import cost data from Excel file
     */
    public static function importCostData($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip header row
            array_shift($rows);
            
            $costModel = new CostData();
            $importedCount = 0;
            
            foreach ($rows as $row) {
                if (empty($row[0])) continue; // Skip empty rows
                
                $costModel->date = $row[0] ?? '';
                $costModel->cost_type = $row[1] ?? '';
                $costModel->amount = $row[2] ?? 0;
                $costModel->currency = $row[3] ?? 'USD';
                $costModel->description = $row[4] ?? '';
                
                if ($costModel->create()) {
                    $importedCount++;
                }
            }
            
            return ['success' => true, 'count' => $importedCount];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Import price data from Excel file
     */
    public static function importPriceData($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip header row
            array_shift($rows);
            
            $priceModel = new PriceData();
            $importedCount = 0;
            
            foreach ($rows as $row) {
                if (empty($row[0])) continue; // Skip empty rows
                
                $priceModel->date = $row[0] ?? '';
                $priceModel->product_type = $row[1] ?? '';
                $priceModel->size_type = $row[2] ?? '';
                $priceModel->precision_class = $row[3] ?? '';
                $priceModel->region = $row[4] ?? '';
                $priceModel->price = $row[5] ?? 0;
                $priceModel->currency = $row[6] ?? 'USD';
                
                if ($priceModel->create()) {
                    $importedCount++;
                }
            }
            
            return ['success' => true, 'count' => $importedCount];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Import investment data from Excel file
     */
    public static function importInvestmentData($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip header row
            array_shift($rows);
            
            $investmentModel = new InvestmentData();
            $importedCount = 0;
            
            foreach ($rows as $row) {
                if (empty($row[0])) continue; // Skip empty rows
                
                $investmentModel->investment_name = $row[0] ?? '';
                $investmentModel->investment_type = $row[1] ?? '';
                $investmentModel->amount = $row[2] ?? 0;
                $investmentModel->currency = $row[3] ?? 'USD';
                $investmentModel->investment_date = $row[4] ?? '';
                $investmentModel->description = $row[5] ?? '';
                
                if ($investmentModel->create()) {
                    $importedCount++;
                }
            }
            
            return ['success' => true, 'count' => $importedCount];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}