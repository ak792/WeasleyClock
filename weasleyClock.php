<?php

//TOOD: 
//support reloading a person's data
//allow for multiple clocks	
//make pretty

//stretch: port to an app

	require_once 'DBHandler.php';

	function run(){
		$dbHandler = new DBHandler();

	
		insertPerson($dbHandler);
		insertCheckin($dbHandler);
		removePerson($dbHandler);

		buildNavbar();
		buildCheckinForm($dbHandler);
		buildAddPersonForm($dbHandler);
		buildRemovePersonForm($dbHandler);

		displayCurrCheckins($dbHandler);

		echo "
		<script>
			showCheckinForm();
		</script>";
	}

	function buildNavbar(){
		?>
			<div id='navbar'>
				<span>
					<a href="" id='checkin-button'>
						Check In
					</a>
				</span>
				
				&nbsp &nbsp
				<span>
					<a href="" id='add-person-button'>
						Add Person
					</a>
				</span>


				&nbsp &nbsp
				<span>
					<a href="" id='remove-person-button'>
						Remove Person
					</a>
				</span>
			</div>
		<?php
	}
	
	function buildCheckinForm($dbHandler){
		?>
		<form name="checkin-form" id="checkin-form" action="" method="post">

			<label for="clock">Clock: </label>
			<?php

			showClocksSelect($dbHandler);

			?>

			<br>

			<label for="names">Name: </label>
			<?php

			$formName = "checkin-form";
			showAccountsSelect($dbHandler, $formName);

			?>

			 <br>

			<label for="location">Location: </label> 
			<select name="location" form="checkin-form" required>
				<option value="Home">Home</option>
				<option value="Class">Class</option>
				<option value="Studying">Studying</option>
				<option value="Meeting">Meeting</option>
				<option value="Gym">Gym</option>
				<option value="Partying">Partying</option>
				<option value="Other">Other</option>
			</select>

			<input type="submit" value="Submit!">
		</form>
		<?php
	}

	function buildAddPersonForm($dbHandler){
		?>
		<form name="add-person-form" id="add-person-form" action="" method="post">
			<label for="clock">Clock: </label>
			<?php

			showClocksSelect($dbHandler);

			?>
			
			<br>

			<input type="text" id='new-name-input' name="new-name-input" form="add-person-form" value="VP" required>
			<input type="submit" value="Add!">
		</form>
			

		<?php
	}

	function buildRemovePersonForm($dbHandler){
		?>
		<form name="remove-person-form" id="remove-person-form" action="" method="post">

			<label for="clock">Clock: </label>
			<?php

			showClocksSelect($dbHandler);

			?>

			<br>
				
			<label for="names">Name: </label>

			<?php
			$formName = "remove-person-form";
			showAccountsSelect($dbHandler, $formName);

			?>
			
			<input type="hidden" form="remove-person-form" id="remove-flag" name="remove" value="remove" required>

			<input type="submit" value="Remove!">
		</form>

		<?php
	}

	
		
	function showClocksSelect($dbHandler){
		$clocks = $dbHandler->getAllClocks();

		if ($clocks->num_rows > 0){
			echo '<select id="clocks-select" name="clock" form="checkin-form" required>';

			while ($row = $clocks->fetch_assoc()){
				$currClockID = $row["id"];
				$currClockName = ucwords($row["name"]);
				echo '<option value=' . $currClockID . '>' . $currClockName . '</option>';
			}
			echo '</select>';
		}
		else {
			//show option to create a new clock

		}
	}


	function showAccountsSelect($dbHandler, $formName){
		$allNames = $dbHandler->getAllPersons();

		//builds select with results
		if ($allNames->num_rows > 0){
			echo "<select id='names-select' name='names' form='$formName' required>";
			while ($row = $allNames->fetch_assoc()){
				$currPersonID = $row["person_id"];
				$currName = ucwords($row["firstname"]);
				echo '<option value=' . $currPersonID . '>' . $currName . '</option>';
			}
			echo '</select>';
		}
		else {
			echo "Nobody there yet - add a new person";
		}
	}

	function insertCheckin($dbHandler){
		if (!isset($_POST['names']) || !isset($_POST['location'])){
			return;
		}

		$dbHandler->insertCheckin($_POST['names'], $_POST['location']);
	}

	function insertPerson($dbHandler){
		if (!isset($_POST['new-name-input'])){
			return;
		}

		$newNameRaw = $_POST['new-name-input'];
		$dbHandler->insertPerson($newNameRaw);
	}

	function removePerson($dbHandler){
		if (!isset($_POST['remove'])){
			return;
		}

		$dbHandler->deletePerson($_POST['names']);

	}
	

	//displays each person's most recent location
	function displayCurrCheckins($dbHandler){
		$currCheckins = $dbHandler->getCurrCheckins();

		if ($currCheckins->num_rows == 0){
			return;
		}

		//build table with results
		echo 
			"<table>
				<tr>
					<th>Name</th>
					<th>Location</th>
					<th>Time</th>
				</tr>";

		while ($row = $currCheckins->fetch_assoc()){
			echo "<tr>";
			echo "<td>" . $row["name"] . "</td>";
			echo "<td>" . $row["location"] . "</td>";
			echo "<td>" . $row["time"] . "</td>";
			echo "</tr>";
		}

		echo "</table>";

		

	}

?>
