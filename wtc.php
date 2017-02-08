<?php //"T&A mode code" changed to "T&A Mode code" 3 occurrences.

if($_FILES['csvfile']['error']){
	$phpFileUploadErrors = array(
		0 => 'There is no error, the file uploaded with success',
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		3 => 'The uploaded file was only partially uploaded',
		4 => 'No file was uploaded',
		6 => 'Missing a temporary folder',
		7 => 'Failed to write file to disk.',
		8 => 'A PHP extension stopped the file upload.'
	);
	echo ($phpFileUploadErrors[$_FILES['csvfile']['error']]);
	exit(1);
}
if(!$_FILES['csvfile']['tmp_name']){
	echo("No file was uploaded.");
	exit(1);
}
//echo ("temp_file:".$_FILES['csvfile']['tmp_name']);
$file_lines = file($_FILES['csvfile']['tmp_name']/*,FILE_IGNORE_NEW_LINES*/);
if (!$file_lines){
	echo("There are problems reading the file.");
	exit(1);
}
if(substr($file_lines[0], 0, 29) !== "#EVENTS FILE - PR Master v4.5"){
	echo("The format is not PR Master v4.5.");
	exit(1);
}
$include_mischeck=false;
if(isset($_POST["include_mischeck"]) && $_POST["include_mischeck"]==="true"){$include_mischeck=true;}

$line_arr = explode(";",rtrim($file_lines[1]));
//print_r($line_arr,false);
$dlmt = chr(hexdec($line_arr[2]));
$has_columns = ($line_arr[3]==="TRUE") ? true : false;
//echo("dlmt:".$dlmt."\r\n");
//echo("has_columns:".var_export($has_columns, true)."\r\n");

$column_names = array("Unique event ID", "Date", "Time", "User ID", "First Name", "Last Name", "T&A Mode code");
$columns_map = array();
foreach($column_names as $column_name)$columns_map[] = -1;

$i = 2;
$col_num = 0;
while($file_lines[$i][0] === "#"){
	if(preg_match("/#COLUMN\d{1,2}=TRUE/",$file_lines[$i])){
		$line_arr = explode(";",$file_lines[$i]);
		for($j=0; $j<count($column_names); $j++){
			if($columns_map[$j] == -1 && in_array($column_names[$j], $line_arr, true)){
				$columns_map[$j] = $col_num;
				break;
			}
		}
		$col_num++;
	}
	$i++;
}
//echo("columns_map:".print_r($columns_map,true)."</br>");
if(in_array(-1, $columns_map)){
	echo("Not all needed columns are found in the supplied file. Please include the following columns:\r\n");
	print_r($column_names,false);
	exit(1);
}
//echo ("line:".$i);
$line_arr = explode($dlmt, rtrim($file_lines[$i]));
//$start_date = date_create_from_format("Y-m-d", trim($line_arr[$columns_map[array_search("Date",$column_names,true)]]));
$start_date = date_create_from_format("Y-m-d,H:i:s",
				trim($line_arr[$columns_map[array_search("Date",$column_names,true)]]).','.
					trim($line_arr[$columns_map[array_search("Time",$column_names,true)]]));
/*echo(
	trim($line_arr[$columns_map[array_search("First Name",$column_names,true)]])."</br>".
	trim($line_arr[$columns_map[array_search("Last Name",$column_names,true)]])."</br>".
	trim($line_arr[$columns_map[array_search("Date",$column_names,true)]])."</br>".
	trim($line_arr[$columns_map[array_search("Time",$column_names,true)]])."</br>".
	trim($line_arr[$columns_map[array_search("T&A Mode code",$column_names,true)]])."</br>"
);*/


$one_day = new DateInterval("P1D");
$users = array();
for(;$i<count($file_lines);$i++){
	$line_arr = explode($dlmt, rtrim($file_lines[$i]));
	//print_r($line_arr,false);
	if(count($line_arr) < count($column_names))
		break;
	$userid = trim($line_arr[$columns_map[array_search("User ID",$column_names,true)]]);
	if($userid > 10000000)
		continue;
	//echo($userid."\r\n");
	$user_index = array_column1($userid, $users, 0);
	if($user_index===false){
		$user_index = count($users);
		$users[] = array($userid,
			'"'.$userid.'","'.trim($line_arr[$columns_map[array_search("First Name",$column_names,true)]]).'","'.trim($line_arr[$columns_map[array_search("Last Name",$column_names,true)]]).'"',
			clone $start_date, 16, 0, 0);
	}
	$oldMode = $users[$user_index][3];
	$newMode = trim($line_arr[$columns_map[array_search("T&A Mode code",$column_names,true)]]);
	$cdate = date_create_from_format("Y-m-d,H:i:s",
				trim($line_arr[$columns_map[array_search("Date",$column_names,true)]]).','.
					trim($line_arr[$columns_map[array_search("Time",$column_names,true)]]));
	$seconds = seconds_by_days_impr($users[$user_index][2], $cdate); //array		
	for($j = 0;$j < count($seconds); $j++){
		if(($oldMode === '0' && $newMode === '16') || ($include_mischeck && $oldMode === $newMode)){						
			$users[$user_index][4]+= $seconds[$j];
		}
		if($j != count($seconds)-1){
			$users[$user_index][1].=',"'.seconds_to_hours($users[$user_index][4]).'"'; //the whole information in csv-format
			$users[$user_index][5]+=$users[$user_index][4]; //total
			$users[$user_index][4] = 0; // seconds of work in a day
		}
	}
	$users[$user_index][2] = $cdate; //current date and time of the record
	$users[$user_index][3] = $newMode; //new T&A mode
}


$line_arr = explode($dlmt, rtrim($file_lines[--$i]));
//$end_date = date_create_from_format("Y-m-d", trim($line_arr[$columns_map[array_search("Date",$column_names,true)]]));
$end_date = date_create_from_format("Y-m-d,H:i:s",
				trim($line_arr[$columns_map[array_search("Date",$column_names,true)]]).','.
					trim($line_arr[$columns_map[array_search("Time",$column_names,true)]]));

$header='"UserID","First Name","Last Name"';
$start_date->setTime(0,0,0);
$end_date->setTime(0,0,0);
//$days_count = (($end_date->getTimestamp() - $start_date->getTimestamp()) / 86400) + 1;
//file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/working_time/wt.log',date('[Y-m-d H:i:s] - ').'start_date - '.print_r($start_date,true)."\r\n",FILE_APPEND);
//file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/working_time/wt.log',date('[Y-m-d H:i:s] - ').'end_date - '.print_r($end_date,true)."\r\n",FILE_APPEND);
//file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/working_time/wt.log',date('[Y-m-d H:i:s] - ').'days_count - '.print_r($days_count,true)."\r\n",FILE_APPEND);
while($start_date<= $end_date){
	$header .= ',"'.$start_date->format("d M").'"';
	$start_date->add($one_day);
}


header('Content-Description: File Transfer');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=wtc.csv');
header('Content-Transfer-Encoding: base64');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
//header('Content-Length: 1000');

echo($header.",\"Total\"\r\n");
//print_r($users,false);
foreach($users as $usr){
	$usr[1].= ',"'.seconds_to_hours($usr[4]).'"';
	$usr[5]+=$usr[4];
	$cdate = $usr[2]->setTime(0,0,0)->add($one_day);
	while($cdate<= $end_date){
		$usr[1].= ',"'.seconds_to_hours(0).'"';
		$cdate->add($one_day);
	}
	$usr[1].= ',"'.seconds_to_hours($usr[5]).'"';
	echo($usr[1]."\r\n");
}
exit(0);




function array_column1($needle, $haystack, $index){
	$i = 0;
	for(;$i<count($haystack); $i++)
		if($haystack[$i][$index]===$needle)
			return $i;
	return false;
}
/*
function intervals_by_days($cDateTime, $endDateTime){
	//print_r($cDateTime->format("Y-m-d,H:i:s"),false);
	//print_r($endDateTime->format("Y-m-d,H:i:s"),false);
	//echo(($cDateTime < $endDateTime)?'TRUE':'FALSE');
	$one_day_interval = new DateInterval('P1D');
	$intervals = array();	
	$cnDateTime = new DateTime("@0");
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


function seconds_by_days($cDateTime, $endDateTime){
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
*/
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
			$cDateTime->setTimestamp($midnightDateTime->getTimestamp());
			$midnightDateTime->add($one_day);
		}
		$seconds[] = $endDateTime->getTimestamp() - $cDateTime->getTimestamp();		
	}
	return $seconds;
}

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
