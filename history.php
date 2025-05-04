<?php
session_start();
require_once 'functions.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
 $db = new PDO('mysql:host=localhost;dbname=riskfree;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ترتيب الجلسات من الأحدث إلى الأقدم
    $stmt = $db->prepare("SELECT * FROM trading_sessions WHERE user_id = ? ORDER BY start_time DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل الجلسات - RiskFree System</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- نفس زر الوضع الليلي ومعلومات المستخدم من index.php -->
<!-- زر الرجوع -->
<a href="javascript:void(0);" onclick="goBack();" class="btn btn-dark" id="backButton">
    <i class="fas fa-arrow-left"></i>
</a>
    <div class="container py-4">
            <div class="text-center mb- logo_site">
            <img class ="animate__animated animate__fadeIn animate__delay-1s logo_header" src="assets/img/logo.png" alt="Commando Trading System" class="logo">
        </div>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-history me-2" style="padding-left:10px;"></i>سجل الجلسات السابقة</h4>
            </div>
            <div class="card-body">
                <?php if (count($sessions) > 0): ?>
                <div class="table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>رأس المال</th>
                                <th>الرصيد النهائي</th>
                                <th>الصفقات</th>
                                <th>الربح</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                        
                            <?php 
                                $counter = 1; // بدء العد التنازلي
                                foreach ($sessions as $session): 
                                $profit = $session['final_balance'] - $session['initial_capital'];
                                $winRate = $session['total_trades'] > 0 
                                    ? round($session['wins'] / $session['total_trades'] * 100, 2)
                                    : 0;
                            ?>


                            <tr>
								<td style="padding-right:5px;"><?= $counter++ ?></td> <!-- عرض العداد تصاعديًا -->
                                <td><?= date('Y-m-d', strtotime($session['start_time'])) ?></td>
                                <td>$<?= number_format($session['initial_capital'], 2) ?></td>
                                <td>$<?= number_format($session['final_balance'], 2) ?></td>
                                <td style="font-weight:700;"><?= $session['wins'] ?> ربح / <?= $session['losses'] ?> خسارة</td>
                                <td style="font-weight:900;" class="<?= $profit >= 0 ? 'text-success' : 'text-danger' ?>">
                                    $<?= number_format($profit, 2) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $session['status'] == 'completed' ? 'success' : 'danger' ?>">
                                        <?= $session['status'] == 'completed' ? 'مكتملة' : 'ملغاة' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
<div class="form-check form-switch" style="direction: rtl; text-align: left;">
    <input class="form-check-input" type="checkbox" id="filterCompleted" checked>
    <label class="form-check-label" for="filterCompleted">عرض الصفقات المكتملة فقط</label>
</div>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    لا توجد جلسات مسجلة حتى الآن
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<!-- زر تغيير الوضع الليلي -->
<button id="darkModeToggle" class="theme-toggle-btn" title="تبديل الوضع">
    <i class="fas fa-moon"></i>
</button>
<!-- معلومات المستخدم -->
<!-- زر الإعدادات العائم -->
<button id="toggleUserInfo" class="floating-btn" title="الإعدادات">
<i class="fas fa-user-cog"></i>
</button>

<!-- قائمة معلومات الحساب -->
<div id="userInfoContainer" class="user-info-container animate__animated animate__fadeInDown" style="display: none;">
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

    <!-- Modal لعرض التفاصيل -->
    <div class="modal fade" id="sessionDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تفاصيل الجلسة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="sessionDetailsContent">
                    جاري تحميل البيانات...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- جميع سكربتات JavaScript من index.php -->
    <script>
    $(document).ready(function() {
        // عرض تفاصيل الجلسة
        $('.view-session').click(function() {
            const sessionId = $(this).data('session-id');
            $('#sessionDetailsContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p>جاري تحميل البيانات...</p></div>');
            
            $.get('api/get_session_details.php?id=' + sessionId, function(data) {
                $('#sessionDetailsContent').html(data);
            }).fail(function() {
                $('#sessionDetailsContent').html('<div class="alert alert-danger">حدث خطأ أثناء جلب البيانات</div>');
            });
            
            $('#sessionDetailsModal').modal('show');
        });
    });
    </script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/script.js"></script>
<script>$(document).ready(function() {
    // التمركز من الأعلى بدلاً من الأسفل
    $('.table-responsive').scrollTop(0);
});</script>

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
            showToast('رأس المال يجب أن لا يقل عن 50 دولار', 'danger');
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
            $(this).after('<div id="initial_capital_feedback" class="invalid-feedback">يجب أن لا يقل عن 50 دولار</div>');
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
            const darkModeEnabled = localStorage.getItem('darkMode') === 'true';
            
            if(darkModeEnabled) {
                $('body').addClass('dark-mode');
                darkModeToggle.html('<i class="fas fa-sun"></i>');
            }
            
            darkModeToggle.click(function() {
                $('body').toggleClass('dark-mode');
                const isDark = $('body').hasClass('dark-mode');
                localStorage.setItem('darkMode', isDark);
                $(this).html(isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>');
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
    function goBack() {
        window.history.back();
    }
</script>
<script>$(document).ready(function() {
    // فلترة الجلسات المكتملة فقط
    function filterCompletedSessions(showOnlyCompleted) {
        $('tbody tr').each(function() {
            const isCompleted = $(this).find('.badge').hasClass('bg-success');
            $(this).toggle(!showOnlyCompleted || isCompleted);
        });
    }

    // تطبيق الفلترة عند التحميل
    filterCompletedSessions(true);

    // تغيير الفلترة عند النقر على الزر
    $('#filterCompleted').change(function() {
        filterCompletedSessions($(this).is(':checked'));
    });
});</script>
</body>
</html>