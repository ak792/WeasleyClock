<?php

class DBHandler {
	private $conn = null;
	private $dbName = "bob"; 
	private $dbTables = array(
		"persons" => "persons",
		"checkins" => "checkins"
	);

	public function getConnection(){
		return $this->conn;
	}

	
	public function setupMySQLConnection(){

		$hostname = "localhost";
		$username = "root";
		$password = "root";
		$dbname = "weasleyclock";

		$this->dbName = $dbname;

		//if was already set up, just return it
		if ($this->conn instanceof MySQLi){
			return $this->dbname;
		}
		
		//build connection
		$this->conn = new mysqli($hostname, $username, $password, $dbname);
		if ($this->conn->connect_error) {
				die("Connection failed: " . $this->conn->connect_error);
		} 

		return $this->dbname;
	}

	public function teardownMySQLConnection(){
		//only close the connection if it exists
		if ($this->conn instanceof MySQLi){
			$this->conn->close();
		}
	}


	public function getDBName(){
		return $this->dbName;
	}

	public function getDBTables(){
		return $this->dbTables;
	}

}
?>
