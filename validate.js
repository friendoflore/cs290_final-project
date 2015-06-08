function empty_fields() {
	document.getElementById("new_username").value = "";
	document.getElementById("new_password").value = "";
	document.getElementById("new_password_confirm").value = "";
}

function verify_account(user, pass, pass_conf) {
	if(user === "") {
		alert("You must enter a username!");
		empty_fields();
		return false;
	}

	testWhiteSpace = new RegExp(/\s/);
	if(testWhiteSpace.test(user)) {
		alert("No whitespace allowed in username!");
		empty_fields();
		return false;
	}
	
	if(pass === "") {
		alert("You must enter a password!");
		empty_fields();
		return false;
	}
	if(pass !== pass_conf) {
		alert("Passwords must match!");
		empty_fields();
		return false;
	}

	var req = new XMLHttpRequest();
	if(!req) {
		throw 'Unable to create HttpRequest.';
	}

	var url = "validate_account.php";
	var username = document.getElementById("new_username").value;
	var password = document.getElementById("new_password").value;
	var params = "new_username=" + username + "&new_password=" + password;
	
	req.open("POST", url, true);
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");


	req.onreadystatechange = function() {
		if((this.readyState === 4) && (this.status == 200)) {
			var returnText = this.responseText;
			document.getElementById("response_box").innerHTML = returnText;
			if(returnText == "User created successfully") {
				window.location = "index.php";
			} else if(returnText == "That username is taken!") {
				empty_fields();
			}
		}
	}

	req.send(params);
}

var create_form = document.getElementById("submit_button");

create_form.onclick = function() {
	var username = document.getElementById("new_username").value;
	var password = document.getElementById("new_password").value;
	var password_confirm = document.getElementById("new_password_confirm").value;

	return verify_account(username, password, password_confirm);
}