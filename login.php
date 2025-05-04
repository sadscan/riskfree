<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>RiskFree - LOGIN VIP USERS</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #0072ff;
      --secondary-color: #00c6ff;
      --error-color: #ff4757;
      --text-color: #ffffff;
      --input-bg: rgba(255, 255, 255, 0.08);
      --border-radius: 35px;
      --transition: all 0.3s ease;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      
    }

    body {
      font-family: 'Cairo', sans-serif !important;
      background: url('assets/img/bg.jpg') no-repeat center center fixed;
      background-size: cover;
      color: var(--text-color);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 100vh;
      line-height: 1.6;
    }

    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 20px;
      flex-grow: 1;
    }

    .logo img {
      height: 120px;
      width: 370px;
      transition: var(--transition);
      filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
    }

    .logo img:hover {
      transform: scale(1.03);
    }

    .login-container {
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(10px);
      padding: 40px;
      border-radius: var(--border-radius);
      width: 100%;
      max-width: 500px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: var(--transition);
    }

    .login-container:hover {
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
    }

    .login-container h2 {
      text-align: center;
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 30px;
      position: relative;
      display: inline-block;
      width: 100%;
    }

    .login-container h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
      border-radius: 3px;
    }

    .form-group {
      margin-bottom: 25px;
      position: relative;
    }

    .form-group label {
      display: block;
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 10px;
      color: var(--text-color);
      padding-right: 15px;
    }

    .form-group .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .form-group input {
      width: 100%;
      padding: 15px 50px 15px 20px;
      border: 2px solid rgba(0, 123, 255, 0.3);
      border-radius: var(--border-radius);
      background: var(--input-bg);
      font-size: 16px;
      color: var(--text-color);
      outline: none;
      transition: var(--transition);
      text-align: right;
      font-weight: 500;
    }

    .form-group input::placeholder {
      font-family: 'Cairo', sans-serif !important;
      color: rgba(255, 255, 255, 0.5);
      font-weight: 400;
    }

    .form-group input:focus {
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 3px rgba(0, 198, 255, 0.3);
      background: rgba(255, 255, 255, 0.1);
    }

    .form-group .input-icon {
      position: absolute;
      right: 20px;
      color: rgba(255, 255, 255, 0.7);
      font-size: 18px;
      transition: var(--transition);
    }

    .form-group input:focus + .input-icon {
      color: var(--secondary-color);
      transform: scale(1.1);
    }

    .password-toggle {
      position: absolute;
      left: 20px;
      color: rgba(255, 255, 255, 0.7);
      cursor: pointer;
      font-size: 18px;
      transition: var(--transition);
    }

    .password-toggle:hover {
      color: var(--secondary-color);
    }

    .login-btn {
      font-family: 'Cairo', sans-serif !important;
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 30px;
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
      color: white;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      transition: var(--transition);
      margin-top: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 4px 15px rgba(0, 114, 255, 0.4);
    }

    .login-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 7px 20px rgba(0, 114, 255, 0.5);
    }

    .login-btn:active {
      transform: translateY(0);
    }

    .helper-text {
      margin-top: 25px;
      font-size: 14px;
      text-align: center;
      color: rgba(255, 255, 255, 0.7);
      line-height: 1.6;
    }

    .helper-text a {
      color: var(--secondary-color);
      text-decoration: none;
      font-weight: 700;
      transition: var(--transition);
    }

    .helper-text a:hover {
      text-decoration: underline;
      color: var(--primary-color);
    }

    footer {
      text-align: center;
      font-size: 13px;
      padding: 20px;
      background: rgba(0, 0, 0, 0.5);
      color: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(5px);
      line-height: 1.6;
    }

    footer a {
      color: white;
      font-weight: 900;
      text-decoration: none;
      transition: var(--transition);
    }

    footer a:hover {
      color: var(--secondary-color);
      text-decoration: underline;
    }

    /* تأثيرات للخطأ */
    .alert-danger {
      background: rgba(255, 71, 87, 0.2) !important;
      color: #fff !important;
      border-radius: var(--border-radius) !important;
      padding: 15px !important;
      margin-bottom: 25px !important;
      border: 1px solid rgba(255, 71, 87, 0.5) !important;
      text-align: center;
      animation: shake 0.5s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }

    /* تأثيرات للجوال */
    @media (max-width: 768px) {
      .logo img {
        height: auto;
        width: 100%;
        max-width: 300px;
      }
      
      .login-container {
        padding: 30px;
      }
      
      .form-group input {
        font-family: 'Cairo', sans-serif !important;
        padding: 12px 45px 12px 15px;
        
      }
    }

    @media (max-width: 480px) {
      .login-container {
        padding: 25px 20px;
      }
      
      .login-container h2 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="logo">
      <img src="assets/img/logo.png" alt="RiskFree" class="logo-img">
    </div>

    <div class="login-container">
      <h2>تسجيل الدخول</h2>
      <?php if (isset($error)): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      
      <form id="loginForm" action="login_process.php" method="POST">
        <div class="form-group">
          <label for="username"> اسم المستخدم</label>
          <div class="input-wrapper">
            <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
            <span class="input-icon"><i class="fas fa-user"></i></span>
          </div>
        </div>

        <div class="form-group">
          <label for="password"> كلمة المرور</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
            <span class="input-icon"><i class="fas fa-key"></i></span>
            <span class="password-toggle"><i class="fas fa-eye"></i></span>
          </div>
        </div>

        <button type="submit" class="login-btn">
          <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
        </button>

        <div class="helper-text">
          <i class="fas fa-info-circle"></i> في حال نسيت إسم المستخدم أو كلمة المرور<br>
          يرجى التواصل معنا على 
          <a href="https://t.me/SadScan"><i class="fab fa-telegram"></i> @SadScan</a>
        </div>
      </form>
    </div>
  </div>

  <footer>
    <i class="far fa-copyright"></i>جميع الحقوق محفوظة 2025 - هذا النظام معد خصيصاً لتداول الخيارات الثنائية
    <a href="https://t.me/SadScan"><i class="fab fa-telegram"></i>@SadScan</a>
  </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function() {
      // تبديل عرض كلمة المرور
      $('.password-toggle').click(function() {
        const passwordInput = $('#password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
          passwordInput.attr('type', 'text');
          icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
          passwordInput.attr('type', 'password');
          icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
      });

      // تأثيرات عند التركيز على الحقول
      $('input').focus(function() {
        $(this).parent().find('.input-icon').css({
          'color': '#00c6ff',
          'transform': 'scale(1.1)'
        });
      }).blur(function() {
        $(this).parent().find('.input-icon').css({
          'color': 'rgba(255, 255, 255, 0.7)',
          'transform': 'scale(1)'
        });
      });

      // تأثير عند إرسال النموذج
      $('#loginForm').submit(function(e) {
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        
        // عرض حالة التحميل
        btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحقق...');
        btn.prop('disabled', true);
        
        // يمكنك إضافة كود AJAX هنا للإرسال الحقيقي
      });
    });
  </script>
</body>
</html>