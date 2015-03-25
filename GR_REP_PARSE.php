<?php


//phpinfo(); exit();

$file=$_FILES['file']['tmp_name'];
//$file="Group_Reports.csv";

$handle=fopen($file,'r');


$sql[]="DELETE FROM group_reports";
while(($row = fgetcsv($handle,1000,",")) !== FALSE) {
	if($row[0] !== 'RETURN_NAME') {
		$right=array();
		$left=array('subname','address1','category_name','dates','description','quantity','unit_price','amount');
		$right[]=strtoupper($row[24]);				// subname
		//$right[]=strtoupper($row[25]);				// address1
		// Added code to strip # symbols on 3/25/2015
		$right[]=preg_replace("/#/","",strtoupper($row[25]));	// address1
		$right[]=$row[30];					// category_name
		$right[]=$row[31];					// dates
		$right[]=preg_replace("/'/","''",$row[32]);		// description
		$right[]=$row[33];					// quanity
		$right[]=$row[34];					// unit price
		$right[]=$row[35];					// amount
		foreach($right as $k=>$v) {
			$right[$k]=addslashes($v);
		}
		$s="INSERT INTO group_reports (".implode(",",$left).") VALUES ('".implode("','",$right)."')";
		$sql[]=$s;
	}
}

fclose($handle);
require_once("config.inc.php");


$conn=MDB2::connect(DSN);
foreach($sql as $stmt) {
	$conn->query($stmt);
}

header("Location: index.php");
?>
