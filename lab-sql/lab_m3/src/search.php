<?php
$conn = new mysqli("lab_m3_db", "root", "root", "lab");
$q = isset($_POST['q']) ? $_POST['q'] : '';
echo "<h2>Product Search</h2>";
echo "<form method='POST'><input type='text' name='q' value='".htmlspecialchars($q)."'><button>Search</button></form>";
if ($q) {
    $sql = "SELECT id,name,price FROM products WHERE name LIKE '%$q%'";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo $r['name']." - $".$r['price']."<br>";
        }
    }
}
// Delay behavior - sleep() based injection
?>
