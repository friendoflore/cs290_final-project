<?php

ob_start();

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "robinsti-db", "j0dbptMAE8H6RoqT", "robinsti-db");
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if(isset($_POST['new_username'])) {

	$username = $_POST['new_username'];
	$password = $_POST['new_password'];
	$toinsert = base64_encode(hash('sha256', $password));
	$tmpUsername = NULL;

	if(!($stmt = $mysqli->prepare("SELECT username FROM users"))) {
		echo "Prepare failed (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if(!$stmt->execute()) {
		echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if(!$stmt->bind_result($tmpUsername)) {
		echo "Binding user test result failed: (" . $stmt->errno . ") " . $stmt->error; 
	}

	$userlower = strtolower($username);
	
	while($stmt->fetch()) {
		$tmpUsername = strtolower($tmpUsername);
		if($userlower == $tmpUsername) {
			echo "That username is taken!";
			return;
		}
	}

	$id;


	if(!($stmt = $mysqli->prepare("INSERT INTO users(id, username, password) VALUES (?, ?, ?)"))) {
		echo "Prepare failed (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if(!$stmt->bind_param("iss", $id, $username, $toinsert)) {
		echo "Binding parameter failed: (" . $stmt->errno . ") " . $stmt->error; 
	}
	if(!$stmt->execute()) {
		echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	session_start();

	if(!$mysqli->query("CREATE TABLE IF NOT EXISTS $userlower (
		id INT PRIMARY KEY AUTO_INCREMENT,
		title VARCHAR(255) UNIQUE NOT NULL,
		category CHAR(64) NOT NULL,
		public INT NOT NULL,
		content TEXT NOT NULL
		)")) {
		echo 'User Table creation failed (' . $mysqli->errno . ") " . $mysqli->error;
	}


	$_SESSION['userID'] = $username;

	echo "User created successfully";


}



?>