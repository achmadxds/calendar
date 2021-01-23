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
$holiday     		   = [ '2021-01-01' => 'Tahun Baru Masehi 2021' ];
$users      		   = query('SELECT `id`, `name` FROM `user` WHERE 1');

$holiday_esp 			 = [];
$holiday_esp_value = query('SELECT `date_holiday`, `note` FROM `holiday_esp` WHERE 1 ');
foreach ($holiday_esp_value as $value) {
	if (!array_key_exists($value['date_holiday'],$holiday_esp))
	{
		$holiday_esp[$value['date_holiday']] = $value['note'];
	}
	else {
		$holiday_esp[$value['date_holiday']] =  $holiday_esp[$value['date_holiday']] . '<br>' . $value['note'];	
	}
}

$notes       		   = [];
$notesvalue        = query('SELECT `nt`.`date_holiday`, 
	`us`.`name`, 
	`nt`.`note`
	FROM `notes` as `nt` LEFT JOIN `user` as `us` 
	ON `nt`.`user_id` = `us`.`id` ');
foreach ($notesvalue as $value) {
	if (!array_key_exists($value['date_holiday'],$notes))
	{
		$notes[$value['date_holiday']] =  '<b>' .$value['name']. '</b> : ' . $value['note'];
	}
	else {
		$notes[$value['date_holiday']] =  $notes[$value['date_holiday']] . '<br><b>' .$value['name']. '</b> : ' . $value['note'];	
	}
}

function DeleteNotes() {
	global $connect;
	$date 			 = $_POST['idDel'];
	$getdate 		 = 'DELETE FROM `notes` WHERE `id`='.$date.' ';
	if(mysqli_query($connect, $getdate)){
		return true;
	}else {
		return false;
	}
}

function GetDateNotes(){
	$dates1      = $_POST['date'];

	$getdatepost = query('SELECT `nt`.`user_id`,
		`nt`.`note`,
		`us`.`name`,
		`nt`.`date_holiday`,
		`nt`.`id`
		FROM `notes` as `nt` LEFT JOIN `user` as `us` 
		ON `nt`.`user_id` = `us`.`id` WHERE `nt`.`date_holiday` = "'.$dates1.'" ');

	return $getdatepost;
}

function GetDateHoliday(){
	$dates2 			= $_POST['datesss'];
	$getdatepost1 = query('SELECT `date_holiday` ,`note` FROM `holiday_esp` WHERE `date_holiday` = "'.$dates2.'" '); 
	return $getdatepost1;
}

function AddNotes() {
	global $connect;
	$name   = $_POST['nameCS'];
	$alasan = $_POST['alasan'];
	$datee  = $_POST['date'];

	$query = 'INSERT INTO `notes` (`user_id`, `date_holiday`, `note`) values (' . $name . ', "' . $datee . '", "' . $alasan . '") ';
	mysqli_query($connect, $query);

	return mysqli_affected_rows($connect);
}

function UpdateNotes() {
	global $connect;
	$id 		= $_POST['idUpdate'];
	$note 	= $_POST['noteUpdate'];

	$query 	= 'UPDATE `notes` SET `note`="'.$note.'" WHERE `id`='.$id.' ';
	mysqli_query($connect, $query);

	return mysqli_affected_rows($connect);
}

function AddHoliday() {
	global $connect;
	$datee 			 = $_POST['date1'];
	$nameHoliday = $_POST['nameHoliday'];

	$query = 'INSERT INTO `holiday_esp` (`date_holiday`, `note`) values ("'.$datee.'", "'.$nameHoliday.'") ';
	mysqli_query($connect, $query);

	return mysqli_affected_rows($connect);
}

if(isset($_POST['task'])){
	switch ($_POST['task']) {
		case 'notess':
		AddNotes();
		break;

		case 'datess':
		$getdateposts = GetDateNotes();
		$returnable['payload'] = $getdateposts;
		$returnable = json_encode($returnable);
		header('Content-Type: application/json');
		echo $returnable;	
		break;

		case 'holidayadd':
		AddHoliday();
		break;

		case 'showholiday':
		$getdatepost = GetDateHoliday();
		$returnablee['hadehh'] = $getdatepost;
		$returnablee = json_encode($returnablee);
		header('Content-Type: application/json');
		echo $returnablee;
		break;

		case 'deleteNote':
		header('Content-Type: application/json');
		$returnable = json_encode($returnable);
		echo $returnable;
		break;

		case 'updateNote':
		UpdateNotes();
		break;
	}
	exit;
}
?>

<!DOCTYPE html>
<html lang="">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Esoftplay's Calender</title>
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
	<h1 class="text-center">Esoftplay's Calender</h1>
	<hr>
	<div class="container">
		<?php
		foreach ($days as $months => $dates) {
			?>
			<div class="col-sm-4 text-center">
				<h2 class="text-center"><?php echo $months_name[$months] ?></h2>
				<table class="table table-bordered">
					<thead>
						<tr>
							<?php
							foreach ($days_name as $days_number => $days_initial) {
								$is_weekend = in_array($days_number, [0, 6]) ? ' weekend' : '';
								?>
								<th class="text-center<?php echo $is_weekend ?>"><?php echo $days_initial ?></th>
								<?php
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						$week_first = array_keys($days[$months]);
						$week_first = reset($week_first);

						foreach ($days[$months] as $week_number => $dates) {
							?>
							<tr>
								<?php
								if ($week_number == $week_first && count($dates) < 7) {
									?>
									<td colspan="<?php echo (7 - count($dates)) ?>"></td>
									<?php
								}
								foreach ($dates as $days_number => $date) {
									$is_weekend     = in_array($days_number, [0, 6]) ? 'weekend' : '';
									$is_holiday     = in_array($date, array_keys($holiday)) ? 1 : 0;
									$is_holiday_esp = in_array($date, array_keys($holiday_esp)) ? ' note_red' : '';
									$date_color     = $is_holiday ? ' holiday' : ' ordinary';
									$is_flag        = !empty($notes[$date]) ? ' note_blue' : '';
									$date_note      = $is_flag ? ' data-toggle="popover" data-container="body" data-placement="top" data-html="true" data-trigger="hover" data-content="' . $notes[$date] . '" data-id="'.$date.'" '
									: ($is_holiday ? ' data-toggle="popover" data-container="body" data-placement="top" data-html="true" data-trigger="hover" data-content="' . $holiday[$date] . '" data-id="'.$date.'" '
										: ($is_holiday_esp ? ' data-toggle="popover" data-container="body" data-placement="top" data-html="true" data-trigger="hover" data-content="' . $holiday_esp[$date] . '" data-id="'.$date.'" '
											: ' data-toggle="popover" data-container="body" data-placement="top" data-html="true" data-trigger="hover" data-id="'.$date.'"'));
											?>
											<td class="<?php echo $is_weekend . $is_flag . $is_holiday_esp ?>">
												<span class="clickable dates<?php echo $date_color ?>" <?php echo $date_note ?>><?php echo date('j', strtotime($date)) ?></span>
											</td>
											<?php
										}
										?>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					</div>
					<?php
					if ($months % 3 == 0) echo '<div class="clearfix"></div>';
				}
				?>
			</div>

			<!-- Add holiday -->
			<div class="modal fade" id="AddHoliday" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Tambah Hari Pengganti</h5>
						</div>
						<div class="modal-body">
							<div class="getnotefromdate2 col-md-12">
							</div>
							<div class="form-group">
								<input type="hidden" id="date2">
								<label for="nameHoliday"> Nama Hari </label>
								<input type="text" class="form-control" id="nameHoliday">
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" id="saveHoliday" class="btn btn-success">Save changes</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Add Notes -->
			<div class="modal fade" id="addNotes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Ada yang ijinkah??</h5>
						</div>
						<div class="modal-body">
							<div class="getnotefromdate1 col-md-12">
							</div>
							<div class="row p-2">
								<div class="col-md-6">
									<div class="form-group">
										<input type="hidden" id="date">
										<label for="nameCS" class="text-primary">Nama :</label><br>
										<select class="form-control" id="nameCS">
											<option disabled selected> Pilih </option>
											<?php
											foreach ($users as $value) {
												?>
												<option value="<?php echo $value['id']; ?>" class="form-control"> <?php echo $value['name']; ?> </option>
												<?php
											}
											?>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="alasan" class="text-primary">Alasan :</label><br>
										<textarea class="form-control" rows="1" id="alasan" placeholder="isi deskrisi..."></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" id="saveNotes" class="btn btn-success">Save changes</button>
						</div>
					</div>
				</div>
			</div>
		</body>
		<!-- jQuery -->
		<script src="//code.jquery.com/jquery.js"></script>
		<!-- Bootstrap JavaScript -->
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
		<script src="assets/js/js.js"></script>

		</html>