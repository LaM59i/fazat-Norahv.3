<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
include 'database.php';
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  // تحقق من الأدمن
  $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();

  $adminResult = $stmt->get_result();

  if ($adminResult && $adminResult->num_rows === 1) {

    $admin = $adminResult->fetch_assoc();

    if ($password === $admin['password']) {

      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_email'] = $admin['email'];

      header("Location: admin_dashboard.php");
      exit;

    } else {
      $error = "كلمة مرور الأدمن غير صحيحة";
    }

  } else {

    // تحقق من الطالب
    $stmt2 = $conn->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();

    $studentResult = $stmt2->get_result();

    if ($studentResult && $studentResult->num_rows === 1) {

      $student = $studentResult->fetch_assoc();

      if (password_verify($password, $student['password'])) {

        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['name'];

        header("Location: student_home.php");
        exit;

      } else {
        $error = "كلمة مرور الطالب غير صحيحة";
      }

    } else {
      $error = "البريد الإلكتروني غير مسجل";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8">
  <title>تسجيل الدخول</title>
<link rel="stylesheet" href="./main.css"></head>

<body>

  <header>
    <h1>تسجيل الدخول</h1>
  </header>

  <div class="card" style="max-width: 450px; margin: auto;">

    <form method="post">

      <label>البريد الإلكتروني</label><br>
      <input type="email" name="email" required><br><br>

      <label>كلمة المرور</label><br>
      <input type="password" name="password" required><br><br>

      <input type="submit" value="تسجيل الدخول" class="button-submit">

    </form>

    <hr style="margin: 20px 0;">

  </div>

  <?php
  if (isset($error))
    echo "<p style='color:red;'>$error</p>";
  ?>

  <footer>

    <p>
      <a class="button" href="index.php"
        style="display: block; margin: 15px 0;text-align: center;">
        رجوع
      </a>
    </p>

  </footer>

</body>

</html>
