<?php

require_once("config.inc.php");

$sql="INSERT INTO addr_matches (group_report,wincable) VALUES ('{$_GET['gr_addr']}','{$_GET['wc_addr']}')";

$conn=MDB2::connect(DSN);
$conn->query($sql);
header("Location: findMatches.php?view=nogreen");

?>
