<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'] ?? '';
    $student_id = $_POST['studentID'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'] ?? '';
    $college = $_POST['college'] ?? '';

    // التحقق من صحة الإيميل
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<p style='color:red;'>⚠️ البريد الإلكتروني غير صالح</p>");
    }

    // التحقق من الرقم الجامعي
    if (!preg_match("/^[0-9]{9}$/", $student_id)) {
        die("<p style='color:red;'>⚠️ الرقم الجامعي يجب أن يكون 9 أرقام بالضبط</p>");
    }

    // التحقق من رقم الجوال
    if (!preg_match("/^0[0-9]{9}$/", $phone)) {
        die("<p style='color:red;'>⚠️ رقم الجوال يجب أن يكون 10 أرقام ويبدأ بـ 0</p>");
    }

    // التحقق من الإيميل إذا كان موجود
    $checkEmailQuery = "SELECT COUNT(*) FROM students WHERE email = ?";
    $checkStmt = $conn->prepare($checkEmailQuery);

    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();

    $checkStmt->bind_result($emailCount);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($emailCount > 0) {
        die("<p style='color:red;'>⚠️ هذا البريد الإلكتروني مسجل مسبقًا</p>");
    }

    // التحقق من الرقم الجامعي
    $checkIDQuery = "SELECT COUNT(*) FROM students WHERE student_id = ?";
    $checkIDStmt = $conn->prepare($checkIDQuery);

    $checkIDStmt->bind_param("s", $student_id);
    $checkIDStmt->execute();

    $checkIDStmt->bind_result($idCount);
    $checkIDStmt->fetch();
    $checkIDStmt->close();

    if ($idCount > 0) {
        die("<p style='color:red;'>⚠️ الرقم الجامعي مسجل مسبقًا</p>");
    }

    // إضافة الطالب
    $sql = "INSERT INTO students 
            (name, student_id, email, password, college, phone, hours)
            VALUES (?, ?, ?, ?, ?, ?, 0)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssssss",
        $name,
        $student_id,
        $email,
        $password,
        $college,
        $phone
    );

    if ($stmt->execute()) {

        echo "<p style='color:green;'>✅ تم إنشاء الحساب بنجاح يمكنك الآن تسجيل الدخول</p>";

    } else {

        echo "<p style='color:red;'>⚠️ حدث خطأ أثناء إنشاء الحساب</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8">
  <title>إنشاء حساب</title>
<link rel="stylesheet" href="./main.css"></head>

<body>

  <header>
    <h1>إنشاء حساب</h1>
  </header>

  <div class="container">

    <div class="card" style="max-width: 450px; margin: auto;">

      <h2>إنشاء حساب جديد</h2>

      <form id="signupForm" action="" method="post">

        <label>الاسم</label><br>
        <input type="text" name="name" required><br><br>

        <label>الرقم الجامعي</label><br>
        <input type="text" name="studentID" required><br><br>

        <label>البريد الإلكتروني</label><br>
        <input type="email" name="email" required><br><br>

        <label>كلمة المرور</label><br>
        <input type="password" name="password" required><br><br>

        <label>رقم الجوال</label><br>
        <input type="text" name="phone" required><br><br>

        <label>الكلية</label><br>
        <input type="text" name="college"><br><br>

        <input type="submit" value="إنشاء حساب" class="button-submit">

      </form>

      <hr style="margin: 20px 0;">

      <a href="student_login.php" class="link-button">
        لديك حساب بالفعل؟ سجل دخول
      </a>

    </div>

  </div>

  <footer>
    <p>
      <a class="button"
         href="index.php"
         style="display: block; margin: 15px 0; text-align: center;">
         رجوع
      </a>
    </p>
  </footer>

</body>

</html>
