<?php

// TOOD: 
//put on github
//handle duplicates
//make a remove person button
//allow for multiple clocks?	
//only load js once
//make pretty
//stretch: port to an app

	require_once 'DBHandler.php';

	function run(){
		$dbHandler = new DBHandler();
		$dbHandler->setupMySQLConnection();

		showCheckinForm($dbHandler);
		insertDataIntoDatabase($dbHandler);
		displayMostRecentLocations($dbHandler);

		$dbHandler->teardownMySQLConnection();
	}
	
	function showCheckinForm($dbHandler){
		?>
		<form name="checkin-form" id="checkin-form" action="" method="post">
			<label for="names">Name: </label>
		<?php
			showAccountsSelect($dbHandler);
		?>

			<input type="text" id='new-name-input' name="new-name-input" value="VP" required>

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

	function showAccountsSelect($dbHandler){
		$conn = $dbHandler->getConnection();
		$dbname = $dbHandler->getDBName();
		$dbTables = $dbHandler->getDBTables();

		//builds select statement
		$allNamesQueryStmt = 
			"SELECT DISTINCT person_id, firstname
			FROM $dbname.{$dbTables['persons']}
			ORDER BY firstname ASC;";

		//executes statement
		if (!$allNames = $conn->query($allNamesQueryStmt)){
			echo "<br>Error: " . $allNamesQueryStmt . "<br>" . $conn->error;
			return;
		}

		//build table with results
		if ($allNames->num_rows > 0){
			echo '<select id="names-select" name="names" form="checkin-form" required>';
			while ($row = $allNames->fetch_assoc()){
				$currPersonID = ucwords($row["person_id"]);
				$currName = ucwords($row["firstname"]);
				echo '<option value=' . $currPersonID . '>' . $currName . '</option>';
			}
			echo '<option value="-1">Add New Person</option>'; //jquery hookin
			echo '</select>';
		}
		else {
			//show hidden

			echo '<script> 	
							<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"></script>
							$(\'#new-name-input\').show();
		 	 			</script>';
		}
	}

	//if was passed POST data, put it in the database
	function insertDataIntoDatabase($dbHandler){
		if (!isset($_POST['names']) || !isset($_POST['location'])){
			return;
		}

		$conn = $dbHandler->getConnection();
		$dbname = $dbHandler->getDBName();
		$dbTables = $dbHandler->getDBTables();

		//personID will be -1 if adding a new person
		$personID = filter_var($_POST['names'], FILTER_SANITIZE_NUMBER_INT);

		if ($personID == -1){
			$personID = insertPersonIntoDatabase($dbHandler, $dbTables, $_POST['new-name-input']);
		}

		$location_raw= $_POST['location'];
		$location_trimmed = trim($location_raw);
		$location_filtered = filter_var($location_trimmed, FILTER_SANITIZE_STRING);
		$location_truncated = substr($location_filtered, 0, 255);
		$location = $location_truncated;

		$currDateTime = date("Y-m-d H:i:s", time());

		
		$checkinInsertStmt = "INSERT INTO $dbname.{$dbTables['checkins']} (location, time, person_id) "
			. "VALUES ('$location', '$currDateTime', '$personID');";
		if ($conn->query($checkinInsertStmt) != TRUE){
			echo "Error: " . $checkinInsertStmt . "<br>" . $conn->error;
		}
	}	

	//inserts new person into DB. sets that person's id as $personID
	function insertPersonIntoDatabase($dbHandler, $dbTables, $name_raw){
			$conn = $dbHandler->getConnection();
			$dbname = $dbHandler->getDBName();
			$dbTables = $dbHandler->getDBTables();

			$name_raw = $_POST['new-name-input'];
			$name_trimmed = trim($name_raw);
			$name_filtered = filter_var($name_trimmed, FILTER_SANITIZE_STRING);
			$name_truncated = substr($name_filtered, 0, 255);
			$name = $name_truncated;

			$personInsertStmt = "INSERT INTO $dbname.{$dbTables['persons']} (firstname) "
				. "VALUES ('$name');";

			if ($conn->query($personInsertStmt) != TRUE){
				echo "Error: " . $personInsertStmt . "<br>" . $conn->error;
				return;
			}
			
			$personID = $conn->insert_id;

				//load scripts only if not loaded
			echo "
				<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"></script>
				<script src=\"weasleyClock.js\"></script>
				<script>
					updateSelectNewPerson($personID, '$name');
				</script>";


			return $personID;
	}

	//displays each person's most recent location
	function displayMostRecentLocations($dbHandler){
		$conn = $dbHandler->getConnection();
		$dbname = $dbHandler->getDBName();
		$dbTables = $dbHandler->getDBTables();

		
		$mostRecentLocsQueryStmt = 
			"SELECT c.firstname, a.location, a.time
			FROM $dbname.{$dbTables['checkins']} a
			INNER JOIN (
				SELECT person_id, max(time) maxtime
	      		FROM $dbname.{$dbTables['checkins']}
	      		GROUP BY person_id) b 
			ON a.person_id = b.person_id 
			AND a.time = b.maxtime
			INNER JOIN $dbname.{$dbTables['persons']} c
			ON a.person_id = c.person_id
			ORDER BY c.firstname ASC;
			";

		if (!$mostRecentLocs = $conn->query($mostRecentLocsQueryStmt)){
			echo "<br>Error: " . $mostRecentLocsQueryStmt . "<br>" . $conn->error;
			return;
		}

		//build table with results
		if ($mostRecentLocs->num_rows > 0){
			echo "<table>";
			echo "<tr>";

			$fields = mysqli_fetch_fields ($mostRecentLocs);
			for ($i = 0; $i < count($fields); $i++){
				echo "<th>" . ucwords($fields[$i]->name) . "</th>"; 
			}

			echo "</tr>";

			while ($row = $mostRecentLocs->fetch_assoc()){
				echo "<tr>";
				echo "<td>" . $row["firstname"] . "</td>";
				echo "<td>" . $row["location"] . "</td>";
				echo "<td>" . $row["time"] . "</td>";
				echo "</tr>";
			}

			echo "</table>";

		}
		else {
			echo "Nobody has checked in yet!";
		}

	}

?>
