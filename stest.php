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


if (isset($_POST['reset'])) {
    unset($_SESSION['submittedCars']);
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
        $stmt->execute();

       
        $selectLotIdQuery = "SELECT lot_id FROM card WHERE card_id = ?";
        $stmt = $conn->prepare($selectLotIdQuery);
        $stmt->bind_param('s', $carId);
        $stmt->execute();
        $stmt->bind_result($lotId);
        $stmt->fetch();
        $stmt->close();

        
        if ($lotId) {
            $updateLotQuery = "UPDATE lot SET status_id = 7 WHERE lot_id = ?";
            $stmt = $conn->prepare($updateLotQuery);
            $stmt->bind_param('s', $lotId);
            $stmt->execute();
        }

       
        $conn->commit();

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
       
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    exit;
}


$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$submittedCars = isset($_SESSION['submittedCars']) ? $_SESSION['submittedCars'] : [];

$query = "SELECT c.card_id, c.user_height, c.user_license_plate, l.lot_id, l.bay_id, b.bay_name 
          FROM card c
          JOIN lot l ON c.lot_id = l.lot_id
          JOIN bay b ON l.bay_id = b.bay_id
          WHERE 1=1";

if ($searchTerm) {
    $searchTerm = $conn->real_escape_string($searchTerm);
    $query .= " AND (c.user_license_plate LIKE '%$searchTerm%' OR c.card_id LIKE '%$searchTerm%')";
}

$result = $conn->query($query);

$cars = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = [
            'id' => $row['card_id'],
            'licensePlate' => $row['user_license_plate'],
            'height' => $row['user_height'],
            'zone' => $row['bay_name'],
            'parkingSlot' => $row['lot_id']
        ];
    }
}


$filteredCars = array_filter($cars, function($car) use ($submittedCars) {
    return !isset($submittedCars[$car['id']]);
});

if (isset($_GET['ajax'])) {
    echo json_encode(array_values($filteredCars));
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parking Management</title>
</head>
<body>
    <h1>Parking Management</h1>
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by License Plate or Card ID" value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit">Search</button>
    </form>
    <form method="POST" action="">
        <button type="submit" name="reset">Reset</button>
    </form>

    <table border="1">
        <thead>
            <tr>
                <th>Card ID</th>
                <th>License Plate</th>
                <th>Height</th>
                <th>Zone</th>
                <th>Parking Slot</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filteredCars as $car): ?>
                <tr>
                    <td><?= htmlspecialchars($car['id']) ?></td>
                    <td><?= htmlspecialchars($car['licensePlate']) ?></td>
                    <td><?= htmlspecialchars($car['height']) ?></td>
                    <td><?= htmlspecialchars($car['zone']) ?></td>
                    <td><?= htmlspecialchars($car['parkingSlot']) ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="carId" value="<?= htmlspecialchars($car['id']) ?>">
                            <button type="submit">Submit</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
