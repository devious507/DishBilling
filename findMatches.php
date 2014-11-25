<?php

require_once("config.inc.php");

$matches=0;
$nomatch=0;
$conn = MDB2::connect(DSN);

$sql="select subname,address1 FROM group_reports GROUP BY subname,address1 ORDER BY subname,address1";

$res = $conn->query($sql);
$rows='';
while(($tmp_row=$res->fetchRow())==true) {
	$name=$tmp_row[0];
	$addr=$tmp_row[1];
	$rows.=getRow($conn,$name,$addr);
}


function getRow($conn,$name,$addr) {
	global $matches,$nomatch,$_GET;
	$sql="SELECT wincable FROM addr_matches WHERE group_report='{$addr}'";
	$res=$conn->query($sql);
	while(($ttmp=$res->fetchRow())==true) {
		$addr=$ttmp[0];
	}
	$sql="select a.* FROM (select subname,address2||' '||address1 AS addr from wincable_data) AS a WHERE addr='{$addr}'";
	$res=$conn->query($sql);
	if(PEAR::isError($res)) {
		print $res->getMessage()."<br>";
		print $sql;
		exit();
	}
	$numRows=0;
	while(($tmp=$res->fetchRow())==true) {
		$numRows++;
	}
	$count=$res->numRows();
	if($numRows > 0) {
		$color="green";
		$status=$numRows;
		$matches++;
	} else {
		$color="red";
		$status="No Match";
		$nomatch++;
		$url_addr=urlencode($addr);
		$addr="<a href=\"fixMatches.php?address1={$url_addr}&name={$name}\">{$addr}</a>";
	}
	if(isset($_GET['view']) && $_GET['view'] == 'nogreen') {
		if($color !== 'green') {
			$rv="<tr><td>{$name}</td><td>{$addr}</td><td bgcolor=\"{$color}\">{$status}</td></tr>\n";
		}
	} else {
			$rv="<tr><td>{$name}</td><td>{$addr}</td><td bgcolor=\"{$color}\">{$status}</td></tr>\n";
	}
	return $rv;
}
?>
<html>
<head>
<title>Address Match Tester</title>
</head>
<table cellpadding="5" cellspacing="0" border="1">
<tr><th colspan="3">Group Reports -> Wincable Matching</th></tr>
<tr><td>Subscriber Name</td><td>Subscriber Address</td><td>Status</td></tr>
<tr><td>Matches: <?php echo $matches;?></td><td>No-Match: <?php echo $nomatch;?></td><td>&nbsp;</td></tr>
<?php echo $rows; ?>
</table>
</head>
