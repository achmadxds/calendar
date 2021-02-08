<?php  
session_start();
$connect = new mysqli('localhost', 'root', '', 'calendar');

if (mysqli_connect_errno()) {
	echo "koneksi database gagal : " . mysqli_connect_error();
	die();
}

function query($query){
	global $connect;
	$result   = mysqli_query($connect, $query);
	$rows     = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	return $rows;

}

function week_max($year){
	$date = new DateTime;
	$date->setISODate($year, 53);
	return ($date->format('W') == '53' ? 53 : 52);
}

function week_number($date){
	$week      = date('W', strtotime($date));
	$day       = date('N', strtotime($date));
	$max_weeks = week_max(date('Y', strtotime($date)));

	if ($day == 7 && $week < $max_weeks) {
		return ++$week;
	} else if ($day == 7) {
		return 1;
	} else {
		return $week;
	}
}

$period = new DatePeriod(
	new DateTime('2021-01-01'),
	new DateInterval('P1D'),
	new DateTime('2022-01-01')
);

$days 				 = [];
foreach ($period as $v) {
	$week_number = week_number($v->format('Y-m-d'));
	$week_number = str_pad($week_number, 2, '0', STR_PAD_LEFT);
	$days[$v->format('n')][$week_number][$v->format('w')] = $v->format('Y-m-d');
}

$days_name   		   = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
$months_name 		   = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

function GetFirstNationHoliday()
{
	$holiday_value = file_get_contents('https://kalenderindonesia.com/api/APIHPnFCegVK2/libur/masehi/2021');
	$holiday_value = json_decode($holiday_value, 1);

	$holidays      = [];

	foreach ($holiday_value as $value) {
		foreach ($value['holiday'] as $value1) {
			$data1 = $value1['data'];
			foreach ($data1 as $hldys) {
				if(array_key_exists("holiday", $holiday_value['data']))
					$holidays[$hldys['date']] = $hldys['name'];
			}
		}
	}

	return $holidays;
}
?>