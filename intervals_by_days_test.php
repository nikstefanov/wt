<?php
echo ("Hi!\r\n");
$arr = intervals_by_days(
		date_create_from_format("Y-m-d,H:i:s","2016-03-20,10:11:33"),
			date_create_from_format("Y-m-d,H:i:s","2016-03-22,20:11:33"));
print_r($arr,false);
exit(0);

function intervals_by_days($stDateTime, $endDateTime){
	//print_r($stDateTime->format("Y-m-d,H:i:s"),false);
	//print_r($endDateTime->format("Y-m-d,H:i:s"),false);
	//echo(($stDateTime < $endDateTime)?'TRUE':'FALSE');
	$one_day_interval = new DateInterval('P1D');
	$intervals = array();
	$cDateTime = $stDateTime;
	$cnDateTime = new DateTime();
	while($cDateTime < $endDateTime){
		$cnDateTime->setTimestamp($cDateTime->getTimestamp());
		$cnDateTime->setTime(0,0,0)->add($one_day_interval);
		//print_r($cDateTime->format("Y-m-d,H:i:s"),false);echo("\r\n");
		//print_r($cnDateTime->format("Y-m-d,H:i:s"),false);echo("\r\n");
		if($endDateTime < $cnDateTime){
			$DateInterval1 = $cDateTime->diff($endDateTime);
		}else{
			$DateInterval1 = $cDateTime->diff($cnDateTime);
		}
		$cDateTime->setTimestamp($cnDateTime->getTimestamp());
		$intervals[] = $DateInterval1;
	}
	return $intervals;
}
?>