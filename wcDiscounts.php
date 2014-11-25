<?php

require_once("config.inc.php");
$sql="select subnum,subname from wincable_data where pkg_amt::numeric < 0 GROUP BY subnum,subname ORDER BY subname";

$conn=MDB2::connect(DSN);
if(PEAR::isError($conn)) {
	        print $conn->getMessage();
		        exit();
}

$res=$conn->query($sql);
$lines='';
while(($row=$res->fetchRow())==true) {
	$url="<a href=\"wcDetail.php?wcid={$row[0]}\">{$row[0]}</a>";
	$line="<tr>";
	$line.="<td>{$url}</td>";
	$line.="<td>{$row[1]}</td>";
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
