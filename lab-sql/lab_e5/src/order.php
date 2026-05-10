<?php
$conn = new mysqli("lab_e5_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
$sql = "SELECT id,user,total FROM orders WHERE id = $id";
$res = $conn->query($sql);
echo "<h2>Order Details</h2>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "Order #".$r['id']." - User: ".$r['user']." - Total: $".$r['total']."<br>";
    }
}
?>
<!-- UI mislead: look for hidden elements -->
<div style="display:none">Hidden text: try UNION</div>
