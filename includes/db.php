<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root"; // หรือชื่อผู้ใช้ฐานข้อมูลของคุณ
$password = "";     // หรือรหัสผ่านของคุณ
$dbname = "sport_borrowing_db";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตั้งค่า character set เป็น utf8
$conn->set_charset("utf8mb4");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// เริ่ม session สำหรับการล็อกอิน
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>