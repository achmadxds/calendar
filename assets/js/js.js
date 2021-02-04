$(document).ready(function() {
	GetFirstNationHoliday();
	GetFirstNote();
	GetFirstHesp();
	
	$('[data-toggle="popover"]').popover();
	$('[data-booked="true"]').popover({
		html: true,
	});

	$(".clickable").click(function(e) {
		$('#addNotes').modal({
			show: true
		});

		var values = $(this).data("id");
		$("#date").val(values);

		ShowDataModalNote(values);
	});

	$(".clickable").bind("contextmenu" ,function(e) {
		$('#AddHoliday').modal({
			show: true
		});

		var values = $(this).data("id");
		$("#date2").val(values);

		ShowDataModalESP(values);
	});

	$("#saveNotes").click(function(){
		var dateraw 	= $("#date").val();
		var nameCSraw = $("#nameCS").val();
		var alasanraw = $("#alasan").val();
		if(dateraw != null && nameCSraw != null && alasanraw != ""){
			$.ajax({ 
				type: "POST", 
				data : {task : "notess", dateNotes : dateraw,  nameCS : nameCSraw, alasan : alasanraw} , 
				success: function(data, textStatus, jqXHR) { 
					GetFirstNote();
					ShowDataModalNote(dateraw);
				}
			});
		}
	});

	$("#saveHoliday").click(function(){
		var dateeraw = $("#date2").val();
		var dayraw 	 = $("#nameHoliday").val();
		if(dateeraw != null && dayraw != null){
			$.ajax({
				type: "POST",
				data: {task : "holidayadd", date1 : dateeraw, nameHoliday : dayraw} ,
				success: function(data) {
					GetFirstHesp();
					ShowDataModalESP(dateeraw);
				}
			});
		}
	});
});

function DeleteNote(id) {
	var values = $('#group-' + id + ' .input-date').val();

	$.ajax({
		type : "POST",
		data : {task : "deleteNote", idDel : id} , 
		success : function(data){
			GetFirstNote();
			ShowDataModalNote(values);
			//Hanya Karena The Last cant gone from this freking code
			// setTimeout(function () {
			// 	history.go(0);
			// }, 500);
		}
	});
}

function DeleteHolidayESP(id) {
	var values = $('#groups-' + id + ' .input-date').val();

	$.ajax({
		type : "POST",
		data : {task : "deleteESP", idDelesp : id} , 
		success : function(data){
			GetFirstHesp();
			ShowDataModalESP(values);
			//Hanya Karena The Last cant gone from this freking code
			// setTimeout(function () {
			// 	history.go(0);
			// }, 500);
		}
	});
}

function ShowButton(id) {
	$('#group-' + id + ' .input-text').attr("readonly", false);
	$('#group-' + id + ' .save-button').css("display", "block");
}

function ShowButtons(id) {
	$('#groups-' + id + ' .input-text').attr("readonly", false);
	$('#groups-' + id + ' .save-button').css("display", "block");
}

function UpdateNotes(id) {
	var note 	 = $('#group-' + id + ' .input-text').val();
	var values = $('#group-' + id + ' .input-date').val();

	$.ajax({
		type : "POST",
		data : {task : "updateNote", idUpdate : id, noteUpdate : note},
		success : function(data) {
			GetFirstNote();
			ShowDataModalNote(values);
		}
	});
}

function UpdateHolidayESP(id) {
	var note 	 = $('#groups-' + id + ' .input-text').val();
	var values = $('#groups-' + id + ' .input-date').val();

	$.ajax({
		type : "POST",
		data : {task : "ESPupdate", idUpdateESP : id, noteESPupdate : note},
		success : function(data) {
			GetFirstHesp();
			ShowDataModalESP(values);
		}
	});
}

function GetFirstNationHoliday() {
	$.ajax({
		type : "POST",
		data : {task : "nationholiday"},
		success : function(data) {
			$.each(data, function(i, val){
				$('[data-id = "'+i+'"]').parent().addClass("holiday");
				$('[data-id = "'+i+'"]').attr("data-content", val);
			});
		}, error : function(textStatus){
			console.log("Gagal dapet");
		}
	});
}

function GetFirstNote()	{
	$.ajax({
		type : "POST",
		data : {task : "getFirstNote"},
		success : function(data) {
			$.each(data, function(i, val){
				$('[data-id = "'+i+'"]').parent().addClass("note_blue");
				$('[data-id = "'+i+'"]').attr("data-content", val);
			});
		}
	});
}

function GetFirstHesp()	{
	$.ajax({
		type : "POST",
		data : {task : "getFirstHesp"},
		success : function(data) {
			$.each(data, function(i, val){
				$('[data-id="'+i+'"]').parent().addClass("note_red");
				$('[data-id="'+i+'"]').attr("data-content", val);
			});
		}
	});
}

function ShowDataModalNote(values){
	$.ajax({ 
		type: "POST", 
		data : {task : "datess", date : values} , 
		success: function(data) {
			var outerdiv = $(".getnotefromdate1");
			outerdiv.empty();
			$.each(data['payload'], function(i, val){
				outerdiv.append(
					`
					<div class="dropdown" id="group-${val['id']}">
					<label id="namaorang"> ${val['name']} </label>

					<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
					<span class="caret"></span>
					</button>

					<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
					<li role="presentation"><a tabindex="-1" onclick="DeleteNote(${val['id']})" >Delete</a></li>
					<li role="presentation"><a tabindex="-1" onclick="ShowButton(${val['id']})">Update</a></li>
					<input type="hidden" class="input-date" value="${val['date_holiday']}">
					</ul>

					<input type="text" class="form-control input-text" value="${val['note']}" readonly>
					<div class="save-button" style="display : none">
					<button type="button" class="btn btn-success updateNote" onclick="UpdateNotes(${val['id']})">Save</button>
					</div>

					</div>
					<hr>
					`
					);
			});
		}
	});
}

function ShowDataModalESP(values){
	$.ajax({ 
		type: "POST", 
		data : {task : "showholiday", datesss : values} , 
		success: function(data) {
			var outerdiv = $(".getnotefromdate2");
			outerdiv.empty();
			$.each(data['hadehh'], function(i, val){
				outerdiv.append(
					`
					<div class="dropdown" id="groups-${val['id']}">

					<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
					<span class="caret"></span>
					</button>

					<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
					<li role="presentation"><a tabindex="-1" onclick="DeleteHolidayESP(${val['id']})" >Delete</a></li>
					<li role="presentation"><a tabindex="-1" onclick="ShowButtons(${val['id']})">Update</a></li>
					<input type="hidden" class="input-date" value="${val['date_holiday']}">
					</ul>

					<input type="text" class="form-control input-text" value="${val['note']}" readonly>
					<div class="save-button" style="display : none">
					<button type="button" class="btn btn-success updateESP" onclick="UpdateHolidayESP(${val['id']})">Save</button>
					</div>

					</div>
					<hr>
					`
					);
			});
		}
	});
}