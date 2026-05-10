<?php
$conn = new mysqli("lab_e2_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
$sql = "SELECT id,name,`desc` FROM products WHERE id = $id";
$res = $conn->query($sql);
echo "<h2>Product Details</h2>";
if ($res) {
    if ($r = $res->fetch_assoc()) {
        echo "<b>".$r['name']."</b><br>";
        echo $r['desc']."<br>";
    }
} else {
    echo "Error: " . $conn->error;
}
// SQL error leak - visible
?>
