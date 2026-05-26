<?php
include 'database.php';

// جلب جميع الفرص
$query = "SELECT * FROM opportunities ORDER BY start_date DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<title>
جميع الفرص | منصة فزعة نورة
</title>

<link rel="stylesheet" href="./main.css"></head>

<body>

<header>

<h1>
جميع الفرص التطوعية
</h1>

<nav>

<a href="admin_dashboard.php">
الرئيسية
</a>

<a href="view_volunteers.php">
المتطوعون
</a>

<a href="manage_requests.php">
الطلبات
</a>

</nav>

<br>

<a href="opportunity_manage.php" class="button">
+ إضافة فرصة جديدة
</a>

</header>

<table>

<thead>

<tr>

<th>
الرقم
</th>

<th>
العنوان
</th>

<th>
تاريخ البداية
</th>

<th>
تاريخ النهاية
</th>

<th>
الوصف
</th>

<th>
الحد الأقصى للمتطوعين
</th>

<th>
الساعات
</th>

<th>
الحالة
</th>

<th>
الإجراءات
</th>

</tr>

</thead>

<tbody>

<?php if ($result && $result->num_rows > 0): ?>

<?php while ($row = $result->fetch_assoc()): ?>

<?php

$status = $row['status'];

// إذا كانت الحالة تلقائي
if ($status === 'تلقائي') {

    $today = date('Y-m-d');

    // عدد المقبولين
    $countQuery = "
    SELECT COUNT(*) AS total
    FROM student_participations
    WHERE opportunity_id = {$row['id']}
    AND status = 'approved'
    ";

    $countResult = $conn->query($countQuery);

    $countRow = $countResult->fetch_assoc();

    $approvedCount = $countRow['total'];

    // إذا انتهى التاريخ أو اكتمل العدد
    if (
        $today > $row['end_date'] ||
        $approvedCount >= $row['max_volunteers']
    ) {

        $status = 'منتهية';

    }

    // إذا بدأت الفرصة
    elseif ($today >= $row['start_date']) {

        $status = 'متاحة';

    }

    // قبل البداية
    else {

        $status = 'قادمة';
    }
}
?>

<tr>

<td>
<?= htmlspecialchars($row['id']); ?>
</td>

<td>
<?= htmlspecialchars($row['title']); ?>
</td>

<td>
<?= htmlspecialchars($row['start_date']); ?>
</td>

<td>
<?= htmlspecialchars($row['end_date']); ?>
</td>

<td>
<?= htmlspecialchars($row['description']); ?>
</td>

<td>
<?= htmlspecialchars($row['max_volunteers']); ?>
</td>

<td>
<?= htmlspecialchars($row['hours']); ?>
</td>

<td>

<?php

if ($status == 'متاحة') {

    echo "<span style='color:green;font-weight:bold;'>متاحة</span>";

}

elseif ($status == 'قادمة') {

    echo "<span style='color:orange;font-weight:bold;'>قادمة</span>";

}

elseif ($status == 'منتهية') {

    echo "<span style='color:red;font-weight:bold;'>منتهية</span>";

}

elseif ($status == 'تلقائي') {

    echo "<span style='color:blue;font-weight:bold;'>تلقائي</span>";

}

else {

    echo "<span style='color:gray;'>غير محددة</span>";

}

?>

</td>

<td>

<a
href="opportunity_manage.php?id=<?= $row['id']; ?>"
class="button">

تعديل

</a>

<a
href="delete_opportunity.php?id=<?= $row['id']; ?>"
class="button"
style="background:#dc3545">

حذف

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="9">
لا توجد فرص حالياً
</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</body>

</html>