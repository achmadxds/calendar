<?php
include 'auth.php';

if(!$_SESSION['karyawan']){
	header('LOCATION: index.php');
} elseif ($_SESSION['admin']) {
	header('LOCATION: admin.php');
}

if(isset($_POST['logout'])){
	$_SESSION['karyawan'] = false;
	$_SESSION['nama'] = "";
	header('LOCATION: index.php');
	exit;
}

	// HOLIDAY ESP
function GetFirstHesp(){
	$holiday_esp 			 = [];
	$holiday_esp_value = query('SELECT `date_holiday`, `note`, `id` FROM `holiday_esp` WHERE 1 ');
	foreach ($holiday_esp_value as $value) {
		if (!array_key_exists($value['date_holiday'],$holiday_esp))
		{
			$holiday_esp[$value['date_holiday']] = $value['note'];
		}
		else {
			$holiday_esp[$value['date_holiday']] =  $holiday_esp[$value['date_holiday']] . '<br>' . $value['note'];	
		}
	}
	return $holiday_esp;
}

function GetDateHesp(){
	$dates2 			= $_POST['datesss'];
	$getdatepost1 = query('SELECT `date_holiday` ,`note`, `id` FROM `holiday_esp` WHERE `date_holiday` = "'.$dates2.'" '); 
	return $getdatepost1;
}

	// NOTES
function GetFirstNote(){
	$notes       		   = [];
	$notesvalue        = query('SELECT `nt`.`date_holiday`, 
		`us`.`name`, 
		`nt`.`note`
		FROM `notes` as `nt` LEFT JOIN `user` as `us` 
		ON `nt`.`user_id` = `us`.`id` WHERE `us`.`name`="'.$_SESSION['nama'].'" ');
	foreach ($notesvalue as $value) {
		if (!array_key_exists($value['date_holiday'],$notes))
		{
			$notes[$value['date_holiday']] =  '<b>' .$value['name']. '</b> : ' . $value['note'];
		}
		else {
			$notes[$value['date_holiday']] =  $notes[$value['date_holiday']] . '<br><b>' .$value['name']. '</b> : ' . $value['note'];	
		}
	}
	return $notes;
}

function GetDateNotes(){
	$dates1      = $_POST['date'];

	$getdatepost = query('SELECT `nt`.`user_id`,
		`nt`.`note`,
		`us`.`name`,
		`nt`.`date_holiday`,
		`nt`.`id`
		FROM `notes` as `nt` LEFT JOIN `user` as `us` 
		ON `nt`.`user_id` = `us`.`id` WHERE `nt`.`date_holiday` = "'.$dates1.'" AND `us`.`name`="'.$_SESSION['nama'].'" ');

	return $getdatepost;
}

if(isset($_POST['task'])){
	switch ($_POST['task']) {

		case 'nationholiday':
		$getnationholiday = GetFirstNationHoliday();
		$getnationholiday = json_encode($getnationholiday);
		header('Content-Type: application/json');
		echo $getnationholiday;
		break;

		case 'datess':
		$getdateposts          = GetDateNotes();
		$returnable['payload'] = $getdateposts;
		$returnable            = json_encode($returnable);
		header('Content-Type: application/json');
		echo $returnable;	
		break;

		case 'getFirstNote':
		$notedata = GetFirstNote();
		$notedata = json_encode($notedata);
		header('Content-Type: application/json');
		echo $notedata;
		break;

		case 'showholiday':
		$getdatepost           = GetDateHesp();
		$returnablee['hadehh'] = $getdatepost;
		$returnablee           = json_encode($returnablee);
		header('Content-Type: application/json');
		echo $returnablee;
		break;

		case 'getFirstHesp':
		$espdata = GetFirstHesp();
		$espdata = json_encode($espdata);
		header('Content-Type: application/json');
		echo $espdata;
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
	<h4 style="display: inline">welcome, <?= $_SESSION['nama']; ?></h4>
	<form method="POST" style="display: inline">
		<button class="pull-right btn btn-danger" name="logout">logout</button>
	</form>

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
									$is_weekend                    = in_array($days_number, [0, 6]) ? 'weekend' : '';
									$is_holiday                    = !empty($holidays[$date]) ? ' holiday' : '';
									$is_holiday_esp                = !empty($holiday_esp[$date]) ? ' note_red' : '';
									$is_flag                       = !empty($notes[$date]) ? ' note_blue' : '';
									$date_note                     = $is_flag ? ' data-toggle="popover" data-container="body" data-placement="top" data-html="true" data-trigger="hover" data-content="" data-id="" '
									: ($is_holiday ? ' data-toggle = "popover" data-container="body" data-placement="top" data-html="true" data-trigger="hover" data-content="" data-id="" '
										: ($is_holiday_esp ? ' data-toggle="popover" data-container="body" data-placement="top" data-html="true" data-trigger="hover" data-content="" data-id="" '
											: ' data-toggle="popover" data-container="body" data-placement="top" data-html="true" data-trigger="hover" data-id="'.$date.'" '));
											?>
											<td class="<?php echo $is_weekend . $is_flag . $is_holiday_esp ?>">
												<span class="clickable dates" <?php echo $date_note ?>> <?php echo date('j', strtotime($date)) ?> </span>
												<br>	
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
							<div class="getnotefromdate2 col-md-12"> </div>
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
							<div class="getnotefromdate1 col-md-12"></div>
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