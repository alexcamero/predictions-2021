<?php
	session_start();
	require_once "pdo.php";
	require_once "vars.php";

	if (isset($_POST["cancel"])) {
		header('Location: index.php');
		return;
	}

	if (isset($_SESSION["user_id"])) {
		$_SESSION["flash"] = "<p style='color:red'>You are already logged in as ".$_SESSION["name"].".</p>";
		header('Location: index.php');
		return;
	}

	$start_email = $_SESSION["email"] ?? "";
	unset($_SESSION["email"]);
	$start_name = $_SESSION["name"] ?? "";
	unset($_SESSION["name"]);
	$flash = $_SESSION["flash"] ?? "";
	unset($_SESSION["flash"]);

	if (isset($_POST["email"])) {
		$name = htmlentities($_POST["name"]);
		$email = htmlentities($_POST["email"]);
		$password = htmlentities($_POST["pass"]);

		if ($email == "" || $password == "" || $name == "") {
			$_SESSION["flash"] = "<p style='color:red;'>Name, email, and password are all required.</p>";
			$_SESSION["email"] = $email;
			$_SESSION["name"] = $name;
			header('Location: register.php');
			return;
		}

		$hashed_pass = password_hash($password,PASSWORD_DEFAULT);

		$sql = "SELECT user_id FROM users WHERE email = :em";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(':em' => $email));
		$existing = $stmt->fetchAll();
		if (count($existing) == 0) {
			$sql = "INSERT INTO users(email, name, hashed_pw) VALUES (:em, :nm, :pw)";
			$stmt = $pdo->prepare($sql);
			
			$stmt->execute(array(
				':em' => $email,
				':nm' => $name,
				':pw' => $hashed_pass));

			$_SESSION['name'] = $name;
			$_SESSION["email"] = $email;
			$_SESSION["user_id"] = $pdo->lastInsertId();

			if (in_array($email, $key_holders)) {
				$sql = "INSERT INTO turnkeys (user_id, turned) VALUES (:id, :t)";
				$stmt = $pdo->prepare($sql);
				$stmt->execute(array(':id' => $_SESSION['user_id'],
				':t' => 5));
			}

			$_SESSION["flash"] = "<p style='color:green'>Registration successful.</p>";
			header('Location: index.php');
			return;
		} else {
			$_SESSION["flash"] = "<p style='color:red;'>This email is already associated with an account.</p>";
			$_SESSION["email"] = $email;
			$_SESSION["name"] = $name;
			header('Location: register.php');
			return;
		}
	}

?>


<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>2021 Predictions: Register</title>
		<link rel="icon" type="img/png" href="icon.png">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<h1>Register</h1>
		<form method="POST">
				<label for="name">Name</label>
				<input type="text" name="name" id="name" value="<?= $start_name ?>"><br/>
				<label for="email">Email</label>
				<input type="email" name="email" id="email" value="<?= $start_email ?>"><br/>
				<label for="pass">Password</label>
				<input type="password" name="pass" id="pass"><br/>
				<input type="submit" value="Sign Up">
				<input type="submit" name="cancel" value="Cancel">
			</form>
		<?php echo $flash; ?>
	</body>
</html>