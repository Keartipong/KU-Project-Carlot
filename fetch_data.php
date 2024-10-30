<?php
session_start();

// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//     header("Location: index.php");
//     exit;
// }


$host = '151.106.124.154'; 
$dbname = 'u583789277_wag7'; 
$username = 'u583789277_wag7'; 
$password = '2567Concept'; 


error_reporting(E_ALL);
ini_set('display_errors', 1);


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


$lastCardId = isset($_GET['lastCardId']) ? $_GET['lastCardId'] : null;


$sql = "
    SELECT 
        c.card_id, 
        c.user_license_plate, 
        l.lot_id, 
        l.number AS parking_slot, 
        l.parked_zone AS zone, 
        b.bay_name
    FROM 
        distance_data dd
    JOIN 
        card c ON dd.card_id = c.card_id
    JOIN 
        lot l ON c.lot_id = l.lot_id
    JOIN 
        bay b ON l.bay_id = b.bay_id
";


if ($lastCardId) {
    $sql .= " WHERE c.card_id > :lastCardId ";
}

$sql .= " ORDER BY c.time DESC LIMIT 1";

$stmt = $pdo->prepare($sql);


if ($lastCardId) {
    $stmt->bindParam(':lastCardId', $lastCardId, PDO::PARAM_INT);
}

try {
    $stmt->execute();
    $carData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}


if (!$carData) {
    $carData = [
        'card_id' => 'No Data',
        'user_license_plate' => 'No Data',
        'parking_slot' => 'No Data',
        'zone' => 'No Data',
        'bay_name' => 'No Data'
    ];
}


header('Content-Type: application/json');
echo json_encode($carData);
?>