<?php
$conn = new mysqli("lab_m2_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
// Keyword filtered - union blocked (case insensitive)
if (preg_match('/union/i', $id)) {
    die("Blocked: Union keyword detected!");
}
$sql = "SELECT id,user,balance FROM accounts WHERE id = $id";
$res = $conn->query($sql);
echo "<h2>Account Info</h2>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "Account #".$r['id']." - User: ".$r['user']." - Balance: $".$r['balance']."<br>";
    }
}
// Keyword filtered - try case variations or encoding
?>
