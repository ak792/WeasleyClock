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
