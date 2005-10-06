<?php
require_once("functions.php");
$type = $_GET['type'];
$view = $_GET['view'];
$delay = $_GET['delay'];
getRandomImage(".", $type, $view, $delay);
?>
