
<!-- <!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบยืม-คืนอุปกรณ์กีฬา</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
  <link rel="stylesheet" href="../assets/css/sidebar-layout.css">
</head>
    <style>
        body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    background: #f4f4f4;
    color: #333;
}

header {
    background: #333;
    color: #fff;
    padding: 1rem 0;
    text-align: center;
}

header h1 {
    margin: 0;
}

nav {
    padding-top: 10px;
}

nav a {
    color: #fff;
    text-decoration: none;
    padding: 0 15px;
}

nav span {
    padding: 0 15px;
}

main {
    padding: 20px;
    max-width: 1200px;
    margin: auto;
}

footer {
    text-align: center;
    padding: 20px;
    background: #333;
    color: #fff;
    position: relative;
    bottom: 0;
    width: 100%;
}

.equipment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.equipment-card {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-align: center;
}

.btn {
    display: inline-block;
    background: #5cb85c;
    color: #fff;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background: #4cae4c;
}

.out-of-stock {
    color: #d9534f;
}

/* Form Styles */
.form-container {
    max-width: 500px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 5px;
}

.form-container label {
    display: block;
    margin-bottom: 5px;
}

.form-container input[type="text"],
.form-container input[type="email"],
.form-container input[type="password"] {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border-radius: 4px;
    border: 1px solid #ddd;
}
/* === Table Styles === */
.table-container {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow-x: auto; /* ทำให้ตารางเลื่อนซ้าย-ขวาได้บนจอเล็ก */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

table th, table td {
    padding: 12px 15px;
    border: 1px solid #ddd;
    text-align: left;
}

table th {
    background-color: #f4f4f4;
    font-weight: bold;
}

table tbody tr:nth-of-type(even) {
    background-color: #f9f9f9;
}

table tbody tr:hover {
    background-color: #f1f1f1;
}

.text-center {
    text-align: center;
}
.text-muted {
    color: #888;
}


/* === Status Badge Styles === */
.status {
    padding: 5px 10px;
    border-radius: 15px;
    color: #fff;
    font-size: 0.9em;
    font-weight: bold;
    text-transform: uppercase;
}

.status-approved {
    background-color: #f0ad4e; /* สีส้ม: อนุมัติแล้ว (รอรับของ) */
}

.status-in-use {
    background-color: #5bc0de; /* สีฟ้า: กำลังใช้งาน */
}

.status-returned {
    background-color: #5cb85c; /* สีเขียว: คืนแล้ว */
}

.status-cancelled {
    background-color: #d9534f; /* สีแดง: ยกเลิก */
}

.status-pending {
    background-color: #777; /* สีเทา: รอดำเนินการ */
}

/* === Admin Page Styles === */
.admin-page h1 {
    text-align: center;
    margin-bottom: 30px;
}
.admin-section {
    background-color: #fff;
    padding: 20px;
    margin-bottom: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.admin-section h2 {
    margin-top: 0;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

/* Return Form Styles */
.return-form {
    display: flex;
    align-items: center;
    gap: 10px;
}
.return-form input[type="number"] {
    width: 120px;
    padding: 8px;
}
.btn-return {
    background-color: #5cb85c;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
}
.btn-return:hover {
    background-color: #4cae4c;
}

/* Action Buttons Styles */
.action-buttons {
    display: flex;
    gap: 10px;
}
.btn-edit, .btn-delete {
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}
.btn-edit { background-color: #f0ad4e; }
.btn-edit:hover { background-color: #ec971f; }

.btn-delete { background-color: #d9534f; font-family: inherit; font-size: inherit; }
.btn-delete:hover { background-color: #c9302c; }
    </style>
<body>
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>
    <header>
        <h1>ระบบยืม-คืนอุปกรณ์กีฬา</h1>
        <nav><?php  session_start(); ?>
            <a href="index.php">หน้าแรก</a>
            <?php if (isset($_SESSION['roll_id'])): ?>
                <a href="../admin/my_reservations.php">การยืมของฉัน</a>
                <span>สวัสดี, <?php echo htmlspecialchars($_SESSION['fname']); ?>!</span>
                <a href="logout.php">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php">เข้าสู่ระบบ</a>
                <a href="register.php">สมัครสมาชิก</a>
            <?php endif; ?>
        </nav>
    </header> -->

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>header-ADDMIN</title>
        <link rel="stylesheet" href="../assets/css/header.css">
    </head>
    <body>
            <header>
        <h1>ระบบยืม-คืนอุปกรณ์กีฬา</h1>
        <nav>
            <a href="../admin/index.php">หน้าแรก</a>
            <?php if (isset($_SESSION['roll_id'])): ?>
                <a href="../admin/reservations.php">รายการยืม</a>
                <span>สวัสดี, <?php echo htmlspecialchars($_SESSION['fname']); ?>!</span>
                <a href="../logout.php">ออกจากระบบ</a>
            <?php else: ?>
                <a href="../login.php">เข้าสู่ระบบ</a>
                <a href="../register.php">สมัครสมาชิก</a>
            <?php endif; ?>
        </nav>
    </header>
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>
  <script src="../assets/js/sidebar-toggle.js"></script>
</body>
    </html>