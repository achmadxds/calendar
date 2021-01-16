<?php
$connect = new mysqli('localhost', 'root', '', 'calendarDB');

if (mysqli_connect_errno()) {
	echo "koneksi database gagal : " . mysqli_connect_error();
	die();
}

function query($query)
{
	global $connect;
	$result   = mysqli_query($connect, $query);
	$rows     = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	return $rows;
}

function week_max($year)
{
	$date = new DateTime;
	$date->setISODate($year, 53);
	return ($date->format('W') == '53' ? 53 : 52);
}

function week_number($date)
{
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

$days 					= [];
foreach ($period as $v) {
	$week_number = week_number($v->format('Y-m-d'));
	$week_number = str_pad($week_number, 2, '0', STR_PAD_LEFT);

	$days[$v->format('n')][$week_number][$v->format('w')] = $v->format('Y-m-d');
}

$days_name   		= ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
$months_name 		= ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$holiday     		= [ '2021-01-01' => 'Tahun Baru Masehi 2021' ];
$holiday_esp 		= [
										'2021-01-21' => 'Tahun Baru Masehi 2021',
										'2021-01-07' => 'Libur esoftplay Tahun Baru Masehi 2021',
										'2021-01-21' => 'Tahun Baru Masehi 2021',
									];

$notes       		= [];
$users      		= query('SELECT `id`, `name` FROM `user` WHERE 1');
$notesvalue     = query('SELECT `nt`.`date`, 
										`us`.`name`, 
										`nt`.`note`
										FROM `notes` as `nt` LEFT JOIN `user` as `us` 
										ON `nt`.`id_user` = `us`.`id` ');
$holday_esp 		= query('SELECT `date`, `note` FROM `holiday__esp` WHERE 1 ');

foreach ($notesvalue as $value) {
	$notes[$value['date']] = '<b>' .$value['name']. '</b> : ' . $value['note'];
}

function GetFromDate(){
	global $connect;
	$datesss     = $_POST['date'];

	$getdatepost = query('SELECT `nt`.`id_user`,
		`nt`.`note`,
		`us`.`name`,
		`nt`.`date`
		FROM `notes` as `nt` LEFT JOIN `user` as `us` 
		ON `nt`.`id_user` = `us`.`id` WHERE `nt`.`date` = "'.$datesss.'" ');

	return $getdatepost;
}

function AddNotes() {
	global $connect;
	$name   = $_POST['nameCS'];
	$alasan = $_POST['alasan'];
	$datee  = $_POST['date'];

	$query = 'INSERT INTO `notes` (`id_user`, `date`, `note`) values (' . $name . ', "' . $datee . '", "' . $alasan . '") ';
	mysqli_query($connect, $query);

	return mysqli_affected_rows($connect);
}

function AddHoliday() {
	global $connect;
	$datee 			 = $_POST['date1'];
	$nameHoliday = $_POST['nameHoliday'];

	$query = 'INSERT INTO `holiday__esp` (`date`, `note`) values ("'.$datee.'", "'.$nameHoliday.'") ';
	mysqli_query($connect, $query);

	return mysqli_affected_rows($connect);
}

if(isset($_POST['saveNote'])){
	if($_POST['saveNote'] == 'notess'){
		AddNotes();
	} elseif ($_POST['saveNote'] == 'datess') {
		$getdateposts = GetFromDate();
		$returnable['payload'] = $getdateposts;
		$returnable = json_encode($returnable);
		header('Content-Type: application/json');
		echo $returnable;
	}
	exit;
}

if(isset($_POST['saveHoliday'])){
	if($_POST['saveHoliday'] == 'holidayadd'){
		AddHoliday();
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

	<!-- Bootstrap CSS -->
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
				<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
				<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
			<![endif]-->
			<style type="text/css">
				.weekend {
					color: #F99;
				}

				.holiday {
					color: #f33;
					cursor: pointer;
				}

				.ordinary {
					cursor: pointer;
				}

				.note_red,
				.note_blue {
					position: relative;
					cursor: pointer;
				}

				.note_red:after,
				.note_blue:after {
					content: "";
					position: absolute;
					top: 0;
					right: 0;
					width: 0;
					height: 0;
					display: block;
					border-left: 10px solid transparent;
					border-bottom: 10px solid transparent;
				}

				.note_red:after {
					border-top: 10px solid #F00;
				}

				.note_blue:after {
					border-top: 10px solid #00F;
				}
			</style>
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
						<div class="modal-dialog" role="document">angular
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Tambah Hari Pengganti</h5>
								</div>
								<div class="modal-body">
									<form method="POST">
										<div class="form-group">
											<div class="getnotefromdate">
											</div>
											<input type="hidden" id="date">
											<label for="nameHoliday"> Nama Hari </label>
											<input type="text" class="form-control" id="nameHoliday">
										</div>
									</form>
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
									<form method="post">
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
										<button type="button" id="saveNote" class="btn btn-success">Save changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					<!-- jQuery -->
					<script src="//code.jquery.com/jquery.js"></script>
					<!-- Bootstrap JavaScript -->
					<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
				</body>

				</html>


				<script type="text/javascript">
					var delay = 200;
					clicks = 0;
					timer = null;

					$(document).ready(function() {
						$('[data-toggle="popover"]').popover();

						$(".clickable").click(function(e) {
							clicks++;
							var values = $(this).data("id");
							$("#date").val(values);

							$.ajax({ 
								type: "POST", 
								data : {saveNote : "datess", date : values} , 
								success: function(data, textStatus, jqXHR) {
									var outerdiv = $(".getnotefromdate1");
									outerdiv.empty();
									$.each(data['payload'], function(i, val){
										outerdiv.append(`<label id="namaorang">${val['name']}</label>
										<input type="text" class="form-control" value="${val['note']}" readonly>	
										<hr>`);
									});
								},
								error: function (jqXHR, textStatus, errorThrown) {
									console.log(" salahom");
								}
							});

							if (clicks == 1) {
								timer = setTimeout(function() {
									$('#addNotes').modal({
										show: true
									});
									clicks = 0;
								}, delay);
							} else {
								clearTimeout(timer);
								clicks = 0;
							}
						});

						$(".clickable").dblclick(function() {
							$('#AddHoliday').modal({
								show: true
							});
						});

						$("#saveNote").click(function(){
							var dateraw = $("#date").val();
							var nameCSraw = $("#nameCS").val();
							var alasanraw = $("#alasan").val();
							if(dateraw != null && nameCSraw != null && alasanraw != ""){
								$.ajax({ 
									type: "POST", 
									data : {saveNote : "notess", date : dateraw,  nameCS : nameCSraw, alasan : alasanraw} , 
									success: function(data, textStatus, jqXHR) { 
										$('#addNotes').modal('toggle');
									},
									error: function (jqXHR, textStatus, errorThrown) {
									console.log(textStatus, " salahom");// if there is an error
								}
							});
							}
						});

						$("#saveHoliday").click(function(){
							var dateeraw = $("#date").val();
							var dayraw 	 = $("#nameHoliday").val();
							console.log(dateeraw, dayraw);
							if(dateeraw != null && dayraw != null){
								$.ajax({
									type: "POST",
									data: {saveHoliday : "holidayadd", date1 : dateeraw, nameHoliday : dayraw} ,
									success: function(data) {
										console.log("Berhasil");
										$('#AddHoliday').modal('toggle');	
									},
									error: function() {
										console.log("GAGAL");
									}
								});
							}
						});
					});
				</script>