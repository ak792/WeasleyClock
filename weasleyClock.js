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
		$('#checkin-form').hide();
		$('#remove-person-form').show();
	});
}	

function showCheckinForm(){
	$(document).ready(function() {
		$('#remove-person-form').hide();
		$('#checkin-form').show();
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
});
