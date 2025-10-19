<?php
// เรียกใช้งานไฟล์ header ซึ่งมีการเชื่อมต่อฐานข้อมูลและส่วนหัวของเว็บ
include '../includes/db.php';

// --- การรักษาความปลอดภัย ---
// ตรวจสอบว่าผู้ใช้ล็อกอินเข้ามาในระบบหรือยัง
// ถ้ายังไม่ได้ล็อกอิน ให้ redirect ไปยังหน้า login.php
if (!isset($_SESSION['roll_id'])) {
    header("Location: login.php");
    exit(); // หยุดการทำงานของสคริปต์ทันที
}

// // ดึง user_id ของผู้ใช้ที่ล็อกอินอยู่
$user_id = $_SESSION['roll_id'];
?>


<!DOCTYPE html>
<html lang="tr" class="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/layout.css">
  <link rel="stylesheet" href="../assets/css/table.css">
  <link rel="stylesheet" href="../assets/css/history.css">
  <script src="https://unpkg.com/feather-icons"></script>
  <title>HR Dashboard</title>
  <link rel="stylesheet" href="../assets/css/sidebar-layout.css">
</head>

<body class="flex flex-col md:flex-row has-sidebar">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>
  <!-- Mobile Header/Hamburger Menu Button -->
  <div class="fixed top-0 left-0 w-full p-4 md:hidden z-40 bg-gray-100 flex items-center justify-between shadow-md">
    <h1 class="text-lg font-bold">HR Dashboard</h1>
    <button id="mobile-menu-button" class="p-2 rounded-md bg-gray-200 text-gray-600">
      <i data-feather="menu" class="h-6 w-6"></i>
    </button>
  </div>

  <!-- Sidebar -->
  <div id="sidebar" class="hr-sidebar transform -translate-x-full md:translate-x-0 fixed md:sticky top-0 left-0 h-full md:h-screen w-64 flex flex-col overflow-hidden z-30 transition-transform duration-300 ease-in-out">
    <!-- Logo and Brand -->
    <div class="p-6 flex items-center justify-between">
      <div class="flex items-center">
        <div class="h-10 w-10 gradient-bg rounded-xl flex items-center justify-center shadow-lg">
          <span class="text-white font-bold text-xl">MB</span>
        </div>
        <div class="ml-3">
          <h1 class="text-lg font-bold">Els-Mbac</h1>
          <div class="flex items-center">
          </div>
        </div>
      </div>
    </div>

    <!-- Menu -->
    <div class="px-4 flex-1 overflow-y-auto">
      <div>
        <p class="category-title">Menu</p>

        <a href="index.php" class="menu-item active">
          <div class="icon-container">
            <i data-feather="home" class="h-5 w-5"></i>
          </div>
          <span class="text-sm">index</span>
        </a>

        <a href="my_reservations.php" class="menu-item">
          <div class="icon-container">
            <i data-feather="briefcase" class="h-5 w-5"></i>
          </div>
          <span class="text-sm">my_reservations</span>
        </a>

        <a href="show_item.php" class="menu-item">
          <div class="icon-container">
            <i data-feather="calendar" class="h-5 w-5"></i>
          </div>
          <span class="text-sm">item sport</span>
        </a>
      </div>
      <div>
        <p class="category-title">Manage data</p>

        <a href="add_all.php" class="menu-item">
          <div class="icon-container">
            <i data-feather="bar-chart-2" class="h-5 w-5"></i>
          </div>
          <span class="text-sm">Add Data</span>
        </a>

        <a href="edit_all.php" class="menu-item">
          <div class="icon-container">
            <i data-feather="award" class="h-5 w-5"></i>
          </div>
          <span class="text-sm">Edit data</span>
        </a>

        <a href="delete_all.php" class="menu-item">
          <div class="icon-container">
            <i data-feather="clipboard" class="h-5 w-5"></i>
          </div>
          <span class="text-sm">Delete Data</span>
        </a>
      </div>
    </div>
    <!-- User Profile -->
    <div class="mt-auto p-4">
      <div class="gradient-bg p-4 rounded-xl text-white">
        <div class="flex items-center mb-3">
          <div class="relative">
            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
              <span class="text-white font-semibold">BD</span>
            </div>
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
          </div>
          <div class="ml-3">
            <p class="font-medium text-sm">Berkay Derin</p>
            <p class="text-xs text-white/70">Frontend Software Engineer</p>
          </div>
          <button class="ml-auto p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
            <a href="../logout.php"><i data-feather="log-out" class="h-4 w-4"></i></a>
          </button>
        </div>
        <div class="flex justify-between text-xs">
          <div>
            <p class="opacity-70">E-posta</p>
            <p class="font-medium mt-1">derinberkay67@gmail.com</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Background overlay - mobile only -->
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-20 hidden md:hidden">

  </div>

  <!-- Main content -->
  <div class="flex-1 pt-20 md:pt-0 p-4 md:p-8 overflow-y-auto">
    <div class="main-content">
        <div class="container">
            <h2>ประวัติการยืมอุปกรณ์</h2>
            <p>นี่คือรายการยืม-คืนอุปกรณ์ทั้งหมดของคุณ</p>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>หมวดหมู่อุปกรณ์</th>
                            <th>วันที่ยืม</th>
                            <th>กำหนดคืน</th>
                            <th>วันที่คืนจริง</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 1. เตรียมคำสั่ง SQL เพื่อดึงข้อมูลประวัติการยืม
                        // เราต้อง JOIN ตาราง reservation_history กับ equipment_category เพื่อเอา "ชื่อ" หมวดหมู่มาแสดง
                        $sql = "SELECT 
                            rh.reservation_date,
                            rh.due_date,
                            rh.end_date,
                            rh.status,
                            ec.category
                        FROM 
                            reservation_history AS rh
                        JOIN 
                            equipment_category AS ec ON rh.equipment_category_id = ec.equipment_category_id
                        WHERE 
                            rh.user_id = ?
                        ORDER BY 
                            rh.reservation_date DESC"; // เรียงจากรายการล่าสุดไปเก่าสุด

                        // 2. เตรียม Statement เพื่อป้องกัน SQL Injection
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id); // "i" หมายถึงตัวแปรเป็น integer

                        // 3. สั่งให้ Statement ทำงาน
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0) {
                            $counter = 1; // ตัวแปรสำหรับนับลำดับ
                            // 4. วนลูปเพื่อแสดงผลข้อมูลในตาราง
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['reservation_date'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['due_date'])); ?></td>
                                    <td>
                                        <?php
                                        // ถ้า end_date ไม่เป็น NULL (มีการคืนแล้ว) ให้แสดงวันที่คืน
                                        // มิฉะนั้นให้แสดงว่า 'ยังไม่คืน'
                                        echo $row['end_date'] ? date('d/m/Y H:i', strtotime($row['end_date'])) : '<span class="text-muted">ยังไม่คืน</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        // เพิ่ม class ให้กับสถานะเพื่อใส่สีให้สวยงาม
                                        $status_class = 'status-' . strtolower(htmlspecialchars($row['status']));
                                        echo '<span class="status ' . $status_class . '">' . htmlspecialchars($row['status']) . '</span>';
                                        ?>
                                    </td>
                                </tr>
                        <?php
                            } // สิ้นสุด while loop
                        } else {
                            // 5. กรณีไม่พบข้อมูลการยืม
                            echo '<tr><td colspan="6" class="text-center">คุณยังไม่มีประวัติการยืมอุปกรณ์</td></tr>';
                        }
                        // ปิด statement
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
  <script src="../assets/js/script.js"></script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="../assets/js/sidebar-toggle.js"></script>
</body>

</html>