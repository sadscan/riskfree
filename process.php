<?php
session_start();
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false];

    try {
        if (isset($_POST['result'])) {
            if (!isset($_SESSION['initialized']) || !$_SESSION['initialized']) {
                throw new Exception('الجلسة غير مهيأة');
            }

            recordResult($_POST['result']);
            $response['success'] = true;
            $response['message'] = 'تم تسجيل النتيجة بنجاح';
        } elseif (isset($_POST['reset'])) {
            resetSession();
            $response['success'] = true;
            $response['message'] = 'تم إعادة تعيين الجلسة';
        }

    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

echo json_encode([
    'success' => false,
    'error' => 'طلب غير صالح'
]);
exit;