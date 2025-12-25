<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Database.php';

// Pastikan user login
if (!isset($_SESSION['nim'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$nim = $_SESSION['nim'];
$day = isset($_GET['day']) ? intval($_GET['day']) : 0;
$month = isset($_GET['month']) ? str_pad($_GET['month'], 2, '0', STR_PAD_LEFT) : '';
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

if ($day == 0 || empty($month) || $year == 0) {
    echo json_encode(['error' => 'Invalid date']);
    exit;
}

$conn = getConnection();

// Query tasks for selected date
$query = "SELECT t.*, m.NamaMK 
          FROM tugas t 
          LEFT JOIN matakuliah m ON t.KodeMK = m.KodeMK 
          WHERE t.NIM = ? 
          AND DAY(t.Deadline) = ? 
          AND MONTH(t.Deadline) = ? 
          AND YEAR(t.Deadline) = ?
          ORDER BY t.Deadline ASC, t.StatusTugas ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "siii", $nim, $day, $month, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$tasks = [];
$now = new DateTime();

while ($row = mysqli_fetch_assoc($result)) {
    $deadline = new DateTime($row['Deadline']);
    
    // Determine urgency
    if ($row['StatusTugas'] == 'Selesai') {
        $urgency = 'completed';
    } else {
        $diff = $now->diff($deadline);
        if ($deadline < $now) {
            $urgency = 'overdue';
        } elseif ($diff->days <= 3) {
            $urgency = 'urgent';
        } else {
            $urgency = 'normal';
        }
    }
    
    $row['urgency'] = $urgency;
    $row['DeadlineFormatted'] = $deadline->format('d M Y, H:i');
    
    $tasks[] = $row;
}

$response = [
    'count' => count($tasks),
    'tasks' => $tasks,
    'date' => [
        'day' => $day,
        'month' => $month,
        'year' => $year
    ]
];

closeConnection($conn);

header('Content-Type: application/json');
echo json_encode($response);
?>


