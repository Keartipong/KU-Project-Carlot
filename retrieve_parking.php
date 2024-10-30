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

    echo json_encode(['status' => 'success']);
    exit;
}


$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$submittedCars = isset($_SESSION['submittedCars']) ? $_SESSION['submittedCars'] : [];

$query = "SELECT c.card_id, c.user_height, c.user_license_plate, l.lot_id, l.number, l.bay_id, b.bay_name, l.parked_zone 
          FROM card c
          JOIN lot l ON c.lot_id = l.lot_id
          JOIN bay b ON l.bay_id = b.bay_id
          WHERE c.status_id = 7";

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
            'parked_zone' => $row['parked_zone'],
            'parkingSlot' => $row['lot_id'],
            'number' => $row['number']
        ];
    }
}

$filteredCars = $cars;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการที่จอดรถอัจฉริยะ</title>
    
   
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai+Looped:wght@100;200;300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
            
            @keyframes textGlow {
    0% {
        text-shadow: 0 0 5px #ff1744, 0 0 0px #ff1744, 0 0 0px #ff1744;
    }
    50% {
        text-shadow: 0 0 0px #ff1744, 0 0 5px #ff1744, 0 0 0px #ff1744;
    }
    100% {
        text-shadow: 0 0 0px #ff1744, 0 0 0px #ff1744, 0 0 5px #ff1744;
    }
}

      
        body {
            background-image: url('https://i.pinimg.com/originals/06/43/34/064334a850251d0e3b63f915d138eb67.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            font-family: 'IBM Plex Sans Thai Looped', sans-serif; /* ใช้ฟอนต์ IBM Plex Sans Thai Looped */
            color: #f7fafc;
        }
        .car-card table {
            font-family: 'Mali', sans-serif; 
        }
        input, button {
            background-color: rgba(255, 255, 255, 0.2); 
            color: #fff;
            border-color: #ff1744; 
        }
        input::placeholder {
            color: #ff5252; 
        }
        input:focus, button:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }
        h1 {
          color: #ff1744; 
          font-size: 3.5rem; 
          font-weight: 800; 
          text-align: center;
          text-transform: uppercase; 
          margin-bottom: 20px; 
          animation: textGlow 1.5s infinite alternate;
          
        }
        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.95); }
        }
        .bg-red-custom {
            background-color: #ff1744; 
        }
        .text-red-custom {
            color:black;
        }
        .border-red-custom {
            border-color: #ff1744; 
        }

    
        .card-id {
    border: 3px solid #ff1744; 
    padding: 12px;
    border-radius: 12px; 
    background-color: rgba(0, 0, 0, 0.5); 
    text-align: center;
    margin-bottom: 15px; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); 
    backdrop-filter: blur(8px); 
    
    
}
.card-id h3 {
    color: #ff1744; 
    font-size: 1.5rem; 
    margin: 0;
    font-weight: 700; 
}
        .card-id h2{
          z-index: 1;
          font-size: 2em;
        }
        .car-management-system {
          background: rgba(0, 0, 0, 0.8); 
          padding: 40px; 
          border-radius: 15px; 
          box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4); 
          backdrop-filter: blur(15px); 
          width: 80%; 
          max-width: 100%; 
          margin: 50px auto; 
          text-align: center; 
        }
        .car-card {
    transition: all 0.3s ease;
    position: relative;
}
.car-card:hover .card-id {
    animation: rgbGlow 1.5s infinite alternate;
}
.submit-car-form button {
    background: linear-gradient(100deg, #ff1744, #ff5252); 
    border: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
    transition: background 0.3s ease, box-shadow 0.3s ease; 
}
.submit-car-form button:hover {
    background: linear-gradient(90deg, #ff5252, #ff1744);
    box-shadow: 0 0 15px rgba(255, 0, 0, 0.7); 
}

.car-management-system h1 {
    color: #ff1744; 
    font-size: 2rem; 
    font-weight: 800; 
    text-transform: uppercase;
    margin-bottom: 20px; 
    animation: textGlow 1.5s infinite alternate; 
}
        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
        .blurred-background {
          backdrop-filter: blur(8px); 
            -webkit-backdrop-filter: blur(8px); 
            background-color: rgba(0, 0, 0, 0.5); 
            padding: 1.5rem; 
            border-radius: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); 
            display: inline-block;
            margin-bottom: 1rem; 
        }
        .text-shadow {
          text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.8); 
        }
       
        @keyframes rgbGlow {
    0% {
        text-shadow: 0 0 5px #ff1744, 0 0 10px #ff1744, 0 0 15px #ff1744, 0 0 20px #ff1744, 0 0 25px #ff1744, 0 0 30px #ff1744, 0 0 35px #ff1744;
        box-shadow: 0 0 0px #ff1744, 0 0 5px #ff1744, 0 0 10px #ff1744, 0 0 10px #ff1744, 0 0 5px #ff1744, 0 0 5px #ff1744, 0 0 0px #ff1744;
    }
    50% {
        text-shadow: 0 0 5px #ff5252, 0 0 10px #ff5252, 0 0 15px #ff5252, 0 0 20px #ff5252, 0 0 25px #ff5252, 0 0 30px #ff5252, 0 0 35px #ff5252;
        box-shadow: 0 0 0px #ff5252, 0 0 5px #ff5252, 0 0 10px #ff5252, 0 0 10px #ff5252, 0 0 5px #ff5252, 0 0 5px #ff5252, 0 0 0px #ff5252;
    }
    100% {
        text-shadow: 0 0 5px #ff1744, 0 0 10px #ff1744, 0 0 15px #ff1744, 0 0 20px #ff1744, 0 0 25px #ff1744, 0 0 30px #ff1744, 0 0 35px #ff1744;
        box-shadow: 0 0 0px #ff1744, 0 0 5px #ff1744, 0 0 10px #ff1744, 0 0 10px #ff1744, 0 0 5px #ff1744, 0 0 5px #ff1744, 0 0 0px #ff1744;
    }
}
.menu-item {
    background: linear-gradient(100deg, #ff1744, #ff5252); 
    border: none; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); 
    transition: background 0.3s ease, box-shadow 0.3s ease; 
    font-size: 1rem;
    padding: 15px; 
    border-radius: 15px; 
    position: fixed; 
    bottom: 10px; 
    right: -600px; 
    text-align: center; 
}

.menu-item:hover {
    background: linear-gradient(90deg, #ff5252, #ff1744); 
    box-shadow: 0 0 15px rgba(255, 0, 0, 0.7);
}
    </style>
</head>
<body>
    <div class="car-management-system p-6">
  
        <div class="blurred-background">
            <h1 class="text-shadow">ตัวเรียกช่องจอด(คืนช่องจอด)</h1>
        </div>
        
        <div class="absolute right-0 top-10 mr-7">
            <input type="text" id="search" class="w-full max-w-wd p-3 bg-gray-500 border-red-500 text-white rounded" placeholder="ค้นหา">
            <svg class="absolute top-1/2 right-2 transform -translate-y-1/2 w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a7 7 0 0 1 7 7 7 7 0 0 1-7 7 7 7 0 0 1-7-7 7 7 0 0 1 7-7zm0 0l6 6" />
            </svg>
        </div>
        
        <div id="car-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
         
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search');
        const carGrid = document.getElementById('car-grid');

        function updateCarList(searchTerm) {
            fetch(`?search=${encodeURIComponent(searchTerm)}&ajax=true`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(cars => {
                    carGrid.innerHTML = '';
                    cars.forEach(car => {
                        const carCard = document.createElement('div');
                        carCard.className = 'car-card bg-gray-800 border-red-custom p-4 rounded-lg';
                        carCard.dataset.carId = car.id;

                        carCard.innerHTML = `
                        <div class="card-id">
                            <h3 class="text-xl font-bold text-red-custom">Card ID: ${car.id}</h3>
                        </div>
                        <table class="w-full text-left text-gray-300">
                            <tr>
                                <td class="py-1">ทะเบียน:</td>
                                <td>${car.licensePlate}</td>
                            </tr>
                            <tr>
                                <td class="py-1">ความสูง:</td>
                                <td>${car.height ? car.height + ' ซม.' : 'ไม่ระบุ'}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Zone:</td>
                                <td>${car.parked_zone}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Bay:</td>
                                <td>${car.zone}</td>
                            </tr>
                            <tr>
                                <td class="py-1">ช่องจอด:</td>
                                <td>${car.number}</td>
                            </tr>
                        </table>
                        <form class="submit-car-form" method="POST" action="updateparking_error.php">
                            <input type="hidden" name="carId" value="${car.id}">
                            <input type="hidden" name="licensePlate" value="${car.licensePlate}">
                            <input type="hidden" name="height" value="${car.height}">
                            <input type="hidden" name="bay" value="${car.zone}">
                            <input type="hidden" name="zone" value="${car.parked_zone}">
                            <input type="hidden" name="parkingSlot" value="${car.number}">
                            <button type="submit" name="submit" class="mt-4 w-full bg-red-custom hover:bg-red-600 text-white p-2 rounded">
                                Submit
                            </button>
                        </form>
                        `;

                        carGrid.appendChild(carCard);
                    });
                })
                .catch(error => console.error('There was a problem with the fetch operation:', error));
        }

        searchInput.addEventListener('input', () => {
            updateCarList(searchInput.value);
        });

        // Initial load
        updateCarList('');
        setInterval(() => {
            updateCarList(searchInput.value);
        }, 5000);
    </script>
</body>
</html>
