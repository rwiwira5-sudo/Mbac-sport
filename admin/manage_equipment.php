<?php
session_start();
require_once '../includes/db.php';

// --- การรักษาความปลอดภัย ---
if (!isset($_SESSION['roll_id']) || $_SESSION['roll_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// --- รับ action ---
$action = $_GET['action'] ?? 'list';
$equipment_id = $_GET['id'] ?? null;
$equipment = null;
$page_title = "เพิ่มอุปกรณ์ใหม่";

// --- ดึงข้อมูลเก่า ถ้าแก้ไข ---
if ($action === 'edit' && $equipment_id) {
    $page_title = "แก้ไขข้อมูลอุปกรณ์";
    $stmt = $conn->prepare("SELECT * FROM sports_equipment WHERE equipment_id = ?");
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipment = $result->fetch_assoc();
}

// --- ลบข้อมูล ---
if ($action === 'delete' && $equipment_id) {
    $stmt = $conn->prepare("DELETE FROM sports_equipment WHERE equipment_id = ?");
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
    header("Location: manage_equipment.php");
    exit();
}

// --- Process Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $status = $_POST['status'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $basic_info = $_POST['basic_info'];
    $rules = $_POST['rules'];
    $usage_maintenance = $_POST['usage_maintenance'];
    $equipment_id_post = $_POST['equipment_id'];
    $image = null;

    // อัพโหลดรูปภาพ
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/images/";
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $filename;
        }
    }

    if (isset($_POST['add_equipment'])) {
        $sql = "INSERT INTO sports_equipment 
        (name, equipment_status, quantity, unit, image, basic_info, rules, usage_maintenance) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssss", $name, $status, $quantity, $unit, $image, $basic_info, $rules, $usage_maintenance);
    } elseif (isset($_POST['edit_equipment'])) {
        if ($image) {
            $sql = "UPDATE sports_equipment 
                    SET name = ?, equipment_status = ?, quantity = ?, unit = ?, image = ?, 
                        basic_info = ?, rules = ?, usage_maintenance = ? 
                    WHERE equipment_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisssssi", $name, $status, $quantity, $unit, $image, $basic_info, $rules, $usage_maintenance, $equipment_id_post);
        } else {
            $sql = "UPDATE sports_equipment 
                    SET name = ?, equipment_status = ?, quantity = ?, unit = ?, 
                        basic_info = ?, rules = ?, usage_maintenance = ? 
                    WHERE equipment_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssissssi", $name, $status, $quantity, $unit, $basic_info, $rules, $usage_maintenance, $equipment_id_post);
        }
    }

    if ($stmt->execute()) {
        header("Location: manage_equipment.php");
        exit();
    } else {
        echo "<div class='error'>เกิดข้อผิดพลาดในการบันทึกข้อมูล</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>จัดการอุปกรณ์</title>
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
    <a href="index.php" class="menu-item"><div class="icon-container"><i data-feather="home" class="h-5 w-5"></i></div><span class="text-sm">หน้าหลัก</span></a>
    <a href="manage_equipment.php" class="menu-item active"><div class="icon-container"><i data-feather="package" class="h-5 w-5"></i></div><span class="text-sm">จัดการอุปกรณ์</span></a>
    <a href="manage_user.php" class="menu-item <?php echo ($current_page == 'manage_user.php') ? 'active' : ''; ?>">
      <div class="icon-container"><i data-feather="users" class="h-5 w-5"></i></div>
      <span class="text-sm">จัดการข้อมูลผู้ใช้งาน</span>
    </a>
     <a href="report.php" class="menu-item"><div class="icon-container"><i data-feather="bar-chart-2"></i></div><span class="text-sm">รายงานสถิติ</span></a>
    <a href="../logout.php" class="menu-item"><div class="icon-container"><i data-feather="log-out" class="h-5 w-5"></i></div><span class="text-sm">ออกจากระบบ</span></a>
  </div>
</div>

<!-- Main Content -->
<div class="flex-1 p-6 md:p-10 overflow-y-auto space-y-10">

  <!-- ฟอร์มเพิ่ม/แก้ไข -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 border-b pb-2"><?php echo $page_title; ?></h1>
    <form action="manage_equipment.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="equipment_id" value="<?php echo $equipment['equipment_id'] ?? ''; ?>">

      <div>
        <label for="name" class="block font-medium">ชื่ออุปกรณ์:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($equipment['name'] ?? ''); ?>" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label for="quantity" class="block font-medium">จำนวน:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($equipment['quantity'] ?? '0'); ?>" class="w-full border rounded p-2" min="0" required>
      </div>

      <div>
        <label for="unit" class="block font-medium">หน่วย:</label>
        <select id="unit" name="unit" class="w-full border rounded p-2" required>
          <?php
          $units = ["ลูก", "ชิ้น", "ชุด", "คู่"];
          foreach ($units as $u) {
              $selected = (isset($equipment) && $equipment['unit'] == $u) ? 'selected' : '';
              echo "<option value='{$u}' {$selected}>{$u}</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label for="status" class="block font-medium">สถานะ:</label>
        <select id="status" name="status" class="w-full border rounded p-2" required>
          <?php
          $statuses = ["พร้อมใช้งาน", "ไม่พร้อมใช้งาน", "กำลังซ่อม"];
          foreach ($statuses as $st) {
              $selected = (isset($equipment) && $equipment['equipment_status'] == $st) ? 'selected' : '';
              echo "<option value='{$st}' {$selected}>{$st}</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label for="basic_info" class="block font-medium">ข้อมูลพื้นฐาน:</label>
        <textarea id="basic_info" name="basic_info" class="w-full border rounded p-2" rows="3"><?php echo htmlspecialchars($equipment['basic_info'] ?? ''); ?></textarea>
      </div>

      <div>
        <label for="rules" class="block font-medium">กติกา:</label>
        <textarea id="rules" name="rules" class="w-full border rounded p-2" rows="3"><?php echo htmlspecialchars($equipment['rules'] ?? ''); ?></textarea>
      </div>

      <div>
        <label for="usage_maintenance" class="block font-medium">วิธีใช้และดูแลรักษา:</label>
        <textarea id="usage_maintenance" name="usage_maintenance" class="w-full border rounded p-2" rows="3"><?php echo htmlspecialchars($equipment['usage_maintenance'] ?? ''); ?></textarea>
      </div>

      <div>
        <label for="image" class="block font-medium">รูปภาพอุปกรณ์:</label>
        <input type="file" id="image" name="image" class="w-full border rounded p-2">
        <?php if (!empty($equipment['image'])): ?>
          <img src="../assets/images/<?php echo $equipment['image']; ?>" alt="รูปอุปกรณ์" class="h-24 mt-2 rounded">
        <?php endif; ?>
      </div>

      <div class="flex space-x-2">
        <?php if ($action === 'edit'): ?>
          <button type="submit" name="edit_equipment" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">บันทึกการแก้ไข</button>
        <?php else: ?>
          <button type="submit" name="add_equipment" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">เพิ่มอุปกรณ์</button>
        <?php endif; ?>
        <a href="manage_equipment.php" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">ยกเลิก</a>
      </div>
    </form>
  </div>

  <!-- ตารางแสดงรายการอุปกรณ์ -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 border-b pb-2">📋 รายการอุปกรณ์ทั้งหมด</h1>
    <div class="overflow-x-auto">
      <table class="w-full border-collapse text-sm">
        <thead>
          <tr class="bg-gray-100 text-gray-700">
            <th class="border p-2 text-center">ลำดับ</th>
            <th class="border p-2">ชื่ออุปกรณ์</th>
            <th class="border p-2 text-center">จำนวน</th>
            <th class="border p-2 text-center">หน่วย</th>
            <th class="border p-2">สถานะ</th>
            <th class="border p-2">ข้อมูลพื้นฐาน</th>
            <th class="border p-2">กติกา</th>
            <th class="border p-2">วิธีใช้และดูแลรักษา</th>
            <th class="border p-2">รูปภาพ</th>
            <th class="border p-2 text-center">การจัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql_list = "SELECT * FROM sports_equipment ORDER BY equipment_id DESC";
          $result_list = $conn->query($sql_list);
          if ($result_list->num_rows > 0) {
              $i = 1;
              while ($row = $result_list->fetch_assoc()) {
    echo "<tr class='hover:bg-gray-50'>
            <td class='border p-2 text-center'>{$i}</td>
            <td class='border p-2'>".htmlspecialchars($row['name'])."</td>
            <td class='border p-2 text-center'>".htmlspecialchars($row['quantity'])."</td>
            <td class='border p-2 text-center'>".htmlspecialchars($row['unit'])."</td>
            <td class='border p-2'>".htmlspecialchars($row['equipment_status'])."</td>
            <td class='border p-2'>".nl2br(htmlspecialchars($row['basic_info']))."</td>
            <td class='border p-2'>".nl2br(htmlspecialchars($row['rules']))."</td>
            <td class='border p-2'>".nl2br(htmlspecialchars($row['usage_maintenance']))."</td>
            <td class='border p-2 text-center'>";
    if (!empty($row['image'])) {
        echo "<img src='../assets/images/{$row['image']}' class='h-12 mx-auto rounded'>";
    } else {
        echo "<span class='text-gray-400'>ไม่มีรูป</span>";
    }
    echo "</td>
          <td class='border p-2 text-center'>
            <div class='flex justify-center gap-2'>
              <a href='manage_equipment.php?action=edit&id={$row['equipment_id']}' 
                 class='px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600'>
                 แก้ไข
              </a>
              <a href='manage_equipment.php?action=delete&id={$row['equipment_id']}' 
                 onclick='return confirm(\"ยืนยันการลบ?\")' 
                 class='px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600'>
                 ลบ
              </a>
            </div>
          </td>
        </tr>";
    $i++;
}

          } else {
              echo "<tr><td colspan='10' class='text-center p-3 text-gray-500'>ไม่มีข้อมูลอุปกรณ์</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();</script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>
