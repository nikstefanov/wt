<?php
echo ("Hi!\r\n");
$one_day = new DateInterval("P1D");
$arr = seconds_by_days_impr(
		date_create_from_format("Y-m-d,H:i:s","2016-03-26,11:11:33"),
			date_create_from_format("Y-m-d,H:i:s","2016-03-30,20:11:33"));
print_r($arr,false);
echo("\n");
exit(0);

function seconds_by_days($cDateTime, $endDateTime){
	//print_r($cDateTime->format("Y-m-d,H:i:s"),false);
	//print_r($endDateTime->format("Y-m-d,H:i:s"),false);
	//echo(($cDateTime < $endDateTime)?'TRUE':'FALSE');
	$one_day_interval = new DateInterval('P1D');
	$seconds = array();	
	$midnightDateTime = new DateTime("@".$cDateTime->getTimestamp());
	$midnightDateTime->setTime(0,0,0);
	$midnightDateTime->setTimestamp($midnightDateTime->getTimestamp() + 86400);	
	if($endDateTime < $midnightDateTime){
		$seconds[] = $endDateTime->getTimestamp() - $cDateTime->getTimestamp();
	}else{		
		$seconds[] = $midnightDateTime->getTimestamp() - $cDateTime->getTimestamp();
		$cDateTime->setTimestamp($midnightDateTime->getTimestamp() + 86400);
		while($cDateTime<$endDateTime){			
			$seconds[] = 86400;
			$cDateTime->setTimestamp($cDateTime->getTimestamp() + 86400);
		}		
		$seconds[] = $endDateTime->getTimestamp() - $cDateTime->getTimestamp() + 86400;
	}
	return $seconds;
}

function seconds_by_days_impr($cDateTime, $endDateTime){
	global $one_day;
	$seconds = array();	
	$midnightDateTime = clone $cDateTime;
	$midnightDateTime->setTime(0,0,0)->add($one_day);
	if($endDateTime < $midnightDateTime){
		$seconds[] = $endDateTime->getTimestamp() - $cDateTime->getTimestamp();
	}else{
		while($midnightDateTime<$endDateTime){
			
			$seconds[] = $midnightDateTime->getTimestamp() - $cDateTime->getTimestamp();
			//echo(print_r($midnightDateTime->format("Y-m-d,H:i:s e"),true)." - ".print_r($cDateTime->format("Y-m-d,H:i:s e"),true)." = ".$seconds."\n");
			$cDateTime->setTimestamp($midnightDateTime->getTimestamp());
			$midnightDateTime->add($one_day);
		}
		$seconds[] = $endDateTime->getTimestamp() - $cDateTime->getTimestamp();
		//echo(print_r($endDateTime->format("Y-m-d,H:i:s e"),true)." - ".print_r($cDateTime->format("Y-m-d,H:i:s e"),true)." = ".$seconds."\n");
	}
	return $seconds;
}
?>