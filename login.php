<?php
ob_start(); // ✅ เริ่ม buffer ป้องกัน warning
session_start();
require_once 'includes/db.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']); 
    $password   = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, fname, lname, student_id, password, roll_id FROM user WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['user_id'];   
            $_SESSION['fname']      = $user['fname'];     
            $_SESSION['lname']      = $user['lname'];     
            $_SESSION['student_id'] = $user['student_id']; 
            $_SESSION['roll_id']    = $user['roll_id'];   

            if ($user['roll_id'] == 1) {
                header("Location: admin/index.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        } else {
            $error_message = "รหัสนักศึกษาหรือรหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error_message = "รหัสนักศึกษาหรือรหัสผ่านไม่ถูกต้อง";
    }
    $stmt->close();
}
ob_end_flush(); // ✅ จบ buffer
?>
<!DOCTYPE html>
<html lang="th">

<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    /* 🌐 Desktop ปกติ */
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #f1f3f6;
    }

    .container {
      display: flex;
      width: 800px;
      height: 500px;
      background: #fff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0px 6px 18px rgba(0,0,0,0.2);
    }

    .left-panel {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #fff;
    }

    .left-panel img { width: 280px; height: auto; }

    .right-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 30px;
    }

    .greeting { margin-bottom: 20px; text-align: center; }
    .greeting h1 { color: #1166db; font-size: 22px; }
    .form-login { width: 100%; max-width: 300px; }
    .form-login h3 { margin-bottom: 15px; }
    .form-group { margin-bottom: 15px; text-align: left; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: bold; }
    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
    }

    .form-links {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
    }

    .form-links a { font-size: 13px; color: #1166db; text-decoration: none; }
    .form-links a:hover { text-decoration: underline; }

    .btn-login {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: #1166db;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
    }
    .btn-login:hover { background: #0b4ea0; }

    .error-message { color: red; margin-bottom: 15px; text-align: center; }

    /* ✅ Mobile: เห็นครบ ไม่ต้องเลื่อน พอดีจอ */
    @media (max-width: 768px) {
      html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100vh;
        background: #f1f3f6;
        overflow: hidden; /* ❗ไม่ให้เลื่อน */
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .container {
        width: 90%;
        max-width: 400px;
        height: 96vh; /* พอดีจอ */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        padding: 18px 20px;
        box-sizing: border-box;
      }

      .left-panel {
        width: 100%;
        display: flex;
        justify-content: center;
        padding: 0;
      }

      .left-panel img {
        width: 110px;
        height: auto;
      }

      .right-panel {
        width: 100%;
        text-align: center;
        padding: 0;
      }

      .greeting h1 {
        font-size: 15px;
        margin: 10px 0;
        line-height: 1.4;
      }

      .form-login {
        width: 100%;
        margin-top: 5px;
      }

      .form-login h3 {
        font-size: 13.5px;
        margin-bottom: 8px;
      }

      .form-group {
        margin-bottom: 10px;
      }

      .form-group label {
        font-size: 13px;
      }

      .form-group input {
        padding: 8px;
        font-size: 13px;
      }

      .form-links {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        margin-top: 5px;
      }

      .form-links a {
        color: #1166db;
        text-decoration: none;
        flex: 1;
        text-align: center;
      }

      .form-links a:hover {
        text-decoration: underline;
      }

      .btn-login {
        margin-top: 8px;
        padding: 10px;
        font-size: 14px;
        border-radius: 6px;
      }
    }
  </style>
</head>
<body>
<?php
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']); 
    $password   = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, fname, lname, student_id, password, roll_id FROM user WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['user_id'];   
            $_SESSION['fname']      = $user['fname'];     
            $_SESSION['lname']      = $user['lname'];     
            $_SESSION['student_id'] = $user['student_id']; 
            $_SESSION['roll_id']    = $user['roll_id'];   
            
            if ($user['roll_id'] == 1) {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error_message = "รหัสนักศึกษาหรือรหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error_message = "รหัสนักศึกษาหรือรหัสผ่านไม่ถูกต้อง";
    }
    $stmt->close();
}
?>

<div class="container">
  <!-- โลโก้ -->
  <div class="left-panel">
    <img src="assets/images/logo.jpg" alt="College Logo">
  </div>

  <!-- ฟอร์มล็อกอิน -->
  <div class="right-panel">
    <div class="greeting">
      <h1>ระบบสืบค้นยืม-คืนอุปกรณ์กีฬา</h1>
    </div>

    <div class="form-login">
      <h3>กรุณาเข้าสู่ระบบด้วยรหัสนักศึกษา</h3>
      <?php if (!empty($error_message)) { ?>
        <p class="error-message"><?php echo $error_message; ?></p>
      <?php } ?>
      <form action="login.php" method="post">
        <div class="form-group">
          <label for="student_id">รหัสนักศึกษา</label>
          <input type="text" id="student_id" name="student_id" required>
        </div>
        <div class="form-group">
          <label for="password">รหัสผ่าน</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="form-links">
          <a href="register.php">สมัครสมาชิก</a>
          <a href="forget_password.php">ลืมรหัสผ่าน?</a>
        </div>
        <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
