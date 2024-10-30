<?php
$servername = "151.106.124.154";
$username = "u583789277_wag7";
$password = "2567Concept";
$dbname = "u583789277_wag7";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// อัปเดตสถานะที่จอดรถ
foreach ($_POST as $key => $value) {
    if (strpos($key, 'status_') === 0) {
        $lot_id = str_replace('status_', '', $key);
        $status_id = intval($value);
        $sql = "UPDATE lot SET status_id = $status_id WHERE lot_id = $lot_id";
        $conn->query($sql);
    }
}


$conn->close();


header("Location: lot.php");
exit();
