<?php
    session_start();
    require_once 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="th" class="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <title>ข้อมูลอุปกรณ์กีฬา</title>
  <link rel="stylesheet" href="assets/css/layout.css">
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="assets/css/sidebar-layout.css">
</head>

<body class="flex flex-col md:flex-row bg-gray-100 has-sidebar">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>

<!-- Sidebar -->
<div id="sidebar" class="hr-sidebar fixed md:sticky top-0 left-0 h-full md:h-screen w-64 flex flex-col overflow-y-auto z-30 transition-transform duration-300 ease-in-out">
  <div class="p-6 flex flex-col items-center justify-center">
    <img src="assets/images/university_logo.jpg" alt="University Logo" class="h-28 w-28 object-contain mb-3">
    <h1 class="text-lg font-bold text-center">MBAC SPORT</h1>
    <?php if (isset($_SESSION['fname'])): ?>
      <p class="text-sm text-gray-700 mt-1 text-center">
        <?= htmlspecialchars($_SESSION['fname'] . " " . $_SESSION['lname']); ?>
      </p>
    <?php endif; ?>
  </div>  

  <div class="px-4">
    <p class="category-title">Menu</p>
    <a href="index.php" class="menu-item"><div class="icon-container"><i data-feather="home"></i></div><span class="text-sm">หน้าหลัก</span></a>
    <a href="equipment.php" class="menu-item active"><div class="icon-container"><i data-feather="package"></i></div><span class="text-sm">ข้อมูลอุปกรณ์</span></a>
    <a href="my_reservations.php" class="menu-item"><div class="icon-container"><i data-feather="calendar"></i></div><span class="text-sm">ประวัติการยืมคืนอุปกรณ์</span></a>
    <a href="profile.php" class="menu-item"><div class="icon-container"><i data-feather="user"></i></div><span class="text-sm">จัดการข้อมูลส่วนตัว</span></a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="logout.php" class="menu-item"><div class="icon-container"><i data-feather="log-out"></i></div><span class="text-sm">ออกจากระบบ</span></a>
    <?php endif; ?>
  </div>
</div>

<!-- Main content -->
<div class="flex-1 pt-20 md:pt-0 p-4 md:p-8 overflow-y-auto">
  <div class="welcome-banner mb-6">
    <h2 class="text-white">📋 ข้อมูลอุปกรณ์กีฬา</h2>
    <p>เลือกอุปกรณ์เพื่อดูรายละเอียดเพิ่มเติม</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php
    // ✅ ดึงจาก sports_equipment แทน
    $sql = "SELECT equipment_id, name, quantity, unit, image 
            FROM sports_equipment 
            ORDER BY name ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="equipment-card bg-white p-4 rounded-lg shadow">';
            echo '  <div class="card-header font-bold mb-2 text-center">' . htmlspecialchars($row['name']) . '</div>';
            echo '  <div class="card-body text-center">';

            // ✅ ใช้รูปจาก DB
            if (!empty($row['image'])) {
                echo '<img src="assets/images/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" class="w-40 h-40 object-contain mx-auto mb-2">';
            } else {
                echo '<img src="assets/images/default.jpg" alt="อุปกรณ์กีฬา" class="w-40 h-40 object-contain mx-auto mb-2">';
            }

            echo '  </div>';

            // ปุ่มดูรายละเอียด
            echo '  <div class="card-footer mt-3 text-center">';
            echo '    <a href="equipment_detail.php?id=' . $row['equipment_id'] . '" class="btn-secondary">ดูรายละเอียด</a>';
            echo '  </div>';
            echo '</div>';
        }
    } else {
        echo "<p class='text-center text-gray-500'>ไม่มีข้อมูลอุปกรณ์</p>";
    }
    ?>
  </div>
</div>

<script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();</script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="assets/js/sidebar-toggle.js"></script>
</body>
</html>
