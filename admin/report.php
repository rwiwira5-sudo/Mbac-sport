<?php 
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['roll_id']) || $_SESSION['roll_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// รับค่า วันที่เริ่ม และ วันที่สิ้นสุด จากฟอร์ม
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

$where = "";
$params = [];
$types = "";

if ($start_date && $end_date) {
    $where = "WHERE rh.reservation_date BETWEEN ? AND ?";
    $params = [$start_date . " 00:00:00", $end_date . " 23:59:59"];
    $types = "ss";
}

// ✅ ดึงข้อมูลจำนวนครั้งการยืมอุปกรณ์ทั้งหมด
$sql1 = "
    SELECT se.name, COUNT(rh.reservation_id) AS total_borrowed
    FROM reservation_history rh
    JOIN sports_equipment se ON rh.equipment_id = se.equipment_id
    $where
    GROUP BY se.equipment_id
    ORDER BY total_borrowed DESC
";
$stmt1 = $conn->prepare($sql1);
if ($where) $stmt1->bind_param($types, ...$params);
$stmt1->execute();
$equipment_query = $stmt1->get_result();

// ✅ ดึงข้อมูลจำนวนครั้งการยืมแยกตามนักศึกษา
$sql2 = "
    SELECT CONCAT(u.fname, ' ', u.lname) AS student_name, COUNT(rh.reservation_id) AS total_borrowed
    FROM reservation_history rh
    JOIN user u ON rh.user_id = u.user_id
    $where
    GROUP BY u.user_id
    ORDER BY total_borrowed DESC
";
$stmt2 = $conn->prepare($sql2);
if ($where) $stmt2->bind_param($types, ...$params);
$stmt2->execute();
$student_query = $stmt2->get_result();

// ✅ แปลงข้อมูลเป็น array สำหรับ Chart.js
$equipment_labels = [];
$equipment_data = [];
while ($row = $equipment_query->fetch_assoc()) {
    $equipment_labels[] = $row['name'];
    $equipment_data[] = $row['total_borrowed'];
}

$student_labels = [];
$student_data = [];
while ($row = $student_query->fetch_assoc()) {
    $student_labels[] = $row['student_name'];
    $student_data[] = $row['total_borrowed'];
}

// ✅ ดึงข้อมูลรายงานค่าปรับรวมตามนักศึกษา
$sql3 = "
    SELECT CONCAT(u.fname, ' ', u.lname) AS student_name, 
           SUM(rh.fine) AS total_fine,
           COUNT(rh.reservation_id) AS total_late
    FROM reservation_history rh
    JOIN user u ON rh.user_id = u.user_id
    WHERE rh.fine > 0
    " . ($where ? "AND rh.reservation_date BETWEEN ? AND ?" : "") . "
    GROUP BY u.user_id
    ORDER BY total_fine DESC
";
$stmt3 = $conn->prepare($sql3);
if ($where) $stmt3->bind_param($types, ...$params);
$stmt3->execute();
$fine_query = $stmt3->get_result();

$total_fine_all = 0;
$fine_data = [];
while ($row = $fine_query->fetch_assoc()) {
    $fine_data[] = $row;
    $total_fine_all += $row['total_fine'];
}

// ✅ ดึงข้อมูลว่านักศึกษาแต่ละคนยืมอุปกรณ์อะไรบ้าง + รวมจำนวนชิ้น + จำนวนครั้ง
$sql_student_items = "
    SELECT 
        CONCAT(u.fname, ' ', u.lname) AS student_name,
        se.name AS equipment_name,
        SUM(rh.quantity) AS total_item,
        COUNT(rh.reservation_id) AS total_times
    FROM reservation_history rh
    JOIN user u ON rh.user_id = u.user_id
    JOIN sports_equipment se ON rh.equipment_id = se.equipment_id
    $where
    GROUP BY u.user_id, se.equipment_id
    ORDER BY student_name ASC, total_item DESC
";
$stmt_items = $conn->prepare($sql_student_items);
if ($where) $stmt_items->bind_param($types, ...$params);
$stmt_items->execute();
$student_items_query = $stmt_items->get_result();

// ✅ จัดกลุ่มข้อมูลให้แสดงใน tooltip
$student_items = [];
while ($row = $student_items_query->fetch_assoc()) {
    $student_items[$row['student_name']][] = [
        'equipment' => $row['equipment_name'],
        'total' => $row['total_item'],
        'times' => $row['total_times']
    ];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>รายงานสถิติ</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/layout.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../assets/css/sidebar-layout.css">
</head>
<body class="flex flex-col md:flex-row bg-gray-100 font-sans has-sidebar">
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>
<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<!-- Sidebar -->
<div id="sidebar" class="hr-sidebar fixed md:sticky top-0 left-0 h-full md:h-screen w-64 flex flex-col overflow-y-auto z-30">
  <div class="p-6 flex flex-col items-center justify-center">
    <img src="../assets/images/university_logo.jpg" alt="University Logo" class="h-28 w-28 object-contain mb-3">
    <h1 class="text-lg font-bold text-center">MBAC SPORT</h1>
    <h1 class="text-lg font-bold text-center">ADMIN</h1>
  </div>

  <div class="px-4">
    <p class="category-title">Admin Menu</p>
    <a href="index.php" class="menu-item"><div class="icon-container"><i data-feather="home"></i></div><span class="text-sm">หน้าหลัก</span></a>
    <a href="manage_equipment.php" class="menu-item"><div class="icon-container"><i data-feather="package"></i></div><span class="text-sm">จัดการอุปกรณ์</span></a>
    <a href="manage_user.php" class="menu-item <?php echo ($current_page == 'manage_user.php') ? 'active' : ''; ?>">
      <div class="icon-container"><i data-feather="users"></i></div><span class="text-sm">จัดการข้อมูลผู้ใช้งาน</span>
    </a>
    <a href="report.php" class="menu-item <?php echo ($current_page == 'report.php') ? 'active' : ''; ?>">
      <div class="icon-container"><i data-feather="bar-chart-2"></i></div><span class="text-sm">รายงานสถิติ</span>
    </a>
    <a href="../logout.php" class="menu-item"><div class="icon-container"><i data-feather="log-out"></i></div><span class="text-sm">ออกจากระบบ</span></a>
  </div>
</div>

<!-- Main Content -->
<div class="flex-1 p-6 md:p-10 overflow-y-auto">
  <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">📊 รายงานสถิติ</h1>

  <!-- ฟอร์มเลือกช่วงวัน -->
  <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end justify-center">
    <div>
      <label for="start_date" class="block text-sm font-medium">วันที่เริ่ม</label>
      <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="border rounded p-2 w-48">
    </div>
    <div>
      <label for="end_date" class="block text-sm font-medium">วันที่สิ้นสุด</label>
      <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="border rounded p-2 w-48">
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-28 text-center">ค้นหา</button>
    <a href="report.php" class="bg-gray-500 text-white px-4 py-2 rounded w-28 text-center">รีเซ็ต</a>
  </form>

  <!-- กราฟ 2 ช่อง -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white shadow p-6 rounded">
      <h2 class="text-xl font-bold text-gray-700 mb-4">📦 จำนวนครั้งที่ยืมอุปกรณ์ทั้งหมด</h2>
      <canvas id="equipmentChart"></canvas>
    </div>

    <div class="bg-white shadow p-6 rounded">
      <h2 class="text-xl font-bold text-gray-700 mb-4">👨‍🎓 จำนวนนักศึกษาที่ยืมอุปกรณ์</h2>
      <canvas id="studentChart"></canvas>
    </div>
  </div>

  <!-- 💰 รายงานค่าปรับ -->
  <div class="bg-white shadow p-6 rounded mt-8">
    <h2 class="text-xl font-bold text-gray-700 mb-4">💰 รายงานค่าปรับรวมตามนักศึกษา</h2>
    <div class="flex flex-wrap justify-between items-center mb-4">
      <div class="flex items-center gap-2">
        <input type="text" id="fineSearch" placeholder="🔍 ค้นหาชื่อนักศึกษา..." class="border rounded p-2 w-64 text-sm">
        <button type="button" id="fineReset" class="bg-gray-500 text-white px-3 py-2 rounded text-sm">รีเซ็ต</button>
      </div>
    </div>
    <?php if (count($fine_data) > 0): ?>
    <div class="overflow-x-auto">
      <table id="fineTable" class="w-full border-collapse text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="border p-2 text-center">ลำดับ</th>
            <th class="border p-2">ชื่อนักศึกษา</th>
            <th class="border p-2 text-center">จำนวนครั้งที่ถูกปรับ</th>
            <th class="border p-2 text-center">ค่าปรับรวม (บาท)</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=1; foreach ($fine_data as $row): ?>
          <tr>
            <td class="border p-2 text-center"><?= $i++ ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['student_name']) ?></td>
            <td class="border p-2 text-center"><?= $row['total_late'] ?></td>
            <td class="border p-2 text-center text-red-600 font-bold"><?= number_format($row['total_fine'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
          <tr class="bg-gray-100 font-bold">
            <td colspan="3" class="border p-2 text-right">รวมทั้งหมด</td>
            <td class="border p-2 text-center text-blue-600"><?= number_format($total_fine_all, 2) ?> บาท</td>
          </tr>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p class="text-center text-gray-500">ไม่พบข้อมูลค่าปรับในช่วงเวลาที่เลือก</p>
    <?php endif; ?>
  </div>
</div>

<!-- ✅ ส่งข้อมูล student_items ไป JS -->
<script>
const studentItems = <?php echo json_encode($student_items, JSON_UNESCAPED_UNICODE); ?>;
</script>

<script>
// ✅ กราฟอุปกรณ์
const equipmentLabels = <?php echo json_encode($equipment_labels); ?>;
const equipmentData = <?php echo json_encode($equipment_data); ?>;
const equipmentColors = equipmentLabels.map((_, i) => {
    const colors = [
        'rgba(255, 99, 132, 0.6)',
        'rgba(54, 162, 235, 0.6)',
        'rgba(255, 206, 86, 0.6)',
        'rgba(75, 192, 192, 0.6)',
        'rgba(153, 102, 255, 0.6)',
        'rgba(255, 159, 64, 0.6)'
    ];
    return colors[i % colors.length];
});
new Chart(document.getElementById('equipmentChart'), {
    type: 'bar',
    data: { labels: equipmentLabels, datasets: [{ label: 'จำนวนครั้งที่ถูกยืม', data: equipmentData, backgroundColor: equipmentColors, borderColor: equipmentColors.map(c => c.replace("0.6","1")), borderWidth: 1 }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

// ✅ กราฟนักศึกษา + tooltip
const studentLabels = <?php echo json_encode($student_labels); ?>;
const studentData = <?php echo json_encode($student_data); ?>;
const studentColors = studentLabels.map((_, i) => {
    const colors = [
        'rgba(255, 99, 132, 0.6)',
        'rgba(54, 162, 235, 0.6)',
        'rgba(255, 206, 86, 0.6)',
        'rgba(75, 192, 192, 0.6)',
        'rgba(153, 102, 255, 0.6)',
        'rgba(255, 159, 64, 0.6)'
    ];
    return colors[i % colors.length];
});
new Chart(document.getElementById('studentChart'), {
    type: 'bar',
    data: { labels: studentLabels, datasets: [{ label: 'จำนวนครั้งที่ยืม', data: studentData, backgroundColor: studentColors, borderColor: studentColors.map(c => c.replace("0.6","1")), borderWidth: 1 }] },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const student = context.label;
                        const items = studentItems[student];
                        if (!items) return "ไม่มีข้อมูล";
                        let text = "ยืมอุปกรณ์:";
                        items.forEach(it => {
                            text += `\n- ${it.equipment} (${it.total} ชิ้น / ${it.times} ครั้ง)`;
                        });
                        return text;
                    }
                }
            }
        },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<script>
document.getElementById('fineSearch').addEventListener('keyup', function() {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll('#fineTable tbody tr');
  rows.forEach(row => { row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none'; });
});
document.getElementById('fineReset').addEventListener('click', function() {
  document.getElementById('fineSearch').value = '';
  document.querySelectorAll('#fineTable tbody tr').forEach(row => row.style.display = '');
});
</script>

<script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();</script>
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>
