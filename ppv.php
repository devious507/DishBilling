<?php

require_once("config.inc.php");

$conn = MDB2::connect(DSN);

$sql="SELECT subname,address1,dates FROM group_reports WHERE description='NVP DIGITAL ACCESS FEE' ORDER BY subname";
$sql="SELECT subname,address1,dates FROM group_reports WHERE description='SUBTOTAL' AND amount='10' ORDER BY subname";
$sql="select a.subname,a.address1,a.dates,a.description,a.amount FROM (select * from group_reports WHERE category_name='One Time Charges') as a ORDER BY a.subname,a.dates";

$res=$conn->query($sql);
$headers=false;
$count=1;

$table_data="<tr><td colspan=\"7\">{$sql}</td></tr>\n";
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
	if($headers == false) {
		$table_data.=getHeaders($row);
		$headers=true;
	}
	$table_data.="<tr>";
	$table_data.="<td>{$count}</td>";
	foreach($row as $k=>$v) {
		if($k == "subname") {
			$table_data.="<td>".getWincableNumber($row['address1'],$conn)."</td>";
		}
		$gr_total=getGroupTotal($row['address1'],$conn);
		$wc_total=getWincableTotal($row['address1'],$conn);
		if($k == 'address1') {
			$url_data=urlencode($v);
			$url="<a href=\"showCompare.php?addr1={$url_data}\">{$v}</a>";
			$table_data.="<td>{$url}</td>";
		} elseif($k == 'amount') {
			$table_data.=sprintf("<td align=\"right\">$ %.02f</td>",$v);
		} else {
			$table_data.="<td>{$v}</td>";
		}
	}
	//$table_data.="<td align=\"right\">".sprintf("%.02f",$gr_total)."</td>";
	//$table_data.="<td align=\"right\">".sprintf("%.02f",$wc_total)."</td>";
	$table_data.="</tr>\n";
	$count++;
}


function getWincableNumber($addr,$conn) {
	$sql="SELECT wincable FROM addr_matches WHERE group_report='{$addr}'";
	$res=$conn->query($sql);
	while($row=$res->fetchRow()) {
		$addr=$row[0];
	}
	$sql="select a.subnum FROM (select *,address2||' '||address1 AS addr from wincable_data) AS a WHERE addr='{$addr}'";
	$res=$conn->query($sql);
	$row=$res->fetchRow();
	return $row[0];
}
function getGroupTotal($addr,$conn) {
	$sql="SELECT amount FROM group_reports WHERE address1='{$addr}' AND description != 'SUBTOTAL'";
	$total=0;
	// print $sql; exit();
	$res=$conn->query($sql);
	if(PEAR::isError($res)) {
		print $res->getMessage();
		exit();
	}
	while(($row=$res->fetchRow())==true) {
		$total+=$row[0];
	}
	return $total;
} 
function getWincableTotal($addr,$conn) {
	$sql="SELECT wincable FROM addr_matches WHERE group_report='{$addr}'";
	$total=0;
	$res=$conn->query($sql);
	while($row=$res->fetchRow()) {
		$addr=$row[0];
	}
	$sql="select a.pkg_total FROM (select *,address2||' '||address1 AS addr from wincable_data) AS a WHERE addr='{$addr}'";
	$res=$conn->query($sql);
	if(PEAR::isError($res)) {
		print $sql."<br>";
		print $res->getMessage();
		exit();
	}
	while(($row=$res->fetchRow())==true) {
		$total+=$row[0];
	}
	return $total;
}

function getHeaders($row) {
	$rv="<tr><td>&nbsp;</td>";
	$rv.="<td>Wincable #</td>";
	foreach($row as $k=>$v) {
		$rv.="<td>{$k}</td>";
	}
	//$rv.="<td>GR Total</td>";
	//$rv.="<td>WC Total</td>";
	$rv.="</tr>\n";
	return $rv;
}
?>
<html>
<head>
<title>AdHod Query Tool</title>
</head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<?php echo $table_data; ?>
</table>
</body>
</html>
