<?php
session_start();
require_once 'functions.php';

if (!$_SESSION['initialized']) {
    header("Location: index.php");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="trading_session_'.date('Y-m-d').'.csv"');

$output = fopen('php://output', 'w');

// كتابة عناوين الأعمدة في CSV
fputcsv($output, [
    '#', 
    'Stake', 
    'Return', 
    'Withdrawal', 
    'Balance', 
    'Max Balance', 
    'Wins', 
    'Losses', 
    'Win Rate',
    'Status'
]);

// كتابة بيانات الجلسة
foreach ($_SESSION['rows'] as $index => $row) {
    $status = ($index == count($_SESSION['rows']) - 1) ? 'Current' : 
             ($row['ritorno'] > 0 ? 'Win' : 'Loss');
    
    fputcsv($output, [
        $index + 1,
        $row['stake'],
        $row['ritorno'],
        $row['prelievo'],
        $row['cassa'],
        $row['max_cassa'],
        $row['wins'],
        $row['losses'],
        round($row['winrate'] * 100, 2) . '%',
        $status
    ]);
}

fclose($output);
exit;
?>