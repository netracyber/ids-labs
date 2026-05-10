<?php
$conn = new mysqli("lab_h5_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
// Hidden route - uses mysqli_multi_query for stacked queries
$sql = "SELECT id,value FROM misc WHERE id = $id";
// Multi-query allows stacked queries
if ($conn->multi_query($sql)) {
    do {
        if ($res = $conn->store_result()) {
            while ($r = $res->fetch_assoc()) {
                echo "Value: ".$r['value']."<br>";
            }
            $res->free();
        }
    } while ($conn->next_result());
}
// Uses mysqli_multi_query - try ; for stacked queries
?>
