<?php
$conn = new mysqli("lab_e3_db", "root", "root", "lab");
$msg = "";
if (isset($_POST['user']) && isset($_POST['pass'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $sql = "SELECT * FROM users WHERE user='$user' AND pass='$pass'";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $msg = "Welcome, $user! Login successful.";
    } else {
        $msg = "Invalid credentials.";
    }
}
echo "<h2>Login</h2>";
echo "<form method='POST'>";
echo "Username: <input type='text' name='user'><br>";
echo "Password: <input type='password' name='pass'><br>";
echo "<button>Login</button></form>";
echo "<p>$msg</p>";
// Login message - OR 1=1
?>
