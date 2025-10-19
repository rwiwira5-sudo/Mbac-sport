<!--  -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบยืม-คืนอุปกรณ์กีฬา</title>
    <link rel="stylesheet" href="../assets/css/header.css"> 
  <link rel="stylesheet" href="../assets/css/sidebar-layout.css">
</head>
<body>
<button id="sidebarToggle" aria-label="Toggle sidebar" class="md:hidden fixed top-3 left-3 z-40 px-3 py-2 rounded-lg shadow focus:outline-none focus:ring bg-white/90">☰</button>
    <header>
        <h1>ระบบยืม-คืนอุปกรณ์กีฬา</h1>
        <nav>
            <a href="index.php">หน้าแรก</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my_reservations.php">การยืมของฉัน</a>
                <span>สวัสดี, <?php echo htmlspecialchars($_SESSION['fname']); ?>!</span>
                <a href="logout.php">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php">เข้าสู่ระบบ</a>
                <a href="register.php">สมัครสมาชิก</a>
            <?php endif; ?>
        </nav>
    </header>