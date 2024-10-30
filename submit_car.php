<?php
session_start();


// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//     header('Content-Type: application/json'); 
//     echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
//     exit;
// }


$servername = "151.106.124.154"; 
$username = "u583789277_wag7"; 
$password = "2567Concept"; 
$dbname = "u583789277_wag7"; 

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    header('Content-Type: application/json'); 
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carId'])) {
    $carId = $_POST['carId'];
    $submittedCars = isset($_SESSION['submittedCars']) ? $_SESSION['submittedCars'] : [];
    $submittedCars[$carId] = true;
    $_SESSION['submittedCars'] = $submittedCars;

    $conn->begin_transaction();
    try {
       
        $updateCardQuery = "UPDATE card SET status_id = 7 WHERE card_id = ?";
        $stmt = $conn->prepare($updateCardQuery);
        $stmt->bind_param('s', $carId);
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        $selectLotIdQuery = "SELECT lot_id FROM card WHERE card_id = ?";
        $stmt = $conn->prepare($selectLotIdQuery);
        $stmt->bind_param('s', $carId);
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }
        $stmt->bind_result($lotId);
        $stmt->fetch();
        $stmt->close();

     
        if ($lotId) {
            $updateLotQuery = "UPDATE lot SET status_id = 7 WHERE lot_id = ?";
            $stmt = $conn->prepare($updateLotQuery);
            $stmt->bind_param('s', $lotId);
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        header('Content-Type: application/json'); 
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    exit;
}

$conn->close();
?>