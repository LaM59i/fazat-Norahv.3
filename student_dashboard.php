<?php
session_start();
include 'database.php';

// نتأكد أن الطالب مسجل دخول
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// بيانات الطالب
$stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// المشاركات
$sql = "SELECT 
            o.title AS opportunity_title, 
            o.hours AS total_hours,
            sp.status AS participation_status,
            sp.created_at AS applied_at
        FROM student_participations sp
        JOIN opportunities o ON sp.opportunity_id = o.id
        WHERE sp.student_id = ?
        ORDER BY sp.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$total_hours = 0;
$approved_count = 0;
$pending_exists = false;

$participations = [];
while ($row = $result->fetch_assoc()) {
  $participations[] = $row;
  if ($row['participation_status'] === 'approved') {
    $approved_count++;
    $total_hours += (int) $row['total_hours'];
  } elseif ($row['participation_status'] === 'pending') {
    $pending_exists = true;
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة التحكم | فزعة نورة</title>
<link rel="stylesheet" href="./main.css">    </head>

<body>
  <header>
    <h1>لوحة التحكم</h1>
    <nav>
      <a href="student_home.php">الرئيسية</a>
      <a href="student_profile.php">الملف الشخصي</a>
    </nav>
  </header>

  <div class="container">
    
    <div class="card">
      <h2>مرحبًا، <?= htmlspecialchars($student['name']) ?> 👋</h2>
      <p>تابع طلبات التطوع والساعات المعتمدة أدناه.</p>
    </div>

    <?php if ($pending_exists): ?>
      <div class="card" style="background-color: #fff3cd; color: #856404; text-align: center;">
        لديك طلبات بانتظار موافقة الإدارة ⏳
      </div>
    <?php endif; ?>

    <div class="card-grid">
      <div class="card">
        <h3>الفرص المعتمدة</h3>
        <p class="stat-number"><strong><?= $approved_count ?></strong></p>
      </div>
      <div class="card">
        <h3>إجمالي الساعات</h3>
        <p class="stat-number"><strong><?= $total_hours ?></strong></p>
      </div>
      <div class="card">
        <h3>آخر تحديث</h3>
        <p class="stat-number"><strong><?= date('Y-m-d H:i') ?></strong></p>
      </div>
    </div>

    <div class="card">
      <h3>مشاركاتك</h3>
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>الفرصة</th>
            <th>الساعات</th>
            <th>الحالة</th>
            <th>تاريخ التقديم</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($participations)): ?>
            <tr><td colspan="4">لا توجد مشاركات حالياً.</td></tr>
          <?php else: ?>
            <?php foreach ($participations as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['opportunity_title']) ?></td>
                <td><?= ($p['participation_status'] === 'approved') ? htmlspecialchars($p['total_hours']) : '-' ?></td>
                <td>
                  <?php
                  if ($p['participation_status'] === 'approved') echo "<span class='status-approved'>✅ معتمدة</span>";
                  elseif ($p['participation_status'] === 'pending') echo "<span class='status-pending'>🕓 قيد الانتظار</span>";
                  else echo "<span class='status-rejected'>❌ مرفوضة</span>";
                  ?>
                </td>
                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($p['applied_at']))) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <footer>
    <div class="footer-container">
      <h3>تواصل معنا</h3>
      <p>ص.ب 84428، الرياض، المملكة العربية السعودية.</p>
      <p>البريد الإلكتروني: <a href="mailto:info@pnu.edu.sa">info@pnu.edu.sa</a></p>
      <p>الهاتف: +966-11-8220000</p>
      <p>&copy; 2026 جميع الحقوق محفوظة.</p>
    </div>
  </footer>
</body>
</html>