<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختيار نظام التداول</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <img src="assets/img/logo.png" alt="Logo" class="logo_header" style="max-width: 300px;">
            <h2 class="mt-3">اختر نظام التداول</h2>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-5 mb-4">
                <div class="card system-card h-100" onclick="location.href='index.php'">
                    <div class="card-body text-center">
                        <i class="fas fa-bolt fa-4x mb-3 text-warning"></i>
                        <h3 class="card-title">الخيارات الثنائية</h3>
                        <p class="card-text">نظام متكامل لإدارة تداول الخيارات الثنائية</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5 mb-4">
                <div class="card system-card h-100" onclick="location.href='forex.php'">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-4x mb-3 text-primary"></i>
                        <h3 class="card-title">تداول الفوركس</h3>
                        <p class="card-text">نظام متقدم لإدارة تداول الفوركس</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.system-card').hover(
                function() {
                    $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
                }
            );
        });
    </script>
</body>
</html>