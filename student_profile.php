<?php
session_start();
include 'database.php';

// التحقق من أن الطالب مسجل دخول
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// جلب بيانات الطالب الحالية
$sql = "SELECT name, email, phone FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

// تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_name = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_phone = trim($_POST['phone']);

    $update_sql = "UPDATE students 
                   SET name = ?, email = ?, phone = ?
                   WHERE id = ?";

    $update_stmt = $conn->prepare($update_sql);

    $update_stmt->bind_param(
        "sssi",
        $new_name,
        $new_email,
        $new_phone,
        $student_id
    );

    if ($update_stmt->execute()) {

        echo "<script>
                alert('تم تحديث الملف الشخصي بنجاح');
                window.location.href='student_profile.php';
              </script>";

    } else {

        echo "<script>
                alert('حدث خطأ أثناء تحديث البيانات');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8">
  <title>الملف الشخصي</title>
<link rel="stylesheet" href="./main.css"></head>

<body>

  <header>

    <h1>الملف الشخصي</h1>

    <nav>
      <a href="student_home.php">الرئيسية</a>
      <a href="student_dashboard.php">لوحة التحكم</a>
    </nav>

  </header>

  <div class="container">

    <div class="card">

      <section>

        <h2>الملف الشخصي</h2>

        <p>
          <strong>الاسم الحالي:</strong>
          <?php echo htmlspecialchars($student['name']); ?>
        </p>

        <p>
          <strong>البريد الإلكتروني الحالي:</strong>
          <?php echo htmlspecialchars($student['email']); ?>
        </p>

        <p>
          <strong>رقم الجوال الحالي:</strong>
          <?php echo htmlspecialchars($student['phone']); ?>
        </p>

        <hr>

        <form method="post">

          <label>تعديل الاسم</label><br>
          <input type="text"
                 name="username"
                 value="<?php echo htmlspecialchars($student['name']); ?>">
          <br><br>

          <label>تعديل البريد الإلكتروني</label><br>
          <input type="email"
                 name="email"
                 value="<?php echo htmlspecialchars($student['email']); ?>">
          <br><br>

          <label>تعديل رقم الجوال</label><br>
          <input type="text"
                 name="phone"
                 value="<?php echo htmlspecialchars($student['phone']); ?>">
          <br><br>

          <input type="submit"
                 value="حفظ"
                 class="button-submit">

        </form>

      </section>

    </div>

  </div>

  <footer>

    <div class="footer-container">

      <h3>تواصل معنا</h3>

      <p>
        ص.ب 84428، الرياض، المملكة العربية السعودية
      </p>

      <p>
        البريد الإلكتروني:
        <a href="mailto:info@pnu.edu.sa">
          info@pnu.edu.sa
        </a>
      </p>

      <p>
        الهاتف: +966-11-8220000
      </p>

      <p>
        جميع الحقوق محفوظة © 2025
      </p>

    </div>

  </footer>

</body>

</html>
