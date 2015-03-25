<?php

require_once("config.inc.php");

$conn=MDB2::connect(DSN);
$addr=$_GET['address1'];
$sql=getSQL($addr);
$broken=false;
while(getCount($conn,$sql) == 0) { 
	$Taddr=preg_split("/ /",$addr);
	array_pop($Taddr);
	if(count($Taddr) == 0) {
		$broken=true;
		break;
	}
	$addr=implode(" ",$Taddr);
	$sql=getSQL($addr);
	//print $sql; exit();
}
$rows=getRows($conn,$sql);





function getRows($conn,$sql) {
	$res=$conn->query($sql);
	$rows='';
	while(($row=$res->fetchRow())==true) {
		$rows.=getRow($row);
	}
	return $rows;
}

function getCount($conn,$sql) {
	$res=$conn->query($sql);
	$count=0;
	while(($row=$res->fetchRow())==true) {
		$count++;
	} 
	return $count;
}
function getSQL($addr) {
	if(preg_match("/MARTIN LUTHER KING/",$addr)) {
		$addr2=preg_replace("/MARTIN LUTHER/","ML",$addr);
	}
	$sql="SELECT * FROM wincable_data WHERE address2 like '{$addr}%'";
	$sql="SELECT distinct a.subname,a.gr_address,address1,address2,address3 FROM (select subname,address2||' '||address1 as gr_address,address1||' '||address2 as bckwards,address1,address2,address3 FROM wincable_data) AS a";
	$sql.=" WHERE gr_address LIKE '{$addr}%' OR bckwards like '{$addr}%'";
	if(isset($addr2)) {
		$sql.=" OR gr_address LIKE '{$addr2}%' OR bckwards like '{$addr2}%'";
	}
        $sql.="	ORDER BY subname";
	return $sql;
}
function getRow($row) {
	$rv="<tr>";
	global $_GET;
	$gr_addr=$_GET['address1'];
	if($row[0] == $_GET['name'])
		$bg="bgcolor=\"lightgreen\"";
	foreach($row as $k=>$v) {
		if($k == 1) {
			$wc_addr=$v;
			$a=urlencode($gr_addr);
			$b=urlencode($wc_addr);
			$rv.="<td {$bg}><a href=\"createMatch.php?gr_addr={$a}&wc_addr={$b}\">{$wc_addr}</a></td>";
		} else {
			$rv.="<td {$bg}>{$v}</td>";
		}
	}
	$rv.="</tr>\n";
	return $rv;
}
?>
<html>
<head><title>Trying to find a match</title></head>
<table cellpadding="5" cellspacing="0" border="1">
<tr><th colspan="5">Group Reports Info</th></tr>
<tR><th colspan="2">Name: <?php echo $_GET['name']; ?></th><th colspan="3">Address: <?php echo $_GET['address1']; ?></th></tr>
<?php echo $rows;?>
</table>
</html>
