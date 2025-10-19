<?php 
    session_start();
    require_once 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="tr" class="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/layout.css">
  <link rel="stylesheet" href="assets/css/index.css">
  <script src="https://unpkg.com/feather-icons"></script>
  <title>MBAC SPORT</title>
  <link rel="stylesheet" href="assets/css/sidebar-layout.css">
</head>

<body class="flex flex-col md:flex-row has-sidebar">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>

<!-- Sidebar -->
<div id="sidebar" class="hr-sidebar fixed md:sticky top-0 left-0 h-full md:h-screen w-64 flex flex-col overflow-y-auto z-30 transition-transform duration-300 ease-in-out">
  <div class="p-6 flex flex-col items-center justify-center">
    <img src="assets/images/university_logo.jpg" alt="University Logo" class="h-28 w-28 object-contain mb-3">
    <h1 class="text-lg font-bold text-center">MBAC SPORT</h1>
    <?php if (isset($_SESSION['fname'])): ?>
      <p class="text-sm text-gray-700 mt-1 text-center">
        <?php echo htmlspecialchars($_SESSION['fname'] . " " . $_SESSION['lname']); ?>
      </p>
    <?php endif; ?>
  </div>

  <div class="px-4">
    <p class="category-title">Menu</p>
    <a href="index.php" class="menu-item active"><div class="icon-container"><i data-feather="home" class="h-5 w-5"></i></div><span class="text-sm">หน้าหลัก</span></a>
    <a href="equipment.php" class="menu-item"><div class="icon-container"><i data-feather="package" class="h-5 w-5"></i></div><span class="text-sm">ข้อมูลอุปกรณ์</span></a>
    <a href="my_reservations.php" class="menu-item"><div class="icon-container"><i data-feather="calendar" class="h-5 w-5"></i></div><span class="text-sm">ประวัติการยืมคืนอุปกรณ์</span></a>
    <a href="profile.php" class="menu-item"><div class="icon-container"><i data-feather="user" class="h-5 w-5"></i></div><span class="text-sm">จัดการข้อมูลส่วนตัว</span></a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="logout.php" class="menu-item"><div class="icon-container"><i data-feather="log-out" class="h-5 w-5"></i></div><span class="text-sm">ออกจากระบบ</span></a>
    <?php endif; ?>
  </div>
</div>

<!-- Main content -->
<div class="flex-1 pt-20 md:pt-0 p-4 md:p-8 overflow-y-auto">
  <div class="welcome-banner">
    <h2 class="text-white">ยินดีต้อนรับสู่ระบบสืบค้นยืม-คืนอุปกรณ์กีฬา</h2>
    <p>เลือกหมวดหมู่อุปกรณ์ที่คุณต้องการยืมได้จากรายการด้านล่าง</p>
  </div>

<h3>รายการอุปกรณ์กีฬา</h3>

<div class="equipment-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
  <?php
  $sql = "SELECT equipment_id, name, quantity, unit, image 
          FROM sports_equipment 
          ORDER BY name ASC";
  $result = $conn->query($sql);

  if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          echo '<div class="equipment-card bg-white p-4 rounded-lg shadow">';
          echo '<div class="card-header font-bold mb-2">' . htmlspecialchars($row['name']) . '</div>';
          echo '<div class="card-body text-center">';

          // ✅ แสดงรูปจาก DB
          if (!empty($row['image'])) {
              echo '<img src="assets/images/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" class="w-40 h-40 object-contain mx-auto mb-2">';
          } else {
              echo '<img src="assets/images/default.jpg" alt="อุปกรณ์กีฬา" class="w-40 h-40 object-contain mx-auto mb-2">';
          }

          // จำนวนคงเหลือ
          echo "<p>จำนวนคงเหลือ:</p>";
          echo "<p class='quantity font-semibold text-lg'><span class='available'>{$row['quantity']}</span></p>";
          echo "<p>" . htmlspecialchars($row['unit']) . "</p>";
          echo "</div>";

          // ปุ่มยืม
          echo '<div class="card-footer mt-3">';
          if ($row['quantity'] > 0 && isset($_SESSION['user_id'])) {
              echo "<a href='reserve.php?equipment_id={$row['equipment_id']}' class='btn bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>ยืมอุปกรณ์นี้</a>";
          } elseif (!isset($_SESSION['user_id'])) {
              echo "<a href='login.php' class='btn-secondary bg-gray-300 text-gray-700 px-4 py-2 rounded'>เข้าสู่ระบบเพื่อยืม</a>";
          } else {
              echo "<button class='btn-disabled bg-gray-200 text-gray-500 px-4 py-2 rounded' disabled>อุปกรณ์หมด</button>";
          }
          echo '</div>';
          echo '</div>';
      }
  } else {
      echo "<p>ไม่พบข้อมูลอุปกรณ์ในระบบ</p>";
  }
  ?>
</div>

</div>

<script src="assets/js/script.js"></script>
<script>feather.replace();</script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="assets/js/sidebar-toggle.js"></script>
</body>
</html>
