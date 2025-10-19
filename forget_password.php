<?php
session_start();
require_once 'includes/db.php';
require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$icon = 'info';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $token = bin2hex(random_bytes(16));
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmtDel = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmtDel->bind_param("s", $email);
        $stmtDel->execute();

        $stmt2 = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt2->bind_param("sss", $email, $token, $expires_at);
        $stmt2->execute();

        $resetLink = "http://localhost/myweb3/reset_password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'aumarada28@gmail.com';
            $mail->Password = 'flowcldeojjoiklc';
            $mail->CharSet  = "UTF-8";
            $mail->Encoding = "base64";
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('Greece44445555@gmail.com', 'MBAC SPORT');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "🔑 รีเซ็ตรหัสผ่าน (MBAC SPORT)";
            $mail->Body = "
                <p>สวัสดี,</p>
                <p>คุณได้ร้องขอการรีเซ็ตรหัสผ่านสำหรับบัญชี MBAC SPORT</p>
                <p>กดลิงก์ด้านล่างเพื่อเปลี่ยนรหัสผ่าน (ลิงก์จะหมดอายุภายใน 1 ชั่วโมง):</p>
                <p><a href='$resetLink' style='color:#1166db;'>$resetLink</a></p>
                <p>หากคุณไม่ได้ทำการร้องขอนี้ โปรดละเว้นอีเมลฉบับนี้</p>
                <br>
                <p>MBAC SPORT</p>
            ";
            $mail->send();
            $message = "ส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของคุณแล้ว";
            $icon = "success";
        } catch (Exception $e) {
            $message = "❌ ไม่สามารถส่งอีเมลได้: " . $mail->ErrorInfo;
            $icon = "error";
        }
    } else {
        $message = "❌ ไม่พบบัญชีผู้ใช้นี้";
        $icon = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ลืมรหัสผ่าน</title>
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
      title: '<?php echo $message; ?>',
      icon: '<?php echo $icon; ?>',
      confirmButtonText: 'ไปที่เข้าสู่ระบบ',
      confirmButtonColor: '#1166db',
      timer: 5000,
      timerProgressBar: true
    }).then(() => {
      window.location = 'login.php';
    });
  </script>
<?php else: ?>

  <div class="container">
    <div class="left-panel">
      <img src="assets/images/logo.jpg" alt="Logo">
    </div>

    <div class="right-panel">
      <div class="form-login">
        <h3>ลืมรหัสผ่าน</h3>
        <form method="POST">
          <div class="form-group">
            <label>อีเมล</label>
            <input type="email" name="email" required>
          </div>
          <button type="submit" class="btn-login">ส่งลิงก์รีเซ็ต</button>
        </form>
        <p style="margin-top:10px"><a href="login.php">กลับไปเข้าสู่ระบบ</a></p>
      </div>
    </div>
  </div>

<?php endif; ?>

</body>
</html>
