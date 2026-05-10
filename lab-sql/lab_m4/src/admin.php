<?php
$conn = new mysqli("lab_m4_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
// int only - numeric injection
$sql = "SELECT id,name,role FROM admins WHERE id = $id";
$res = $conn->query($sql);
echo "<h2>Admin Panel</h2>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "Admin #".$r['id']." - Name: ".$r['name']." - Role: ".$r['role']."<br>";
    }
}
// Role hint - int only expected
?>
