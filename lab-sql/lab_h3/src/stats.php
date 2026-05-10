<?php
$conn = new mysqli("lab_h3_db", "root", "root", "lab");
// Encoded param - double decoding
$id = isset($_GET['id']) ? $_GET['id'] : '1';
// First decode
$id = urldecode($id);
// Second decode (simulating double encoding)
$id = urldecode($id);
$sql = "SELECT id,data FROM stats WHERE id = $id";
$res = $conn->query($sql);
echo "<h2>Statistics</h2>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "Stat: ".$r['data']."<br>";
    }
}
// Encoded param - try %2527 for single quote
?>
