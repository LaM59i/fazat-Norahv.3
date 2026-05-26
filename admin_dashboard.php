<?php
include 'database.php';

// حذف المتطوع ومشاركاته
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_volunteer'])) {

  $student_id = $_POST['student_id'];
  $opportunity_id = $_POST['opportunity_id'];

  // حذف من المشاركات
  $deleteParticipation = $conn->prepare("
    DELETE FROM student_participations
    WHERE student_id = ? AND opportunity_id = ?
  ");

  $deleteParticipation->bind_param(
    "ii",
    $student_id,
    $opportunity_id
  );

  $deleteParticipation->execute();

  // حذف من المتطوعين
  $deleteVolunteer = $conn->prepare("
    DELETE FROM volunteers
    WHERE student_id = ? AND opportunity_id = ?
  ");

  $deleteVolunteer->bind_param(
    "ii",
    $student_id,
    $opportunity_id
  );

  $deleteVolunteer->execute();

  if (
    $deleteParticipation->affected_rows > 0 ||
    $deleteVolunteer->affected_rows > 0
  ) {

    echo "
    <script>
      alert('✅ تم حذف بيانات التطوع والمشاركة بنجاح');
      window.location.href='admin_dashboard.php';
    </script>
    ";

  } else {

    echo "
    <script>
      alert('⚠️ لم يتم العثور على بيانات مطابقة');
      window.location.href='admin_dashboard.php';
    </script>
    ";
  }

  exit;
}

// الإحصائيات
$totalVolunteers =
$conn->query("SELECT COUNT(*) AS total FROM students")
->fetch_assoc()['total'] ?? 0;

$totalOpportunities =
$conn->query("SELECT COUNT(*) AS total FROM opportunities")
->fetch_assoc()['total'] ?? 0;

$activeOpportunities =
$conn->query("
  SELECT COUNT(*) AS total
  FROM opportunities
  WHERE status = 'active'
")->fetch_assoc()['total'] ?? 0;

// الفرص والمتطوعين
$opportunitiesQuery = "
SELECT
    o.id,
    o.title AS name,
    o.max_volunteers,
    COUNT(v.id) AS current_volunteers,
    o.status

FROM opportunities o

LEFT JOIN volunteers v
ON o.id = v.opportunity_id

GROUP BY o.id
";

$opportunitiesResult = $conn->query($opportunitiesQuery);
?>

<!DOCTYPE html>
<html lang="ar">

<head>

  <meta charset="UTF-8">

  <title>
    لوحة تحكم الأدمن |  منصة فزعة نورة
  </title>

<link rel="stylesheet" href="./main.css">
  <script>

    function toggleVolunteers(id) {

      const el =
      document.getElementById('volunteers-' + id);

      el.style.display =
      el.style.display === 'table-row'
      ? 'none'
      : 'table-row';
    }

    function confirmDelete(studentId, opportunityId) {

      if (
        confirm("هل أنت متأكد من حذف المتطوع ومشاركته؟")
      ) {

        const form =
        document.createElement('form');

        form.method = 'POST';
        form.action = '';

        form.innerHTML = `
          <input type="hidden"
                 name="student_id"
                 value="${studentId}">

          <input type="hidden"
                 name="opportunity_id"
                 value="${opportunityId}">

          <input type="hidden"
                 name="delete_volunteer"
                 value="1">
        `;

        document.body.appendChild(form);
        form.submit();
      }
    }

  </script>

</head>

<body>

  <header>

    <h1>لوحة تحكم الأدمن</h1>

    <nav>

    
      <a href="view_volunteers.php">
        المتطوعون
      </a>

      <a href="view_opportunities.php">
        الفرص
      </a>

      <a href="manage_requests.php">
        الطلبات
      </a>

    </nav>

  </header>

  <div class="container">

    <div class="card">

      <h2>إحصائيات المنصة</h2>

      <div class="card-grid">

        <div class="card">

          <h3>إجمالي المتطوعين</h3>

          <p>
            <?= $totalVolunteers ?>
          </p>

        </div>

        <div class="card">

          <h3>إجمالي الفرص</h3>

          <p>
            <?= $totalOpportunities ?>
          </p>

        </div>

        <div class="card">

          <h3>الفرص النشطة</h3>

          <p>
            <?= $activeOpportunities ?>
          </p>

        </div>

      </div>

    </div>

    <div class="card">

      <h2>الفرص والمتطوعون</h2>

      <table>

        <thead>

          <tr>

            <th>الفرصة</th>
            <th>عدد المتطوعين</th>
            <th>الحد الأقصى</th>
            <th>الحالة</th>
            <th>الإجراء</th>

          </tr>

        </thead>

        <tbody>

          <?php while ($row = $opportunitiesResult->fetch_assoc()): ?>

            <tr>

              <td>
                <?= htmlspecialchars($row['name']) ?>
              </td>

              <td>
                <?= $row['current_volunteers'] ?>
              </td>

              <td>
                <?= $row['max_volunteers'] ?>
              </td>

              <td>

                <?=
                $row['current_volunteers'] >= $row['max_volunteers']

                ? "<span style='color:red; font-weight:bold;'>مكتملة</span>"

                : "متاحة"
                ?>

              </td>

              <td>

                <button
                  class="button-submit"
                  onclick="toggleVolunteers(<?= $row['id'] ?>)">

                  عرض المتطوعين

                </button>

              </td>

            </tr>

<?php
$volQuery = "
SELECT
    s.id AS student_id,
    s.name,
    s.email,
    s.phone

FROM students s

JOIN volunteers v
ON s.id = v.student_id

WHERE v.opportunity_id = " . $row['id'];

$volResult = $conn->query($volQuery);
?>

<tr id="volunteers-<?= $row['id'] ?>"
    style="display:none;">

<td colspan="5">

<?php if ($volResult && $volResult->num_rows > 0): ?>

<table>

<tr>

<th>الاسم</th>
<th>البريد الإلكتروني</th>
<th>رقم الجوال</th>
<th>الإجراء</th>

</tr>

<?php while ($vol = $volResult->fetch_assoc()): ?>

<tr>

<td>
<?= htmlspecialchars($vol['name']) ?>
</td>

<td>
<?= htmlspecialchars($vol['email']) ?>
</td>

<td>
<?= htmlspecialchars($vol['phone']) ?>
</td>

<td>

<button
class="button-submit"
style="background-color:#d9534f;"
onclick="confirmDelete(
<?= $vol['student_id'] ?>,
<?= $row['id'] ?>
)">

حذف

</button>

</td>

</tr>

<?php endwhile; ?>

</table>

<?php else: ?>

<p>
لا يوجد متطوعون حتى الآن
</p>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>
