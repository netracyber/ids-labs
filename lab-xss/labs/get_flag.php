<?php
session_start();
require_once __DIR__ . '/FlagGenerator.php';
if (empty($_SESSION['xss_solved'])) {
    http_response_code(403);
    echo 'Solve the challenge first!';
    exit;
}
if (!isset($_SESSION['flag'])) {
    $flagGen = new FlagGenerator();
    $_SESSION['flag'] = $flagGen->generate_flag();
}
echo $_SESSION['flag'];
?>
