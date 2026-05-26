<?php
session_start();
include 'database.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

// البحث
$search = '';

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// إذا المستخدم كتب بحث
if (!empty($search)) {

    $sql = "
    SELECT *
    FROM opportunities
    WHERE title LIKE ?
    OR description LIKE ?
    ORDER BY start_date ASC
    ";

    $stmt = $conn->prepare($sql);

    $searchTerm = "%{$search}%";

    $stmt->bind_param("ss", $searchTerm, $searchTerm);

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    // عرض كل الفرص
    $sql = "
    SELECT *
    FROM opportunities
    ORDER BY start_date ASC
    ";

    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<title>
الرئيسية | منصة فزعة نورة
</title>

<link rel="stylesheet" href="./main.css">
</head>

<body>

<!-- Header -->

<header>

<h1>
منصة فزعة نورة
</h1>

<nav>


<a href="student_dashboard.php">
لوحة التحكم
</a>

<a href="student_profile.php">
الملف الشخصي
</a>

</a>

</nav>

</header>

<!-- Search -->

<div class="container">

<div class="card">

<h2>
ابحث عن فرصة تطوعية
</h2>

<form method="GET">

<input
type="text"
name="search"
placeholder="ابحث باسم الفرصة..."
value="<?= htmlspecialchars($search); ?>">

<br><br>

<input
type="submit"
value="بحث"
class="button-submit">

</form>

</div>

</div>

<!-- Opportunities -->

<div class="container">

<div class="card-grid">

<?php
if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $status = $row['status'];

        // الحالة التلقائية
        if ($status === 'تلقائي') {

            $today = date('Y-m-d');

            $countQuery = "
            SELECT COUNT(*) AS total
            FROM student_participations
            WHERE opportunity_id = {$row['id']}
            AND status = 'approved'
            ";

            $countResult = $conn->query($countQuery);

            $countRow = $countResult->fetch_assoc();

            $approvedCount = $countRow['total'];

            // منتهية
            if (
                $today > $row['end_date'] ||
                $approvedCount >= $row['max_volunteers']
            ) {

                $status = 'منتهية';

            }

            // متاحة
            elseif ($today >= $row['start_date']) {

                $status = 'متاحة';

            }

            // قادمة
            else {

                $status = 'قادمة';
            }
        }

        echo '<div class="card">';

        echo '<h3>' .
        htmlspecialchars($row['title']) .
        '</h3>';

        echo '<p><strong>الوصف:</strong> ' .
        htmlspecialchars($row['description']) .
        '</p>';

        echo '<p><strong>تاريخ البداية:</strong> ' .
        htmlspecialchars($row['start_date']) .
        '</p>';

        echo '<p><strong>تاريخ النهاية:</strong> ' .
        htmlspecialchars($row['end_date']) .
        '</p>';

        echo '<p><strong>عدد الساعات:</strong> ' .
        htmlspecialchars($row['hours']) .
        '</p>';

        echo '<p><strong>الحالة:</strong> ' .
        htmlspecialchars($status) .
        '</p>';

        // زر الانضمام
        if ($status == 'متاحة') {

            echo '

            <form action="join_opportunity.php" method="POST">

            <input
            type="hidden"
            name="opportunity_id"
            value="' . $row['id'] . '">

            <input
            type="submit"
            value="انضم الآن"
            class="button-submit">

            </form>
            ';
        }

        elseif ($status == 'قادمة') {

            echo '
            <p style="color:orange;font-weight:bold;">
            الفرصة لم تبدأ بعد
            </p>
            ';
        }

        else {

            echo '
            <p style="color:red;font-weight:bold;">
            تم إغلاق الفرصة
            </p>
            ';
        }

        echo '</div>';
    }

} else {

    echo '
    <div class="card">
    لا توجد فرص مطابقة
    </div>
    ';
}
?>

</div>

</div>

<!-- Footer -->

<footer>

<div class="footer-container">

<h3>
تواصل معنا
</h3>

<p>
ص.ب 84428، الرياض، المملكة العربية السعودية.
</p>

<p>
البريد الإلكتروني:
<a href="mailto:info@pnu.edu.sa">
info@pnu.edu.sa
</a>
</p>

<p>
الهاتف:
+966-11-8220000
</p>

<p>
© 2025 جميع الحقوق محفوظة.
</p>

</div>

</footer>

</body>

</html>