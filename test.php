<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking History</title>
    <style>
    </style>
</head>
<body>

<div class="container">
    <h1>Departure Time</h1>
    <input type="text" id="search" placeholder="Search by His ID, Card ID, License Plate...">
    <button onclick="searchData()">Search</button>

    <table id="historyTable">
        <tr>
            <th>ลำดับ</th>
            <th>Card ID</th>
            <th>ระยะทาง</th> <!-- เปลี่ยนชื่อหัวข้อเป็นระยะทาง -->
            <th>ป้ายทะเบียน</th>
            <th>Lot</th>
            <th>เวลาออก</th>
        </tr>

        <?php
        // สร้างการเชื่อมต่อฐานข้อมูล
        $servername = "151.106.124.154"; // เปลี่ยนเป็นเซิร์ฟเวอร์ของคุณ
        $username = "u583789277_wag7"; // เปลี่ยนเป็นชื่อผู้ใช้ของคุณ
        $password = "2567Concept"; // เปลี่ยนเป็นรหัสผ่านของคุณ
        $dbname = "u583789277_wag7"; // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ

        // สร้างการเชื่อมต่อ
        $conn = new mysqli($servername, $username, $password, $dbname);

        // ตรวจสอบการเชื่อมต่อ
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // กำหนดจำนวนรายการต่อหน้า
        $items_per_page = 10;

        // รับหมายเลขหน้าปัจจุบันจาก URL (ถ้าไม่มีให้ตั้งเป็น 1)
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($current_page - 1) * $items_per_page;

        // คำสั่ง SQL เพื่อดึงข้อมูลจากตาราง update_history พร้อมกับข้อมูลจากตาราง lot
        $sql = "
            SELECT u.*, l.number as lot_number
            FROM update_history u
            JOIN lot l ON u.lot_id = l.lot_id
            WHERE u.time_out IS NOT NULL -- ตรวจสอบว่า time_out มีค่า
            ORDER BY u.time_out DESC
            LIMIT $offset, $items_per_page
        ";
        $result = $conn->query($sql);

        // ตรวจสอบว่ามีข้อมูลหรือไม่
        if ($result->num_rows > 0) {
            // เริ่มลำดับ
            $count = $offset + 1;

            // แสดงข้อมูลที่ดึงมา
            while($row = $result->fetch_assoc()) {
                // ปรับเวลาที่ดึงมาจากฐานข้อมูล (ถ้าจำเป็น) เช่น ถ้าต้องการเพิ่ม 7 ชั่วโมง
                $time_out = new DateTime($row["time_out"]);
                $time_out->modify('+7 hours'); // ปรับเวลาถ้าต้องการเพิ่ม 7 ชั่วโมง
        
                echo "<tr>";
                echo "<td>" . $count . "</td>";
                echo "<td>" . $row["card_id"] . "</td>";
                echo "<td>" . $row["distance"] . "</td>"; // เปลี่ยนให้แสดงความสูง (distance)
                echo "<td>" . $row["user_license_plate"] . "</td>"; // เปลี่ยนให้แสดงป้ายทะเบียน
                echo "<td>" . $row["lot_number"] . "</td>"; // แสดงหมายเลขจากตาราง lot
                echo "<td>" . $time_out->format('Y-m-d H:i:s') . "</td>"; // แสดงเวลาที่ปรับแล้ว
                echo "</tr>";
        
                // เพิ่มลำดับ
                $count++;
            }
        } else {
            echo "<tr><td colspan='6'>No results found.</td></tr>";
        }

        echo "</table>";

        // คำสั่ง SQL เพื่อหาจำนวนรวมของข้อมูลในตาราง update_history ที่มี time_out
        $total_sql = "SELECT COUNT(*) as total FROM update_history WHERE time_out IS NOT NULL";
        $total_result = $conn->query($total_sql);
        $total_row = $total_result->fetch_assoc();
        $total_items = $total_row['total'];

   
        $total_pages = ceil($total_items / $items_per_page);

   
        echo "<div class='pagination'>";
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                echo "<a class='active' href='?page=$i'>$i</a>";
            } else {
                echo "<a href='?page=$i'>$i</a>";
            }
        }
        echo "</div>";

     
        $conn->close();
        ?>
    </div>
</body>

<script>
function searchData() {
    let input = document.getElementById('search').value;
    let table = document.getElementById('historyTable');
    let tr = table.getElementsByTagName('tr');

    // แสดงข้อมูลใหม่เมื่อกดปุ่มค้นหา
    let searchResults = [];
    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let found = false;

        for (let j = 1; j < td.length; j++) { // เริ่มจาก 1 เพราะไม่ต้องการตรวจสอบคอลัมน์ที่ 0
            if (td[j]) {
                let txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toLowerCase().indexOf(input.toLowerCase()) > -1) {
                    found = true;
                    break;
                }
            }
        }
        if (found) {
            searchResults.push(tr[i]); // บันทึกแถวที่ตรงกับคำค้นหา
        }
    }

    // ซ่อนทั้งหมดก่อนแล้วจึงแสดงผลลัพธ์ที่ตรงกัน
    for (let i = 1; i < tr.length; i++) {
        tr[i].style.display = "none"; // ซ่อนแถวทั้งหมด
    }

    for (let i = 0; i < searchResults.length; i++) {
        searchResults[i].style.display = ""; // แสดงเฉพาะแถวที่ตรงกัน
    }
}
</script>
</html>
