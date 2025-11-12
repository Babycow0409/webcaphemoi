<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin']) && !isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$month = isset($_GET['month']) ? trim($_GET['month']) : '';

if ($employee_id <= 0 || empty($month)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Lấy thông tin ca làm việc đã hoàn thành trong tháng
$sql = "SELECT sa.hours_worked, ws.hourly_rate
        FROM shift_assignments sa
        JOIN work_shifts ws ON sa.shift_id = ws.id
        WHERE sa.employee_id = ? 
        AND sa.status = 'completed'
        AND DATE_FORMAT(sa.work_date, '%Y-%m') = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $employee_id, $month);
$stmt->execute();
$result = $stmt->get_result();

$total_shifts = 0;
$total_hours = 0;
$shift_earnings = 0;

while ($row = $result->fetch_assoc()) {
    if ($row['hours_worked'] > 0) {
        $total_shifts++;
        $total_hours += $row['hours_worked'];
        $shift_earnings += $row['hours_worked'] * $row['hourly_rate'];
    }
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'total_shifts' => $total_shifts,
    'total_hours' => round($total_hours, 1),
    'shift_earnings' => $shift_earnings
]);
?>




