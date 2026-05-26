<?php
include 'database.php';

// جلب جميع المتطوعين
$result = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html lang="ar">

<head>

  <meta charset="UTF-8">

  <title>
    قائمة المتطوعين | منصة فزعة نورة
  </title>

<link rel="stylesheet" href="./main.css"></head>

<body>

  <header>

    <h1>قائمة المتطوعين</h1>

    <nav>

      <a href="admin_dashboard.php">
        الرئيسية
      </a>

      <a href="view_opportunities.php">
        الفرص
      </a>

      <a href="manage_requests.php">
        الطلبات
      </a>

    </nav>

    <a
      href="volunteer_manage.php"
      class="button">

      + إضافة متطوع جديد

    </a>

  </header>

  <table>

    <thead>

      <tr>

        <th>الاسم</th>
        <th>الرقم الجامعي</th>
        <th>البريد الإلكتروني</th>
        <th>الكلية</th>
        <th>رقم الجوال</th>
        <th>الإجراءات</th>

      </tr>

    </thead>

    <tbody>

<?php while ($row = $result->fetch_assoc()): ?>

<tr id="row-<?= $row['id'] ?>">

<td>
<?= htmlspecialchars($row['name']) ?>
</td>

<td>
<?= htmlspecialchars($row['student_id']) ?>
</td>

<td>
<?= htmlspecialchars($row['email']) ?>
</td>

<td>
<?= htmlspecialchars($row['college']) ?>
</td>

<td>
<?= htmlspecialchars($row['phone']) ?>
</td>

<td>

<a
href="volunteer_manage.php?id=<?= $row['id'] ?>"
class="button">

تعديل

</a>

<button
class="button delete-btn"
onclick="deleteVolunteer(<?= $row['id'] ?>)">

حذف

</button>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<script>

function deleteVolunteer(id) {

  if (
    confirm("هل أنت متأكد من حذف هذا المتطوع؟")
  ) {

    fetch(
      `delete_volunteer.php?id=${id}`,
      { method: 'GET' }
    )

    .then(res => res.text())

    .then(data => {

      if (data.trim() === "success") {

        document
        .getElementById(`row-${id}`)
        .remove();

        alert("تم حذف المتطوع بنجاح");

      } else {

        alert("حدث خطأ أثناء الحذف");
      }
    });
  }
}

</script>

</body>

</html>
