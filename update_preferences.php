<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dark_mode'])) {
    $_SESSION['dark_mode'] = (bool)$_POST['dark_mode'];
    echo json_encode(['success' => true]);
    exit;
}

header("HTTP/1.1 400 Bad Request");
exit;
?>