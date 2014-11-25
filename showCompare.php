<?php

require_once("config.inc.php");

$wc_total=0;
$gr_total=0;
$addr=$_GET['addr1'];
$sql0="SELECT wincable FROM addr_matches WHERE group_report='{$addr}'";
$conn=MDB2::connect(DSN);
$res=$conn->query($sql0);
while(($row=$res->fetchRow())) {
	$addr=$row[0];
}

$t1=getGroupReports($conn,$_GET['addr1']);
$t2=getWincable($conn,$addr);

$wc_total=sprintf("$ %.02f",$wc_total);
$gr_total=sprintf("$ %.02f",$gr_total);

$topLine=$_GET['addr1'];
if($wc_total == "$ 0.00") {
	$name=getName($conn,$_GET['addr1']);
	$topLine.=' | <a href="fixMatches.php?address1='.$_GET['addr1'].'&name='.$name.'">Find Matches</a>';
}
function getName($conn,$addr) {
	$sql="select distinct subname from group_reports WHERE address1='{$addr}'";
	$res=$conn->query($sql);
	$row=$res->fetchRow();
	return $row[0];
}
function getGroupReports($conn,$addr) {
	global $gr_total;
	global $_GET;
	$rv="<table cellpadding=\"2\" cellspacing=\"0\" border=\"1\">\n";
	$sql="SELECT subname,dates,description,quantity,unit_price,amount FROM group_reports WHERE address1='{$addr}'";
	$res=$conn->query($sql);
	if(PEAR::isError($res)) {
		print $sql."<br>";
		print $res->getMessage();
		exit();
	}
	$header=false;
	while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
		if($header == false) {
			$rv.=getSqlHeaders($row);
			$header=true;
		}
		$rv.="<tr>";
		foreach($row as $k=>$v) {
			if($k == 'amount') {
				$rv.=sprintf("<td align=\"right\">$ %.02f</td>",$v);
			} else {
				$rv.="<td>{$v}</td>";
			}
		}
		if($row['description'] == 'SUBTOTAL') {
			$gr_total+=$row['amount'];
		}
		$rv.="</tr>\n";
	}
	$rv.="</table>\n";
	return $rv;
}
function getWincable($conn,$addr) {
	global $wc_total;
	$rv="<table cellpadding=\"2\" cellspacing=\"0\" border=\"1\">\n";
	$sql="select a.subnum,a.subname,a.qty,a.pkg_name,a.pkg_amt,a.pkg_total FROM (select *,address2||' '||address1 AS addr from wincable_data) AS a WHERE addr='{$addr}'";
	$rv.="<!-- {$sql} -->\n";
	$res=$conn->query($sql);
	if(PEAR::isError($res)) {
		print $sql."<br>";
		print $res->getMessage();
		exit();
	}
	$header=false;
	while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
		if($header == false) {
			$rv.=getSqlHeaders($row);
			$header=true;
		}
		$rv.="<tr>";
		foreach($row as $k=>$v) {
			switch($k) {
			case "pkg_total":
				$v=preg_replace("/,/",'',$v);
				$v=floatval($v);
				$wc_total+=$v;
				$rv.=sprintf('<td align="right">$ %.02f</td>',$v);
				break;
			case "pkg_amt":
				$v=preg_replace("/,/",'',$v);
				$v=floatval($v);
				$rv.=sprintf('<td align="right">%.02f</td>',$v);
				break;
			default:
				$rv.="<td>{$v}</td>";
				break;
			}
		}
		$rv.="</tr>";
	}
	$rv.="</table>\n";
	return $rv;
}


function getSqlHeaders($row) {
	$rv="<tr>";
	foreach($row as $k=>$v) {
		$rv.="<td>{$k}</td>\n";
	}
	$rv.="</tr>";
	return $rv;
}
?>
<html>
<head>
<title>Comparision View</title>
</head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td colspan="2" align="center"><?php echo $topLine; ?></td></tr>
<tr><td align="right"><?php echo $gr_total;?></td><td align="right"><?php echo $wc_total; ?></td></tr>
<tr><td valign="top"><?php echo $t1; ?></td><td valign="top"><?php echo $t2; ?></td></tr>
</table>
</body>
</html>
