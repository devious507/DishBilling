<?php

define("LOCAL_DEBUG",FALSE);
require_once("config.inc.php");


$file=$_FILES['file']['tmp_name'];
//$file="ToBeParsed.csv";


$handle = fopen($file,'r');
$status="ready";
/* 
 * Pass 1 -- Clean Up The Wincable Export
 *
 */
$newCSV=array();
while(($row = fgetcsv($handle,1000,",")) !== FALSE) {
	if($status !== "done") {
		$status = checkRow($row);
		if(!LOCAL_DEBUG) {
			if(($status !== NULL) && ($status !== "done")) {
				$newCSV[] = $row;
			}
		}
	} else {
		if(LOCAL_DEBUG)
			print "Block-Reject: ".implode($row,",")."\n";
	}
}

fclose($handle);
/*
 * Use the new CSV Data to build SQL Statements
 */

$sql='DELETE FROM wincable_data'."\n";
foreach($newCSV as $row) {
	if(preg_match("/^[0-9][0-9][0-9]-[0-9][0-9][0-9][0-9][0-9]/",$row[0])) {
		if(isset($sub)) {
			$sql.=getSQL($sub);
			$sub=array();
			$ct=0;
		} else {
			$sub=array();
			$ct=0;
		}
		$sub['number']=$row[0];
		$name=preg_replace("/'/","''",$row[1]);
		$tmp=preg_split('/ /',$row[1]);
		$ttmp=$tmp[1];
		$tmp[1]=$tmp[0];
		$tmp[0]=$ttmp;
		$name=strtoupper(implode(",",$tmp));
		$sub['name']=$name;
		if(LOCAL_DEBUG) {
			print "Sub Number: {$row[0]}.\n";
			print "Sub Name  : {$row[1]}.\n";
		}
	}
	if($row[4] != '') {
		if(!isset($sub['address1'])) {
			$sub['address1']=strtoupper($row[4]);
		} elseif(!isset($sub['address2'])) {
			$sub['address2']=strtoupper($row[4]);
		} else {
			$sub['address3']=strtoupper($row[4]);
		}
	}
	if($row[7] != '') {
		$pkg_name=preg_replace("/'/","''",$row[9]);
		$sub['services'][$ct]['qty']=$row[7];
		$sub['services'][$ct]['pkg_id']=$row[8];
		$sub['services'][$ct]['pkg_name']=$pkg_name;
		$sub['services'][$ct]['pkg_amt']=$row[12];
		$sub['services'][$ct]['pkg_total']=$row[15];
		$ct++;
	}
}

$sql_statements = preg_split("/\n/",$sql);
unset($sql);

$conn=MDB2::connect(DSN);
foreach($sql_statements as $stmt) {
	$res = $conn->query($stmt);
}
header("Location: index.php");

function getSQL($sub) {
	$sub['number']=addslashes($sub['number']);
	$sub['name']=addslashes($sub['name']);
	$sub['address1']=addslashes($sub['address1']);
	$sub['address2']=addslashes($sub['address2']);
	$sql='';
	$statements='';
	$left=array('subnum','subname','address1','address2','address3','qty','pkg_id','pkg_name','pkg_amt','pkg_total');
	foreach($sub['services'] as $svc) {
		$right=array();
		if(!isset($sub['address1']))
			$sub['address1']='';
		if(!isset($sub['address2']))
			$sub['address2']='';
		if(!isset($sub['address3']))
			$sub['address3']='';
		$right[]=$sub['number'];
		$right[]=$sub['name'];
		$right[]=$sub['address1'];
		$right[]=$sub['address2'];
		$right[]=$sub['address3'];
		$right[]=$svc['qty'];
		$right[]=$svc['pkg_id'];
		$right[]=$svc['pkg_name'];
		$right[]=$svc['pkg_amt'];
		$right[]=$svc['pkg_total'];
		$sql="INSERT INTO wincable_data (".implode(",",$left).") VALUES ('".implode("','",$right)."')\n";
		$statements.=$sql;
	}
	return $statements;
}
function checkRow($row) {
	if(preg_match("/^Date:/",$row[1])) {
		if(LOCAL_DEBUG)
			print "Rejecting ".implode(",",$row)."\n";
		return;
	}
	if(preg_match("/^Time:/",$row[1])) {
		if(LOCAL_DEBUG)
			print "Rejecting ".implode(",",$row)."\n";
		return;
	}
	if(preg_match("/^Vision Systems$/",$row[4])) {
		if(LOCAL_DEBUG)
			print "Rejecting ".implode(",",$row)."\n";
		return;
	}
	if(preg_match("/^USR code:/",$row[0])) {
		if(LOCAL_DEBUG)
			print "Rejecting ".implode(",",$row)."\n";
		return;
	}
	if(preg_match("/^FR#$/",$row[0])) {
		if(LOCAL_DEBUG)
			print "Rejecting ".implode(",",$row)."\n";
		return;
	}
	if(preg_match("/^Report Criteria:$/",$row[0])) {
		if(LOCAL_DEBUG)
			print "Rejecting ".implode(",",$row)."\n";
		return "done";
	}
	return "yes";
}
