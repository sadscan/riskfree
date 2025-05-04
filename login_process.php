<?php
session_start();

$error = '';

// تأكد من وجود البيانات
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    $error = "يرجى إدخال اسم المستخدم وكلمة المرور.";
    include("login.php");
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

// الاتصال بقاعدة البيانات على localhost
$host = 'localhost';
$db = 'riskfree';
$db_user = 'root';     // المستخدم الافتراضي في XAMPP أو WAMP
$db_pass = '';         // عادة بدون كلمة مرور

$conn = new mysqli($host, $db_user, $db_pass, $db);

// فحص الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}


$conn = new mysqli($host, $db_user, $db_pass, $db);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// الاستعلام عن المستخدم
$stmt = $conn->prepare("SELECT id, password, is_active FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $hashed_password, $is_active);
    $stmt->fetch();

    // التحقق من كلمة المرور
    if (hash('sha256', $password) === $hashed_password) {
        if ($is_active == 1) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $error = "تم إيقاف هذا الحساب ، تواصل معنا لمزيد من التفاصيل .";
        }
    } else {
        $error = "إسم المستخدم أو كلمة المرور غير صحيحة.";
    }
} else {
    $error = "إسم المستخدم أو كلمة المرور غير صحيحة.";
}

$stmt->close();
$conn->close();
include("login.php");
?>