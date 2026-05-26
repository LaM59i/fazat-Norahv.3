<?php
header('Content-Type: application/json');
include 'database.php';

// 🔹 تسجيل حساب جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {
    $data = json_decode(file_get_contents("php://input"), true);
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['email'], $hashedPassword, $data['role']]);
    echo json_encode(['message' => 'User registered successfully!']);
}

// 🔹 تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'], $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['message' => 'Login successful!', 'role' => $user['role']]);
    } else {
        echo json_encode(['message' => 'Invalid email or password']);
    }
}

// 🔹 إضافة متطوعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'addVolunteer') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sql = "INSERT INTO volunteers (name, email, phone, id_university, number_id, major) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['id_university'], $data['number_id'], $data['major']]);
    echo json_encode(['message' => 'Volunteer added successfully!']);
}

// 🔹 جلب جميع المتطوعين
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getVolunteers') {
    $stmt = $pdo->query("SELECT * FROM volunteers");
    $volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($volunteers);
}

// 🔹 تحديث معلومات متطوعة
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'updateVolunteer') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sql = "UPDATE volunteers SET name = ?, email = ?, phone = ?, id_university = ?, number_id = ?, major = ? WHERE volunteer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['id_university'], $data['number_id'], $data['major'], $data['volunteer_id']]);
    echo json_encode(['message' => 'Volunteer updated successfully!']);
}

// 🔹 إضافة نشاط
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'addActivity') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sql = "INSERT INTO activities (title, description, location, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['title'], $data['description'], $data['location'], $data['start_date'], $data['end_date']]);
    echo json_encode(['message' => 'Activity added successfully!']);
}

// 🔹 جلب جميع الأنشطة
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getActivities') {
    $stmt = $pdo->query("SELECT * FROM activities");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($activities);
}

// 🔹 تسجيل مشاركة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'addParticipation') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sql = "INSERT INTO participation (volunteer_id, activity_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['volunteer_id'], $data['activity_id']]);
    echo json_encode(['message' => 'Participation recorded successfully!']);
}

// 🔹 جلب المشاركات
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getParticipation') {
    $stmt = $pdo->query("SELECT p.participation_id, v.name AS volunteer_name, a.title AS activity_title, p.participation_date 
                          FROM participation p 
                          JOIN volunteers v ON p.volunteer_id = v.volunteer_id 
                          JOIN activities a ON p.activity_id = a.activity_id");
    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($participations);
}

// 🔹 البحث عن الأنشطة
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'searchActivities') {
    $searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    $sql = "SELECT * FROM activities WHERE 1=1";
    if (!empty($searchTerm)) {
        $sql .= " AND title LIKE '%" . $searchTerm . "%'";
    }
    if (!empty($status)) {
        $sql .= " AND status = '" . $status . "'";
    }

    $stmt = $pdo->query($sql);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($activities);
}

// 🔹 إحصائيات الداشبورد
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getDashboardStats') {
    // إحصائيات عامة
    $stmtTotalVolunteers = $pdo->query("SELECT COUNT(*) AS total_volunteers FROM volunteers");
    $totalVolunteers = $stmtTotalVolunteers->fetch(PDO::FETCH_ASSOC)['total_volunteers'];

    $stmtTotalActivities = $pdo->query("SELECT COUNT(*) AS total_activities FROM activities");
    $totalActivities = $stmtTotalActivities->fetch(PDO::FETCH_ASSOC)['total_activities'];

    $stmtTotalParticipations = $pdo->query("SELECT COUNT(*) AS total_participations FROM participation");
    $totalParticipations = $stmtTotalParticipations->fetch(PDO::FETCH_ASSOC)['total_participations'];

    // معلومات خاصة بالطالب (إذا كان الطالب مسجل الدخول)
    $studentStats = [];
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
        $userId = $_SESSION['user_id'];
        $stmtStudentParticipations = $pdo->prepare("SELECT COUNT(*) AS student_participations FROM participation WHERE volunteer_id = ?");
        $stmtStudentParticipations->execute([$userId]);
        $studentParticipations = $stmtStudentParticipations->fetch(PDO::FETCH_ASSOC)['student_participations'];
        $studentStats['student_participations'] = $studentParticipations;
    }

    echo json_encode([
        'total_volunteers' => $totalVolunteers,
        'total_activities' => $totalActivities,
        'total_participations' => $totalParticipations,
        'student_stats' => $studentStats
    ]);
}
?>