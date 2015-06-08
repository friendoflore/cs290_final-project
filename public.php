<?php

ob_start();

session_start();

ini_set('display_errors', 'On');

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "robinsti-db", "j0dbptMAE8H6RoqT", "robinsti-db");
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if(!$mysqli->query("CREATE TABLE IF NOT EXISTS users(
	id INT PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(255) UNIQUE NOT NULL,
	password CHAR(64) NOT NULL
	)")) {
	echo 'User Table creation failed (' . $mysqli->errno . ") " . $mysqli->error;
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
				<div id="login_box">';

if(isset($_SESSION['userID'])) {
	$current_user = $_SESSION['userID'];
	echo '<a href="index.php"><div id="username">Welcome, ' . $current_user . '</div></a>';
	echo '<a href="logout.php"><div id="logout">Logout</div></a>';
	echo '</div>';
	echo '</div>';
	
} else {
	echo '<a href="login.php"><div id="login">Login</div></a>
			<a href="newaccount.html"><div id="create_account">Create</div></a>
				</div>
			</div>';
}


	echo '<div id="main_content_box">';
	if(!($stmt = $mysqli->prepare("CREATE TABLE IF NOT EXISTS posts(
		id INT PRIMARY KEY AUTO_INCREMENT,
		username VARCHAR(255) NOT NULL,
		title VARCHAR(255) NOT NULL,
		category VARCHAR(255) NOT NULL,
		content TEXT NOT NULL,
		postdate DATE NOT NULL 
		)"))) {
		echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	if(!$stmt->execute()) {
		echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	$content_array = array();

	$out_id = NULL;
	$out_username = NULL;
	$out_title = NULL;
	$out_category = NULL;
	$out_content = NULL;
	$out_postdate = NULL;

	if(!($stmt = $mysqli->prepare("SELECT id, username, title, category, content, postdate FROM posts"))) {
		echo "Select preparation failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if(!$stmt->execute()) {
		echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	if(!$stmt->bind_result($out_id, $out_username, $out_title, $out_category, $out_content, $out_postdate)) {
		echo "Binding result failed: (" . $stmt->errno . ") " . $mysqli->error;
	}
	echo '<h2>Public Posts</h2>';
	echo '<table id="post_table">
			<!--<tr>
					<th>ID
					<th>Username
					<th>Title
					<th>Category
					<th>Post Date -->
					';
	while($stmt->fetch()) {
		echo '<tr class="title_row">';
		echo '<td align="center" class="id_cell">' . $out_id;
		$out_title = str_replace("\'", "'", $out_title);
		$out_title = str_replace('\"', '"', $out_title);
		echo '<td class="title_cell">' . $out_title;
		echo '<td>';
		echo '<td>';
		echo '<td align="right" class="post_button_cell"><form action="public.php" method="post">
					<input type="hidden" name="display_post" value="' . $out_id . '">
					<input type="submit" value="Show Post">
				</form>';
		echo '<tr class="subtitle_row">';
		echo '<td>';
		echo '<td>Posted by ' . $out_username;
		echo '<td align="center"> in ' . $out_category;
		echo '<td>';
		echo '<td align="right"> on ' . $out_postdate;
		$content_array[$out_id] = $out_content;
	}
	echo '</table>';


if(isset($_POST["display_post"])) {
	$tmp = $_POST["display_post"];
	echo '<div id="post_content">';

	$out_title = NULL;
	$out_category = NULL;
	$out_username = NULL;
	$out_postdate = NULL;

	if(!($stmt = $mysqli->prepare("SELECT username, title, category, postdate FROM posts WHERE id = $tmp"))) {
		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if(!$stmt->execute()) {
		echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	if(!$stmt->bind_result($out_username, $out_title, $out_category, $out_postdate)) {
		echo "Binding result failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	while($stmt->fetch()) {
		echo '<br /><br />
				<h2 class="public_post_header">' . $out_title . '</h2>';
		echo '<h4 class="public_post_header">' . $out_category . '</h3>';
		echo '<h5 class="public_post_header">Posted by ' . $out_username . ' on ' . $out_postdate . '</h5>';
		echo '<hr>';
	}

	$content_array[$tmp] = str_replace("\'", "'", $content_array[$tmp]);
	$content_array[$tmp] = str_replace('\"', '"', $content_array[$tmp]);
	echo $content_array[$tmp];
	echo '</div>';
}


	echo '</div>';
	echo '</div>';


?>
	</body>
</html>