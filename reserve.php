<?php 
session_start();
require_once 'includes/db.php';

// ถ้าไม่ได้ล็อกอิน → กลับไป login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th" class="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/layout.css">
  <link rel="stylesheet" href="assets/css/reserve.css">
  <script src="https://unpkg.com/feather-icons"></script>
  <title>ยืมอุปกรณ์ - MBAC SPORT</title>
  <link rel="stylesheet" href="assets/css/sidebar-layout.css">
</head>

<body class="flex flex-col md:flex-row has-sidebar">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>

<!-- Sidebar -->
<div id="sidebar" class="hr-sidebar fixed md:sticky top-0 left-0 h-full md:h-screen w-64 flex flex-col overflow-y-auto z-30">
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
    <a href="index.php" class="menu-item"><div class="icon-container"><i data-feather="home" class="h-5 w-5"></i></div><span class="text-sm">หน้าหลัก</span></a>
    <a href="equipment.php" class="menu-item"><div class="icon-container"><i data-feather="package" class="h-5 w-5"></i></div><span class="text-sm">ข้อมูลอุปกรณ์</span></a>
    <a href="my_reservations.php" class="menu-item"><div class="icon-container"><i data-feather="calendar" class="h-5 w-5"></i></div><span class="text-sm">ประวัติการยืมคืน</span></a>
    <a href="profile.php" class="menu-item"><div class="icon-container"><i data-feather="user" class="h-5 w-5"></i></div><span class="text-sm">จัดการข้อมูลส่วนตัว</span></a>
    <a href="logout.php" class="menu-item"><div class="icon-container"><i data-feather="log-out" class="h-5 w-5"></i></div><span class="text-sm">ออกจากระบบ</span></a>
  </div>
</div>

<!-- Main content -->
<div class="flex-1 p-4 md:p-8 overflow-y-auto mt-6">
  <?php
  // === ประมวลผลการยืม ===
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserve'])) {
      $user_id = $_SESSION['user_id'];
      $equipment_id = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
      $quantity_to_borrow = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
      $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));

      if ($quantity_to_borrow <= 0) {
          echo "<p class='error'>กรุณาระบุจำนวนที่ถูกต้อง</p>";
      } else {
          $conn->begin_transaction();
          try {
              // ตรวจสอบจำนวนคงเหลือ
              $stmt = $conn->prepare("SELECT quantity, unit, name FROM sports_equipment WHERE equipment_id = ? FOR UPDATE");
              $stmt->bind_param("i", $equipment_id);
              $stmt->execute();
              $result = $stmt->get_result();
              $equipment = $result->fetch_assoc();

              if (!$equipment || $equipment['quantity'] < $quantity_to_borrow) {
                  throw new Exception("อุปกรณ์ไม่เพียงพอ!");
              }

              // บันทึกประวัติ
              $stmt_insert = $conn->prepare("INSERT INTO reservation_history (user_id, equipment_id, quantity, status, reservation_date, due_date) 
                                             VALUES (?, ?, ?, 'รอดำเนินการ', NOW(), ?)");
              $stmt_insert->bind_param("iiis", $user_id, $equipment_id, $quantity_to_borrow, $due_date);
              $stmt_insert->execute();

              $conn->commit();

              echo "<div class='success text-center'>
                    <h3 class='text-xl font-bold mb-2'>กรุณารอเจ้าหน้าที่ดำเนินการ</h3>
                    <p>คุณได้ยืม " . htmlspecialchars($equipment['name']) . " จำนวน " . $quantity_to_borrow . " " . htmlspecialchars($equipment['unit']) . "</p>
                    <p>กรุณาคืนภายในวันที่ " . date('d/m/Y', strtotime($due_date)) . "</p>
                    <p class='text-red-600 font-bold mt-2'>
                        ⚠️ หากนักศึกษาคืนล่าช้าจะมีค่าปรับ 50 บาท<br>
                        ⚠️ หากนักศึกษาทำอุปกรณ์สูญหายหรือชำรุดต้องชำระตามราคาอุปกรณ์
                    </p>
                    <a href='index.php' class='btn mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700'>กลับหน้าหลัก</a>
                    </div>";

          } catch (Exception $e) {
              $conn->rollback();
              echo "<p class='error'>การยืมล้มเหลว: " . $e->getMessage() . "</p>";
          }
      }
  } else {
      // แสดงฟอร์มยืม
      $equipment_id = filter_input(INPUT_GET, 'equipment_id', FILTER_VALIDATE_INT);
      if ($equipment_id) {
          $stmt = $conn->prepare("SELECT name, quantity, unit, image FROM sports_equipment WHERE equipment_id = ?");
          $stmt->bind_param("i", $equipment_id);
          $stmt->execute();
          $result = $stmt->get_result();
          $equipment = $result->fetch_assoc();

          if ($equipment):
              ?>
              <div class="reserve-header mb-6 text-center">
                  <h2 class="text-2xl font-bold">ยืมอุปกรณ์: <?php echo htmlspecialchars($equipment['name']); ?></h2>
                  <p class="text-gray-700 mt-1">
                      จำนวนคงเหลือ: <?php echo $equipment['quantity'] . " " . htmlspecialchars($equipment['unit']); ?>
                  </p>

                  <!-- ✅ แสดงรูปจาก manage_equipment -->
                  <?php if (!empty($equipment['image'])): ?>
                      <img src="assets/images/<?php echo htmlspecialchars($equipment['image']); ?>" 
                          alt="<?php echo htmlspecialchars($equipment['name']); ?>" 
                          class="mx-auto mt-4 w-48 h-48 object-cover rounded-lg shadow">
                  <?php else: ?>
                      <div class="mx-auto mt-4 w-48 h-48 flex items-center justify-center bg-gray-200 text-gray-500 rounded-lg">
                          ไม่มีรูป
                      </div>
                  <?php endif; ?>
              </div>

              <form action="reserve.php" method="post" class="form-container bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
                  <input type="hidden" name="equipment_id" value="<?php echo $equipment_id; ?>">
                  <label for="quantity" class="block font-medium mb-2">จำนวนที่ต้องการยืม:</label>
                  <input type="number" id="quantity" name="quantity" min="1" 
                         max="<?php echo $equipment['quantity']; ?>" required
                         class="w-full border px-3 py-2 rounded-lg mb-4">

                  <button type="submit" name="reserve" 
                          class="btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 w-full">
                      ยืนยันการยืม
                  </button>
              </form>
              <?php
          else:
              echo "<p>ไม่พบข้อมูลอุปกรณ์</p>";
          endif;
      } else {
          echo "<p>ไม่พบอุปกรณ์</p>";
      }
  }
  ?>
</div>

<script>feather.replace();</script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="assets/js/sidebar-toggle.js"></script>
</body>
</html>
