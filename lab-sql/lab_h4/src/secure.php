<?php
$conn = new mysqli("lab_h4_db", "root", "root", "lab");
$id = isset($_GET['id']) ? $_GET['id'] : '1';
// WAF simulate - regex block
$waf_patterns = [
    '/\bunion\b/i',
    '/\bselect\b/i',
    '/\bfrom\b/i',
    '/\bor\b/i',
    '/\band\b/i',
    '/\bwhere\b/i',
    '/--/',
    '/#/',
    '/\//'
];
foreach ($waf_patterns as $pattern) {
    if (preg_match($pattern, $id)) {
        die("WAF: Blocked!");
    }
}
$sql = "SELECT id,data FROM secure WHERE id = '$id'";
$res = $conn->query($sql);
echo "<h2>Secure Data</h2>";
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo "Data: ".$r['data']."<br>";
    }
} else {
    echo "Error";
}
// regex block - try case variations, encoding, or comments
?>
