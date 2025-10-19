<?php
ob_start();
session_start();
require_once 'includes/db.php';

$errors = []; 
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $fname      = trim($_POST['fname']);
    $lname      = trim($_POST['lname']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone_number']);
    $password   = $_POST['password'];

    $check = $conn->prepare("SELECT student_id, email, phone_number 
                             FROM user 
                             WHERE student_id = ? OR email = ? OR phone_number = ?");
    $check->bind_param("sss", $student_id, $email, $phone);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['student_id'] === $student_id) {
                $errors['student_id'] = "รหัสนักศึกษานี้ถูกใช้แล้ว";
            }
            if ($row['email'] === $email) {
                $errors['email'] = "อีเมลนี้ถูกใช้แล้ว";
            }
            if (!empty($phone) && $row['phone_number'] === $phone) {
                $errors['phone_number'] = "เบอร์โทรศัพท์นี้ถูกใช้แล้ว";
            }
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (student_id, fname, lname, email, phone_number, password, roll_id) 
                                VALUES (?, ?, ?, ?, ?, ?, 2)");
        $stmt->bind_param("ssssss", $student_id, $fname, $lname, $email, $phone, $hashed_password);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors['general'] = "เกิดข้อผิดพลาด: " . $stmt->error;
        }
        $stmt->close();
    }
    $check->close();
}
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="th">
<head>  
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สมัครสมาชิก</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* 🌐 Desktop */
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
      width: 900px;
      min-height: 550px;
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

    .left-panel img {
      width: 280px;
      height: auto;
    }

    .right-panel {
      flex: 1.5;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 30px;
    }

    .form-login {
      width: 100%;
      max-width: 350px;
      text-align: center;
    }

    .form-login h3 {
      margin-bottom: 20px;
      color: #1166db;
    }

    .form-group {
      margin-bottom: 15px;
      text-align: left;
    }

    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
    }

    .error-text {
      color: red;
      font-size: 13px;
      margin-top: 4px;
    }

    .form-links {
      display: flex;
      justify-content: center;
      margin-bottom: 15px;
    }

    .form-links a {
      font-size: 13px;
      color: #1166db;
      text-decoration: none;
    }

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

    .btn-login:hover {
      background: #0b4ea0;
    }

    /* 📱 Mobile (เต็มจอ, ปุ่มล่าง, scroll ได้) */
    @media (max-width: 768px) {
      html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100vh;
        background: #f1f3f6;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .container {
        flex-direction: column;
        justify-content: space-between; /* ดันปุ่มลงล่าง */
        align-items: center;
        width: 90%;
        max-width: 420px;
        height: 96vh;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        padding: 18px 20px;
        box-sizing: border-box;
        overflow-y: auto; /* เลื่อนลงได้ */
      }

      .left-panel {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: 10px;
      }

      .left-panel img {
        width: 120px;
        height: auto;
      }

      .right-panel {
        width: 100%;
        text-align: center;
        flex-grow: 1; /* ให้ฟอร์มขยาย */
        display: flex;
        flex-direction: column;
        justify-content: center;
      }

      .form-login {
        width: 100%;
        max-width: 100%;
      }

      .form-login h3 {
        font-size: 16px;
        margin-bottom: 10px;
      }

      .form-group label {
        font-size: 13px;
      }

      .form-group input {
        padding: 8px;
        font-size: 13px;
      }

      .btn-login {
        padding: 12px;
        font-size: 14px;
        border-radius: 6px;
      }

      .form-links {
        font-size: 13px;
        justify-content: center;
      }

      .error-text {
        font-size: 12px;
      }
    }
  </style>
</head>
<body>

<?php if ($success): ?>
<script>
Swal.fire({
  title: 'สมัครสมาชิกสำเร็จ!',
  text: 'คุณสามารถเข้าสู่ระบบได้แล้ว',
  icon: 'success',
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
    <img src="assets/images/logo.jpg" alt="College Logo">
  </div>

  <div class="right-panel">
    <div class="form-login">
      <h3>สมัครสมาชิก</h3>
      <form action="register.php" method="post">
        <div class="form-group">
          <label>รหัสนักศึกษา</label>
          <input type="text" name="student_id" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" required>
          <?php if (!empty($errors['student_id'])): ?><p class="error-text"><?php echo $errors['student_id']; ?></p><?php endif; ?>
        </div>
        <div class="form-group">
          <label>ชื่อจริง</label>
          <input type="text" name="fname" value="<?php echo htmlspecialchars($_POST['fname'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label>นามสกุล</label>
          <input type="text" name="lname" value="<?php echo htmlspecialchars($_POST['lname'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label>อีเมล</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
          <?php if (!empty($errors['email'])): ?><p class="error-text"><?php echo $errors['email']; ?></p><?php endif; ?>
        </div>
        <div class="form-group">
          <label>เบอร์โทรศัพท์</label>
          <input type="text" name="phone_number" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>">
          <?php if (!empty($errors['phone_number'])): ?><p class="error-text"><?php echo $errors['phone_number']; ?></p><?php endif; ?>
        </div>
        <div class="form-group">
          <label>รหัสผ่าน</label>
          <input type="password" name="password" required>
        </div>
        <div class="form-links"><a href="login.php">มีบัญชีแล้ว? เข้าสู่ระบบ</a></div>
        <button type="submit" class="btn-login">สมัครสมาชิก</button>
      </form>
    </div>
  </div>
</div>

<?php endif; ?>
</body>
</html>
