<?php

	class DBHandler {
		private $conn = null;
		private $dbName = "bob"; 
		private $dbTables = array(
			"persons" => "persons",
			"checkins" => "checkins",
			"clocks" => "clocks"
		);


		public function __construct(){
			$this->setupMySQLConnection();
		}

		public function __destruct(){
			$this->teardownMySQLConnection();
		}

		public function setupMySQLConnection(){

			$hostname = "localhost";
			$username = "root";
			$password = "root";
			$dbName = "weasleyclock";

			$this->dbName = $dbName;

			//if was already set up, just return
			if ($this->conn instanceof MySQLi){
				return;
			}
			
			//build connection
			$this->conn = new mysqli($hostname, $username, $password, $this->dbName);
			if ($this->conn->connect_error) {
					die("Connection failed: " . $this->conn->connect_error);
			} 
		}
		

		public function teardownMySQLConnection(){
			//only close the connection if it exists
			if ($this->conn instanceof MySQLi){
				$this->conn->close();
			}
		}

		public function getConnection(){
			return $this->conn;
		}

		public function getDBName(){
			return $this->dbName;
		}

		public function getDBTables(){
			return $this->dbTables;
		}

		//include clock in all queries

		public function getAllClocks(){
			$clocksQueryStmt = 
				"SELECT DISTINCT id, name
				FROM $this->dbName.{$this->dbTables['clocks']}
				ORDER BY name ASC";

			if (!$clocks = $this->conn->query($clocksQueryStmt)){
				echo "<br>Error: " . $clocksQueryStmt . "<br>" . $this->conn->error;
				return;
			}

			return $clocks;
		}


		public function getAllPersons($clockIDRaw){
			$clockID = $this->sanitizeInt($clockIDRaw);

			$allNamesQueryStmt = 
				"SELECT DISTINCT person_id, firstname
				FROM $this->dbName.{$this->dbTables['persons']}
				WHERE clock_id = $clockID
				ORDER BY firstname ASC;";

			//executes statement
			if (!$allNames = $this->conn->query($allNamesQueryStmt)){
				echo "<br>Error: " . $allNamesQueryStmt . "<br>" . $this->conn->error;
				return;
			}

			return $allNames;
		}

		public function getCurrCheckins($clockIDRaw){

			$clockID = $this->sanitizeInt($clockIDRaw);

			$currCheckinsQueryStmt = 
				"SELECT c.firstname as name, a.location, a.time
				FROM $this->dbName.{$this->dbTables['checkins']} a
				INNER JOIN (
					SELECT person_id, max(time) maxtime
							FROM $this->dbName.{$this->dbTables['checkins']}
							GROUP BY person_id) b 
				ON a.person_id = b.person_id 
				AND a.time = b.maxtime
				INNER JOIN $this->dbName.{$this->dbTables['persons']} c
				ON a.person_id = c.person_id
				WHERE c.clock_id = $clockID
				ORDER BY c.firstname ASC;
			";

			if (!$currCheckins = $this->conn->query($currCheckinsQueryStmt)){
				echo "<br>Error: " . $currCheckinsQueryStmt . "<br>" . $this->conn->error;
				return;
			}

			return $currCheckins;
		}

		public function insertCheckin($personIDRaw, $locationRaw, $newNameRaw){
			//personID will be -1 if adding a new person
			$personID = $this->sanitizeInt($personIDRaw);

			if ($personID == -1){
				$personID = $this->insertPerson($newNameRaw);
			}

			$location = $this->sanitizeString($locationRaw);

			$currDateTime = date("Y-m-d H:i:s", time());

			
			$checkinInsertStmt =
				"INSERT INTO $this->dbName.{$this->dbTables['checkins']} (location, time, person_id)
				VALUES ('$location', '$currDateTime', '$personID');";
			if ($this->conn->query($checkinInsertStmt) != TRUE){
				echo "Error: " . $checkinInsertStmt . "<br>" . $this->conn->error;
			}
		}


		public function insertPerson($newNameRaw, $clockIDRaw){
			$name = $this->sanitizeString($newNameRaw);
			$clockID = $this->sanitizeInt($clockIDRaw);

			if ($this->personIsInDB($name, $clockID) === true){
				echo "<br>Error: Someone is already named $name in clock $clockID";
				echo "<br>Please choose another name.<br><br>";
				return;
			}

			$personInsertStmt = 
				"INSERT INTO $this->dbName.{$this->dbTables['persons']} (firstname, clock_id) 
				VALUES ('$name', '$clockID');";

			if ($this->conn->query($personInsertStmt) != TRUE){
				echo "Error: " . $personInsertStmt . "<br>" . $this->conn->error;
				return;
			}

			$personID = $this->conn->insert_id;

			return $personID;
		}

		public function deletePerson($personIDRaw, $clockIDRaw){
				$personID = $this->sanitizeInt($personIDRaw);
				$clockID = $this->sanitizeInt($clockIDRaw);


				$personDeleteStmt = 
					"DELETE FROM $this->dbName.{$this->dbTables['persons']}
					WHERE person_id = '$personID'
					AND clock_id = '$clockID';";

				if ($this->conn->query($personDeleteStmt) != TRUE){
					echo "Error: " . $personDeleteStmt . "<br>" . $this->conn->error;
					return;
				}
		}

		public function insertClock($newClockNameRaw){
			$clockName = $this->sanitizeString($newClockNameRaw);

			//implement
			if ($this->clockIsInDB($clockName) === true){
				echo "<br>Error: A clock is already named $clockName";
				echo "<br>Please choose another name.<br><br>";
				return;
			}

			$clockInsertStmt = 
				"INSERT INTO $this->dbName.{$this->dbTables['clocks']} (name) 
				VALUES ('$clockName');";

			if ($this->conn->query($clockInsertStmt) != TRUE){
				echo "Error: " . $clockInsertStmt . "<br>" . $this->conn->error;
				return;
			}

			$clockID = $this->conn->insert_id;

			return $clockID;
		}

		public function deleteClock($clockIDRaw){
				$clockID = $this->sanitizeInt($clockIDRaw);
				
				$personDeleteStmt = 
					"DELETE FROM $this->dbName.{$this->dbTables['persons']}
					WHERE clock_id = '$clockID';";

				if ($this->conn->query($personDeleteStmt) != TRUE){
					echo "Error: " . $personDeleteStmt . "<br>" . $this->conn->error;
					return;
				}

				$clockDeleteStmt = 
					"DELETE FROM $this->dbName.{$this->dbTables['clocks']}
					WHERE id = '$clockID';";

				if ($this->conn->query($clockDeleteStmt) != TRUE){
					echo "Error: " . $clockDeleteStmt . "<br>" . $this->conn->error;
					return;
				}
		}

		public function personIsInDB($name, $clockID){
			$name = $this->sanitizeString($name);
			$clockID = $this->sanitizeInt($clockID);
			
			$checkDuplicatesSelectStmt =
				"SELECT person_id
				FROM $this->dbName.{$this->dbTables['persons']}
				WHERE firstname = '$name'
				AND clock_id = '$clockID'";
			
			if (!$duplicates = $this->conn->query($checkDuplicatesSelectStmt)){
				echo "<br>Error: " . $checkDuplicatesSelectStmt . "<br>" . $this->conn->error;
				return false;
			}

			return ($duplicates->num_rows > 0);
		}

		public function clockIsInDB($clockName){
			$clockName = $this->sanitizeString($clockName);
			
			$checkDuplicatesSelectStmt =
				"SELECT id
				FROM $this->dbName.{$this->dbTables['clocks']}
				WHERE name = '$clockName'";
			
			if (!$duplicates = $this->conn->query($checkDuplicatesSelectStmt)){
				echo "<br>Error: " . $checkDuplicatesSelectStmt . "<br>" . $this->conn->error;
				return false;
			}

			return ($duplicates->num_rows > 0);
		}

		private function sanitizeString($strRaw){
			$strTrimmed = trim($strRaw);
			$strFiltered = filter_var($strTrimmed, FILTER_SANITIZE_STRING);
			$strTrunc = substr($strFiltered, 0, 255);
			$strSanitized = $strTrunc;

			return $strSanitized;
		}

		private function sanitizeInt($intRaw){
			$intSanitized = filter_var($intRaw, FILTER_SANITIZE_NUMBER_INT);
			return $intSanitized;
		}


	}
?>
