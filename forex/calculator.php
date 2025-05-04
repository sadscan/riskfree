<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $balance = floatval($data['balance']);
    $riskPercent = floatval($data['riskPercent']);
    $stopLoss = floatval($data['stopLoss']);
    $pipValue = floatval($data['pipValue']);
    $lotSize = isset($data['lotSize']) ? floatval($data['lotSize']) : null;
    
    // Validate inputs
    if ($balance <= 0 || $riskPercent <= 0 || $stopLoss <= 0) {
        echo json_encode(['error' => 'القيم المدخلة غير صالحة']);
        exit;
    }
    
    // Calculate risk amount
    $riskAmount = ($balance * $riskPercent) / 100;
    
    // Calculate position size
    if ($lotSize) {
        $positionSize = $lotSize;
    } else {
        $positionSize = $riskAmount / ($stopLoss * $pipValue * 10);
    }
    
    // Calculate pip value and potential loss
    $calculatedPipValue = $positionSize * $pipValue * 10;
    $potentialLoss = $positionSize * $stopLoss * $pipValue * 10;
    
    // Prepare response
    $response = [
        'riskAmount' => round($riskAmount, 2),
        'positionSize' => round($positionSize, 2),
        'pipValue' => round($calculatedPipValue, 2),
        'potentialLoss' => round($potentialLoss, 2)
    ];
    
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'طريقة الطلب غير مسموحة']);
}
?>