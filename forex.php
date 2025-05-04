<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// نظام اللغات
$lang = isset($_GET['lang']) && $_GET['lang'] == 'ar' ? 'ar' : 'en';
$translations = [
    'en' => [
        'title' => 'Forex Trading System',
        'position_calculator' => 'Position Calculator',
        'margin_calculator' => 'Margin Calculator',
        'profit_calculator' => 'Profit/Loss Calculator',
        'pip_calculator' => 'Pip Value Calculator',
        'risk_management' => 'Risk Management',
        'performance' => 'Performance Analysis',
        'saved_data' => 'Saved Trades',
        'account_balance' => 'Account Balance',
        'risk_percent' => 'Risk Percentage',
        'stop_loss_pips' => 'Stop Loss (Pips)',
        'currency_pair' => 'Currency Pair',
        'calculate' => 'Calculate',
        'position_size' => 'Position Size',
        'risk_amount' => 'Risk Amount',
        'pip_value' => 'Pip Value',
        'potential_loss' => 'Potential Loss',
        'lot_size' => 'Lot Size',
        'leverage' => 'Leverage',
        'margin_required' => 'Margin Required',
        'entry_price' => 'Entry Price',
        'exit_price' => 'Exit Price',
        'profit_loss' => 'Profit/Loss',
        'trade_size' => 'Trade Size (Lots)',
        'save_trade' => 'Save Trade',
        'trade_history' => 'Trade History',
        'performance_stats' => 'Performance Statistics',
        'total_trades' => 'Total Trades',
        'winning_trades' => 'Winning Trades',
        'losing_trades' => 'Losing Trades',
        'win_rate' => 'Win Rate',
        'total_profit' => 'Total Profit',
        'avg_win' => 'Avg Win',
        'avg_loss' => 'Avg Loss',
        'best_trades' => 'Best Trades',
        'worst_trades' => 'Worst Trades',
        'pair_performance' => 'Pair Performance'
    ],
    'ar' => [
        'title' => 'نظام تداول الفوركس',
        'position_calculator' => 'حاسبة حجم الصفقة',
        'margin_calculator' => 'حاسبة الهامش',
        'profit_calculator' => 'حاسبة الربح/الخسارة',
        'pip_calculator' => 'حاسبة قيمة النقطة',
        'risk_management' => 'إدارة المخاطر',
        'performance' => 'تحليل الأداء',
        'saved_data' => 'الصفقات المحفوظة',
        'account_balance' => 'رصيد الحساب',
        'risk_percent' => 'نسبة المخاطرة',
        'stop_loss_pips' => 'وقف الخسارة (نقاط)',
        'currency_pair' => 'زوج العملات',
        'calculate' => 'حساب',
        'position_size' => 'حجم المركز',
        'risk_amount' => 'مبلغ المخاطرة',
        'pip_value' => 'قيمة النقطة',
        'potential_loss' => 'الخسارة المحتملة',
        'lot_size' => 'حجم اللوت',
        'leverage' => 'الرافعة المالية',
        'margin_required' => 'الهامش المطلوب',
        'entry_price' => 'سعر الدخول',
        'exit_price' => 'سعر الخروج',
        'profit_loss' => 'الربح/الخسارة',
        'trade_size' => 'حجم الصفقة (لوت)',
        'save_trade' => 'حفظ الصفقة',
        'trade_history' => 'سجل الصفقات',
        'performance_stats' => 'إحصائيات الأداء',
        'total_trades' => 'إجمالي الصفقات',
        'winning_trades' => 'الصفقات الرابحة',
        'losing_trades' => 'الصفقات الخاسرة',
        'win_rate' => 'معدل الربح',
        'total_profit' => 'إجمالي الربح',
        'avg_win' => 'متوسط الربح',
        'avg_loss' => 'متوسط الخسارة',
        'best_trades' => 'أفضل الصفقات',
        'worst_trades' => 'أسوأ الصفقات',
        'pair_performance' => 'أداء أزواج العملات'
    ]
];

// معالجة حفظ الصفقات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_trade'])) {
    $tradeData = [
        'pair' => $_POST['pair'],
        'position_size' => $_POST['position_size'],
        'entry_price' => $_POST['entry_price'],
        'exit_price' => $_POST['exit_price'],
        'profit' => $_POST['profit_loss'],
        'risk_percent' => $_POST['risk_percent'],
        'notes' => $_POST['notes']
    ];
    
    if (saveForexTrade($_SESSION['user_id'], $tradeData)) {
        $_SESSION['success_message'] = $lang == 'ar' ? 'تم حفظ الصفقة بنجاح' : 'Trade saved successfully';
    } else {
        $_SESSION['error_message'] = $lang == 'ar' ? 'حدث خطأ أثناء حفظ الصفقة' : 'Error saving trade';
    }
    
    header("Location: forex.php?lang=$lang");
    exit;
}

// جلب أداء المستخدم
$performance = getUserPerformance($_SESSION['user_id']);
$tradeHistory = getForexTradeHistory($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang == 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RiskFree System - Forex Trading</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2469ff;
            --secondary: #00D8FF;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #fd7e14;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --border-radius: 8px;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .dark-mode {
            background-color: #1b2431;
            color: #e0e0e0;
        }
        
        .dark-mode .card,
        .dark-mode .stat-card {
            background-color: #273142;
            border-color: #313d4f;
            color: #e0e0e0;
        }
        
        .dark-mode .table {
            color: #e0e0e0;
        }
        
        .dark-mode .form-control,
        .dark-mode .form-select {
            background-color: #2d3a4f;
            color: #ffffff;
            border-color: #3d4d65;
        }
        
        .dark-mode .form-floating>label {
            color: #9eb0e8;
        }
        
        .language-switcher {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 9999;
            display: flex;
            gap: 10px;
        }
        
        .language-switcher a {
            border-radius: 50%;
            overflow: hidden;
            transition: transform 0.3s;
            border: 2px solid transparent;
        }
        
        .language-switcher a:hover {
            transform: scale(1.1);
        }
        
        .language-switcher a.active {
            border-color: var(--primary);
        }
        
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav-tabs .nav-link {
            font-weight: bold;
            color: #495057;
            border: none;
            padding: 12px 20px;
            transition: all 0.3s;
            margin-right: 5px;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            border: none;
        }
        
        .nav-tabs .nav-item.show .nav-link, 
        .nav-tabs .nav-link.active {
            color: #fff;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-color: transparent;
        }
        
        .tab-content {
            background: white;
            padding: 20px;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            border: 1px solid #dee2e6;
            border-top: none;
        }
        
        .dark-mode .tab-content {
            background-color: #273142;
            border-color: #313d4f;
        }
        
        .stat-card {
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .dark-mode .stat-card {
            background-color: #2d3a4f;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            margin-left: 10px;
        }
        
        .theme-toggle-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--dark);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 9999;
            border: none;
        }
        
        @media (max-width: 768px) {
            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-mode' : ''; ?>">
    <!-- زر تغيير اللغة -->
    <div class="language-switcher">
        <a href="?lang=en" class="<?php echo $lang == 'en' ? 'active' : ''; ?>">
            <img src="assets/img/uk-flag.png" alt="English" width="30">
        </a>
        <a href="?lang=ar" class="<?php echo $lang == 'ar' ? 'active' : ''; ?>">
            <img src="assets/img/sa-flag.png" alt="العربية" width="30">
        </a>
    </div>
    
    <!-- زر الوضع الداكن -->
    <button id="darkModeToggle" class="theme-toggle-btn">
        <i class="fas <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'fa-sun' : 'fa-moon'; ?>"></i>
    </button>

    <div class="container py-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <div class="card shadow-lg mb-4">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> <?php echo $translations[$lang]['title']; ?></h2>
            </div>
            
            <div class="card-body">
                <ul class="nav nav-tabs" id="forexTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="position-tab-btn" data-bs-toggle="tab" data-bs-target="#position-tab" type="button" role="tab">
                            <i class="fas fa-calculator"></i> <?php echo $translations[$lang]['position_calculator']; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="margin-tab-btn" data-bs-toggle="tab" data-bs-target="#margin-tab" type="button" role="tab">
                            <i class="fas fa-percentage"></i> <?php echo $translations[$lang]['margin_calculator']; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profit-tab-btn" data-bs-toggle="tab" data-bs-target="#profit-tab" type="button" role="tab">
                            <i class="fas fa-money-bill-wave"></i> <?php echo $translations[$lang]['profit_calculator']; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pip-tab-btn" data-bs-toggle="tab" data-bs-target="#pip-tab" type="button" role="tab">
                            <i class="fas fa-chart-pie"></i> <?php echo $translations[$lang]['pip_calculator']; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="risk-tab-btn" data-bs-toggle="tab" data-bs-target="#risk-tab" type="button" role="tab">
                            <i class="fas fa-shield-alt"></i> <?php echo $translations[$lang]['risk_management']; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="performance-tab-btn" data-bs-toggle="tab" data-bs-target="#performance-tab" type="button" role="tab">
                            <i class="fas fa-chart-bar"></i> <?php echo $translations[$lang]['performance']; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="history-tab-btn" data-bs-toggle="tab" data-bs-target="#history-tab" type="button" role="tab">
                            <i class="fas fa-history"></i> <?php echo $translations[$lang]['saved_data']; ?>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3" id="forexTabsContent">
                    <!-- حاسبة حجم الصفقة -->
                    <div class="tab-pane fade show active" id="position-tab" role="tabpanel" aria-labelledby="position-tab-btn">
                        <form id="positionForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['account_balance']; ?> ($)</label>
                                    <input type="number" class="form-control" id="accountBalance" required>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['risk_percent']; ?> (%)</label>
                                    <input type="number" class="form-control" id="riskPercent" value="2" step="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['stop_loss_pips']; ?></label>
                                    <input type="number" class="form-control" id="stopLossPips" required>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['currency_pair']; ?></label>
                                    <select class="form-select" id="currencyPair">
                                        <option value="1">Standard (XXX/USD, EUR/XXX)</option>
                                        <option value="0.0001">JPY Pairs (XXX/JPY)</option>
                                        <option value="10">Gold (XAU/USD)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-calculator"></i> <?php echo $translations[$lang]['calculate']; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="results" id="positionResults" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-primary">
                                                <i class="fas fa-chart-area"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1"><?php echo $translations[$lang]['position_size']; ?></h6>
                                                <h4 class="mb-0" id="positionSizeResult">0.00 لوت</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-danger">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1"><?php echo $translations[$lang]['risk_amount']; ?></h6>
                                                <h4 class="mb-0" id="riskAmountResult">$0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-info">
                                                <i class="fas fa-coins"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1"><?php echo $translations[$lang]['pip_value']; ?></h6>
                                                <h4 class="mb-0" id="pipValueResult">$0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-warning">
                                                <i class="fas fa-skull"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1"><?php echo $translations[$lang]['potential_loss']; ?></h6>
                                                <h4 class="mb-0" id="potentialLossResult">$0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- حاسبة الهامش -->
                    <div class="tab-pane fade" id="margin-tab" role="tabpanel" aria-labelledby="margin-tab-btn">
                        <form id="marginForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['lot_size']; ?></label>
                                    <input type="number" class="form-control" id="marginLotSize" step="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['currency_pair']; ?></label>
                                    <select class="form-select" id="marginCurrencyPair">
                                        <option value="EURUSD">EUR/USD</option>
                                        <option value="GBPUSD">GBP/USD</option>
                                        <option value="USDJPY">USD/JPY</option>
                                        <option value="XAUUSD">XAU/USD</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['leverage']; ?></label>
                                    <select class="form-select" id="leverage">
                                        <option value="50">1:50</option>
                                        <option value="100">1:100</option>
                                        <option value="200">1:200</option>
                                        <option value="500">1:500</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-calculator"></i> <?php echo $translations[$lang]['calculate']; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="results" id="marginResults" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-success">
                                                <i class="fas fa-percentage"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1"><?php echo $translations[$lang]['margin_required']; ?></h6>
                                                <h4 class="mb-0" id="marginRequiredResult">$0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- حاسبة الربح/الخسارة -->
                    <div class="tab-pane fade" id="profit-tab" role="tabpanel" aria-labelledby="profit-tab-btn">
                        <form id="profitForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['trade_size']; ?></label>
                                    <input type="number" class="form-control" id="profitLotSize" step="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['currency_pair']; ?></label>
                                    <select class="form-select" id="profitCurrencyPair">
                                        <option value="1">Standard (XXX/USD, EUR/XXX)</option>
                                        <option value="0.0001">JPY Pairs (XXX/JPY)</option>
                                        <option value="10">Gold (XAU/USD)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['entry_price']; ?></label>
                                    <input type="number" class="form-control" id="entryPrice" step="0.00001" required>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['exit_price']; ?></label>
                                    <input type="number" class="form-control" id="exitPrice" step="0.00001" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-calculator"></i> <?php echo $translations[$lang]['calculate']; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="results" id="profitResults" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-success">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1"><?php echo $translations[$lang]['profit_loss']; ?></h6>
                                                <h4 class="mb-0" id="profitLossResult">$0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <form id="saveTradeForm" method="post" class="mt-4">
                                <input type="hidden" name="save_trade" value="1">
                                <input type="hidden" name="pair" id="savePair">
                                <input type="hidden" name="position_size" id="savePositionSize">
                                <input type="hidden" name="entry_price" id="saveEntryPrice">
                                <input type="hidden" name="exit_price" id="saveExitPrice">
                                <input type="hidden" name="profit_loss" id="saveProfitLoss">
                                <input type="hidden" name="risk_percent" id="saveRiskPercent">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label><?php echo $lang == 'ar' ? 'ملاحظات' : 'Notes'; ?></label>
                                        <textarea class="form-control" name="notes" rows="3"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> <?php echo $translations[$lang]['save_trade']; ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- حاسبة قيمة النقطة -->
                    <div class="tab-pane fade" id="pip-tab" role="tabpanel" aria-labelledby="pip-tab-btn">
                        <form id="pipForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['trade_size']; ?></label>
                                    <input type="number" class="form-control" id="pipLotSize" step="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $translations[$lang]['currency_pair']; ?></label>
                                    <select class="form-select" id="pipCurrencyPair">
                                        <option value="1">Standard (XXX/USD, EUR/XXX)</option>
                                        <option value="0.0001">JPY Pairs (XXX/JPY)</option>
                                        <option value="10">Gold (XAU/USD)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-calculator"></i> <?php echo $translations[$lang]['calculate']; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="results" id="pipResults" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-info">
                                                <i class="fas fa-chart-pie"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1"><?php echo $translations[$lang]['pip_value']; ?></h6>
                                                <h4 class="mb-0" id="pipValueResult2">$0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- إدارة المخاطر -->
                    <div class="tab-pane fade" id="risk-tab" role="tabpanel" aria-labelledby="risk-tab-btn">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-shield-alt"></i> <?php echo $translations[$lang]['risk_management']; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <form id="riskForm">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label><?php echo $translations[$lang]['account_balance']; ?> ($)</label>
                                                    <input type="number" class="form-control" id="riskAccountBalance" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label><?php echo $translations[$lang]['risk_percent']; ?> (%)</label>
                                                    <input type="number" class="form-control" id="riskRiskPercent" value="2" step="0.01" required>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-calculator"></i> <?php echo $translations[$lang]['calculate']; ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        
                                        <div class="mt-4" id="riskResults" style="display: none;">
                                            <div class="alert alert-info">
                                                <h5><?php echo $lang == 'ar' ? 'إستراتيجية إدارة المخاطر' : 'Risk Management Strategy'; ?></h5>
                                                <p id="riskStrategyText"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-lightbulb"></i> <?php echo $lang == 'ar' ? 'نصائح إدارة المخاطر' : 'Risk Management Tips'; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><?php echo $lang == 'ar' ? 'لا تخاطر بأكثر من 1-2% من رأس المال في صفقة واحدة' : 'Never risk more than 1-2% of capital on a single trade'; ?></li>
                                            <li class="list-group-item"><?php echo $lang == 'ar' ? 'استخدم أوامر وقف الخسارة في كل صفقة' : 'Always use stop-loss orders'; ?></li>
                                            <li class="list-group-item"><?php echo $lang == 'ar' ? 'حافظ على نسبة الربح إلى الخسارة 2:1 على الأقل' : 'Maintain at least 2:1 profit to loss ratio'; ?></li>
                                            <li class="list-group-item"><?php echo $lang == 'ar' ? 'تجنب زيادة الرافعة المالية بشكل مفرط' : 'Avoid excessive leverage'; ?></li>
                                            <li class="list-group-item"><?php echo $lang == 'ar' ? 'تنويع المحفظة الاستثمارية' : 'Diversify your portfolio'; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- تحليل الأداء -->
                    <div class="tab-pane fade" id="performance-tab" role="tabpanel" aria-labelledby="performance-tab-btn">
                        <?php if ($performance): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h4><i class="fas fa-chart-bar"></i> <?php echo $translations[$lang]['performance_stats']; ?></h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="stat-card bg-primary-light">
                                                        <h6 class="text-muted mb-1"><?php echo $translations[$lang]['total_trades']; ?></h6>
                                                        <h4 class="mb-0"><?php echo $performance['stats']['total_trades']; ?></h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="stat-card bg-success-light">
                                                        <h6 class="text-muted mb-1"><?php echo $translations[$lang]['winning_trades']; ?></h6>
                                                        <h4 class="mb-0"><?php echo $performance['stats']['winning_trades']; ?></h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="stat-card bg-danger-light">
                                                        <h6 class="text-muted mb-1"><?php echo $translations[$lang]['losing_trades']; ?></h6>
                                                        <h4 class="mb-0"><?php echo $performance['stats']['losing_trades']; ?></h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="stat-card bg-info-light">
                                                        <h6 class="text-muted mb-1"><?php echo $translations[$lang]['win_rate']; ?></h6>
                                                        <h4 class="mb-0"><?php echo round(($performance['stats']['winning_trades'] / $performance['stats']['total_trades']) * 100, 2); ?>%</h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="stat-card bg-warning-light">
                                                        <h6 class="text-muted mb-1"><?php echo $translations[$lang]['total_profit']; ?></h6>
                                                        <h4 class="mb-0">$<?php echo number_format($performance['stats']['total_profit'], 2); ?></h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="stat-card bg-success-light">
                                                        <h6 class="text-muted mb-1"><?php echo $translations[$lang]['avg_win']; ?></h6>
                                                        <h4 class="mb-0">$<?php echo number_format($performance['stats']['avg_win'], 2); ?></h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="stat-card bg-danger-light">
                                                        <h6 class="text-muted mb-1"><?php echo $translations[$lang]['avg_loss']; ?></h6>
                                                        <h4 class="mb-0">$<?php echo number_format($performance['stats']['avg_loss'], 2); ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h4><i class="fas fa-trophy"></i> <?php echo $translations[$lang]['best_trades']; ?></h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo $translations[$lang]['currency_pair']; ?></th>
                                                            <th><?php echo $translations[$lang]['profit_loss']; ?></th>
                                                            <th><?php echo $lang == 'ar' ? 'التاريخ' : 'Date'; ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($performance['extreme_trades'] as $trade): ?>
                                                            <?php if ($trade['type'] == 'best'): ?>
                                                                <tr class="table-success">
                                                                    <td><?php echo $trade['pair']; ?></td>
                                                                    <td>$<?php echo number_format($trade['profit'], 2); ?></td>
                                                                    <td><?php echo date('Y-m-d', strtotime($trade['trade_date'])); ?></td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card">
                                        <div class="card-header">
                                            <h4><i class="fas fa-exclamation-triangle"></i> <?php echo $translations[$lang]['worst_trades']; ?></h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo $translations[$lang]['currency_pair']; ?></th>
                                                            <th><?php echo $translations[$lang]['profit_loss']; ?></th>
                                                            <th><?php echo $lang == 'ar' ? 'التاريخ' : 'Date'; ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($performance['extreme_trades'] as $trade): ?>
                                                            <?php if ($trade['type'] == 'worst'): ?>
                                                                <tr class="table-danger">
                                                                    <td><?php echo $trade['pair']; ?></td>
                                                                    <td>-$<?php echo number_format(abs($trade['profit']), 2); ?></td>
                                                                    <td><?php echo date('Y-m-d', strtotime($trade['trade_date'])); ?></td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-flag"></i> <?php echo $translations[$lang]['pair_performance']; ?></h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $translations[$lang]['currency_pair']; ?></th>
                                                    <th><?php echo $translations[$lang]['total_trades']; ?></th>
                                                    <th><?php echo $translations[$lang]['total_profit']; ?></th>
                                                    <th><?php echo $translations[$lang]['avg_profit']; ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($performance['pair_performance'] as $pair): ?>
                                                    <tr>
                                                        <td><?php echo $pair['pair']; ?></td>
                                                        <td><?php echo $pair['trade_count']; ?></td>
                                                        <td class="<?php echo $pair['total_profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                            $<?php echo number_format($pair['total_profit'], 2); ?>
                                                        </td>
                                                        <td class="<?php echo $pair['avg_profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                            $<?php echo number_format($pair['avg_profit'], 2); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php echo $lang == 'ar' ? 'لا توجد بيانات أداء متاحة بعد. قم بحفظ بعض الصفقات أولاً.' : 'No performance data available yet. Save some trades first.'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- سجل الصفقات -->
                    <div class="tab-pane fade" id="history-tab" role="tabpanel" aria-labelledby="history-tab-btn">
                        <?php if ($tradeHistory): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo $translations[$lang]['currency_pair']; ?></th>
                                            <th><?php echo $translations[$lang]['position_size']; ?></th>
                                            <th><?php echo $translations[$lang]['entry_price']; ?></th>
                                            <th><?php echo $translations[$lang]['exit_price']; ?></th>
                                            <th><?php echo $translations[$lang]['profit_loss']; ?></th>
                                            <th><?php echo $translations[$lang]['risk_percent']; ?></th>
                                            <th><?php echo $lang == 'ar' ? 'التاريخ' : 'Date'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tradeHistory as $trade): ?>
                                            <tr>
                                                <td><?php echo $trade['id']; ?></td>
                                                <td><?php echo $trade['pair']; ?></td>
                                                <td><?php echo number_format($trade['position_size'], 2); ?></td>
                                                <td><?php echo number_format($trade['entry_price'], 5); ?></td>
                                                <td><?php echo number_format($trade['exit_price'], 5); ?></td>
                                                <td class="<?php echo $trade['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    $<?php echo number_format($trade['profit'], 2); ?>
                                                </td>
                                                <td><?php echo $trade['risk_percent']; ?>%</td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($trade['trade_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php echo $lang == 'ar' ? 'لا توجد صفقات محفوظة بعد.' : 'No saved trades yet.'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // تبديل الوضع الداكن
            $('#darkModeToggle').click(function() {
                $('body').toggleClass('dark-mode');
                const isDark = $('body').hasClass('dark-mode');
                $(this).html(isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>');
                
                // حفظ التفضيل في الجلسة عبر AJAX
                $.post('update_preferences.php', { dark_mode: isDark ? 1 : 0 });
            });
            
            // حاسبة حجم الصفقة
            $('#positionForm').submit(function(e) {
                e.preventDefault();
                
                const balance = parseFloat($('#accountBalance').val());
                const riskPercent = parseFloat($('#riskPercent').val());
                const stopLoss = parseFloat($('#stopLossPips').val());
                const pipValue = parseFloat($('#currencyPair').val());
                
                if (isNaN(balance) || isNaN(riskPercent) || isNaN(stopLoss)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please enter valid values for all fields',
                        icon: 'error'
                    });
                    return;
                }
                
                // حساب مبلغ المخاطرة
                const riskAmount = (balance * riskPercent) / 100;
                
                // حساب حجم المركز
                const positionSize = (riskAmount / (stopLoss * pipValue * 10)).toFixed(2);
                
                // حساب قيمة النقطة
                const calculatedPipValue = (positionSize * pipValue * 10).toFixed(2);
                
                // حساب الخسارة المحتملة
                const potentialLoss = (positionSize * stopLoss * pipValue * 10).toFixed(2);
                
                // عرض النتائج
                $('#positionSizeResult').text(positionSize + ' <?php echo $lang == "ar" ? "لوت" : "Lots"; ?>');
                $('#riskAmountResult').text('$' + riskAmount.toFixed(2));
                $('#pipValueResult').text('$' + calculatedPipValue);
                $('#pipValueResult2').text('$' + calculatedPipValue);
                $('#potentialLossResult').text('$' + potentialLoss);
                
                $('#positionResults').fadeIn();
            });
            
            // حاسبة الهامش
            $('#marginForm').submit(function(e) {
                e.preventDefault();
                
                const lotSize = parseFloat($('#marginLotSize').val());
                const pair = $('#marginCurrencyPair').val();
                const leverage = parseInt($('#leverage').val());
                
                if (isNaN(lotSize)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please enter valid lot size',
                        icon: 'error'
                    });
                    return;
                }
                
                // حساب الهامش (هذا مثال مبسط - الحساب الفعلي يعتمد على السعر الحالي للزوج)
                let margin;
                if (pair === 'XAUUSD') {
                    margin = (lotSize * 100) / leverage; // مثال لذهب
                } else {
                    margin = (lotSize * 100000) / leverage; // مثال لأزواج العملات
                }
                
                $('#marginRequiredResult').text('$' + margin.toFixed(2));
                $('#marginResults').fadeIn();
            });
            
            // حاسبة الربح/الخسارة
            $('#profitForm').submit(function(e) {
                e.preventDefault();
                
                const lotSize = parseFloat($('#profitLotSize').val());
                const pipValue = parseFloat($('#profitCurrencyPair').val());
                const entryPrice = parseFloat($('#entryPrice').val());
                const exitPrice = parseFloat($('#exitPrice').val());
                
                if (isNaN(lotSize) || isNaN(entryPrice) || isNaN(exitPrice)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please enter valid values for all fields',
                        icon: 'error'
                    });
                    return;
                }
                
                // حساب الربح/الخسارة (بالدولار)
                const priceDifference = exitPrice - entryPrice;
                const profitLoss = (priceDifference / pipValue) * (lotSize * 100000);
                
                $('#profitLossResult').text(profitLoss >= 0 ? '$' + profitLoss.toFixed(2) : '-$' + Math.abs(profitLoss).toFixed(2));
                
                // تعبئة حقول حفظ الصفقة
                $('#savePair').val($('#profitCurrencyPair option:selected').text());
                $('#savePositionSize').val(lotSize);
                $('#saveEntryPrice').val(entryPrice);
                $('#saveExitPrice').val(exitPrice);
                $('#saveProfitLoss').val(profitLoss);
                $('#saveRiskPercent').val($('#riskPercent').val() || 2);
                
                $('#profitResults').fadeIn();
            });
            
            // حاسبة قيمة النقطة
            $('#pipForm').submit(function(e) {
                e.preventDefault();
                
                const lotSize = parseFloat($('#pipLotSize').val());
                const pipValue = parseFloat($('#pipCurrencyPair').val());
                
                if (isNaN(lotSize)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please enter valid lot size',
                        icon: 'error'
                    });
                    return;
                }
                
                // حساب قيمة النقطة
                const calculatedPipValue = (lotSize * pipValue * 10).toFixed(2);
                
                $('#pipValueResult2').text('$' + calculatedPipValue);
                $('#pipResults').fadeIn();
            });
            
            // إدارة المخاطر
            $('#riskForm').submit(function(e) {
                e.preventDefault();
                
                const balance = parseFloat($('#riskAccountBalance').val());
                const riskPercent = parseFloat($('#riskRiskPercent').val());
                
                if (isNaN(balance) || isNaN(riskPercent)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please enter valid values for all fields',
                        icon: 'error'
                    });
                    return;
                }
                
                const riskAmount = (balance * riskPercent) / 100;
                
                let strategyText = '';
                if (riskPercent > 5) {
                    strategyText = '<?php echo $lang == "ar" ? "نسبة المخاطرة عالية جداً (>5%). يوصى بتقليل المخاطرة إلى 1-2% كحد أقصى." : "Very high risk (>5%). Recommended to reduce risk to 1-2% max."; ?>';
                } else if (riskPercent > 2) {
                    strategyText = '<?php echo $lang == "ar" ? "نسبة المخاطرة مرتفعة (2-5%). يمكن تقليلها لتحسين إدارة رأس المال." : "High risk (2-5%). Can be reduced for better capital management."; ?>';
                } else {
                    strategyText = '<?php echo $lang == "ar" ? "نسبة المخاطرة مثالية (1-2%). تستمر في إدارة رأس المال بشكل جيد." : "Ideal risk (1-2%). Keep up the good capital management."; ?>';
                }
                
                strategyText += '<br><br><?php echo $lang == "ar" ? "مبلغ المخاطرة لكل صفقة: $" : "Risk amount per trade: $"; ?>' + riskAmount.toFixed(2);
                
                $('#riskStrategyText').html(strategyText);
                $('#riskResults').fadeIn();
            });
            
            // تهيئة التبويبات
            var tabElms = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabElms.forEach(function(tabEl) {
                tabEl.addEventListener('click', function (event) {
                    event.preventDefault();
                    var tab = new bootstrap.Tab(this);
                    tab.show();
                });
            });
        });
    </script>
</body>
</html>