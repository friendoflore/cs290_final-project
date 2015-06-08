<?php

session_start();

ini_set('display_errors', 'On');

if(isset($_SESSION['userID'])) {
	header("Location: index.php");
} else {
	ob_start();
}

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "robinsti-db", "j0dbptMAE8H6RoqT", "robinsti-db");
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if(!$mysqli->query("CREATE TABLE IF NOT EXISTS users(
	id INT PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(255) UNIQUE NOT NULL,
	password CHAR(64) NOT NULL
	)")) {
	echo 'User Table creation failed (' . $mysqli->errno . ') ' . $mysqli->error;
}

echo '<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="icon" type="image/png" href="favicon.png" sizes="32x32">
		<link rel="icon" type="image/png" href="favicon.png" sizes="16x16">
		<meta charset="utf-8"/>
		<title>Final Project</title>
		<link rel="stylesheet" type="text/css" href="styles.css">
	</head>
	<body>
		<div id="wrapper">
			<div id="header_box">
				<a href="public.php"><div id="home_button"><img id="home_icon" src="home_icon.png" width="37px" height="40px"></div></a>
				<div id="create_page_head">Login</div>
				<div id="login_box">
					<a href="newaccount.html"><div id="login">Create Account</div></a>
				</div>
			</div>
			<div id="main_content_box">
					<form action="login.php" name="login" method="post">
						<input type="text" name="login_username" placeholder="Username" /><br />
						<input type="password" name="login_password" placeholder="Password" /><br />
					
						<input type="submit" value="Login" /><br />
					</form>';

if(isset($_POST['login_username'])) {
	if($_REQUEST['login_username'] == '') {
		echo 'You must enter a username!';
		return;
	}
	if($_REQUEST['login_password'] == '') {
		echo 'You must enter a password!';
		return;
	}
	$username = $_REQUEST['login_username'];
	$password = $_REQUEST['login_password'];
	$check_pwd = base64_encode(hash('sha256', $password));
	$check_pwd = mb_substr($check_pwd, 0, 64);
	$tmpUsername = NULL;
	$tmpPassword = NULL;

	if(!($stmt = $mysqli->prepare("SELECT password FROM users WHERE username = '" . $username . "'"))) {
		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if(!$stmt->execute()) {
		echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error; 
	}
	if(!$stmt->bind_result($tmpPassword)) {
		echo "Binding pwd result failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	while($stmt->fetch()) {
		if($tmpPassword == $check_pwd) {
			echo "Logged in successfully!";
			session_start();
			$_SESSION['userID'] = $username;
			header("Location: index.php");
			return;
		}
	}
	echo "Either the username or password didn't match!";

}
echo '</div>
	</body>
</html>';


?>