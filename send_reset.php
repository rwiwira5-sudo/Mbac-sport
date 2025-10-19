<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // ตรวจสอบว่ามีผู้ใช้นี้หรือไม่
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        // สร้าง token
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // บันทึก token
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        // ส่งอีเมล
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // ถ้าใช้ Gmail
            $mail->SMTPAuth   = true;
            $mail->Username   = 'yourgmail@gmail.com'; // อีเมลผู้ส่ง
            $mail->Password   = 'your-app-password'; // ใช้ App Password ไม่ใช่รหัสจริง
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('yourgmail@gmail.com', 'MBAC Sport System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body    = "คลิกที่ลิงก์นี้เพื่อรีเซ็ตรหัสผ่านของคุณ: 
                              <a href='http://localhost/myweb3/reset_password.php?token=$token'>รีเซ็ตรหัสผ่าน</a>";

            $mail->send();
            echo "เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปที่อีเมลของคุณแล้ว";
        } catch (Exception $e) {
            echo "ไม่สามารถส่งอีเมลได้. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "ไม่พบอีเมลนี้ในระบบ";
    }
}
