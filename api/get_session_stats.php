<?php
require_once '../functions.php';

header('Content-Type: application/json');

$response = [
    'winRate' => ($_SESSION['wins'] + $_SESSION['losses']) > 0 
        ? round($_SESSION['wins']/($_SESSION['wins']+$_SESSION['losses'])*100, 2)
        : 0,
    'remainingTrades' => $_SESSION['total_trades'] - ($_SESSION['wins'] + $_SESSION['losses']),
    'requiredWins' => $_SESSION['wins_needed'] - $_SESSION['wins'],
    'allowedLosses' => $_SESSION['total_trades'] - $_SESSION['wins_needed'] - $_SESSION['losses']
];

echo json_encode($response);
?>