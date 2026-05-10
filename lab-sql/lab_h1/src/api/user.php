<?php
header('Content-Type: application/json');
$conn = new mysqli("lab_h1_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
$sql = "SELECT id,user,hash FROM users WHERE id = $id";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    $r = $res->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $r]);
} else {
    echo json_encode(["status" => "error", "message" => "Not found"]);
}
// Blind no-error - JSON output
?>
