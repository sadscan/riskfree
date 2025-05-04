<?php
// إنشاء سجل الأخطاء
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/trading_errors.log');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // المستخدم غير مسجل الدخول
    header("Location: login.php");
    exit;
}
?>

<?php
function buildNetwork($total_trades, $wins_needed, $payout) {
        // تحقق إضافي من الشروط
    if ($total_trades > 100) {
        throw new Exception('عدد الصفقات الإجمالي يجب أن لا يزيد عن 100');
    }
    
    if ($wins_needed > $total_trades) {
        throw new Exception('عدد صفقات الربح يجب أن لا يزيد عن عدد الصفقات الإجمالي');
    }
    
    if ($payout < 1.5 || $payout > 2) {
        throw new Exception('عائد الربح يجب أن يكون بين 1.5 و 2 فقط');
    }
    $rows = $total_trades;  // تغيير من +1 إلى بدون إضافة
    $cols = $wins_needed + 1;
    
    $V = array_fill(0, $rows, array_fill(0, $cols, 0.0));
    
    // تعبئة القيم الأساسية
    for ($i = $rows - 1; $i >= 0; $i--) {
        for ($j = 0; $j < $cols; $j++) {
            // حالة الفوز الكامل
            if ($j == $wins_needed) {
                $V[$i][$j] = 1.0;
            }
            // حالة القطر الرئيسي
            elseif (($wins_needed - $j) == ($rows - $i)) {
                $V[$i][$j] = pow($payout, ($rows - $i));
            }
            // حساب القيم المتبقية
            else {
                $v0 = $V[$i + 1][$j] ?? 0;
                $v1 = $V[$i + 1][$j + 1] ?? 0;
                
                $denominator = $v0 + ($payout - 1) * $v1;
                $V[$i][$j] = ($denominator != 0) 
                    ? ($payout * $v0 * $v1) / $denominator 
                    : 0.0;
            }
        }
    }
    
    return $V;
}

function saveSessionToDatabase($userId, $initialCapital, $finalBalance, $wins, $losses, $status) {
    try {
        $db = new PDO('mysql:host=localhost;dbname=riskfree;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("INSERT INTO trading_sessions (user_id, initial_capital, final_balance, wins, losses, total_trades, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $initialCapital, $finalBalance, $wins, $losses, $wins + $losses, $status]);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
    }
}


function calculateProfile($V) {
    return round($V[0][0], 6);
}


function calculateStake($V, $wins, $losses, $cassa, $payout) {
    try {
        $total_trades = $_SESSION['total_trades'];
        $wins_needed = $_SESSION['wins_needed'];
        $remaining_trades = $total_trades - $wins - $losses;
        $needed_wins = $wins_needed - $wins;

        // الحالة الخاصة: عندما تصبح الصفقات المتبقية = الصفقات الرابحة المطلوبة
        if ($remaining_trades == $needed_wins && $remaining_trades > 0) {
            return max(1, round($cassa, 2)); // التأكد من أن المبلغ لا يقل عن 1
        }

        $current_step = $wins + $losses;
        $row = $current_step + 1;
        $col_win = $wins + 1;
        $col_loss = $wins;

        $prob_loss = $V[$row][$col_loss] ?? 0;
        $prob_win = $V[$row][$col_win] ?? 0;

        // إذا لم تعد هناك احتمالية للربح (خسارة مؤكدة)
        if ($prob_win <= 0 || $needed_wins <= 0) {
            return 0; // لا داعي للدخول
        }

        // المعادلة الأساسية
        $denominator = $prob_loss + ($payout - 1) * $prob_win;
        if ($denominator <= 0) {
            return round($cassa, 2); // الدخول بالرصيد كإجراء احتياطي
        }

        $stake = (1 - ($payout * $prob_win) / $denominator) * $cassa;

        // التأكد من أن المبلغ ضمن الحدود المنطقية
        return max(1, min(round($stake, 2), round($cassa, 2)));
    } catch (Exception $e) {
        return max(1, round($cassa * 0.05, 2)); // قيمة احتياطية لا تقل عن 1
    }
}

function displayNetwork($V, $current_wins, $current_losses) {
    $current_step = $current_wins + $current_losses;
    $prob_win_pos = [$current_step + 1, $current_wins + 1];
    $prob_loss_pos = [$current_step + 1, $current_wins];
    
    echo '<style>
        .prob-win { background-color: #d4edda !important; font-weight: bold; }
        .prob-loss { background-color: #d1ecf1 !important; font-weight: bold; }
        .start-point { background-color: #28a745 !important; color: white !important; }
        .network-table { font-size: 0.9rem; }
        .network-table th, .network-table td { padding: 0.3rem; text-align: center; }
    </style>';

    echo '<div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4>شبكة الاحتمالات</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm network-table">';
    
    // Header
    echo '<tr><th>Step\Wins</th>';
    for ($j = 0; $j < count($V[0]); $j++) {
        echo '<th>'.$j.'</th>';
    }
    echo '</tr>';
    
    // Rows
    for ($i = 0; $i < count($V); $i++) {
        echo '<tr><td>'.$i.'</td>';
        for ($j = 0; $j < count($V[$i]); $j++) {
            $class = '';
            if ($i == $prob_win_pos[0] && $j == $prob_win_pos[1]) $class = 'prob-win';
            if ($i == $prob_loss_pos[0] && $j == $prob_loss_pos[1]) $class = 'prob-loss';
            if ($i == 0 && $j == 0) $class = 'start-point';
            
            echo '<td class="'.$class.'">'.round($V[$i][$j], 6).'</td>';
        }
        echo '</tr>';
    }
    
    echo '</table></div>';
    
    // Legend
    echo '<div class="mt-3 p-3 bg-light rounded">
            <h5>دليل الألوان:</h5>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge prob-win">prob_win (Vincite+1)</span>
                <span class="badge prob-loss">prob_loss (Vincite)</span>
                <span class="badge start-point">نقطة البداية</span>
            </div>
            <div class="alert alert-info p-2">
                <strong>الموقع الحالي:</strong> Step '.($current_step + 1).' | Wins '.$current_wins.'<br>
                <strong>prob_win:</strong> الصف '.$prob_win_pos[0].' العمود '.$prob_win_pos[1].'<br>
                <strong>prob_loss:</strong> الصف '.$prob_loss_pos[0].' العمود '.$prob_loss_pos[1].'
            </div>
          </div>';
    
    echo '</div></div>';
}

function addNewRow() {
    if ($_SESSION['finished']) return;
    
    $investment = calculateStake(
        $_SESSION['V'], 
        $_SESSION['wins'], 
        $_SESSION['losses'], 
        $_SESSION['cassa'], 
        $_SESSION['payout']
    );
    
    $_SESSION['rows'][] = [
        'stake' => $investment,
        'ritorno' => 0,
        'prelievo' => 0,
        'cassa' => $_SESSION['cassa'],
        'max_cassa' => $_SESSION['max_cassa'],
        'losses' => $_SESSION['losses'],
        'wins' => $_SESSION['wins'],
        'winrate' => ($_SESSION['wins'] + $_SESSION['losses']) > 0 
            ? round($_SESSION['wins'] / ($_SESSION['wins'] + $_SESSION['losses']), 4) 
            : 0
    ];
}

function recordResult($result) {
    if ($_SESSION['finished']) return;
    
    // التحقق من انتهاء الجلسة بسبب انخفاض الرصيد
    if ($_SESSION['cassa'] < 1) {
        $_SESSION['finished'] = true;
        checkEnd();
        return;
    }
    $current_row = &$_SESSION['rows'][count($_SESSION['rows']) - 1];
    $stake = $current_row['stake'];

    if ($result == 'win') {
        $ritorno = $stake * ($_SESSION['payout'] - 1);
        $correction = min($ritorno, max(0, $_SESSION['max_cassa'] - $_SESSION['cassa']));
        $prelievo = max(0, round($ritorno - $correction, 2));
        $_SESSION['cassa'] += $correction + $prelievo;
        $_SESSION['wins']++;
        if ($_SESSION['cassa'] > $_SESSION['max_cassa']) {
            $_SESSION['max_cassa'] = $_SESSION['cassa'];
        }
        $current_row['ritorno'] = round($ritorno, 2);
        $current_row['prelievo'] = $prelievo;
        $_SESSION['total_profit'] += $prelievo;
    } else {
        $_SESSION['cassa'] -= $stake;
        $_SESSION['losses']++;
        $current_row['ritorno'] = 0;
        $current_row['prelievo'] = 0;
    }

    $current_row['cassa'] = round($_SESSION['cassa'], 2);
    $current_row['max_cassa'] = round($_SESSION['max_cassa'], 2);
    $current_row['losses'] = $_SESSION['losses'];
    $current_row['wins'] = $_SESSION['wins'];
    $current_row['winrate'] = round($current_row['winrate'], 4);

    checkEnd();
    addNewRow();
}

function checkEnd() {
    $target_profit = $_SESSION['target_profit'];
    $current_profit = $_SESSION['total_profit'];
    $wins = $_SESSION['wins'];
    $wins_needed = $_SESSION['wins_needed'];
    $total_trades = $_SESSION['total_trades'];
    $current_trades = $wins + $_SESSION['losses'];
    
    // حالة الخسارة (الرصيد أقل من 1 دولار)
    if ($_SESSION['cassa'] < 1) {
        $_SESSION['finished'] = true;
        saveSessionToHistory(true);
        return;
    }
    
    // حالة الربح
    $profit_condition = ($current_profit >= $target_profit) || 
                       (abs($target_profit - $current_profit) < 1 && $current_profit > 0);
    
    if ($wins >= $wins_needed && $profit_condition) {
        $_SESSION['finished'] = true;
        saveSessionToHistory(true);
        return;
    }
    
    // حالة الخسارة (استنفاذ جميع الصفقات)
    if ($current_trades >= $total_trades) {
        $_SESSION['finished'] = true;
        saveSessionToHistory(true);
    }
}
function resetSession() {
    error_log("بدء عملية إعادة تعيين الجلسة");
    
    // لا تحفظ الجلسة عند إعادة التعيين إلا إذا كانت تحتوي على صفقات
    if (!empty($_SESSION['rows'])) {
        $saveResult = saveSessionToHistory();
        if (!$saveResult) {
            error_log("تحذير: فشل في حفظ الجلسة السابقة قبل إعادة التعيين");
        }
    }
    
    // التحقق من الشروط قبل إعادة التعيين
    if ($_SESSION['total_trades'] > 100) {
        $_SESSION['total_trades'] = 100;
    }
    
    if ($_SESSION['wins_needed'] > $_SESSION['total_trades']) {
        $_SESSION['wins_needed'] = $_SESSION['total_trades'];
    }
    
    if ($_SESSION['payout'] < 1.5) {
        $_SESSION['payout'] = 1.5;
    } elseif ($_SESSION['payout'] > 2) {
        $_SESSION['payout'] = 2;
    }
    
    // إعادة إنشاء الشبكة مع الإعدادات الحالية
    $_SESSION['V'] = buildNetwork(
        $_SESSION['total_trades'],
        $_SESSION['wins_needed'],
        $_SESSION['payout']
    );
    
    // إعادة تعيين جميع المتغيرات
    $_SESSION['rows'] = [];
    $_SESSION['cassa'] = $_SESSION['initial_capital'];
    $_SESSION['max_cassa'] = $_SESSION['initial_capital'];
    $_SESSION['wins'] = 0;
    $_SESSION['losses'] = 0;
    $_SESSION['finished'] = false;
    $_SESSION['total_profit'] = 0;
    $_SESSION['show_params'] = true;
    
    addNewRow();
    error_log("تمت إعادة تعيين الجلسة بنجاح");
}
function saveSessionToHistory($forceSave = false) {
    if (empty($_SESSION['rows']) && !$forceSave) {
        error_log("لا يمكن حفظ الجلسة: لا توجد صفقات");
        return false;
    }

    try {
        $db = new PDO('mysql:host=localhost;dbname=riskfree;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $winRate = ($_SESSION['wins'] + $_SESSION['losses']) > 0 
            ? round($_SESSION['wins'] / ($_SESSION['wins'] + $_SESSION['losses']) * 100, 2)
            : 0;

        // تحديد حالة الجلسة
        $status = 'aborted'; // افتراضي: ملغية
        
        if ($_SESSION['finished']) {
            if ($_SESSION['wins'] >= $_SESSION['wins_needed']) {
                $status = 'completed'; // مكتملة (ربح)
            } elseif ($_SESSION['cassa'] < 1 || ($_SESSION['wins'] + $_SESSION['losses']) >= $_SESSION['total_trades']) {
                $status = 'failed'; // خسارة
            }
        }

        // التحقق من عدم تكرار الحفظ
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM trading_sessions WHERE user_id = ? AND initial_capital = ? AND start_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmtCheck->execute([$_SESSION['user_id'], $_SESSION['initial_capital']]);
        $existingSessions = $stmtCheck->fetchColumn();

        if ($existingSessions > 0 && !$forceSave) {
            error_log("تم تجاهل حفظ الجلسة: جلسة مشابهة مسجلة حديثاً");
            return false;
        }

        $stmt = $db->prepare("INSERT INTO trading_sessions 
                            (user_id, start_time, end_time, initial_capital, final_balance, 
                             total_trades, wins, losses, win_rate, total_profit, status, session_data) 
                             VALUES (?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $result = $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['initial_capital'],
            $_SESSION['cassa'],
            $_SESSION['total_trades'],
            $_SESSION['wins'],
            $_SESSION['losses'],
            $winRate,
            $_SESSION['total_profit'],
            $status,
            json_encode([
                'parameters' => [
                    'initial_capital' => $_SESSION['initial_capital'],
                    'total_trades' => $_SESSION['total_trades'],
                    'wins_needed' => $_SESSION['wins_needed'],
                    'payout' => $_SESSION['payout']
                ],
                'trades' => $_SESSION['rows'],
                'performance' => [
                    'win_rate' => $winRate,
                    'total_profit' => $_SESSION['total_profit'],
                    'max_balance' => $_SESSION['max_cassa']
                ]
            ], JSON_UNESCAPED_UNICODE)
        ]);

        if ($result) {
            $_SESSION['session_saved'] = true; // علامة أن الجلسة تم حفظها
            error_log("تم حفظ الجلسة بنجاح - الحالة: " . $status);
            return true;
        } else {
            error_log("فشل في حفظ الجلسة: " . implode(", ", $stmt->errorInfo()));
            return false;
        }

    } catch (PDOException $e) {
        error_log("حدث استثناء عند حفظ الجلسة: " . $e->getMessage());
        return false;
    }
}
// FOREX 

// حفظ بيانات تداول الفوركس
function saveForexTrade($userId, $data) {
    try {
        $db = new PDO('mysql:host=localhost;dbname=riskfree;charset=utf8', 'root', '');
        $stmt = $db->prepare("INSERT INTO forex_trades 
                            (user_id, pair, position_size, entry_price, exit_price, 
                             profit, risk_percent, notes, trade_date) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $userId,
            $data['pair'],
            $data['position_size'],
            $data['entry_price'],
            $data['exit_price'],
            $data['profit'],
            $data['risk_percent'],
            $data['notes']
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

// تحليل أداء المستخدم
function getUserPerformance($userId) {
    try {
        $db = new PDO('mysql:host=localhost;dbname=riskfree;charset=utf8', 'root', '');
        
        // إحصائيات عامة
        $stats = $db->query("
            SELECT 
                COUNT(*) as total_trades,
                SUM(CASE WHEN profit > 0 THEN 1 ELSE 0 END) as winning_trades,
                SUM(CASE WHEN profit <= 0 THEN 1 ELSE 0 END) as losing_trades,
                SUM(profit) as total_profit,
                AVG(CASE WHEN profit > 0 THEN profit END) as avg_win,
                AVG(CASE WHEN profit <= 0 THEN profit END) as avg_loss
            FROM forex_trades
            WHERE user_id = $userId
        ")->fetch(PDO::FETCH_ASSOC);
        
        // أفضل وأسوأ الصفقات
        $extremeTrades = $db->query("
            (SELECT 'best' as type, pair, profit, trade_date 
             FROM forex_trades 
             WHERE user_id = $userId AND profit > 0 
             ORDER BY profit DESC LIMIT 3)
            UNION
            (SELECT 'worst' as type, pair, profit, trade_date 
             FROM forex_trades 
             WHERE user_id = $userId AND profit <= 0 
             ORDER BY profit ASC LIMIT 3)
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // أداء حسب أزواج العملات
        $pairPerformance = $db->query("
            SELECT pair, 
                   COUNT(*) as trade_count,
                   SUM(profit) as total_profit,
                   AVG(profit) as avg_profit
            FROM forex_trades
            WHERE user_id = $userId
            GROUP BY pair
            ORDER BY total_profit DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'stats' => $stats,
            'extreme_trades' => $extremeTrades,
            'pair_performance' => $pairPerformance
        ];
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}// جلب سجل صفقات الفوركس للمستخدم
function getForexTradeHistory($userId) {
    try {
        $db = new PDO('mysql:host=localhost;dbname=riskfree;charset=utf8', 'root', '');
        $stmt = $db->prepare("SELECT * FROM forex_trades WHERE user_id = ? ORDER BY trade_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

// حساب قيمة النقطة لأزواج مختلفة
function calculatePipValue($pair, $lotSize) {
    $pipValues = [
        'EURUSD' => 10,
        'GBPUSD' => 10,
        'USDJPY' => 9.09, // تقريبي لليورو/ين
        'XAUUSD' => 1 // قيمة تقريبية للذهب
    ];
    
    return isset($pipValues[$pair]) ? $pipValues[$pair] * $lotSize : 10 * $lotSize;
}

// حساب الهامش المطلوب
function calculateMarginRequired($pair, $lotSize, $leverage) {
    // هذه قيم افتراضية - يجب تعديلها حسب السعر الحالي للزوج
    $contractSizes = [
        'EURUSD' => 100000,
        'GBPUSD' => 100000,
        'USDJPY' => 100000,
        'XAUUSD' => 100 // أوقية للذهب
    ];
    
    $contractSize = isset($contractSizes[$pair]) ? $contractSizes[$pair] : 100000;
    return ($lotSize * $contractSize) / $leverage;
}
?>