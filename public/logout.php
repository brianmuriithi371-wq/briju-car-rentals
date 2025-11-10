<?php
include '../config/config.php';

// Destroy the session
session_unset();
session_destroy();

// Redirect to the landing page (index.php)
header("Location: index.php");
exit();
?>
