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
	echo 'User Table creation failed (' . $mysqli->errno . ') ' . $mysqli->error;
}

if(!$mysqli->query("CREATE TABLE IF NOT EXISTS posts(
	id INT PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(255) NOT NULL,
	title VARCHAR(255) NOT NULL,
	category VARCHAR(255) NOT NULL,
	content TEXT NOT NULL,
	postdate DATE NOT NULL
	)")) {
	echo 'Post table creation failed (' . $mysqli->errno . ') ' . $mysqli->error;
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
	echo '<a href="logout.php"><div id="logout">Logout</div></a></div>';
	
	$userlower = strtolower($current_user);

		echo '</div>';
		echo '<div id=main_content_box>';

	$content_array = array();

	if(isset($_POST['update_id'])) {

		//Get and store old content
		//Update all the old content in the posts database where the old content is equal to the content field
		$id_to_update = $_POST['update_id'];
		$updated_title = $_POST['updated_title'];
		$updated_content = $_POST['updated_content'];
		$updated_category = $_POST['updated_category'];

		$public_to_update = NULL;

		if(!($stmt5 = $mysqli->prepare("SELECT content FROM $userlower WHERE id = $id_to_update"))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt5->execute()) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if(!$stmt5->bind_result($public_to_update)) {
			echo "Binding result failed: (" . $stmt->errno . ") " . $stmt->error;
		}


		while($stmt5->fetch()) {
			$stmt5->store_result();
			$public_to_update = $mysqli->real_escape_string($public_to_update);
			if(!($stmt6 = $mysqli->prepare("UPDATE posts SET title = '$updated_title', content = '$updated_content', category = '$updated_category' WHERE content = '$public_to_update'"))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if(!$stmt6->execute()) {
				echo "Public update failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			
		}



		if(!($stmt9 = $mysqli->prepare("UPDATE $userlower SET title = '$updated_title', content = '$updated_content', category = '$updated_category' WHERE id = $id_to_update"))) {
			echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt9->execute()) {
			echo "Update execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}

	}

	if(!isset($_POST['add_new_draft']) && !isset($_POST['new_title']) && !isset($_POST['delete_from_list']) && !isset($_POST['post_to_public']) && !isset($_POST['edit_post'])) {

		if(!($stmt = $mysqli->prepare("SELECT id, title, category, public, content FROM $userlower"))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt->execute()) {
			echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		echo '<table>';
		$out_id = NULL;
		$out_title = NULL;
		$out_category = NULL;
		$out_public = NULL;
		$out_content = NULL;

		if(!$stmt->bind_result($out_id, $out_title, $out_category, $out_public, $out_content)) {
			echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}

		echo '<h2>Post Drafts</h3>';
		echo '<tr>
		<!--	<th>ID
			<th>Title
			<th>Category
			<th>Public -->
			';

	while($stmt->fetch()) {
		$content_array[$out_id] = $out_content;
		echo '<tr class="title_row">
					<td align="center" class="id_cell">' . $out_id;
		echo 	  '<td class="title_cell">' . $out_title;
		echo    '<td align="center" class="category_cell">' . $out_category;
		if($out_public == 1) {
			echo '<td align="center" class="public_cell">Posted in public';
		echo '<td>
				<form action="index.php" method="post">
					<input type="hidden" name="post_to_public" value="' . $out_id . '" />
					<input type="submit" value="Unpost" />
				</form>';
		} else {
			echo '<td align="center" class="public_cell">Not posted';
				echo '<td>
				<form action="index.php" method="post">
					<input type="hidden" name="post_to_public" value="' . $out_id . '" />
					<input type="submit" value="Post" />
				</form>';
		}
		echo '<td>
				<form action="index.php" method="post">
					<input type="hidden" name="delete_from_list" value=" ' . $out_id . '" />
					<input type="submit" value="Delete">
				</form>';
		echo '<td>
				<form action="index.php" method="post">
					<input type="hidden" name="show_post" value="' . $out_id . '" />
					<input type="submit" value="Show Post">
				</form>';
	}
		echo '</table>';

		echo '<br /><form action="index.php" method="post">
					<input type="hidden" name="add_new_draft" value="1">
					<input type="submit" value="Add New Draft">
				</form>';
	} else if(!isset($_POST['new_title']) && !isset($_POST['delete_from_list']) && !isset($_POST['post_to_public']) && !isset($_POST['edit_post'])) {
		echo '<form action="index.php" method="post">
					<input type="text" name="new_title" placeholder="Title" /><br />
					<textarea style="width: 280px; height: 160px" name="new_content" placeholder="Content"></textarea><br />
					<input type="text" name="new_category" placeholder="Category" /><br />
					<input type="submit" value="Add New Draft">
				</form>';
	} else if(isset($_POST['new_title'])) {
		if(($_POST['new_title'] == '') || ($_POST['new_content'] == "") || ($_POST['new_category'] == '')) {
			echo "You must submit complete information!";
		} else {
			$id;
			$new_title = $_POST['new_title'];
			$new_content = $_POST['new_content'];
			$new_category = $_POST['new_category'];
			$public = 0;
			if(!($stmt = $mysqli->prepare("INSERT INTO $userlower(id, title, category, public, content) VALUES (?, ?, ?, ?, ?)"))) {
				echo "Prepare failed (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if(!$stmt->bind_param("issis", $id, $new_title, $new_category, $public, $new_content)) {
				echo "Parameter binding failed (" . $stmt->errno . ") " . $stmt->error;
			}

			if(!$stmt->execute()) {
				echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}

			header("Location: index.php");
		}
	} else if(isset($_POST['delete_from_list'])) {
		$tmp = $_POST['delete_from_list'];

		$out_content = NULL;
		$content_to_post = NULL;


		if(!($stmt2 = $mysqli->prepare("SELECT content FROM $userlower WHERE id = $tmp"))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt2->execute()) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if(!$stmt2->bind_result($out_content)) {
			echo "Binding result failed: (" . $stmt->errno . ") " . $stmt->error;
		}

		while($stmt2->fetch()) {
			$content_to_post = $out_content;
		}

		$content_to_post = $mysqli->real_escape_string($content_to_post);

		if(!($stmt3 = $mysqli->prepare("DELETE FROM posts WHERE '$content_to_post' = content"))) {
			echo "Prepare for delete failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt3->execute()) {
			echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}


		if(!($stmt = $mysqli->prepare("DELETE FROM $userlower WHERE id = $tmp"))) {
			echo "Prepare for deletion failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt->execute()) {
			echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}



		header("Location: index.php");
	} else if(isset($_POST['post_to_public'])) {
		$out_id;
		$out_username = $userlower;
		$out_title = NULL;
		$out_category = NULL;
		$out_content = NULL;
		$out_date = date('Y-m-d');

		$title_to_post = NULL;
		$category_to_post = NULL;
		$content_to_post = NULL;
		$public_to_use = NULL;

		$out_public = NULL;


		$tmp = $_POST['post_to_public'];
		if(!($stmt = $mysqli->prepare("UPDATE $userlower SET public = public ^ 1 WHERE id = $tmp"))) {
			echo "Update prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt->execute()) {
			echo "Execution failed: (" . $stmt->errno . ") " . $stmt->error;
		}


		if(!($stmt2 = $mysqli->prepare("SELECT title, category, content, public FROM $userlower WHERE id = $tmp"))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt2->execute()) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if(!$stmt2->bind_result($out_title, $out_category, $out_content, $out_public)) {
			echo "Binding result failed: (" . $stmt->errno . ") " . $stmt->error;
		}


		while($stmt2->fetch()) {
			$title_to_post = $out_title;
			$category_to_post = $out_category;
			$content_to_post = $out_content;
			$public_to_use = $out_public;
		}


		if($public_to_use) {

			if(!($stmt3 = $mysqli->prepare("INSERT INTO posts(id, username, title, category, content, postdate) VALUES (?, ?, ?, ?, ?, ?)"))) {
				echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if(!$stmt3->bind_param("isssss", $out_id, $out_username, $title_to_post, $category_to_post, $content_to_post, $out_date)) {
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			}
			if(!$stmt3->execute()) {
				echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}

		} else {
			$content_to_post = $mysqli->real_escape_string($content_to_post);
			if(!($stmt3 = $mysqli->prepare("DELETE FROM posts WHERE '$content_to_post' = content"))) {
				echo "Prepare for delete failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if(!$stmt3->execute()) {
				echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
		}
		
		header("Location: index.php");
	}

	if(isset($_POST['show_post'])) {
		$tmp = $_POST['show_post'];
		echo '<div id="post_content">';

		$out_title = NULL;

		if(!($stmt = $mysqli->prepare("SELECT title FROM $userlower WHERE id = $tmp"))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt->execute()) {
			echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt->bind_result($out_title)) {
			echo "Binding result failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}

		while($stmt->fetch()) {
			echo '<br /><br /><h3 class="show_post_title">' . $out_title . '</h3>';
		}
		echo '<form action="index.php" method="post" class="edit_button">
					<input type="hidden" name="edit_post" value="' . $tmp . '">
					<input type="submit" value="Edit Post">
				</form>';

		echo '<hr>';
		$content_array[$tmp] = str_replace("\'", "'", $content_array[$tmp]);
		$content_array[$tmp] = str_replace('\"', '"', $content_array[$tmp]);
		echo $content_array[$tmp];
		echo '<br />
				<br />
				<form action="index.php" method="post">
					<input type="hidden" name="edit_post" value="' . $tmp . '">
					<input type="submit" value="Edit Post">
				</form>';
		echo '</div>';
	}

	if(isset($_POST['edit_post'])) {
		$tmp_id = $_POST['edit_post'];
		$out_title = NULL;
		$out_content = NULL;
		$out_category = NULL;

		if(!($stmt = $mysqli->prepare("SELECT title, content, category FROM $userlower WHERE id = $tmp_id"))) {
			echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt->execute()) {
			echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if(!$stmt->bind_result($out_title, $out_content, $out_category)) {
			echo "Binding result failed: (" . $stmt->errno . ") " . $stmt->error;
		}

		while($stmt->fetch()) {

		$out_content = str_replace("\'", "'", $out_content);
		$out_content = str_replace('\"', '"', $out_content);

		echo '<form action="index.php" method="post">
				<input type="hidden" name="update_id" value="' . $tmp_id . '">
				<input type="text" name="updated_title" value="' . $out_title . '" /><br />
				<textarea style="width: 280px; height: 160px" name="updated_content">' . $out_content . '</textarea><br />
				<input type="text" name="updated_category" value="' . $out_category . '" /><br />
				<input type="submit" value="Update">
			</form>';
		}

	}


} else {
	echo '<a href="login.php"><div id="login">Login</div></a>
			<a href="newaccount.html"><div id="create_account">Create</div></a>
				</div>
			</div>
		</div>';
		header("Location: public.php");
}


?>
	</body>
</html>