<?php
session_start();
include 'database.php';

// التحقق من تسجيل دخول الأدمن
if (!isset($_SESSION['admin_id'])) {
    header("Location: student_login.php");
    exit;
}


/*
========================================
تحديث حالات الفرص تلقائياً
========================================
*/


// إذا انتهى تاريخ الفرصة تصبح منتهية
$conn->query("
UPDATE opportunities
SET status = 'منتهية'
WHERE end_date < CURDATE()
");


// قبل البداية بيوم تتقفل الطلبات
$conn->query("
UPDATE opportunities
SET status = 'مغلقة'
WHERE start_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
AND status = 'متاحة'
");


// إذا اكتمل العدد تصبح منتهية
$checkFull = $conn->query("
SELECT
o.id,
o.max_volunteers,
COUNT(v.id) AS total

FROM opportunities o

LEFT JOIN volunteers v
ON o.id = v.opportunity_id

GROUP BY o.id
");

while ($row = $checkFull->fetch_assoc()) {

    if ($row['total'] >= $row['max_volunteers']) {

        $update = $conn->prepare("
        UPDATE opportunities
        SET status = 'منتهية'
        WHERE id = ?
        ");

        $update->bind_param(
            "i",
            $row['id']
        );

        $update->execute();
    }
}



// تحديث حالة الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {

    $id = intval($_POST['id']);

    $status = $_POST['status'];

    // تحديث حالة الطلب
    $update = $conn->prepare("
    UPDATE student_participations
    SET status = ?
    WHERE id = ?
    ");

    $update->bind_param(
        "si",
        $status,
        $id
    );

    $update->execute();




    // إذا تمت الموافقة
    if ($status === 'approved') {

        // جلب بيانات الطلب
        $getData = $conn->prepare("
        SELECT student_id, opportunity_id
        FROM student_participations
        WHERE id = ?
        ");

        $getData->bind_param(
            "i",
            $id
        );

        $getData->execute();

        $data = $getData
        ->get_result()
        ->fetch_assoc();



        // التحقق إذا المتطوع موجود
        $check = $conn->prepare("
        SELECT *
        FROM volunteers
        WHERE student_id = ?
        AND opportunity_id = ?
        ");

        $check->bind_param(
            "ii",
            $data['student_id'],
            $data['opportunity_id']
        );

        $check->execute();

        $exists = $check->get_result();




        // إضافته للمتطوعين
        if ($exists->num_rows === 0) {

            $insert = $conn->prepare("
            INSERT INTO volunteers
            (
                student_id,
                opportunity_id
            )
            VALUES (?, ?)
            ");

            $insert->bind_param(
                "ii",
                $data['student_id'],
                $data['opportunity_id']
            );

            $insert->execute();
        }



        // إعادة فحص العدد
        $countStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM volunteers
        WHERE opportunity_id = ?
        ");

        $countStmt->bind_param(
            "i",
            $data['opportunity_id']
        );

        $countStmt->execute();

        $countResult = $countStmt
        ->get_result()
        ->fetch_assoc();




        // جلب الحد الأقصى
        $maxStmt = $conn->prepare("
        SELECT max_volunteers
        FROM opportunities
        WHERE id = ?
        ");

        $maxStmt->bind_param(
            "i",
            $data['opportunity_id']
        );

        $maxStmt->execute();

        $maxResult = $maxStmt
        ->get_result()
        ->fetch_assoc();




        // إذا اكتمل العدد تصبح منتهية
        if (
            $countResult['total']
            >=
            $maxResult['max_volunteers']
        ) {

            $finish = $conn->prepare("
            UPDATE opportunities
            SET status = 'منتهية'
            WHERE id = ?
            ");

            $finish->bind_param(
                "i",
                $data['opportunity_id']
            );

            $finish->execute();
        }
    }



    echo json_encode([
        'success' => true
    ]);

    exit;
}



// عرض الطلبات
$sql = "
SELECT

sp.id,
sp.status,
sp.created_at,

s.name AS student_name,

o.title AS opportunity_title

FROM student_participations sp

JOIN students s
ON sp.student_id = s.id

JOIN opportunities o
ON sp.opportunity_id = o.id

ORDER BY sp.created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<title>
إدارة الطلبات | منصة فزعة نورة
</title>

<link rel="stylesheet" href="./main.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>

<header>

<h1>
إدارة طلبات الانضمام
</h1>

<nav>

<a href="admin_dashboard.php">
الرئيسية
</a>

<a href="view_volunteers.php">
المتطوعون
</a>

<a href="view_opportunities.php">
الفرص
</a>

</nav>

</header>

<div class="container">

<div class="card">

<h2>
طلبات الطلاب
</h2>

<table>

<thead>

<tr>

<th>الطالب</th>

<th>الفرصة</th>

<th>الحالة</th>

<th>الإجراء</th>

</tr>

</thead>

<tbody>

<?php if ($result->num_rows > 0): ?>

<?php while ($row = $result->fetch_assoc()): ?>

<tr id="row-<?= $row['id'] ?>">

<td>
<?= htmlspecialchars($row['student_name']) ?>
</td>

<td>
<?= htmlspecialchars($row['opportunity_title']) ?>
</td>

<td class="status">

<?php

if ($row['status'] === 'approved') {

echo "<span style='color:green;'>تمت الموافقة</span>";

}

elseif ($row['status'] === 'pending') {

echo "<span style='color:orange;'>قيد الانتظار</span>";

}

else {

echo "<span style='color:red;'>مرفوض</span>";

}

?>

</td>

<td class="action-cell">

<?php if ($row['status'] === 'pending'): ?>

<button
class="button-submit approve"
data-id="<?= $row['id'] ?>"
data-status="approved">

قبول

</button>

<button
class="button-submit reject"
data-id="<?= $row['id'] ?>"
data-status="rejected"
style="background-color:#d9534f;">

رفض

</button>

<?php else: ?>

<span style="color:gray;">—</span>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="4">
لا توجد طلبات حالياً
</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

<script>

$(document).ready(function () {

$('.button-submit').click(function () {

const btn = $(this);

const id = btn.data('id');

const status = btn.data('status');

const row = btn.closest('tr');

$.post(

'manage_requests.php',

{
id: id,
status: status
},

function () {

let statusHtml = '';

if (status === 'approved') {

statusHtml = `
<span style="color:green;">
تمت الموافقة
</span>
`;

}

else {

statusHtml = `
<span style="color:red;">
مرفوض
</span>
`;

}

row.find('.status').html(statusHtml);

row.find('.action-cell').html(`
<span style="color:gray;">
—
</span>
`);

}

);

});

});

</script>

</body>

</html>