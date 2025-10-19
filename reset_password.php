<?php
session_start();
require_once 'includes/db.php';

$message = '';
$icon = 'info';
$redirect = '';

$email = null;
$expires_at = null;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $resetData = $result->fetch_assoc();
        $email = $resetData['email'];
        $expires_at = $resetData['expires_at'];

        if ($expires_at > date("Y-m-d H:i:s")) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
                $stmt2->bind_param("ss", $new_password, $email);

                if ($stmt2->execute()) {
                    $conn->query("DELETE FROM password_resets WHERE email = '$email'");
                    $message = "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว!";
                    $icon = "success";
                    $redirect = "login.php";
                } else {
                    $message = "เกิดข้อผิดพลาด โปรดลองใหม่อีกครั้ง";
                    $icon = "error";
                }
            }
        } else {
            $message = "⏰ ลิงก์รีเซ็ตรหัสผ่านหมดอายุแล้ว";
            $icon = "error";
            $redirect = "login.php";
        }
    } else {
        $message = "❌ ไม่พบ token ที่ใช้ได้";
        $icon = "error";
        $redirect = "login.php";
    }
} else {
    $message = "❌ ไม่มี token ที่ใช้ได้";
    $icon = "error";
    $redirect = "login.php";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>รีเซ็ตรหัสผ่าน</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  /* 🌐 Desktop */
  body, html {
    height:100%;
    margin:0;
    font-family:'Segoe UI',sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#f1f3f6;
  }

  .container {
    display:flex;
    width:800px;
    min-height:450px;
    background:#fff;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0px 6px 18px rgba(0,0,0,0.2);
  }

  .left-panel {
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#fff;
  }

  .left-panel img {
    width:250px;
    height:auto;
  }

  .right-panel {
    flex:1;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    padding:30px;
  }

  .form-login {
    width:100%;
    max-width:300px;
    text-align:center;
  }

  .form-login h3 {
    margin-bottom:20px;
    color:#1166db;
  }

  .form-group {
    margin-bottom:15px;
    text-align:left;
  }

  .form-group label {
    display:block;
    margin-bottom:6px;
    font-weight:bold;
  }

  .form-group input {
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:8px;
    font-size:14px;
  }

  .btn-login {
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#1166db;
    color:#fff;
    font-size:16px;
    cursor:pointer;
  }

  .btn-login:hover {
    background:#0b4ea0;
  }

  /* 📱 Mobile: เต็มจอ 100%, ปุ่มล่างสุด, scroll ได้ */
  @media (max-width: 768px) {
    html, body {
      margin:0;
      padding:0;
      width:100%;
      height:100vh;
      background:#f1f3f6;
      display:flex;
      justify-content:center;
      align-items:center;
    }

    .container {
      flex-direction:column;
      justify-content:space-between;
      align-items:center;
      width:90%;
      max-width:420px;
      height:96vh;
      background:#fff;
      border-radius:12px;
      box-shadow:0 6px 18px rgba(0,0,0,0.1);
      padding:20px;
      box-sizing:border-box;
      overflow-y:auto;
    }

    .left-panel {
      width:100%;
      display:flex;
      justify-content:center;
      align-items:center;
      padding-top:15px;
    }

    .left-panel img {
      width:120px;
      height:auto;
    }

    .right-panel {
      width:100%;
      flex-grow:1;
      display:flex;
      flex-direction:column;
      justify-content:center;
      align-items:center;
      text-align:center;
    }

    .form-login {
      width:100%;
      max-width:100%;
    }

    .form-login h3 {
      font-size:16px;
      margin-bottom:10px;
    }

    .form-group label {
      font-size:13px;
    }

    .form-group input {
      padding:8px;
      font-size:13px;
    }

    .btn-login {
      margin-top:10px;
      padding:12px;
      font-size:14px;
      border-radius:6px;
    }

    p a {
      font-size:13px;
      text-decoration:none;
      color:#1166db;
    }
  }
</style>
</head>
<body>

<?php if (!empty($message)): ?>
  <script>
    Swal.fire({
      icon: '<?php echo $icon; ?>',
      title: '<?php echo $message; ?>',
      confirmButtonText: 'ไปที่เข้าสู่ระบบ',
      confirmButtonColor: '#1166db',
      timer: 5000,
      timerProgressBar: true
    }).then(() => {
      window.location.href = '<?php echo $redirect ?: "login.php"; ?>';
    });
  </script>
<?php else: ?>

  <div class="container">
    <div class="left-panel">
      <img src="assets/images/logo.jpg" alt="Logo">
    </div>

    <div class="right-panel">
      <div class="form-login">
        <h3>ตั้งรหัสผ่านใหม่</h3>
        <form method="POST">
          <div class="form-group">
            <label>รหัสผ่านใหม่</label>
            <input type="password" name="password" required>
          </div>
          <button type="submit" class="btn-login">บันทึกรหัสผ่าน</button>
        </form>
        <p style="margin-top:10px"><a href="login.php">กลับไปเข้าสู่ระบบ</a></p>
      </div>
    </div>
  </div>

<?php endif; ?>
</body>
</html>
