<?php
session_start();
require_once '../includes/db.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['roll_id']) || $_SESSION['roll_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// === การจัดการ Form ===
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reservation_id = intval($_POST['reservation_id']);
    $action = $_POST['action'];

    // 📌 ฟังก์ชันอัปโหลดรูป
    function uploadImage($fileInputName) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
            $target_dir = "../assets/uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $filename = time() . "_" . basename($_FILES[$fileInputName]["name"]);
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $target_file)) {
                return "assets/uploads/" . $filename;
            }
        }
        return null;
    }

    // ✅ อัปโหลดรูปก่อนยืม
    if ($action === "upload_before") {
        $before_image = uploadImage("before_image");
        if ($before_image) {
            $stmt = $conn->prepare("UPDATE reservation_history SET before_image=? WHERE reservation_id=?");
            $stmt->bind_param("si", $before_image, $reservation_id);
            $stmt->execute();
        }

    // ✅ อนุมัติ
    } elseif ($action === "approve") {
        $sql = "SELECT rh.equipment_id, rh.quantity, se.quantity AS stock
                FROM reservation_history rh
                JOIN sports_equipment se ON rh.equipment_id = se.equipment_id
                WHERE rh.reservation_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && $res['stock'] >= $res['quantity']) {
            $stmt = $conn->prepare("UPDATE reservation_history SET status='อนุมัติ' WHERE reservation_id=?");
            $stmt->bind_param("i", $reservation_id);
            $stmt->execute();

            $stmt2 = $conn->prepare("UPDATE sports_equipment SET quantity = quantity - ? WHERE equipment_id=?");
            $stmt2->bind_param("ii", $res['quantity'], $res['equipment_id']);
            $stmt2->execute();
        } else {
            echo "<script>alert('❌ ไม่สามารถอนุมัติได้: อุปกรณ์ไม่พอ');</script>";
        }

    // ❌ ไม่อนุมัติ
    } elseif ($action === "reject") {
        $stmt = $conn->prepare("UPDATE reservation_history SET status='ไม่อนุมัติ' WHERE reservation_id=?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();

    // ✅ อัปโหลดรูปหลังยืม
    } elseif ($action === "upload_return") {
        $return_image = uploadImage("return_image");
        if ($return_image) {
            $stmt = $conn->prepare("UPDATE reservation_history SET return_image=? WHERE reservation_id=?");
            $stmt->bind_param("si", $return_image, $reservation_id);
            $stmt->execute();
        }

    // 🔄 คืนอุปกรณ์
    } elseif ($action === "return") {
        $sql = "SELECT equipment_id, quantity, due_date FROM reservation_history WHERE reservation_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res) {
            $now = new DateTime();
            $due = new DateTime($res['due_date']);
            $fine = 0;

            // 📌 ถ้าเกินกำหนด → ค่าปรับ 50 บาท
            if ($now > $due) {
    $daysLate = $due->diff($now)->days;
    if ($daysLate <= 7) {
        $fine = 50 * $res['quantity'];
    } else {
        $fine = 100 * $res['quantity'];
    }
}

            $stmt = $conn->prepare("UPDATE reservation_history 
                                    SET status='คืนแล้ว', end_date=NOW(), fine=? 
                                    WHERE reservation_id=?");
            $stmt->bind_param("di", $fine, $reservation_id);
            $stmt->execute();

            $stmt2 = $conn->prepare("UPDATE sports_equipment SET quantity = quantity + ? WHERE equipment_id=?");
            $stmt2->bind_param("ii", $res['quantity'], $res['equipment_id']);
            $stmt2->execute();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/layout.css">
  <link rel="stylesheet" href="../assets/css/index.css">
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
    <a href="index.php" class="menu-item active"><div class="icon-container"><i data-feather="home"></i></div><span class="text-sm">หน้าหลัก</span></a>
    <a href="manage_equipment.php" class="menu-item"><div class="icon-container"><i data-feather="package"></i></div><span class="text-sm">จัดการอุปกรณ์</span></a>
    <a href="manage_user.php" class="menu-item"><div class="icon-container"><i data-feather="users"></i></div><span class="text-sm">จัดการข้อมูลผู้ใช้งาน</span></a>
    <a href="report.php" class="menu-item"><div class="icon-container"><i data-feather="bar-chart-2"></i></div><span class="text-sm">รายงานสถิติ</span></a>
    <a href="../logout.php" class="menu-item"><div class="icon-container"><i data-feather="log-out"></i></div><span class="text-sm">ออกจากระบบ</span></a>
  </div>
</div>

<!-- Main Content -->
<div class="flex-1 pt-20 md:pt-0 p-4 md:p-8 overflow-y-auto space-y-10">

  <!-- ✅ รอดำเนินการ -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 border-b pb-2 text-blue-600 text-center">📌 รายการรอดำเนินการ</h1>
    <div class="flex justify-end mb-2">
      <input type="text" placeholder="ค้นหา..." class="search-input border px-2 py-1 rounded text-sm" data-table="pendingTable">
    </div>
    <div class="overflow-x-auto">
      <table id="pendingTable" class="w-full border-collapse text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="border p-2">ลำดับ</th>
            <th class="border p-2">ชื่อ</th>
            <th class="border p-2">รหัส</th>
            <th class="border p-2">อุปกรณ์</th>
            <th class="border p-2">จำนวน</th>
            <th class="border p-2">วันที่ยืม</th>
            <th class="border p-2">วันคืน</th>
            <th class="border p-2">อัปโหลดก่อนยืม</th>
            <th class="border p-2">สถานะ</th>
            <th class="border p-2">การจัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT rh.*, u.fname, u.lname, u.student_id, se.name AS equipment_name 
          FROM reservation_history rh
          JOIN user u ON rh.user_id=u.user_id
          JOIN sports_equipment se ON rh.equipment_id=se.equipment_id
          WHERE rh.status='รอดำเนินการ'
          ORDER BY rh.reservation_date ASC";
          $res = $conn->query($sql);
          $i=1;
          while ($row = $res->fetch_assoc()):
          ?>
          <tr>
            <td class="border p-2 text-center"><?= $i++ ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['fname']." ".$row['lname']) ?></td>
            <td class="border p-2 text-center"><?= $row['student_id'] ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['equipment_name']) ?></td>
            <td class="border p-2 text-center"><?= $row['quantity'] ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['reservation_date'])) ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['due_date'])) ?></td>
            <td class="border p-2 text-center">
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                <input type="file" name="before_image" class="text-sm w-32">
                <button type="submit" name="action" value="upload_before" class="px-2 py-1 bg-gray-500 text-white rounded text-sm">อัปโหลด</button>
              </form>
            </td>
            <td class="border p-2 text-orange-500 text-center"><?= $row['status'] ?></td>
            <td class="border p-2 text-center">
              <form method="POST" class="inline-block">
                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                <button type="submit" name="action" value="approve" class="px-3 py-1 bg-green-500 text-white rounded">อนุมัติ</button>
              </form>
              <form method="POST" class="inline-block">
                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                <button type="submit" name="action" value="reject" class="px-3 py-1 bg-red-500 text-white rounded">ไม่อนุมัติ</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ✅ ยังไม่คืน -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 border-b pb-2 text-red-600 text-center">📌 รายการที่ยังไม่คืน</h1>
    <div class="flex justify-end mb-2">
      <input type="text" placeholder="ค้นหา..." class="search-input border px-2 py-1 rounded text-sm" data-table="notReturnTable">
    </div>
    <div class="overflow-x-auto">
      <table id="notReturnTable" class="w-full border-collapse text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="border p-2">ลำดับ</th>
            <th class="border p-2">ชื่อ</th>
            <th class="border p-2">รหัส</th>
            <th class="border p-2">อุปกรณ์</th>
            <th class="border p-2">จำนวน</th>
            <th class="border p-2">วันที่ยืม</th>
            <th class="border p-2">วันคืนที่กำหนด</th>
            <th class="border p-2">ก่อนยืม</th>
            <th class="border p-2">อัปโหลดหลังยืม</th>
            <th class="border p-2">ค่าปรับ</th>
            <th class="border p-2">สถานะ</th>
            <th class="border p-2">การจัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT rh.*, u.fname, u.lname, u.student_id, se.name AS equipment_name 
          FROM reservation_history rh
          JOIN user u ON rh.user_id=u.user_id
          JOIN sports_equipment se ON rh.equipment_id=se.equipment_id
          WHERE rh.status='อนุมัติ' AND rh.end_date IS NULL
          ORDER BY rh.due_date ASC";
          $res = $conn->query($sql);
          $i=1;
          while ($row = $res->fetch_assoc()):
          ?>
          <tr>
            <td class="border p-2 text-center"><?= $i++ ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['fname']." ".$row['lname']) ?></td>
            <td class="border p-2 text-center"><?= $row['student_id'] ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['equipment_name']) ?></td>
            <td class="border p-2 text-center"><?= $row['quantity'] ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['reservation_date'])) ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['due_date'])) ?></td>
            <td class="border p-2 text-center">
              <?php if ($row['before_image']): ?>
                <img src="../<?= $row['before_image'] ?>" class="h-16 mx-auto">
              <?php else: ?>
                <span class="text-gray-400">-</span>
              <?php endif; ?>
            </td>
            <td class="border p-2 text-center">
              <form method="POST" enctype="multipart/form-data" class="flex items-center justify-center gap-1">
                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                <input type="file" name="return_image" class="text-sm w-32">
                <button type="submit" name="action" value="upload_return" class="px-2 py-1 bg-gray-500 text-white rounded text-sm">อัปโหลด</button>
              </form>
            </td>
            <td class="border p-2 text-center">
  <?php
  $today = new DateTime();
  $due = new DateTime($row['due_date']);
  $fine_display = "-";

  if ($today > $due) {
      $daysLate = $due->diff($today)->days;
      if ($daysLate <= 7) {
          $fine_display = "<span class='text-red-600 font-bold'>50 บาท</span>";
      } else {
          $fine_display = "<span class='text-red-600 font-bold'>100 บาท</span>";
      }
  }
  echo $fine_display;
  ?>
</td>

            <td class="border p-2 text-red-500 text-center">ยังไม่คืน</td>
            <td class="border p-2 text-center">
              <form method="POST">
                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                <button type="submit" name="action" value="return" class="px-3 py-1 bg-blue-500 text-white rounded">คืนแล้ว</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- ❌ ไม่อนุมัติ -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 border-b pb-2 text-gray-700 text-center">📌 รายการที่ไม่อนุมัติ</h1>
    <div class="flex justify-end mb-2">
      <input type="text" placeholder="ค้นหา..." class="search-input border px-2 py-1 rounded text-sm" data-table="rejectedTable">
    </div>
    <div class="overflow-x-auto">
      <table id="rejectedTable" class="w-full border-collapse text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="border p-2">ลำดับ</th>
            <th class="border p-2">ชื่อ</th>
            <th class="border p-2">รหัส</th>
            <th class="border p-2">อุปกรณ์</th>
            <th class="border p-2">จำนวน</th>
            <th class="border p-2">วันที่ยืม</th>
            <th class="border p-2">วันคืน</th>
            <th class="border p-2">สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT rh.*, u.fname, u.lname, u.student_id, se.name AS equipment_name 
                  FROM reservation_history rh
                  JOIN user u ON rh.user_id=u.user_id
                  JOIN sports_equipment se ON rh.equipment_id=se.equipment_id
                  WHERE rh.status='ไม่อนุมัติ'
                  ORDER BY rh.reservation_date ASC";
          $res = $conn->query($sql);
          $i=1;
          while ($row = $res->fetch_assoc()):
          ?>
          <tr>
            <td class="border p-2 text-center"><?= $i++ ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['fname']." ".$row['lname']) ?></td>
            <td class="border p-2 text-center"><?= $row['student_id'] ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['equipment_name']) ?></td>
            <td class="border p-2 text-center"><?= $row['quantity'] ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['reservation_date'])) ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['due_date'])) ?></td>
            <td class="border p-2 text-center text-gray-500">ไม่อนุมัติ</td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ✅ คืนแล้ว -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 border-b pb-2 text-green-600 text-center">📌 ประวัติการคืนแล้ว</h1>
    <div class="flex justify-end mb-2">
      <input type="text" placeholder="ค้นหา..." class="search-input border px-2 py-1 rounded text-sm" data-table="returnedTable">
    </div>
    <div class="overflow-x-auto">
      <table id="returnedTable" class="w-full border-collapse text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="border p-2">ลำดับ</th>
            <th class="border p-2">ชื่อ</th>
            <th class="border p-2">รหัส</th>
            <th class="border p-2">อุปกรณ์</th>
            <th class="border p-2">จำนวน</th>
            <th class="border p-2">วันที่ยืม</th>
            <th class="border p-2">วันคืนที่กำหนด</th>
            <th class="border p-2">วันคืนจริง</th>
            <th class="border p-2">ก่อนยืม</th>
            <th class="border p-2">หลังยืม</th>
            <th class="border p-2">ค่าปรับ</th>
            <th class="border p-2">สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT rh.*, u.fname, u.lname, u.student_id, se.name AS equipment_name 
          FROM reservation_history rh
          JOIN user u ON rh.user_id=u.user_id
          JOIN sports_equipment se ON rh.equipment_id=se.equipment_id
          WHERE rh.status='คืนแล้ว'
          ORDER BY rh.end_date ASC";
          $res = $conn->query($sql);
          $i=1;
          while ($row = $res->fetch_assoc()):
          ?>
          <tr>
            <td class="border p-2 text-center"><?= $i++ ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['fname']." ".$row['lname']) ?></td>
            <td class="border p-2 text-center"><?= $row['student_id'] ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['equipment_name']) ?></td>
            <td class="border p-2 text-center"><?= $row['quantity'] ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['reservation_date'])) ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['due_date'])) ?></td>
            <td class="border p-2 text-center"><?= date("d/m/Y", strtotime($row['end_date'])) ?></td>
            <td class="border p-2 text-center">
              <?php if ($row['before_image']): ?>
                <img src="../<?= $row['before_image'] ?>" class="h-16 mx-auto">
              <?php else: ?>
                <span class="text-gray-400">-</span>
              <?php endif; ?>
            </td>
            <td class="border p-2 text-center">
              <?php if ($row['return_image']): ?>
                <img src="../<?= $row['return_image'] ?>" class="h-16 mx-auto">
              <?php else: ?>
                <span class="text-gray-400">-</span>
              <?php endif; ?>
            </td>
            <td class="border p-2 text-center">
              <?= $row['fine'] > 0 ? "<span class='text-red-600 font-bold'>".number_format($row['fine'],2)." บาท</span>" : "-" ?>
            </td>
            <td class="border p-2 text-green-600 text-center">คืนแล้ว</td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

                
<!-- Script ค้นหา -->
<script>
  document.querySelectorAll('.search-input').forEach(input => {
    input.addEventListener('keyup', function() {
      const tableId = this.getAttribute('data-table');
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll(`#${tableId} tbody tr`);
      rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
      });
    });
  });
</script>
<script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();</script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>
