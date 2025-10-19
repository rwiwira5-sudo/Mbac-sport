<?php
session_start();
require_once 'includes/db.php';

// ถ้าไม่ได้ล็อกอิน → กลับไปหน้า login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// --- ดึงข้อมูลผู้ใช้จากฐานข้อมูล ---
$stmt = $conn->prepare("SELECT fname, lname, email, phone_number FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// --- อัปเดตข้อมูล ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname  = trim($_POST['fname']);
    $lname  = trim($_POST['lname']);
    $email  = trim($_POST['email']);
    $phone  = trim($_POST['phone_number']);
    $new_password = $_POST['password'];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET fname=?, lname=?, email=?, phone_number=?, password=? WHERE user_id=?");
        $stmt->bind_param("sssssi", $fname, $lname, $email, $phone, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET fname=?, lname=?, email=?, phone_number=? WHERE user_id=?");
        $stmt->bind_param("ssssi", $fname, $lname, $email, $phone, $user_id);
    }

    if ($stmt->execute()) {
        $message = "อัปเดตข้อมูลสำเร็จ ✅";
        $_SESSION['fname'] = $fname; // อัปเดต session ด้วย
        $_SESSION['lname'] = $lname;
        $_SESSION['email'] = $email;
    } else {
        $message = "เกิดข้อผิดพลาด ❌: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>จัดการข้อมูลส่วนตัว</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/sidebar-layout.css">
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>

<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
  <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">จัดการข้อมูลส่วนตัว</h2>

  <?php if (!empty($message)): ?>
    <p class="mb-4 text-center text-green-600"><?php echo $message; ?></p>
  <?php endif; ?>

  <form method="POST" action="profile.php" class="space-y-4">
    <div>
      <label class="block font-medium">ชื่อจริง</label>
      <input type="text" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>" required
        class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block font-medium">นามสกุล</label>
      <input type="text" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" required
        class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block font-medium">อีเมล</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
        class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block font-medium">เบอร์โทรศัพท์</label>
      <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>"
        class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block font-medium">เปลี่ยนรหัสผ่าน (ถ้าไม่เปลี่ยนให้เว้นว่าง)</label>
      <input type="password" name="password" class="w-full border rounded-lg px-3 py-2">
    </div>

    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">บันทึกการเปลี่ยนแปลง</button>
    <a href="index.php" class="block text-center mt-4 text-sm text-gray-600 hover:text-blue-600">⬅ กลับหน้าหลัก</a>
  </form>
</div>

<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="assets/js/sidebar-toggle.js"></script>
</body>
</html>
