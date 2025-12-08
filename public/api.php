<?php

require_once __DIR__ . '/../autoload.php';

use App\Controllers\ROIController;
use App\Utils\ROICalculator;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove the /api.php part from the URI
$requestUri = str_replace('/api.php', '', $requestUri);

try {
    switch ($requestMethod) {
        case 'GET':
            if ($requestUri === '/status') {
                echo json_encode(['status' => 'API is running', 'timestamp' => date('Y-m-d H:i:s')]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'POST':
            if ($requestUri === '/calculate') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    $input = $_POST;
                }
                
                $startDate = $input['start_date'] ?? date('Y-m-01', strtotime('-1 year'));
                $endDate = $input['end_date'] ?? date('Y-m-d');
                $discountRate = $input['discount_rate'] ?? 0.1;
                $forecastYears = $input['forecast_years'] ?? 3;
                
                $controller = new ROIController();
                $results = $controller->calculateROIMetrics($startDate, $endDate, $discountRate, $forecastYears);
                
                echo json_encode([
                    'success' => true,
                    'data' => $results,
                    'parameters' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'discount_rate' => $discountRate,
                        'forecast_years' => $forecastYears
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'POST':
            if ($requestUri === '/import') {
                if (isset($_FILES['file'])) {
                    $file = $_FILES['file'];
                    $fileType = $input['type'] ?? 'production'; // production, cost, price, investment
                    
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        $uploadPath = $uploadDir . basename($file['name']);
                        
                        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                            // Import data based on type
                            switch ($fileType) {
                                case 'production':
                                    $result = \App\Utils\DataImporter::importProductionData($uploadPath);
                                    break;
                                case 'cost':
                                    $result = \App\Utils\DataImporter::importCostData($uploadPath);
                                    break;
                                case 'price':
                                    $result = \App\Utils\DataImporter::importPriceData($uploadPath);
                                    break;
                                case 'investment':
                                    $result = \App\Utils\DataImporter::importInvestmentData($uploadPath);
                                    break;
                                default:
                                    $result = ['success' => false, 'error' => 'Invalid file type'];
                            }
                            
                            echo json_encode($result);
                        } else {
                            http_response_code(500);
                            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'File upload error']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}