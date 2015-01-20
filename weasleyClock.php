<?php

//TOOD: 
//allow for multiple clocks	
//make pretty

//stretch: port to an app

	require_once 'DBHandler.php';


	function run(){
		$dbHandler = new DBHandler();

	
		insertPerson($dbHandler);
		insertCheckin($dbHandler);
		deletePerson($dbHandler);
		insertClock($dbHandler);
		deleteClock($dbHandler);

		buildNavbar();
		buildCheckinForm($dbHandler);
		buildAddPersonForm($dbHandler);
		buildDeletePersonForm($dbHandler);
		buildAddClockForm($dbHandler);
		buildDeleteClockForm($dbHandler);

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
					<a href="" class="tabs" id='checkin-button'>
						Check In
					</a>
				</span>
				&nbsp &nbsp
				<span>
					<a href="" class="tabs" id='add-person-button'>
						Add Person
					</a>
				</span>
				&nbsp &nbsp
				<span>
					<a href="" class="tabs" id='remove-person-button'>
						Remove Person
					</a>
				</span>
				&nbsp &nbsp
				<span>
					<a href="" class="tabs" id='add-clock-button'>
						Add Clock
					</a>
				</span>
				&nbsp &nbsp
				<span>
					<a href="" class="tabs" id='delete-clock-button'>
						Delete Clock
					</a>
				</span>


			</div>
		<?php
	}


	function buildCheckinForm($dbHandler){
		?>
		<form name="checkin-form" class="forms" id="checkin-form" action="" method="post">

			<?php

			$formName = "checkin-form";
			if (showClocksSelect($dbHandler, $formName) && showAccountsSelect($dbHandler, $formName)){
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
				<br>
				<input type="submit" value="Submit!">
			<?php
			}
			?>

		</form>
		<?php
	}


	function buildAddPersonForm($dbHandler){
		?>
		<form name="add-person-form" class="forms" id="add-person-form" action="" method="post">
			<?php
			
			$formName = "add-person-form";
			if (showClocksSelect($dbHandler, $formName)){
			?>
				<br>
				<input type="text" id='new-name-input' name="new-name-input" form="add-person-form" value="VP" required>
				<br>
				<input type="submit" value="Add!">
			<?php
			}
			?>

		</form>
			

		<?php
	}


	function buildDeletePersonForm($dbHandler){
		?>
		<form name="remove-person-form" class="forms" id="remove-person-form" action="" method="post">

			<?php
		
			$formName = "remove-person-form";
			if (showClocksSelect($dbHandler, $formName) && showAccountsSelect($dbHandler, $formName)){
			?>			
				<input type="hidden" form="remove-person-form" class="forms" id="remove-flag" name="remove" value="remove" required>
				<br>
				<input type="submit" value="Remove!">

			<?php
			}
			?>
		</form>

		<?php
	}


	function buildAddClockForm($dbHandler){
		?>
		<form name="add-clock-form" class="forms" id="add-clock-form" action="" method="post">			
			<input type="text" id='new-clock-input' name="new-clock-input" form="add-clock-form" value="MyDefaultClock" required>
			<br>
			<input type="submit" value="Add Clock!">
		</form>
		<?php
	}


	function buildDeleteClockForm($dbHandler){
		?>
		<form name="delete-clock-form" class="forms" id="delete-clock-form" action="" method="post">
			<?php
			$formName = "delete-clock-form";
			if (showClocksSelect($dbHandler, $formName)){
			?>
				<input type="hidden" form="delete-clock-form" class="forms" name="delete-clock" value="delete" required>
				<br>
				<input type="submit" value="Remove!">
			<?php
			}
			?>
		</form>

		<?php
	}


	function showClocksSelect($dbHandler, $formName){
		$clocks = $dbHandler->getAllClocks();

		if (isset($_POST['clock'])){
			$activeClock = $_POST['clock'];
		}
		else {
			$activeClock = 1;
		}

		if ($clocks->num_rows == 0){
			echo "No clock there yet - add a new clock";
			return false;
		}

		echo "<br>";
		echo '<label for="clock">Clock: </label>';
		echo "<select id='clocks-select' name='clock' form='$formName' required>";

		while ($row = $clocks->fetch_assoc()){
			$currClockID = $row["id"];
			$currClockName = ucwords($row["name"]);
			echo "<option value=$currClockID";
			if ($currClockID == $activeClock){
				echo " selected";
			}
			echo ">$currClockName</option>";
		}
		echo "</select>";
		return true;
	}


	function showAccountsSelect($dbHandler, $formName){
		if (isset($_POST['clock'])){
			$clockIDRaw = $_POST['clock'];
		}
		else {
			$clocks = $dbHandler->getAllClocks();
			if ($clocks->num_rows > 0){
				$row = $clocks->fetch_assoc();
				$clockIDRaw = $row["id"];
			} 
			else {
				$clockIDRaw = 1;
			}
		}

		$allNames = $dbHandler->getAllPersons($clockIDRaw);

		if ($allNames->num_rows == 0){
			echo "<br>Nobody there yet - add a new person";
			return false;
		}

		//builds select with results
		echo "<br>";
		echo '<label for="names">Name: </label>';
		echo "<select id='names-select' name='names' form='$formName' required>";
		while ($row = $allNames->fetch_assoc()){
			$currPersonID = $row["person_id"];
			$currName = ucwords($row["firstname"]);
			echo '<option value=' . $currPersonID . '>' . $currName . '</option>';
		}
		echo '</select>';
		return true;
		
	}


	//displays each person's most recent location
	function displayCurrCheckins($dbHandler){
		if (isset($_POST['clock'])){
			$clockIDRaw = $_POST['clock'];
		}
		else {
			$clockIDRaw = 1;
		}

		$currCheckins = $dbHandler->getCurrCheckins($clockIDRaw);

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


	function insertCheckin($dbHandler){
		if (!isset($_POST['names']) || !isset($_POST['location'])){
			return;
		}

		$dbHandler->insertCheckin($_POST['names'], $_POST['location']);
	}


	function insertPerson($dbHandler){
		if (!isset($_POST['clock']) || !isset($_POST['new-name-input'])){
			return;
		}

		$newNameRaw = $_POST['new-name-input'];
		$clockIDRaw = $_POST['clock'];

		$dbHandler->insertPerson($newNameRaw, $clockIDRaw);
	}


	function deletePerson($dbHandler){
		if (!isset($_POST['clock']) || !isset($_POST['remove'])){
			return;
		}

		$nameRaw = $_POST['names'];
		$clockIDRaw = $_POST['clock'];

		$dbHandler->deletePerson($nameRaw, $clockIDRaw);
	}


	function insertClock($dbHandler){
		if (!isset($_POST['new-clock-input'])){
			return;
		}

		$clockNameRaw = $_POST['new-clock-input'];

		$dbHandler->insertClock($clockNameRaw);
	}


	function deleteClock($dbHandler){
		if (!isset($_POST['clock']) || !isset($_POST['delete-clock'])){
			return;
		}

		$clockIDRaw = $_POST['clock'];

		$dbHandler->deleteClock($clockIDRaw);
	}
 

?>
