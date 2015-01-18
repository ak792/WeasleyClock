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

			//if was already set up, just return it
			if ($this->conn instanceof MySQLi){
				return $this->dbname;
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


		public function getAllClocks(){
			$clocksQueryStmt = 
				"SELECT DISTINCT id, name
				FROM $this->dbname.{$this->dbTables['clocks']}
				ORDER BY name ASC";

			if (!$clocks = $this->conn->query($clocksQueryStmt)){
				echo "<br>Error: " . $clocksQueryStmt . "<br>" . $this->conn->error;
				return;
			}

			return $clocks;
		}


		public function getAllPersons(){
			$allNamesQueryStmt = 
				"SELECT DISTINCT person_id, firstname
				FROM $this->dbname.{$this->dbTables['persons']}
				ORDER BY firstname ASC;";

			//executes statement
			if (!$allNames = $this->conn->query($allNamesQueryStmt)){
				echo "<br>Error: " . $allNamesQueryStmt . "<br>" . $this->conn->error;
				return;
			}

			return $allNames;
		}

		public function getCurrCheckins(){

			$currCheckinsQueryStmt = 
				"SELECT c.firstname as name, a.location, a.time
				FROM $this->dbname.{$this->dbTables['checkins']} a
				INNER JOIN (
					SELECT person_id, max(time) maxtime
							FROM $this->dbname.{$this->dbTables['checkins']}
							GROUP BY person_id) b 
				ON a.person_id = b.person_id 
				AND a.time = b.maxtime
				INNER JOIN $this->dbname.{$this->dbTables['persons']} c
				ON a.person_id = c.person_id
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
			$personID = filter_var($personIDRaw, FILTER_SANITIZE_NUMBER_INT);

			if ($personID == -1){
				$personID = $this->insertPerson($newNameRaw);
			}

			$location = $this->sanitizeString($locationRaw);

			$currDateTime = date("Y-m-d H:i:s", time());

			
			$checkinInsertStmt = "INSERT INTO $this->dbname.{$this->dbTables['checkins']} (location, time, person_id) "
				. "VALUES ('$location', '$currDateTime', '$personID');";
			if ($this->conn->query($checkinInsertStmt) != TRUE){
				echo "Error: " . $checkinInsertStmt . "<br>" . $this->conn->error;
			}
		}


		public function insertPerson($newNameRaw){
			$name = $this->sanitizeString($newNameRaw);

			if ($this->personIsInDB($name) === true){
				echo "<br>Error: Someone is already named " . $name;
				echo "<br>Please choose another name.<br><br>";
				return;
			}

			$personInsertStmt = "INSERT INTO $this->dbname.{$this->dbTables['persons']} (firstname) "
				. "VALUES ('$name');";

			if ($this->conn->query($personInsertStmt) != TRUE){
				echo "Error: " . $personInsertStmt . "<br>" . $this->conn->error;
				return;
			}
			
			$personID = $this->conn->insert_id;

			//load scripts only if not loaded
			echo "
				<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"></script>
				<script src=\"weasleyClock.js\"></script>
				<script>
					updateSelectNewPerson($personID, '$name');
				</script>";


			return $personID;
		}

		public function personIsInDB($name){
			$name = $this->sanitizeString($name);
			$checkDuplicatesSelectStmt =
				"SELECT person_id
				FROM $this->dbname.{$this->dbTables['persons']}
				WHERE firstname = '$name'";
			
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
			$str = $strTrunc;

			return $str;
		}


	}
?>
