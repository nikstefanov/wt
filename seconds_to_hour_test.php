<?php
$a=86400;
echo ($a.'sec. = '.seconds_to_hours($a)."\n");
function seconds_to_hours($s){
	$sec=$s%60;
	$s-=$sec;
	$s/=60;
	$min=$s%60;
	$s-=$min;
	$s/=60;
	return sprintf("%02d:%02d:%02d",$s,$min,$sec);
}
?>