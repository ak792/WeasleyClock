var inactiveLink = "#0000FF";
var activeLink = "#009933";


function showCheckinForm(){
	$(document).ready(function() {
		$('.forms').hide();
		$('.tabs').css({color: inactiveLink});

		$('#checkin-form').show();
		$('#checkin-button').css({color: activeLink});
	});
}

function showAddPersonForm(){
	$(document).ready(function() {
		$('.forms').hide();
		$('.tabs').css({color: inactiveLink});

		$('#add-person-form').show();	
		$('#add-person-button').css({color: activeLink});
	});
}

function showRemovePersonForm(){
	$(document).ready(function() {
		$('.forms').hide();
		$('.tabs').css({color: inactiveLink});

		$('#remove-person-form').show();
		$('#remove-person-button').css({color: activeLink});
	});
}

function showAddClockForm(){
	$(document).ready(function() {
		$('.forms').hide();
		$('.tabs').css({color: inactiveLink});

		$('#add-clock-form').show();	
		$('#add-clock-button').css({color: activeLink});
	});
}

function showDeleteClockForm(){
	$(document).ready(function() {
		$('.forms').hide();
		$('.tabs').css({color: inactiveLink});

		$('#delete-clock-form').show();
		$('#delete-clock-button').css({color: activeLink});
	});
}	


$(document).ready(function(){
	$("#checkin-button").click(function(e){
		e.preventDefault();

		showCheckinForm();
	});

	$("#add-person-button").click(function(e){
		e.preventDefault();
		
		showAddPersonForm();
	});

	$("#remove-person-button").click(function(e){
		e.preventDefault();
		
		showRemovePersonForm();
	});

	$("#add-clock-button").click(function(e){
		e.preventDefault();
		
		showAddClockForm();
	});

	$("#delete-clock-button").click(function(e){
		e.preventDefault();
		
		showDeleteClockForm();
	});


	
});
