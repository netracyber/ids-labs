<?php
$conn = new mysqli("lab_e1_db", "root", "root", "lab");
$q = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT id,name,price FROM products WHERE name LIKE '%$q%'";
$res = $conn->query($sql);
echo "<h2>Product Search</h2>";
echo "<form method='GET'><input type='text' name='q' value='".htmlspecialchars($q)."'><button>Search</button></form>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo $r['name']." - $".$r['price']."<br>";
    }
}
// admin testing parameter q
?>
