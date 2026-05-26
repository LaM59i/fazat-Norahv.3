<?php
session_start();
include 'database.php';

// التصحيح: توجيه غير المسجلين لصفحة تسجيل الدخول وليس لوحة التحكم
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $opportunity_id = intval($_POST['opportunity_id']);

    // التحقق من عدم التكرار
    $check = $conn->prepare("SELECT * FROM student_participations WHERE student_id = ? AND opportunity_id = ?");
    $check->bind_param("ii", $student_id, $opportunity_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('لقد قمت بالانضمام لهذه الفرصة مسبقاً'); window.location.href='student_home.php';</script>";
        exit;
    }

    // إضافة طلب الانضمام
    $stmt = $conn->prepare("INSERT INTO student_participations (student_id, opportunity_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->bind_param("ii", $student_id, $opportunity_id);

    if ($stmt->execute()) {
        // التوجيه الناجح إلى لوحة التحكم
        echo "<script>alert('تم إرسال طلب الانضمام بنجاح'); window.location.href='student_dashboard.php';</script>";
    } else {
        echo "<script>alert('حدث خطأ أثناء الإرسال'); window.location.href='student_home.php';</script>";
    }
}
?>