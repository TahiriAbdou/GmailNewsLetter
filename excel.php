<?php 

$mails = file_get_contents('mails');

$mails = explode("\n",$mails);

$sheet = [];

foreach ($mails as $key => $value) {
	$c = explode(',',$value);
	$name = current($c);
	$email = end($c);
	$email = trim($email);
	if(empty($email) || strlen($email)==0){
		$pattern = '/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i';
		preg_match($pattern,$name,$matches);
		$email = current($matches);
	}
	if(!empty($email)) $sheet[] = compact('name','email');
}



$flag = false;
$s = '';
foreach($sheet as $row) {
	if(!$flag) {
		$s .= implode("\t", array_keys($row)) . "\r\n";
		$flag = true;
	}
	if(strlen($row['name'])!==0 || strlen($row['email'])!==0)
	$s .= implode("\t", array_values($row)) . "\r\n";
}

header("Content-type: application/vnd.ms-excel");
header("Content-disposition: attachment; filename=contacts-location-voiture.xls");
print $s;
exit;