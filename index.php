<?php
require_once("config.inc.php");

$conn=MDB2::connect(DSN);
if(PEAR::isError($conn)) {
	print $conn->getMessage();
	exit();
}

$sql="SELECT count(*) FROM wincable_data";
$res=$conn->query($sql);
$row=$res->fetchRow();
$wcCount=$row[0];
$sql="SELECT count(*) FROM group_reports";
$res=$conn->query($sql);
$row=$res->fetchRow();
$grCount=$row[0];

?>
<html>
	<head>
		<title>Dish Billing Comparitor</title>
	</head>
	<body>
		<ul>
		<li><a href="uploadWincableReport.html">Upload Wincable Report</a> (<?php echo $wcCount; ?>)</li>
		<li><a href="uploadGroupReports.html">Upload Dish Group Reports</a> (<?php echo $grCount; ?>)</li>
		<li><a href="clearReports.php">Clear Report Data</a></li>
		<li><a href="findMatches.php">Find Matches</a></li>
		<li><a href="gr_report_list.php?type=nvp">Subs Being Charged NVP By Dish</a></li>
		<li><a href="gr_report_list.php?type=std">Group Report -> Wincable List</a></li>
		<li><a href="ppv.php">Pay Per View Billing</a></li>
		<li><a href="wcDiscounts.php">Accounts with Discounts</a></li>
		</ul>
	</body>
</html>
