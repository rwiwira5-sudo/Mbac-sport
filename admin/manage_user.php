<?php 
session_start();
require_once '../includes/db.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['roll_id']) || $_SESSION['roll_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$edit_user = null;

// ================== เพิ่มผู้ใช้ใหม่ ==================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = intval($_POST['role']);

    // 🔎 ตรวจสอบว่ามีซ้ำหรือไม่
    $check = $conn->prepare("SELECT * FROM user WHERE student_id=? OR email=? OR (fname=? AND lname=?)");
    $check->bind_param("ssss", $student_id, $email, $fname, $lname);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        header("Location: manage_user.php?error=exists");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO user (fname, lname, student_id, email, password, roll_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $fname, $lname, $student_id, $email, $password, $role);
    $stmt->execute();
    header("Location: manage_user.php?success=added");
    exit();
}

// ================== ดึงข้อมูลผู้ใช้มาแก้ไข ==================
if (isset($_GET['edit'])) {
    $user_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
}

// ================== อัปเดตผู้ใช้ ==================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $role = intval($_POST['role']);

    // 🔎 ตรวจสอบว่ามีซ้ำหรือไม่ (ยกเว้น user_id ตัวเอง)
    $check = $conn->prepare("SELECT * FROM user WHERE (student_id=? OR email=? OR (fname=? AND lname=?)) AND user_id<>?");
    $check->bind_param("ssssi", $student_id, $email, $fname, $lname, $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        header("Location: manage_user.php?error=exists");
        exit();
    }

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE user SET fname=?, lname=?, student_id=?, email=?, password=?, roll_id=? WHERE user_id=?");
        $stmt->bind_param("ssssssi", $fname, $lname, $student_id, $email, $password, $role, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET fname=?, lname=?, student_id=?, email=?, roll_id=? WHERE user_id=?");
        $stmt->bind_param("ssssii", $fname, $lname, $student_id, $email, $role, $user_id);
    }
    $stmt->execute();
    header("Location: manage_user.php?success=updated");
    exit();
}

// ================== ลบผู้ใช้ ==================
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);

    // ลบประวัติการยืม
    $stmt = $conn->prepare("DELETE FROM reservation_history WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // ลบ admin ถ้ามี
    $stmt = $conn->prepare("DELETE FROM admin WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // ลบผู้ใช้จริง
    $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    header("Location: manage_user.php?success=deleted");
    exit();
}

// ตัวแปรตรวจสอบไฟล์ปัจจุบัน
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>จัดการผู้ใช้</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/layout.css">
  <link rel="stylesheet" href="../assets/css/sidebar-layout.css">
</head>
<body class="flex flex-col md:flex-row bg-gray-100 font-sans has-sidebar">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>

<!-- Sidebar -->
<div id="sidebar" class="hr-sidebar fixed md:sticky top-0 left-0 h-full md:h-screen w-64 flex flex-col overflow-y-auto z-30">
  <div class="p-6 flex flex-col items-center justify-center">
    <img src="../assets/images/university_logo.jpg" alt="University Logo" class="h-28 w-28 object-contain mb-3">
    <h1 class="text-lg font-bold text-center">MBAC SPORT</h1>
    <h1 class="text-lg font-bold text-center">ADMIN</h1>
  </div>
  <div class="px-4">
    <p class="category-title">Admin Menu</p>
    <a href="index.php" class="menu-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
      <div class="icon-container"><i data-feather="home"></i></div><span class="text-sm">หน้าหลัก</span>
    </a>
    <a href="manage_equipment.php" class="menu-item <?php echo ($current_page == 'manage_equipment.php') ? 'active' : ''; ?>">
      <div class="icon-container"><i data-feather="package"></i></div><span class="text-sm">จัดการอุปกรณ์</span>
    </a>
    <a href="manage_user.php" class="menu-item <?php echo ($current_page == 'manage_user.php') ? 'active' : ''; ?>">
      <div class="icon-container"><i data-feather="users"></i></div><span class="text-sm">จัดการข้อมูลผู้ใช้งาน</span>
    </a>
    <a href="report.php" class="menu-item"><div class="icon-container"><i data-feather="bar-chart-2"></i></div><span class="text-sm">รายงานสถิติ</span></a>
    <a href="../logout.php" class="menu-item"><div class="icon-container"><i data-feather="log-out"></i></div><span class="text-sm">ออกจากระบบ</span></a>
  </div>
</div>

<!-- Main Content -->
<div class="flex-1 p-6 md:p-10 overflow-y-auto space-y-10">

  <!-- ฟอร์มเพิ่ม/แก้ไข -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 border-b pb-2">
      <?php echo $edit_user ? "✏️ แก้ไขผู้ใช้" : "➕ เพิ่มผู้ใช้ใหม่"; ?>
    </h1>
    <form method="POST" class="grid grid-cols-2 gap-4" id="userForm">
      <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id'] ?? ''; ?>">

      <div>
        <label class="block font-medium">ชื่อ</label>
        <input type="text" name="fname" value="<?php echo $edit_user['fname'] ?? ''; ?>" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block font-medium">นามสกุล</label>
        <input type="text" name="lname" value="<?php echo $edit_user['lname'] ?? ''; ?>" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block font-medium">รหัสนักศึกษา</label>
        <input type="text" name="student_id" value="<?php echo $edit_user['student_id'] ?? ''; ?>" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block font-medium">อีเมล</label>
        <input type="email" name="email" value="<?php echo $edit_user['email'] ?? ''; ?>" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block font-medium">รหัสผ่าน <?php echo $edit_user ? "(ถ้าไม่เปลี่ยนให้เว้นว่าง)" : ""; ?></label>
        <input type="password" name="password" class="w-full border rounded p-2" <?php echo $edit_user ? "" : "required"; ?>>
      </div>
      <div>
        <label class="block font-medium">สิทธิ์</label>
        <select name="role" class="w-full border rounded p-2" required>
          <option value="1" <?php echo (isset($edit_user) && $edit_user['roll_id']==1) ? "selected" : ""; ?>>Admin</option>
          <option value="2" <?php echo (isset($edit_user) && $edit_user['roll_id']==2) ? "selected" : ""; ?>>User</option>
        </select>
      </div>
      <div class="col-span-2">
        <?php if ($edit_user): ?>
          <button type="button" onclick="confirmUpdate()" class="px-4 py-2 bg-blue-500 text-white rounded">บันทึกการแก้ไข</button>
          <a href="manage_user.php" class="px-4 py-2 bg-gray-400 text-white rounded">ยกเลิก</a>
        <?php else: ?>
          <button type="button" onclick="confirmAdd()" class="px-4 py-2 bg-green-500 text-white rounded">เพิ่มผู้ใช้</button>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- ตารางผู้ใช้ -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 border-b pb-2">📋 รายการผู้ใช้</h1>
    <table class="w-full border-collapse text-sm">
      <thead>
        <tr class="bg-gray-100">
          <th class="border p-2 text-center">#</th>
          <th class="border p-2">ชื่อ-นามสกุล</th>
          <th class="border p-2">รหัสนักศึกษา</th>
          <th class="border p-2">อีเมล</th>
          <th class="border p-2 text-center">สิทธิ์</th>
          <th class="border p-2 text-center">การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result = $conn->query("SELECT * FROM user ORDER BY user_id DESC");
        if ($result->num_rows > 0) {
            $i = 1;
            while ($row = $result->fetch_assoc()) {
                $role_name = ($row['roll_id'] == 1) ? "Admin" : "User";
                echo "<tr>
                        <td class='border p-2 text-center'>{$i}</td>
                        <td class='border p-2'>".htmlspecialchars($row['fname']." ".$row['lname'])."</td>
                        <td class='border p-2'>".htmlspecialchars($row['student_id'])."</td>
                        <td class='border p-2'>".htmlspecialchars($row['email'])."</td>
                        <td class='border p-2 text-center'>{$role_name}</td>
                        <td class='border p-2 text-center'>
                          <a href='manage_user.php?edit={$row['user_id']}' class='px-3 py-1 bg-yellow-500 text-white rounded'>แก้ไข</a>
                          <button onclick='confirmDelete({$row['user_id']})' class='px-3 py-1 bg-red-500 text-white rounded'>ลบ</button>
                        </td>
                      </tr>";
                $i++;
            }
        } else {
            echo "<tr><td colspan='6' class='text-center p-3 text-gray-500'>ไม่มีผู้ใช้</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>feather.replace();</script>

<script>
function confirmDelete(userId) {
  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: "คุณต้องการลบผู้ใช้นี้จริงหรือไม่",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "manage_user.php?delete=" + userId;
    }
  });
}

function confirmUpdate() {
  Swal.fire({
    title: 'ยืนยันการแก้ไข?',
    text: "คุณต้องการบันทึกการเปลี่ยนแปลงนี้หรือไม่",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'ใช่, บันทึกเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then((result) => {
    if (result.isConfirmed) {
      let form = document.getElementById("userForm");
      form.insertAdjacentHTML("beforeend", '<input type="hidden" name="update_user" value="1">');
      form.submit();
    }
  });
}

function confirmAdd() {
  Swal.fire({
    title: 'เพิ่มผู้ใช้ใหม่?',
    text: "คุณต้องการเพิ่มผู้ใช้นี้หรือไม่",
    icon: 'info',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'ใช่, เพิ่มเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then((result) => {
    if (result.isConfirmed) {
      let form = document.getElementById("userForm");
      form.insertAdjacentHTML("beforeend", '<input type="hidden" name="add_user" value="1">');
      form.submit();
    }
  });
}

// ✅ แสดง SweetAlert success/error
document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);

  if (urlParams.has("success")) {
    let msg = "";
    if (urlParams.get("success") === "deleted") msg = "ลบผู้ใช้เรียบร้อยแล้ว";
    if (urlParams.get("success") === "added") msg = "เพิ่มผู้ใช้เรียบร้อยแล้ว";
    if (urlParams.get("success") === "updated") msg = "อัปเดตข้อมูลผู้ใช้เรียบร้อยแล้ว";

    if (msg !== "") {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: msg });
    }
  }

  if (urlParams.has("error")) {
    if (urlParams.get("error") === "exists") {
      Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ข้อมูลนี้มีอยู่แล้วในระบบ' });
    }
  }
});
</script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>
