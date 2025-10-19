<?php
session_start();
require_once 'includes/db.php';

// รับค่า id ของอุปกรณ์จาก URL
$equipment_id = $_GET['id'] ?? 0;

// ✅ ดึงข้อมูลอุปกรณ์หลัก
$sql = "SELECT equipment_id, name, image, basic_info, rules, usage_maintenance 
        FROM sports_equipment 
        WHERE equipment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$equipment = $stmt->get_result()->fetch_assoc();

// ✅ ดึงข้อมูลเสริม + รูปภาพจาก equipment_detail
$sql2 = "SELECT ed.description, ed.rules AS detail_rules, ed.instructions, ed.images, ec.category
         FROM equipment_detail ed
         JOIN equipment_category ec ON ec.equipment_category_id = ed.equipment_category_id
         WHERE ec.category = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $equipment['name']); 
$stmt2->execute();
$detail = $stmt2->get_result()->fetch_assoc();

// แปลง JSON เป็น array
$images = $detail && $detail['images'] ? json_decode($detail['images'], true) : [];
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>รายละเอียดอุปกรณ์ - <?= htmlspecialchars($equipment['name']); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
  <link rel="stylesheet" href="/myweb3/assets/css/layout.css">
  <link rel="stylesheet" href="/myweb3/assets/css/index.css">
  <link rel="stylesheet" href="assets/css/sidebar-layout.css">
</head>
<body class="flex flex-col md:flex-row bg-gray-100 font-sans has-sidebar">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>

<!-- ✅ Sidebar -->
<div id="sidebar" class="hr-sidebar fixed md:sticky top-0 left-0 h-full md:h-screen w-64 flex flex-col overflow-y-auto z-30">
  <div class="p-6 flex flex-col items-center justify-center">
    <img src="/myweb3/assets/images/university_logo.jpg" alt="University Logo" class="h-28 w-28 object-contain mb-3">
    <h1 class="text-lg font-bold text-center">MBAC SPORT</h1>
    <?php if (isset($_SESSION['fname'])): ?>
      <p class="text-sm text-gray-700 mt-1 text-center">
        <?= htmlspecialchars($_SESSION['fname'] . " " . $_SESSION['lname']); ?>
      </p>
    <?php endif; ?>
  </div>

  <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
  <div class="px-4">
    <p class="category-title">Menu</p>
    <a href="/myweb3/index.php" class="menu-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
      <div class="icon-container"><i data-feather="home" class="h-5 w-5"></i></div>
      <span class="text-sm">หน้าหลัก</span>
    </a>
    <a href="/myweb3/equipment.php" class="menu-item <?= in_array($current_page, ['equipment.php','equipment_detail.php']) ? 'active' : '' ?>">
      <div class="icon-container"><i data-feather="package" class="h-5 w-5"></i></div>
      <span class="text-sm">ข้อมูลอุปกรณ์</span>
    </a>
    <a href="/myweb3/my_reservations.php" class="menu-item <?= $current_page == 'my_reservations.php' ? 'active' : '' ?>">
      <div class="icon-container"><i data-feather="calendar" class="h-5 w-5"></i></div>
      <span class="text-sm">ประวัติการยืมคืนอุปกรณ์</span>
    </a>
    <a href="/myweb3/profile.php" class="menu-item <?= $current_page == 'profile.php' ? 'active' : '' ?>">
      <div class="icon-container"><i data-feather="user" class="h-5 w-5"></i></div>
      <span class="text-sm">จัดการข้อมูลส่วนตัว</span>
    </a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="/myweb3/logout.php" class="menu-item">
        <div class="icon-container"><i data-feather="log-out" class="h-5 w-5"></i></div>
        <span class="text-sm">ออกจากระบบ</span>
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- ✅ Main content -->
<div class="flex-1 pt-20 md:pt-0 p-4 md:p-8 overflow-y-auto">

  <!-- ชื่ออุปกรณ์ -->
  <h1 class="text-3xl font-bold mb-4 text-blue-600">
    <?= htmlspecialchars($equipment['name']); ?>
  </h1>

  <!-- ✅ สไลด์โชว์รูปภาพ -->
  <div class="swiper mySwiper mb-6 rounded-lg shadow bg-white">
    <div class="swiper-wrapper">
      <?php if (!empty($images)): ?>
        <?php foreach ($images as $img): ?>
          <div class="swiper-slide">
            <img src="assets/images/<?= htmlspecialchars($img); ?>" 
                 class="w-full h-96 object-contain rounded-lg"
                 alt="รูป <?= htmlspecialchars($equipment['name']); ?>">
          </div>
        <?php endforeach; ?>
      <?php elseif (!empty($equipment['image'])): ?>
        <div class="swiper-slide">
          <img src="assets/images/<?= htmlspecialchars($equipment['image']); ?>" 
               class="w-full h-96 object-contain rounded-lg"
               alt="<?= htmlspecialchars($equipment['name']); ?>">
        </div>
      <?php else: ?>
        <div class="swiper-slide">
          <img src="assets/images/no-image.png" 
               class="w-full h-96 object-contain rounded-lg" 
               alt="ไม่มีภาพ">
        </div>
      <?php endif; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>

  <!-- ✅ ข้อมูลพื้นฐาน -->
  <div class="bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-xl font-semibold mb-2">📌 ข้อมูลพื้นฐาน</h2>
    <p><?= nl2br(htmlspecialchars($equipment['basic_info'] ?? $detail['description'] ?? 'ยังไม่มีข้อมูลเพิ่มเติม')); ?></p>
  </div>

  <!-- ✅ กติกา -->
  <div class="bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-xl font-semibold mb-2">📖 กติกา</h2>
    <p><?= nl2br(htmlspecialchars($equipment['rules'] ?? $detail['detail_rules'] ?? 'ยังไม่มีข้อมูลกติกา')); ?></p>
  </div>

  <!-- ✅ วิธีใช้และดูแลรักษา -->
  <div class="bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-xl font-semibold mb-2">⚙ วิธีใช้และดูแลรักษา</h2>
    <p><?= nl2br(htmlspecialchars($equipment['usage_maintenance'] ?? $detail['instructions'] ?? 'ยังไม่มีข้อมูลการใช้งาน/ดูแลรักษา')); ?></p>
  </div>

  <div class="mt-6">
    <a href="equipment.php" class="btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
      🔄 ดูรายละเอียดอุปกรณ์อื่น
    </a>
  </div>

</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script>
  feather.replace();
  var swiper = new Swiper(".mySwiper", {
    loop: true,
    autoplay: { delay: 2500 },
    pagination: { el: ".swiper-pagination", clickable: true },
    navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" }
  });
</script>

<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="assets/js/sidebar-toggle.js"></script>
</body>
</html>
