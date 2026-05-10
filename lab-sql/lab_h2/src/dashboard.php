<?php
$conn = new mysqli("lab_h2_db", "root", "root", "lab");
// Store input first
if (isset($_POST['data'])) {
    $data = $_POST['data'];
    $conn->query("INSERT INTO user_input (data) VALUES ('$data')");
    echo "Data stored!";
}
// Second-order: use stored data in another query
$res = $conn->query("SELECT data FROM user_input ORDER BY id DESC LIMIT 1");
if ($res && $r = $res->fetch_assoc()) {
    $stored = $r['data'];
    // Indirect - stored trigger
    $res2 = $conn->query("SELECT * FROM logs WHERE event LIKE '%$stored%'");
    echo "<h2>Dashboard Logs</h2>";
    if ($res2) {
        while ($row = $res2->fetch_assoc()) {
            echo "Log: ".$row['event']."<br>";
        }
    }
}
?>
<form method="POST">
<input type="text" name="data" placeholder="Enter search term">
<button>Submit</button>
</form>
