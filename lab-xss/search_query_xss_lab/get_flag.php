<?php
session_start();
require_once __DIR__ . "/FlagGenerator.php";
if (!isset($_SESSION['flag'])) {
    $flag_generator = new FlagGenerator();
    $_SESSION['flag'] = $flag_generator->generate_flag();
}
echo $_SESSION['flag'];
?>
