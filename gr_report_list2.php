<?php

require_once("config.inc.php");

$conn = MDB2::connect(DSN);

if(isset($_GET['type'])) {
	switch($_GET['type']) {
	case 'nvp':
		$type=$_GET['type'];
		break;
	default:
		$type='std';
		break;
	}
} else {
	$type='std';
}
if($type == 'nvp') {
	$sql="SELECT subname,address1,dates FROM group_reports WHERE description='SUBTOTAL' AND amount='10' ORDER BY subname";
	$sql="SELECT subname,address1,dates FROM group_reports WHERE description='NVP DIGITAL ACCESS FEE' ORDER BY subname";
} else {
	$sql="SELECT subname,address1,dates FROM group_reports GROUP BY subname,address1,dates ORDER BY subname";
}

$res=$conn->query($sql);
$headers=false;
$count=1;

$table_data="<tr><td colspan=\"10\">{$sql}</td></tr>\n";
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
		$tmp_total=getWincableTotal($row['address1'],$conn);
		$wc_total=$tmp_total['wc'];
		$data_total=$tmp_total['data'];
		$basic_total=$tmp_total['basic'];
		$discount_total=$tmp_total['discount'];
		//$wc_total=getWincableTotal($row['address1'],$conn);
		//$data_total=getWincableTotal($row['address1'],$conn,true);
		//$discount_total=getWincableTotal($row['address1'],$conn,'discount');
		if($k == 'address1') {
			$url_data=urlencode($v);
			$url="<a href=\"showCompare.php?addr1={$url_data}\">{$v}</a>";
			$table_data.="<td>{$url}</td>";
		} else {
			$table_data.="<td>{$v}</td>";
		}
	}
	if($gr_total > $wc_total) {
		$bgcolor="red";
	} elseif($gr_total == $wc_total) {
		$bgcolor="green";
	} else {
		$bgcolor="white";
	}
	$table_data.="<td align=\"right\" bgcolor=\"{$bgcolor}\">".sprintf("%.02f",$gr_total)."</td>";
	$table_data.="<td align=\"right\" bgcolor=\"{$bgcolor}\">".sprintf("%.02f",$wc_total)."</td>";
	$table_data.="<td align=\"right\" bgcolor=\"white\">".sprintf("%.02f",$basic_total)."</td>";
	$table_data.="<td align=\"right\" bgcolor=\"white\">".sprintf("%.02f",$data_total)."</td>";
	$table_data.="<td align=\"right\" bgcolor=\"white\">".sprintf("%.02f",$discount_total)."</td>";
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

function getWincableTotal($addr,$conn,$data=false) {
	$sql="SELECT wincable FROM addr_matches WHERE group_report='{$addr}'";
	$total=0;
	$res=$conn->query($sql);
	$totals['wc']=0;
	$totals['data']=0;
	$totals['discount']=0;
	$totals['basic']=0;
	while($row=$res->fetchRow()) {
		$addr=$row[0];
	}
	$sql="select a.pkg_total,a.pkg_name FROM (select *,address2||' '||address1 AS addr from wincable_data) AS a WHERE addr='{$addr}'";
	$res=$conn->query($sql);
	if(PEAR::isError($res)) {
		print $sql."<br>";
		print $res->getMessage();
		exit();
	}
	while(($row=$res->fetchRow())==true) {
		$row[0]=floatval(preg_replace("/,/",'',$row[0]));
		if(preg_match("/(Internet|Modem)/",$row[1])) {
			$totals['data']+=$row[0];
		} elseif(preg_match("/(Basic Cable|Expanded Basic)/",$row[1])) {
			$totals['basic']+=$row[0];
		} elseif($row[0] < 0) {
			$totals['discount']+=$row[0];
		} else {
			$totals['wc']+=$row[0];
		}
	}
	return $totals;
}

function getHeaders($row) {
	$rv="<tr><td>&nbsp;</td>";
	$rv.="<td>Wincable #</td>";
	foreach($row as $k=>$v) {
		$rv.="<td>{$k}</td>";
	}
	$rv.="<td>GR Total</td>";
	$rv.="<td>WC  Prog.</td>";
	$rv.="<td>Basic + Exp</td>";
	$rv.="<td>WC Data </td>";
	$rv.="<td>WC Discount</td>";
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
