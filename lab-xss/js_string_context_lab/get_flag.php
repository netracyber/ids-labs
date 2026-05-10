<?php
session_start();
if (empty($_SESSION['xss_solved'])) {
    http_response_code(403);
    echo 'Solve the challenge first!';
    exit;
}
require_once __DIR__ . "/FlagGenerator.php";
if (!isset($_SESSION['flag'])) {
    $flag_generator = new FlagGenerator();
    $_SESSION['flag'] = $flag_generator->generate_flag();
}
echo $_SESSION['flag'];
?>
