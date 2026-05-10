<?php
$conn = new mysqli("lab_m5_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
// Sorting issue - order by idx
$sql = "SELECT id,file FROM exports WHERE id = $id ORDER BY $sort";
$res = $conn->query($sql);
echo "<h2>Export Files</h2>";
echo "<a href='?id=1&sort=id'>Sort by ID</a> | <a href='?id=1&sort=file'>Sort by File</a><br><br>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "File: ".$r['file']."<br>";
    }
}
// order by idx - column enumeration
?>
