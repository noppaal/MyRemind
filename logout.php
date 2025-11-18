<?php
session_start();
$_SESSION = []; // Kosongkan array sesi
session_unset();
session_destroy(); // Hancurkan sesi

header("Location: login.php");
exit;
?>