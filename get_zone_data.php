<?php
session_start();

// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//     header("Location: index.php");
//     exit;
// }


$servername = "151.106.124.154";
$username = "u583789277_wag7";
$password = "2567Concept";
$dbname = "u583789277_wag7";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_GET['zone_id'])) {
    echo json_encode([
        'zone' => 0,
        'slots' => []
    ]);
    exit;
}


$zoneId = intval($_GET['zone_id']);


$zoneMap = [
    1 => 'A',
    2 => 'B',
    3 => 'C',
    4 => 'D',
    5 => 'E',
    6 => 'F',
    7 => 'G',
    8 => 'H'
];


if (!array_key_exists($zoneId, $zoneMap)) {
    echo json_encode([
        'zone' => 'Invalid Zone',
        'slots' => []
    ]);
    exit;
}


$sql = "SELECT number, status_id FROM lot WHERE bay_id = $zoneId";
$result = $conn->query($sql);

if (!$result) {
    die("Error retrieving data: " . $conn->error);
}


function getStatusClassAndText($status_id) {
    switch ($status_id) {
        case 1: return ['class' => 'bg-blue-500', 'text' => '🚗ว่าง']; 
        case 6: return ['class' => 'bg-yellow-400 text-black', 'text' => '🛑จอง']; 
        case 7: return ['class' => 'bg-green-500', 'text' => '🅿️จอด']; 
        case 3: return ['class' => 'bg-red-500', 'text' => '⚠️พัง']; 
        default: return ['class' => 'bg-gray-500', 'text' => 'ไม่ทราบ']; 
    }
}


$slots = [];
while ($row = $result->fetch_assoc()) {
    $status = getStatusClassAndText($row['status_id']);
    $slots[] = [
        'number' => $row['number'],
        'class' => $status['class'],
        'status' => $status['text']
    ];
}


echo json_encode([
    'zone' => $zoneMap[$zoneId],
    'slots' => $slots
]);

$conn->close();
?>
