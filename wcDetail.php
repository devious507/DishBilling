<?php

require_once("config.inc.php");

if(!isset($_GET['wcid'])) {
	header("Location: wcDiscounts.php");
	exit();
} else {
	$wcID=$_GET['wcid'];
}
$sql="select subname,address1,address2,address3,qty,pkg_id,pkg_name,pkg_amt,pkg_total from wincable_data where subnum='{$wcID}'";

$conn=MDB2::connect(DSN);
if(PEAR::isError($conn)) {
	        print $conn->getMessage();
		        exit();
}

$res=$conn->query($sql);
$lines='';
$header=false;
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	$url="<a href=\"wcDetail.php?wcid={$row[0]}\">{$row[0]}</a>";
	$line="<tr>";
	if($header == false) {
		foreach($row as $k=>$v) {
			$line.="<td>{$k}</td>";
		}
		$line.="</tr>\n";
		$lines.=$line;
		$line="<tr>";
		$header=true;
	}
	foreach($row as $k=>$v) {
		switch($k) {
			
		default:
			$line.="<td>{$v}</td>";
			break;
		}
	}
	$line.="</tr>\n";
	$lines.=$line;
}
?>
<html>
<head>
<title>Discounts in Wincable</title>
</head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<?php echo $lines; ?>
</table>
</body>
</html>
