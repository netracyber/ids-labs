<?php
$conn = new mysqli("lab_e4_db", "root", "root", "lab");
$cat = isset($_GET['cat']) ? $_GET['cat'] : '1';
$sql = "SELECT id,name FROM categories WHERE id = $cat";
$res = $conn->query($sql);
echo "<h2>Category Filter</h2>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "Category: ".$r['name']."<br>";
    }
}
?>
<script>
// Column hint: this query returns 2 columns
console.log("Debug: id, name");
</script>
