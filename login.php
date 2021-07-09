<?php
	session_start();

	require_once "vars.php";

	$flash = $_SESSION["flash"] ?? "";
	unset($_SESSION["flash"]);

	if (isset($_POST["cancel"])) {
		header('Location: index.php');
		return;
	}

	if (isset($_SESSION["user_id"])) {
		$_SESSION["flash"] = "<p style='color:red;'>".$_SESSION["name"].", you are already logged in.</p>";
		header('Location: index.php');
		return;
	}	

	if (isset($_POST["email"])) {
		if (($_POST["email"] == "") || ($_POST["password"] == "")) {
			$_SESSION["flash"] = "<p style='color:red;'>Both fields must be filled out</p>";
			header('Location: login.php');
			return;
		}

		$email = htmlentities($_POST["email"]);
		$password = htmlentities($_POST["password"]);

		require_once "pdo.php";

		$sql = "SELECT * from users WHERE email = :em";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(':em' => $email));
		$users = $stmt->fetchAll();

		if (count($users) != 1) {
			$_SESSION["flash"] = "<p style='color:red;'>That email was not recognized.</p>";
			header('Location: login.php');
			return;
		}

		$stored_pw = $users[0]["hashed_pw"];

		if (!password_verify($password,$stored_pw)) {
			$_SESSION["flash"] = "<p style='color:red;'>Incorrect password.</p>";
			header('Location: login.php');
			return;
		}

		$_SESSION["user_id"] = $users[0]["user_id"];
		$_SESSION["name"] = $users[0]["name"];
		$_SESSION["email"] = $email;
		$_SESSION["key"] = $password;
		header('Location: index.php');
		return;
	}


?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>2021 Predictions: Log In</title>
		<link rel="icon" type="img/png" href="icon.png">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		
<h1>Log In</h1>
<form method="POST">
<label for="email">Email</label>
<input type="email" name="email" id="email"><br/>
<label for="password">Password</label>
<input type="password" name="password" id="password"><br/>
<input type="submit" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>

<?php echo $flash; ?>
	</body>
</html>