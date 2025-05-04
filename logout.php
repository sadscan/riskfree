<?php
// تأكد من بدء الجلسة أولاً
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تدمير جميع بيانات الجلسة
$_SESSION = array();

// إذا كنت تريد تدمير الجلسة تماماً، احذف أيضاً كوكيز الجلسة
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// أخيراً، دمر الجلسة
session_destroy();

// توجيه إلى صفحة تسجيل الدخول مع إضافة header لمنع التخزين المؤقت
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: login.php");
exit;
?>