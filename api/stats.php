<?php
session_start();
require_once '../functions.php';

header('Content-Type: application/json');

// Calculate required stats
$total_trades = $_SESSION['total_trades'] ?? 0;
$wins = $_SESSION['wins'] ?? 0;
$losses = $_SESSION['losses'] ?? 0;
$success_rate = ($wins + $losses) > 0 ? round($wins / ($wins + $losses) * 100, 2) : 0;
$total_profit = $_SESSION['total_profit'] ?? 0;

$data = [
    'total_trades' => $total_trades,
    'success_rate' => $success_rate,
    'total_profit' => $total_profit
];

echo json_encode($data);
?>