<?php
include 'database.php';

// إذا فيه id نجيب بيانات الفرصة للتعديل
$opportunity = null;

if (isset($_GET['id'])) {

  $id = intval($_GET['id']);

  $result = $conn->query("
    SELECT *
    FROM opportunities
    WHERE id = $id
  ");

  if ($result && $result->num_rows > 0) {
    $opportunity = $result->fetch_assoc();
  }
}

// إضافة أو تعديل
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $title = trim($_POST['title']);

  $start_date = $_POST['start_date'];

  $end_date = $_POST['end_date'];

  $description = trim($_POST['description']);

  $max_volunteers = intval($_POST['volunteers']);

  $hours = intval($_POST['hours']);

  $status = $_POST['status'];

  // تعديل فرصة
  if (
    isset($_POST['id']) &&
    $_POST['id'] !== ''
  ) {

    $id = intval($_POST['id']);

    $sql = "
    UPDATE opportunities

    SET
      title = ?,
      start_date = ?,
      end_date = ?,
      description = ?,
      max_volunteers = ?,
      hours = ?,
      status = ?

    WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
      "ssssissi",
      $title,
      $start_date,
      $end_date,
      $description,
      $max_volunteers,
      $hours,
      $status,
      $id
    );

    $stmt->execute();

  } else {

    // إضافة فرصة جديدة
    $sql = "
    INSERT INTO opportunities
    (
      title,
      start_date,
      end_date,
      description,
      max_volunteers,
      hours,
      status
    )

    VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
      "ssssiis",
      $title,
      $start_date,
      $end_date,
      $description,
      $max_volunteers,
      $hours,
      $status
    );

    $stmt->execute();
  }

  header("Location: view_opportunities.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<title>
<?= $opportunity ? 'تعديل فرصة تطوعية' : 'إضافة فرصة تطوعية'; ?>
| منصة فزعة نورة
</title>

<link rel="stylesheet" href="./main.css"></head>

<body>

<header>

<h1>
<?= $opportunity ? 'تعديل فرصة تطوعية' : 'إضافة فرصة تطوعية'; ?>
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

<a href="view_opportunities.php" class="button">
العودة للفرص
</a>

</header>

<div class="container">

<div class="card">

<form method="POST">

<?php if ($opportunity): ?>

<input
type="hidden"
name="id"
value="<?= htmlspecialchars($opportunity['id']); ?>">

<?php endif; ?>

<label>
عنوان الفرصة:
</label>

<input
type="text"
name="title"
value="<?= htmlspecialchars($opportunity['title'] ?? ''); ?>"
required>

<br><br>

<label>
تاريخ البداية:
</label>

<input
type="date"
name="start_date"
value="<?= htmlspecialchars($opportunity['start_date'] ?? ''); ?>"
required>

<br><br>

<label>
تاريخ النهاية:
</label>

<input
type="date"
name="end_date"
value="<?= htmlspecialchars($opportunity['end_date'] ?? ''); ?>"
required>

<br><br>

<label>
وصف الفرصة:
</label>

<textarea
name="description"
rows="4"
required><?= htmlspecialchars($opportunity['description'] ?? ''); ?></textarea>

<br><br>

<label>
عدد المتطوعين المطلوب:
</label>

<input
type="number"
name="volunteers"
value="<?= htmlspecialchars($opportunity['max_volunteers'] ?? ''); ?>"
required>

<br><br>

<label>
عدد الساعات:
</label>

<input
type="number"
name="hours"
value="<?= htmlspecialchars($opportunity['hours'] ?? ''); ?>"
required>

<br><br>

<label>
حالة الفرصة:
</label>

<select name="status" required>

<option
value="متاحة"
<?= (!empty($opportunity['status']) && $opportunity['status'] == 'متاحة') ? 'selected' : ''; ?>>

متاحة

</option>

<option
value="قادمة"
<?= (!empty($opportunity['status']) && $opportunity['status'] == 'قادمة') ? 'selected' : ''; ?>>

قادمة

</option>

<option
value="منتهية"
<?= (!empty($opportunity['status']) && $opportunity['status'] == 'منتهية') ? 'selected' : ''; ?>>

منتهية

</option>

</select>

<br><br>

<input
type="submit"
value="<?= $opportunity ? 'حفظ التعديلات' : 'إضافة الفرصة'; ?>"
class="button">

</form>

</div>

</div>

</body>

</html>
