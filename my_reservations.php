<?php
session_start();
require_once 'includes/db.php';

// --- ตรวจสอบการล็อกอิน ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = basename($_SERVER['PHP_SELF']); // ดึงชื่อไฟล์ปัจจุบัน
?>

<!DOCTYPE html>
<html lang="th" class="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/layout.css">
  <link rel="stylesheet" href="assets/css/table.css">
  <link rel="stylesheet" href="assets/css/history.css">
  <script src="https://unpkg.com/feather-icons"></script>
  <title>ประวัติการยืมอุปกรณ์</title>
  <link rel="stylesheet" href="assets/css/sidebar-layout.css">
</head>

<body class="flex flex-col md:flex-row has-sidebar">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>

<!-- Sidebar -->
<div id="sidebar" class="hr-sidebar fixed md:sticky top-0 left-0 h-full md:h-screen w-64 flex flex-col overflow-y-auto z-30 transition-transform duration-300 ease-in-out">

  <!-- Logo -->
  <div class="p-6 flex flex-col items-center justify-center">
    <img src="assets/images/university_logo.jpg" alt="University Logo" class="h-28 w-28 object-contain mb-3">
    <h1 class="text-lg font-bold text-center">MBAC SPORT</h1>
    <?php if (isset($_SESSION['fname'])): ?>
      <p class="text-sm text-gray-700 mt-1 text-center">
        <?php echo htmlspecialchars($_SESSION['fname'] . " " . $_SESSION['lname']); ?>
      </p>
    <?php endif; ?>
  </div>

  <!-- Menu -->
  <div class="px-4">
    <p class="category-title">Menu</p>

    <a href="index.php" class="menu-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
      <div class="icon-container"><i data-feather="home" class="h-5 w-5"></i></div>
      <span class="text-sm">หน้าหลัก</span>
    </a>

    <a href="equipment.php" class="menu-item <?= $current_page == 'equipment.php' ? 'active' : '' ?>">
      <div class="icon-container"><i data-feather="package" class="h-5 w-5"></i></div>
      <span class="text-sm">ข้อมูลอุปกรณ์</span>
    </a>

    <a href="my_reservations.php" class="menu-item <?= $current_page == 'my_reservations.php' ? 'active' : '' ?>">
      <div class="icon-container"><i data-feather="calendar" class="h-5 w-5"></i></div>
      <span class="text-sm">ประวัติการยืมคืนอุปกรณ์</span>
    </a>

    <a href="profile.php" class="menu-item <?= $current_page == 'profile.php' ? 'active' : '' ?>">
      <div class="icon-container"><i data-feather="user" class="h-5 w-5"></i></div>
      <span class="text-sm">จัดการข้อมูลส่วนตัว</span>
    </a>

    <a href="logout.php" class="menu-item">
      <div class="icon-container"><i data-feather="log-out" class="h-5 w-5"></i></div>
      <span class="text-sm">ออกจากระบบ</span>
    </a>
  </div>
</div>

<!-- Main content -->
<div class="flex-1 pt-20 md:pt-0 p-4 md:p-8 overflow-y-auto">
  <div class="main-content">
    <div class="container">
      <h2>ประวัติการยืมคืนอุปกรณ์ของฉัน</h2>
      <p>นี่คือรายการยืม-คืนอุปกรณ์ทั้งหมดของคุณ</p>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>ลำดับ</th>
              <th>ชื่ออุปกรณ์</th>
              <th>จำนวน</th>
              <th>วันที่ยืม</th>
              <th>กำหนดคืน</th>
              <th>วันที่คืนจริง</th>
              <th>ค่าปรับ</th>
              <th>สถานะ</th>
            </tr>
          </thead>
 <tbody>
<?php
$sql = "SELECT 
            rh.reservation_date,
            rh.due_date,
            rh.end_date,
            rh.status,
            rh.quantity,
            rh.fine,
            se.name AS equipment_name,
            se.unit
        FROM reservation_history AS rh
        JOIN sports_equipment AS se ON rh.equipment_id = se.equipment_id
        WHERE rh.user_id = ?
        ORDER BY rh.reservation_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        // ✅ กำหนดสีสถานะ
        $status_color = '';
        switch ($row['status']) {
            case 'คืนแล้ว':
                $status_color = 'text-green-600 font-bold';
                break;
            case 'อนุมัติ':
                $status_color = 'text-yellow-600 font-semibold';
                break;
            case 'ไม่อนุมัติ':
                $status_color = 'text-red-600 font-semibold';
                break;
            case 'รอดำเนินการ':
                $status_color = 'text-gray-500 font-medium';
                break;
            default:
                $status_color = 'text-gray-700';
        }
?>
<tr>
  <td class="text-center"><?php echo $counter++; ?></td>
  <td><?php echo htmlspecialchars($row['equipment_name']); ?></td>
  <td class="text-center"><?php echo htmlspecialchars($row['quantity']) . " " . htmlspecialchars($row['unit']); ?></td>

  <!-- วันที่ยืม -->
  <td class="text-center"><?php echo date('d/m/Y', strtotime($row['reservation_date'])); ?></td>

  <!-- วันกำหนดคืน -->
  <td class="text-center"><?php echo date('d/m/Y', strtotime($row['due_date'])); ?></td>

  <!-- วันที่คืนจริง -->
  <td class="text-center">
    <?php 
      if ($row['status'] == 'ไม่อนุมัติ') {
          echo '<span class="text-gray-400">-</span>';
      } elseif ($row['end_date']) {
          echo date('d/m/Y', strtotime($row['end_date']));
      } else {
          echo '<span class="text-gray-400">ยังไม่คืน</span>';
      }
    ?>
  </td>

  <!-- ค่าปรับ -->
  <td class="text-center">
    <?php 
      if ($row['status'] == 'ไม่อนุมัติ') {
          echo '<span class="text-gray-400">-</span>';
      } elseif ($row['fine'] > 0) {
          echo "<span class='text-red-600 font-bold'>".number_format($row['fine'], 2)." บาท</span>";
      } else {
          echo '-';
      }
    ?>
  </td>

  <!-- สถานะ -->
  <td class="text-center">
    <span class="<?= $status_color ?>">
      <?= htmlspecialchars($row['status']) ?>
    </span>
  </td>
</tr>
<?php
    }
} else {
    echo '<tr><td colspan="8" class="text-center text-gray-500">คุณยังไม่มีประวัติการยืมอุปกรณ์</td></tr>';
}
$stmt->close();
?>
</tbody>


        </table>
      </div>

      <div style="margin-top: 20px;">
        <a href="index.php" class="btn">ยืมอุปกรณ์เพิ่มเติม</a>
      </div>

    </div>
  </div>
</div>
<script src="assets/js/script.js"></script>
<script>feather.replace();</script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="assets/js/sidebar-toggle.js"></script>
</body>
</html>
