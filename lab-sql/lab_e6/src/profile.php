<?php
$conn = new mysqli("lab_e6_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
$sql = "SELECT id,user,email FROM users WHERE id = $id";
$res = $conn->query($sql);
echo "<h2>User Profile</h2>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "ID: ".$r['id']."<br>";
        echo "Username: ".$r['user']."<br>";
        echo "Email: ".$r['email']."<br>";
    }
}
// Hidden param - try id parameter
?>
