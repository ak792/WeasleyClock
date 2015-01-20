var inactiveLink = "#0000FF";
var activeLink = "#009933";

$(document).ready(function() {
	$('#names-select').change(function(){

		if ($('#names-select').val() == -1){
			$('#new-name-input').show();
		}
		else {
			$('#new-name-input').hide();
		}
	});
});

function updateSelectNewPerson(personID, name){
	$(document).ready(function() {

		//best = insert and maintain sorted order
		$('#names-select')
			.prepend(
				$("<option></option>")
					.attr("value", personID)
					.text(name));
	});
}

function removePersonFromSelect(personID){
	$(document).ready(function() {
		$("#names-select option[value='" + personID + "']").remove();
	});
}


function showRemovePersonForm(){
	$(document).ready(function() {
		$('#add-person-form').hide();
		$('#checkin-form').hide();
		$('#remove-person-form').show();

		$('#add-person-button').css({color: inactiveLink});
		$('#checkin-button').css({color: inactiveLink});
		$('#remove-person-button').css({color: activeLink});
	});
}	

function showCheckinForm(){
	$(document).ready(function() {
		$('#add-person-form').hide();
		$('#checkin-form').show();
		$('#remove-person-form').hide();
		
		$('#add-person-button').css({color: inactiveLink});
		$('#checkin-button').css({color: activeLink});
		$('#remove-person-button').css({color: inactiveLink});
	});
}

function showAddPersonForm(){
	$(document).ready(function() {		
		$('#add-person-form').show();
		$('#checkin-form').hide();
		$('#remove-person-form').hide();
		
		$('#add-person-button').css({color: activeLink});
		$('#checkin-button').css({color: inactiveLink});
		$('#remove-person-button').css({color: inactiveLink});
	});
}


$(document).ready(function(){
	$("#checkin-button").click(function(e){
		e.preventDefault();

		showCheckinForm();
	});

	$("#remove-person-button").click(function(e){
		e.preventDefault();
		
		showRemovePersonForm();
	});

	$("#add-person-button").click(function(e){
		e.preventDefault();
		
		showAddPersonForm();
	});

});
