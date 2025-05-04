<?php
session_start();
require_once '../functions.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'غير مسجل الدخول']));
}

// التحقق من وجود ID
if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'معرف الجلسة مطلوب']));
}

try {
    $db = new PDO('mysql:host=localhost;dbname=your_db_name', 'username', 'password');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // جلب بيانات الجلسة المحددة
    $stmt = $db->prepare("SELECT * FROM trading_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        die('<div class="alert alert-danger">الجلسة غير موجودة أو لا تملك صلاحية الوصول</div>');
    }
    
    $sessionData = json_decode($session['session_data'], true);
    $profit = $session['final_balance'] - $session['initial_capital'];
    $winRate = $session['total_trades'] > 0 
        ? round($session['wins'] / $session['total_trades'] * 100, 2)
        : 0;
    
    ob_start();
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6>معلومات الجلسة</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>تاريخ البدء:</span>
                            <span><?= date('Y-m-d H:i', strtotime($session['start_time'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>تاريخ الانتهاء:</span>
                            <span><?= date('Y-m-d H:i', strtotime($session['end_time'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>رأس المال:</span>
                            <span>$<?= number_format($session['initial_capital'], 2) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>الرصيد النهائي:</span>
                            <span>$<?= number_format($session['final_balance'], 2) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6>الأداء</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>إجمالي الصفقات:</span>
                            <span><?= $session['total_trades'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>الصفقات الرابحة:</span>
                            <span class="text-success"><?= $session['wins'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>الصفقات الخاسرة:</span>
                            <span class="text-danger"><?= $session['losses'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>معدل الربح:</span>
                            <span><?= $winRate ?>%</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6>تطور الرصيد</h6>
                </div>
                <div class="card-body">
                    <canvas id="balanceChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const balances = [
            <?= $session['initial_capital'] ?>,
            <?php 
            if (isset($sessionData['trades'])) {
                $balances = array_column($sessionData['trades'], 'cassa');
                echo implode(', ', $balances);
            }
            ?>
        ];
        
        new Chart(
            document.getElementById('balanceChart').getContext('2d'),
            {
                type: 'line',
                data: {
                    labels: Array.from({length: balances.length}, (_, i) => `صفقة ${i}`),
                    datasets: [{
                        label: 'الرصيد',
                        data: balances,
                        borderColor: '#2469ff',
                        backgroundColor: 'rgba(36, 105, 255, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            }
        );
    });
    </script>
    <?php
    echo ob_get_clean();
    
} catch (PDOException $e) {
    die('<div class="alert alert-danger">حدث خطأ في قاعدة البيانات: ' . $e->getMessage() . '</div>');
}