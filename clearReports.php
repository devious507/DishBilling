<?php

require_once("config.inc.php");

$conn=MDB2::connect(DSN);
$conn->query("DELETE FROM wincable_data");
$conn->query("DELETE FROM group_reports");
header("Location: index.php");
?>
