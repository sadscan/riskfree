<?php
ini_set('session.cookie_lifetime', 0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['session_saved'])) {
    $_SESSION['session_saved'] = false;
}
// ⏱ كود انتهاء الجلسة بعد 10 دقائق بدون نشاط
$timeout_duration = 600; // 10 دقائق
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
 
 

if (!isset($_SESSION['user_id'])) {
    // المستخدم غير مسجل الدخول
    header("Location: login.php");
    exit;
}
// منع التخزين المؤقت للصفحات
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'functions.php';

// تهيئة الجلسة
if (!isset($_SESSION['initialized'])) {
    $_SESSION['initialized'] = false;
    $_SESSION['rows'] = [];
    $_SESSION['cassa'] = 0;
    $_SESSION['max_cassa'] = 0;
    $_SESSION['wins'] = 0;
    $_SESSION['losses'] = 0;
    $_SESSION['finished'] = false;
    $_SESSION['total_profit'] = 0;
    $_SESSION['show_params'] = true;
}

// في قسم معالجة بدء الجلسة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_session'])) {
    // التحقق من رأس المال
    $initial_capital = floatval($_POST['initial_capital']);
    if ($initial_capital < 50) {
        $_SESSION['error'] = 'رأس المال يجب أن لا يقل عن 50 دولار';
        header("Location: index.php");
        exit;
    }
    
    // التحقق من عدد الصفقات
    $total_trades = intval($_POST['total_trades']);
    if ($total_trades > 100) {
        $_SESSION['error'] = 'عدد الصفقات الإجمالي يجب أن لا يزيد عن 100';
        header("Location: index.php");
        exit;
    }
    
    
    // التحقق من صفقات الربح
    $wins_needed = intval($_POST['wins_needed']);
    if ($wins_needed > $total_trades) {
        $_SESSION['error'] = 'عدد صفقات الربح يجب أن لا يزيد عن عدد الصفقات الإجمالي';
        header("Location: index.php");
        exit;
    }
    
    // التحقق من عائد الربح
    $payout = floatval($_POST['payout']);
    if ($payout < 1.5 || $payout > 2) {
        $_SESSION['error'] = 'عائد الربح يجب أن يكون بين 1.5 و 2 فقط';
        header("Location: index.php");
        exit;
    }

    
    // إذا اجتازت جميع الشروط
    $_SESSION['initial_capital'] = $initial_capital;
    $_SESSION['total_trades'] = $total_trades;
    $_SESSION['wins_needed'] = $wins_needed;
    $_SESSION['payout'] = $payout;
    
    $_SESSION['V'] = buildNetwork($_SESSION['total_trades'], $_SESSION['wins_needed'], $_SESSION['payout']);
    $_SESSION['target_profit'] = round((calculateProfile($_SESSION['V']) - 1) * $_SESSION['initial_capital'], 2);
    
    resetSession();
    $_SESSION['initialized'] = true;
    $_SESSION['show_params'] = false;
	$_SESSION['session_saved'] = false;

    
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RiskFree System - Binary Options</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
	<link href="assets/css/style.css" rel="stylesheet">
    <script>
        // تطبيق الوضع الداكن قبل تحميل المحتوى
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeEnabled = localStorage.getItem('darkMode') === 'true';
            if(darkModeEnabled) {
                document.body.classList.add('dark-mode');
                document.getElementById('darkModeToggle').innerHTML = '<i class="fas fa-sun"></i>';
            }
        });
    </script>

</head>
<body>
<!-- زر الإعدادات العائم -->
<button id="toggleUserInfo" class="floating-btn" title="معلومات الحساب">
    <i class="fas fa-user-cog"></i>
</button>

<!-- زر تغيير الوضع الليلي -->
<button id="darkModeToggle" class="theme-toggle-btn" title="تبديل الوضع">
    <i class="fas fa-moon"></i>
</button>
<!-- معلومات المستخدم -->
<!------------- معلومات الحساب ------------->
<div id="userInfoContainer" class="user-info-container animate__animated animate__fadeInDown" style="display: none;">
    <?php if(isset($_SESSION['username'])): ?>
        <h5 class="info-title"><i class="fas fa-info-circle"></i> معلومات الحساب</h5>

        <div class="info-item">
            <span><i class="fas fa-user"></i> اسم المستخدم:</span>
            <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
        </div>

        <div class="info-item">
            <span><i class="fas fa-clock"></i> وقت تسجيل الدخول:</span>
            <strong><?= date('H:i:s') ?></strong>
        </div>

        <hr>

        <form action="logout.php" method="post">
            <button type="submit" onclick="return confirmLogout()" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">يجب تسجيل الدخول أولاً</div>
    <?php endif; ?>
</div>
<!-- Loader -->
<div id="loader-wrapper">
  <div id="loader">
    <img src="assets/img/logo.png" alt="Logo" class="logo" />
    <div id="loader-text">
      <span id="loader-percentage">0%</span></br> جاري التحميل
    </div>
  </div>
</div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="text-center mb- logo_site">
            <img class ="animate__animated animate__fadeIn animate__delay-1s logo_header" src="assets/img/logo.png" alt="Risk Free System" class="logo">
        </div>
        
        <div class="card shadow-lg mb-4 card-animate">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="fas fa-cog me-2" style="padding-left:15px;"></i>إعدادات الجلسة</h2>				
            </div>
            
            <div class="card-body">
                <!-- قسم إدخال المعاملات -->
                <?php if (!$_SESSION['initialized'] || $_SESSION['show_params']): ?>
                <form method="post" action="" class="animate__animated animate__fadeIn">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="initial_capital" name="initial_capital"  min="50" step="0.01" required placeholder="رأس المال">
                                <label for="initial_capital">رأس المال ($)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="total_trades" name="total_trades" min="1" max="100" required placeholder="إجمالي الصفقات">
                                <label for="total_trades">إجمالي الصفقات</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="wins_needed" name="wins_needed" min="1" max="100" required placeholder="صفقات الربح المطلوبة">
                                <label for="wins_needed">صفقات الربح المطلوبة</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="payout" name="payout" min="1.5" max="2" step="0.01" required placeholder="نسبة العائد">
                                <label for="payout">نسبة العائد - مثال 1.86</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="start_session" class="btn btn-success w-100 py-3 pulse">
                                <i class="fas fa-solid fa-flag-checkered me-2" style="padding-left:25px;"></i>بدء الجلسة
                            </button>
                        </div>
                    </div>
                </form>
                
                    <!-- تعليمات هامة  -->
                    </br>
                    <div class="col-12">
                        <div class="alert alert-danger p-2 pt-3">
                            <i class="fas fa-exclamation-triangle me-2 p-2"></i>
                            <small>
                                <strong class="pr-2 mt-3 text-danger">تعليمات هامة :</strong><br>
    <ul>
  <li>حدد عدد صفقات ربح تتناسب مع إجمالي صفقات الجلسة</li>
  <li>عدم تحقيق صفقات الربح المطلوب يأدي لخسارة رأس المال بالكامل</li>
  <li>العائد لكل ربح - مثلاً 1.9 تعني 90% نسبة العائد للزوج</li>
  <li>ضبط النفس والتوقف عند تحقيق الهدف هو العامل الأهم في تحقيق الربح فالتداول</li>
</ul>                            </small>
                        </div>
                    </div>
                    <!-- تعليمات هامة -->
                <?php endif; ?>
                <?php if ($_SESSION['initialized'] && !$_SESSION['show_params']): ?>
                <!-- كروت المعطيات -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3 col-6 animate__animated animate__fadeInLeft">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">رأس المال</h6>
                                    <h4 class="mb-0">$<?= number_format($_SESSION['initial_capital'], 2) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 animate__animated animate__fadeInLeft animate__delay-1s">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">إجمالي الصفقات</h6>
                                    <h4 class="mb-0"><?= $_SESSION['total_trades'] ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-6 animate__animated animate__fadeInLeft animate__delay-2s">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">صفقات الربح</h6>
                                    <h4 class="mb-0"><?= $_SESSION['wins_needed'] ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 animate__animated animate__fadeInLeft animate__delay-3s">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">العائد</h6>
                                    <h4 class="mb-0"><?= $_SESSION['payout'] ?>x</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- كروت الأداء -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4 col-6 animate__animated animate__fadeInLeft">
                        <div class="stat-card h-100">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-secondary">
                                    <i class="fas fa-percent"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">نسبة الربح</h6>
                                    <h4 class="mb-0">  <?= round((calculateProfile($_SESSION['V']) - 1) * 100, 2) ?>%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6 animate__animated animate__fadeInLeft animate__delay-1s">
                        <div class="stat-card h-100">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">الربح المستهدف</h6>
                                    <h4 class="mb-0">$<?= $_SESSION['target_profit'] ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12 animate__animated animate__fadeInLeft animate__delay-2s">
                        <div class="stat-card h-100">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-piggy-bank"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">الرصيد المستهدف</h6>
                                    <h4 class="mb-0">$<?= round($_SESSION['initial_capital'] + $_SESSION['target_profit'], 2) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<!-- سجل التداول -->
<div class="card mb-4 card-animate">
    <?php if (!empty($_SESSION['rows']) && (!isset($_SESSION['finished']) || !$_SESSION['finished'])): ?>
        <?php $lastIndex = count($_SESSION['rows']); ?>
        <div class="card-header">
            <h3 class="h5 mb-0">
                <i class="fas fa-line-chart me-2" style="padding-left:10px;"></i>
                الصفقة الحالية
                <span style="padding-right:10px;" class="badge bg-dark ms-2 fw-bold  m-1">الصفقة رقم # <?= $lastIndex ?></span>
            </h3>
        </div>

        <?php 
            $lastIndex = count($_SESSION['rows']) - 1;
            $lastRow = $_SESSION['rows'][$lastIndex]; 
            $remaining_trades = $_SESSION['total_trades'] - ($_SESSION['wins'] + $_SESSION['losses']);
        ?>

        <div class="card-body">
            <div class="row">
                <!-- القسم الأيمن: مبلغ التداول والأزرار (col-md-8) -->
                <div class="col-md-8">
                    <!-- كارت مبلغ التداول -->
                    <div class="stat-card bg-info-light p-3 mb-3 dark-mode-compatible animate__animated animate__fadeIn">
                        <div class="row align-items-center">
                            <div class="col-md-6 text-end">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info me-3">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <h5 class="text-muted mb-0 ms-auto fw-bold">مبلغ التداول</h5>
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size:15px;">
					الرصيد : $<?= isset($_SESSION['cassa']) ? number_format($_SESSION['cassa'], 1) : '0.0' ?>
                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 text-start mt-2 mt-md-0">
                                <span style="background:#087DBA linear-gradient(207deg,rgba(8, 125, 186, 1) 19%, rgba(26, 219, 254, 1) 100%)!important;" class="badge bg-info bg-opacity-10 text-white fs-3 fw-bold p-3 w-100">
                                    $<?= number_format($lastRow['stake'], 1) ?>
                                </span>
                            </div>
                        </div>
                    </div>

<!-- أزرار ربح / خسارة -->
<div class="row g-3"> <!-- استخدم نظام grid بدلاً من flex -->
    <div class="col-6"> <!-- عمود لكل زر -->
        <!-- زر الربح -->
        <form method="post" action="process.php" class="trade-form h-100">
            <input type="hidden" name="result" value="win">
            <button type="submit" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center rounded-3 shadow-sm hover-shadow h-100">
                <i class="fas fa-check-circle fs-4"></i>&nbsp;
                <span class="d-none d-sm-inline">&nbsp;</span>
                <div class="text-start">
                    <div><strong>ربــــــح</strong></div>
                </div>
            </button>
        </form>
    </div>
    
    <div class="col-6"> <!-- عمود لكل زر -->
        <!-- زر الخسارة -->
        <form method="post" action="process.php" class="trade-form h-100">
            <input type="hidden" name="result" value="loss">
            <button type="submit" class="btn btn-danger btn-lg w-100 d-flex align-items-center justify-content-center rounded-3 shadow-sm hover-shadow h-100">
                <i class="fas fa-times-circle fs-4"></i>&nbsp;
                <span class="d-none d-sm-inline">&nbsp;</span>
                <div class="text-start">
                    <div><strong>خســـارة</strong></div>
                </div>
            </button>
        </form>
    </div>
</div>
                </div>

                <!-- القسم الأيسر: البطاقات (col-md-4) -->
                <div class="col-md-4 d-flex flex-column gap-3 mt-3 mt-md-0">
                    <!-- بطاقة ربح متبقي -->
                    <div class="stat-card bg-info-light p-2 dark-mode-compatible animate__animated animate__fadeInRight">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success me-3">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="flex-grow-6">
                                <h6 class="text-muted mb-1 dark-mode-text">ربح متبقي</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0 text-info dark-mode-text fw-bold"><?= $_SESSION['wins_needed'] - $_SESSION['wins'] ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

<?php
$remaining_trades = $_SESSION['total_trades'] - ($_SESSION['wins'] + $_SESSION['losses']);
$lowTradesWarning = ($_SESSION['total_trades'] > 5 && $remaining_trades <= 3 && $remaining_trades > 0 && !$_SESSION['finished']);
?>

<div class="stat-card  <?= $lowTradesWarning ? 'bg-danger-light p-3 mb-2' : 'bg-info-light p-2 mb-2' ?> dark-mode-compatible animate__animated animate__fadeInRight">
    <div class="d-flex align-items-center">
        <?= $lowTradesWarning 
            ? '<div class="stat-icon bg-danger me-2"><i class="fas fa-exclamation-triangle"></i></div>' 
            : '<div class="stat-icon bg-secondary me-2"><i class="fas fa-hourglass"></i></div>'
        ?>
        <div class="flex-grow-6">
            <h6 class="<?= $lowTradesWarning ? 'text-muted mb-1' : 'text-muted mb-1' ?>">صفقات متبقية</h6>
            <div class="d-flex align-items-center">
                <h4 class="fw-bold <?= $lowTradesWarning ? 'mb-0 text-danger' : 'mb-0 text-info fw-bold' ?>">
                    <?= $remaining_trades ?>
                </h4>
            </div>
        </div>
    </div>
</div>
                </div>
            </div> <!-- /row -->
        </div>
 <!-- /card-body -->
    <?php endif; ?>
</div>

<!-- سجل التداول -->
<div class="card mb-4 card-animate">
    <div class="card-header">
        <h3 class="h5 mb-0"><i class="fas fa-history me-2" style="padding-left:15px;"></i>سجل الجلسة والصفقات</h3>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 responsive-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center w-5">#</th>
                        <th class="text-center">مبلغ التداول</th>
                        <th class="text-center d-none d-md-table-cell">الأرباح</th>
                        <th class="text-center d-none d-md-table-cell">العائد</th>
                        <th class="text-center">الرصيد</th>
                        <th class="text-center">النتيجة</th>
                    </tr>
                </thead>
<tbody>
    <?php if (!empty($_SESSION['rows'])): ?>
        <?php foreach ($_SESSION['rows'] as $index => $row): ?>
        <tr class="<?= $index === count($_SESSION['rows']) - 1 ? 'table-active' : '' ?>">
            <td data-label="#" class="text-center"><?= $index + 1 ?></td>
            <td data-label="مبلغ التداول" class="text-center">
                <span class="fw-bold">$<?= number_format($row['stake'],1) ?></span>
            </td>                        
            <td class="text-center fw-bold d-none d-md-table-cell <?= $row['ritorno'] > 0 ? 'text-success' : ($row['ritorno'] < 0 ? 'text-danger' : '') ?>">
                <?php if ($row['ritorno'] > 0): ?>
                    <i class="fas fa-caret-up"></i> +$<?= $row['ritorno'] ?>
                <?php elseif ($row['ritorno'] < 0): ?>
                    <i class="fas fa-caret-down"></i> -$<?= abs($row['ritorno']) ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td class="text-center d-none d-md-table-cell">$<?= $row['prelievo'] ?></td>
            <td data-label="الرصيد" class="text-center">$<?= number_format($row['cassa'], 1) ?></td>
            <td data-label="الحالة" class="text-center">
                <?php if ($row['ritorno'] > 0): ?>
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> ربح</span>
                <?php elseif (isset($row['status']) && $row['status'] === 'ended'): ?>
                    <span class="badge bg-secondary"><i class="fas fa-ban me-1"></i> إنتهاء الجلسة</span>
                <?php elseif ($index === count($_SESSION['rows']) - 1 && $row['ritorno'] == 0 && !$_SESSION['finished']): ?>
                    <span class="badge bg-warning" style="color:#303030;"><i class="fas fa-clock me-1"></i> قيد الإنتظار</span>
                <?php else: ?>
                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i> خسارة</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($_SESSION['finished']): ?>

    <?php if ($_SESSION['wins'] >= $_SESSION['wins_needed']): ?>
	<?php if ($_SESSION['finished'] && !isset($_SESSION['session_saved'])) {
    $status = ($_SESSION['wins'] >= $_SESSION['wins_needed']) ? 'completed' : 'failed';
    saveSessionToDatabase($_SESSION['user_id'], $_SESSION['initial_capital'], $_SESSION['cassa'], $_SESSION['wins'], $_SESSION['losses'], $status);
} ?>
        <?php saveSessionToDatabase($_SESSION['user_id'], $_SESSION['initial_capital'], $_SESSION['cassa'], $_SESSION['wins'], $_SESSION['losses'], 'completed'); ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: "تم تحقيق الهدف بنجاح !",
                html: `
                    <div style="text-align:center; direction: rtl;">
                        <p class="mb-3 fw-bold fs-4">إجمالي الربح: <span style="color:green;">$<?= number_format($_SESSION['total_profit'], 2) ?></span></p>
                        <p class="mb-0 fw-bold fs-6">الرصيد النهائي: <span style="color:green;">$<?= number_format($_SESSION['cassa'], 2) ?></span></p>
                    </div>
                `,
                icon: "success",
        showCancelButton: true,
        cancelButtonText: '<i class="fas fa-redo"></i> جلسة جديدة',
        confirmButtonText: 'العودة',
        customClass: {
            cancelButton: 'btn btn-warning',
            confirmButton: 'btn btn-primary'
        },
		cancelButtonColor: '#F39C12', 
        confirmButtonColor: '#1B2431', 
        showDenyButton: true, // تفعيل زر جديد
        denyButtonText: '<i class="fas fa-history"></i> سجل التداولات',
		customClass: {
            DenyButton: 'btn btn-history',
        },
        denyButtonColor: '#0C63E3', // اللون الأخضر للزر الجديد
        reverseButtons: true,
        focusConfirm: false,
        allowOutsideClick: false
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            // عند الضغط على زر "جلسة جديدة"
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'reset=1'
            }).then(response => {
                if(response.ok) {
                    location.reload(); // إعادة تحميل الصفحة بعد إعادة التعيين
                }
            });
        } else if (result.isDenied) {
            // عند الضغط على زر "سجل التداولات"
            window.location.href = 'history.php'; // الانتقال إلى صفحة سجل التداولات
        }
            });
        });
        </script>
    <?php else: ?>
        <?php saveSessionToDatabase($_SESSION['user_id'], $_SESSION['initial_capital'], $_SESSION['cassa'], $_SESSION['wins'], $_SESSION['losses'], 'failed'); ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: "إنتهت الجلسة بالخسارة !",
                html: `
                    <div style="text-align:center; direction: rtl;">
                        <p class="mb-3 fw-bold fs-5">الرصيد النهائي: <span style="color:red;">$<?= number_format($_SESSION['cassa'], 2) ?></span></p>
                        <p class="mb-0 fw-bold fs-6">الصفقات المتبقية: <?= $_SESSION['total_trades'] - ($_SESSION['wins'] + $_SESSION['losses']) ?></p>
                    </div>
                `,
                icon: "error",
				showCancelButton: true,
				cancelButtonText: '<i class="fas fa-redo"></i> جلسة جديدة',
				confirmButtonText: 'العودة',
				customClass: {
					cancelButton: 'btn btn-warning',
					confirmButton: 'btn btn-primary'
				},
				cancelButtonColor: '#F39C12', 
				confirmButtonColor: '#1B2431', 
				showDenyButton: true, // تفعيل زر جديد
				denyButtonText: '<i class="fas fa-history"></i> سجل التداولات',
				customClass: {
					DenyButton: 'btn btn-history',
				},
				denyButtonColor: '#0C63E3', // اللون الأخضر للزر الجديد
				reverseButtons: true,
				focusConfirm: false,
				allowOutsideClick: false
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    fetch('process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'reset=1'
                    }).then(response => {
                        if(response.ok) {
                            location.reload();
                        }
                    });
                } else if (result.isDenied) {
                    window.location.href = 'history.php';
                }
            });
        });
        </script>
    <?php endif; ?>
<?php endif; ?>
<!-----------------تنبيه انتهاء الجلسة بالربح----------------->
<?php if ($_SESSION['finished']): ?>
    <?php if ($_SESSION['wins'] >= $_SESSION['wins_needed']): ?>
        <!-- رسالة الربح -->
        <div class="alert alert-success d-flex align-items-center animate__animated animate__bounceIn" role="alert">
            <i class="fas fa-check-circle me-3 fs-3"></i>
            <div>
                <h5 class="alert-heading fw-bold px-3">تم تحقيق الهدف بنجاح !</h5>
                <p class="mb-0 px-3">إجمالي الربح: $<?= number_format($_SESSION['total_profit'], 2) ?></p>
                <p class="mb-0 px-3">الرصيد الحالي: $<?= number_format($_SESSION['cassa'], 2) ?></p>
            </div>
        </div>
    <?php else: ?>
        <!-- رسالة الخسارة -->
        <div class="alert alert-danger d-flex align-items-center animate__animated animate__bounceIn" role="alert">
            <i class="fas fa-times-circle me-3 fs-3"></i>
            <div>
                <h5 class="alert-heading fw-bold px-3">انتهت الجلسة بالخسارة!</h5>
                <p class="mb-0 px-3">الرصيد النهائي: $<?= number_format($_SESSION['cassa'], 2) ?></p>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<!-----------------تنبيه انتهاء الجلسة بالخسارة---------------->


<!-- أداء الجلسة -->
<div class="card mb-4 card-animate">
    <div class="card-header">
        <h3 class="h5 mb-0"><i class="fas fa-chart-simple me-2" style="padding-left:15px;"></i>نتائج الجلسة</h3>
    </div>
    <div class="card-body">
        <div class="row">
<!-- القسم الأيمن - الأداء والصفقات -->
<div class="col-md-6">
    <!-- بطاقة الصفقات الرابحة -->
<div class="stat-card bg-success-light p-3 mb-3 dark-mode-compatible">
    <div class="d-flex align-items-center">
        <div class="stat-icon bg-success me-3">
            <i class="fas fa-check"></i>
        </div>
		
        <div class="flex-grow-1">
            <h6 class="text-muted mb-1 dark-mode-text">الصفقات الرابحة</h6>
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-success dark-mode-text"><?= $_SESSION['wins'] ?></h4>
                <span class="badge bg-success bg-opacity-10 text-success dark-mode-badge">
                    <?= ($_SESSION['wins'] + $_SESSION['losses']) > 0 ? round($_SESSION['wins'] / ($_SESSION['wins'] + $_SESSION['losses']) * 100, 2) : 0 ?>% نجاح
                </span>
            </div>
        </div>
    </div>
</div>
    <!-- بطاقة الصفقات الخاسرة -->
    <div class="stat-card bg-danger-light p-3 mb-3">
        <div class="d-flex align-items-center">
            <div class="stat-icon bg-danger me-3">
                <i class="fas fa-times"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="text-muted mb-1">الصفقات الخاسرة</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-danger"><?= $_SESSION['losses'] ?></h4>
                    <span class="badge bg-danger bg-opacity-10 text-danger">
                        متبقي <?= $_SESSION['total_trades'] - ($_SESSION['wins'] + $_SESSION['losses']) ?> صفقات
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- بطاقة الصفقات الخاسرة -->
    <div class="stat-card bg-danger-light p-3 mb-3">
        <div class="d-flex align-items-center">
            <div class="stat-icon bg-danger me-3">
                <i class="fas fa-times"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="text-muted mb-1">الصفقات الخاسرة</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-danger"><?= $_SESSION['losses'] ?></h4>
                    <span class="badge bg-danger bg-opacity-10 text-danger">
                        متبقي <?= $_SESSION['total_trades'] - ($_SESSION['wins'] + $_SESSION['losses']) ?> صفقات
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>            
<!-- القسم الأيسر - الرسم البياني -->
            <div class="col-md-6">
                <div class="chart-container h-100" style="min-height: 300px;">
                    <canvas id="capitalChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row g-3 mb-4 d-flex justify-content-between">
    <div class="col-md-4 col-6">
        <form method="post" action="process.php" class="reset-form">
            <input type="hidden" name="reset" value="1">
            <button type="submit" class="btn btn-warning w-100 py-3">
                <i class="fas fa-redo me-2" style="padding-left:15px; font-weight:bold;"></i>إعادة تعيين الجلسة
            </button>
        </form>
    </div>
    <div class="col-md-4 col-6">
        <button id="exportData" class="btn btn-info w-100 py-3">
            <i class="fas fa-file-export me-2" style="padding-left:15px;"></i>تصدير البيانات
        </button>
    </div>
    <div class="col-md-4 col-12">
        <a href="history.php" class="btn btn-primary w-100 py-3">
            <i class="fas fa-history me-2"  style="padding-left:15px;"></i>سجل التداولات
        </a>
    </div>
</div>

                <?php endif; ?>
            </div>
        </div>
	<footer class="stat-card bg-info-light p-2 dark-mode-compatible" style="border-radius=20px !important ;">
    <i class="far fa-copyright py-3 px-3"></i>نظام ريسك فري لإدارة رأس المال - تداول الخيارات الثنائية
    <a href="https://t.me/SadScan"><i class="fab fa-telegram px-2"></i>SadScan</a>
  </footer>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
// في قسم script في index.php
$(document).ready(function() {
    // التحقق من المدخلات قبل الإرسال
    $('form').on('submit', function(e) {
        const initialCapital = parseFloat($('#initial_capital').val());
        const totalTrades = parseInt($('#total_trades').val());
        const winsNeeded = parseInt($('#wins_needed').val());
        const payout = parseFloat($('#payout').val());
        
        // التحقق من رأس المال
        if (initialCapital < 50) {
            showToast('رأس المال يجب أن لا يقل عن 50', 'danger');
            e.preventDefault();
            return;
        }
        
        // التحقق من عدد الصفقات
        if (totalTrades > 100) {
            showToast('عدد الصفقات الإجمالي يجب أن لا يزيد عن 100', 'danger');
            e.preventDefault();
            return;
        }
        
        // التحقق من صفقات الربح
        if (winsNeeded > totalTrades) {
            showToast('عدد صفقات الربح يجب أن لا يزيد عن عدد الصفقات الإجمالي', 'danger');
            e.preventDefault();
            return;
        }
        
        // التحقق من عائد الربح
        if (payout < 1.5 || payout > 2) {
            showToast('عائد الربح يجب أن يكون بين 1.5 و 2 فقط', 'danger');
            e.preventDefault();
            return;
        }
    });
    
    // التحقق أثناء الكتابة لرأس المال
    $('#initial_capital').on('input', function() {
        const value = parseFloat($(this).val()) || 0;
        
        if (value < 50) {
            $(this).addClass('is-invalid');
            $('#initial_capital_feedback').remove();
            $(this).after('<div id="initial_capital_feedback" class="invalid-feedback">يجب أن لا يقل عن 50</div>');
        } else {
            $(this).removeClass('is-invalid');
            $('#initial_capital_feedback').remove();
        }
    });
    
    // التحقق أثناء الكتابة لعدد الصفقات
    $('#total_trades').on('input', function() {
        const value = parseInt($(this).val()) || 0;
        
        if (value > 100) {
            $(this).addClass('is-invalid');
            $('#total_trades_feedback').remove();
            $(this).after('<div id="total_trades_feedback" class="invalid-feedback">يجب أن لا يزيد عن 100</div>');
        } else {
            $(this).removeClass('is-invalid');
            $('#total_trades_feedback').remove();
        }
        
        // تحديث الحد الأقصى لصفقات الربح
        const winsNeeded = parseInt($('#wins_needed').val()) || 0;
        if (winsNeeded > value) {
            $('#wins_needed').val(value);
        }
    });
    
    // التحقق أثناء الكتابة لصفقات الربح
    $('#wins_needed').on('input', function() {
        const totalTrades = parseInt($('#total_trades').val()) || 0;
        const value = parseInt($(this).val()) || 0;
        
        if (value > totalTrades) {
            $(this).addClass('is-invalid');
            $('#wins_needed_feedback').remove();
            $(this).after('<div id="wins_needed_feedback" class="invalid-feedback">يجب أن لا يزيد عدد صفقات الربح عن عدد الصفقات الكلي</div>');
        } else {
            $(this).removeClass('is-invalid');
            $('#wins_needed_feedback').remove();
        }
    });
    
    // التحقق أثناء الكتابة لعائد الربح
    $('#payout').on('input', function() {
        const value = parseFloat($(this).val()) || 0;
        
        if (value < 1.5 || value > 2) {
            $(this).addClass('is-invalid');
            $('#payout_feedback').remove();
            $(this).after('<div id="payout_feedback" class="invalid-feedback">يجب أن يكون بين 1.5 و 2</div>');
        } else {
            $(this).removeClass('is-invalid');
            $('#payout_feedback').remove();
        }
    });
});
        $(document).ready(function() {
            // Hide loader
            $(".loader-wrapper").fadeOut("slow");
            
// Dark Mode Toggle
const darkModeToggle = $('#darkModeToggle');
darkModeToggle.click(function() {
    // إضافة صنف مؤقت لمنع الوميض
    document.body.classList.add('dark-mode-transition');
    
    $('body').toggleClass('dark-mode');
    const isDark = $('body').hasClass('dark-mode');
    localStorage.setItem('darkMode', isDark);
    $(this).html(isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>');
    
    // إزالة الصنف المؤقت بعد الانتقال
    setTimeout(() => {
        document.body.classList.remove('dark-mode-transition');
    }, 500);
});
            
            // Toast Function
            function showToast(message, type = 'success') {
                const toast = $(`
                    <div class="toast show align-items-center text-white bg-${type} border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);
                $('.toast-container').append(toast);
                setTimeout(() => toast.remove(), 3000);
            }
            
            // Handle trade form submissions
            $('.trade-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const button = form.find('button[type="submit"]');
                const originalText = button.html();
                
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> جاري المعالجة...');
                
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(), 
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('تم تسجيل الصفقة بنجاح', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showToast(response.error || 'حدث خطأ أثناء المعالجة', 'danger');
                        }
                    },
                    error: function() {
                        showToast('حدث خطأ في الاتصال بالخادم', 'danger');
                    },
                    complete: function() {
                        button.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            // Handle reset form submission
            $('.reset-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const button = form.find('button[type="submit"]');
                const originalText = button.html();
                
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> جاري المعالجة...');
                
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('تم إعادة تعيين الجلسة بنجاح', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showToast(response.error || 'حدث خطأ أثناء المعالجة', 'danger');
                        }
                    },
                    error: function() {
                        showToast('حدث خطأ في الاتصال بالخادم', 'danger');
                    },
                    complete: function() {
                        button.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            // Export Data
            $('#exportData').click(function() {
                const data = {
                    session: <?= json_encode($_SESSION) ?>,
                    date: new Date().toLocaleString()
                };
                
                const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
                const url = URL.createObjectURL(blob);
                
                const a = document.createElement('a');
                a.href = url;
                a.download = `trading-session-${new Date().toISOString()}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                showToast('تم تصدير بيانات الجلسة بنجاح', 'info');
            });
            
            // Auto-scroll to bottom of table
            $('.table-responsive').scrollTop($('.table-responsive')[0].scrollHeight);
        });
    </script>
<!-- عرض شبكة الاحتمالات 
<div class="card mb-4 card-animate">
    <div class="card-header">
        <h3 class="h5 mb-0"><i class="fas fa-project-diagram me-2"></i>شبكة الاحتمالات</h3>
    </div>
    <div class="card-body">
        <?php 
        displayNetwork(
            $_SESSION['V'], 
            $_SESSION['wins'], 
            $_SESSION['losses']
        ); 
        ?>
    </div>
</div> -->
<script>
document.querySelectorAll('.trade-form button[type="submit"]').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault(); // منع الإرسال التقليدي

        const form = this.closest('form'); // حدد الفورم الحالي فقط
        const formData = new FormData(form);

        fetch('process.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // نجاح = أعد تحميل الصفحة
            } else {
                alert(data.error || 'حدث خطأ في الاتصال بالخادم');
            }
        })
        .catch(() => {
            alert('حدث خطأ في الاتصال بالخادم');
        });
    });
});
</script>
<script>
// بداية: تأكد من ظهور اللودر أولاً
document.getElementById('loader-wrapper').style.display = 'flex';

// نظام التحميل الرئيسي
function startLoader() {
    let percentage = 0;
    const loader = document.getElementById('loader-wrapper');
    const percentElem = document.getElementById('loader-percentage');
    
    const interval = setInterval(() => {
        percentage += Math.random() * 15;
        if (percentage > 100) percentage = 100;
        percentElem.textContent = `${Math.floor(percentage)}%`;
        
        if (percentage >= 100) {
            clearInterval(interval);
            loader.style.transition = 'opacity 0.5s ease';
            loader.style.opacity = '0';
            
            // الإزالة النهائية بعد انتهاء الانتقال
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }
    }, 50);
}

// بدء التحميل بعد تأخير بسيط
setTimeout(startLoader, 100);

// نظام احتياطي: إخفاء اللودر عند اكتمال تحميل الصفحة
window.addEventListener('load', () => {
    const loader = document.getElementById('loader-wrapper');
    if (loader) {
        loader.style.transition = 'opacity 0.5s ease';
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.style.display = 'none';
        }, 500);
    }
});

// إلغاء أي سكريبتات أخرى تتحكم في اللودر
$(document).ready(function() {
    // إزالة أي fadeOut آخر للودر
    $(".loader-wrapper").stop(true, true).removeAttr('style').hide();
});
</script>
<script>
document.addEventListener('contextmenu', function(e) {
    if (e.target.id === 'logo') {
        e.preventDefault();
    }
});
</script>
<script>
// رسم بياني لتطور رأس المال
function prepareChartData() {
    const capitalData = [<?= $_SESSION['initial_capital'] ?>];
    const labels = ['البداية'];
    
    <?php if (!empty($_SESSION['rows'])): ?>
<?php foreach ($_SESSION['rows'] as $index => $row): ?>
    capitalData.push(<?= $row['cassa'] ?>);
    labels.push('صفقة <?= $index + 1 ?>');
<?php endforeach; ?>
    <?php endif; ?>
    
    return { capitalData, labels };
}

function renderCapitalChart() {
    const { capitalData, labels } = prepareChartData();
    const ctx = document.getElementById('capitalChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'تطور رأس المال',
                data: capitalData,
                borderColor: '#2469ff',
                backgroundColor: 'rgba(36, 105, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    rtl: true,
                    labels: {
                        font: {
                            family: 'Cairo',
                          size: 14
                    }  
                        }
                },
                tooltip: {
                    rtl: true,
                    callbacks: {
                        label: function(context) {
                            return '$' + context.raw.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
}

// استدعاء الرسم البياني عند تحميل الصفحة
$(document).ready(function() {
    renderCapitalChart();
})
</script>
</body>
</html>