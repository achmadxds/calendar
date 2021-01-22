$(document).ready(function() {
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

		$.ajax({ 
			type: "POST", 
			data : {task : "datess", date : values} , 
			success: function(data) {
				var outerdiv = $(".getnotefromdate1");
				outerdiv.empty();
				$.each(data['payload'], function(i, val){
					outerdiv.append(
						`
						<div class="dropdown">
						<label id="namaorang"> ${val['name']} </label>
						
						<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
						<span class="caret"></span>
						</button>

						<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
						<li role="presentation"><a tabindex="-1" onclick="DeleteData(${val['id']})" >Delete</a></li>
						<li role="presentation"><a tabindex="-1" onclick="UpdateData(${val['id']})">Update</a></li>
						</ul>

						</div>
						<input type="text" id="inputText" class="form-control" value="${val['note']}" readonly>
						<div id="saveButton" name="saveButton" style="display : none">
							<button type="button" class="btn btn-success updateNote">Save</button>
						</div>
						<hr>
						`
						);
				});
			}
		});
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
				console.log(textStatus);
			}
		});
	});

	$("#saveNotes").click(function(){
		var dateraw = $("#date").val();
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

	$(document).on("click", ".updateNote", function() {
		
	});
});

function DeleteData(id) {
	$.ajax({
		type : "POST",
		data : {task : "deleteData", idDel : id} , 
		success : function(data){
			alert("Terhapus");
		}
	});
}

function UpdateData(id) {
	var style = $('[name=saveButton]');
	style.style.display = 'block';
}