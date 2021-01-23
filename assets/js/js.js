$(document).ready(function() {
	GetFirstNote();

	$("#addNotes").on('hide.bs.modal', function(){
		GetFirstNote();
	});

	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})

	$(function () {
		$('[data-toggle="popover"]').popover()
	})

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

		$.ajax({
			type: "POST",
			data : {task : "showholiday", datesss : values},
			success: function(data){
				var outerdiv2 = $(".getnotefromdate2");
				outerdiv2.empty();
				$.each(data['hadehh'], function(i, val){
					outerdiv2.append(`<input type="text" class="form-control" value="${val['note']}" readonly> 
						<hr> `);
				});
			}, 
			error: function(textStatus) {
			}
		});
	});

	$("#saveNotes").click(function(){
		var dateraw 	= $("#date").val();
		var nameCSraw = $("#nameCS").val();
		var alasanraw = $("#alasan").val();
		if(dateraw != null && nameCSraw != null && alasanraw != ""){
			$.ajax({ 
				type: "POST", 
				data : {task : "notess", date : dateraw,  nameCS : nameCSraw, alasan : alasanraw} , 
				success: function(data, textStatus, jqXHR) { 
					$('#addNotes').modal('toggle');
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
					$('#AddHoliday').modal('toggle');	
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
			ShowDataModalNote(values);
		}
	});
}

function ShowButton(id) {
	$('#group-' + id + ' .input-text').attr("readonly", false);
	$('#group-' + id + ' .save-button').css("display", "block");
}

function UpdateNotes(id) {
	var note 	 = $('#group-' + id + ' .input-text').val();
	var values = $('#group-' + id + ' .input-date').val();

	$.ajax({
		type : "POST",
		data : {task : "updateNote", idUpdate : id, noteUpdate : note},
		success : function(data) {
			ShowDataModalNote(values);
		}
	});
}
var temp = [];
function GetFirstNote()	{
	$.ajax({
		type : "POST",
		data : {task : "getFirstNote"},
		success : function(data) {
			$.each(data, function(i, val){
				if(temp.includes(i)){
					console.log("BWA BAW");
				}
				$('[data-id="'+i+'"]').parent().addClass("note_blue");
				$('[data-id="'+i+'"]').attr("data-content", val);
				temp.push(i);
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