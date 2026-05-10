<?php
$conn = new mysqli("lab_m1_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
$sql = "SELECT id,title,data FROM reports WHERE id = $id";
$res = $conn->query($sql);
echo "<h2>Report Viewer</h2>";
if ($res && $res->num_rows > 0) {
    $r = $res->fetch_assoc();
    echo "<b>".$r['title']."</b><br>";
    echo $r['data']."<br>";
} else {
    // No error displayed - boolean blind
    echo "Report not found.";
}
// Timing text - no error
?>
