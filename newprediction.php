<?php

session_start();
require_once "vars.php";
require_once "pdo.php";

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

if (!isset($_SESSION['user_id'])) {
	die("ACCESS DENIED");
}

if (isset($_POST["cancel"])) {
	header('Location: index.php');
	return;
}

if (isset($_POST["prediction"])) {
	$plaintext = htmlentities($_POST["prediction"]);
	$subject = htmlentities($_POST["subject"]);
	if ($plaintext == "") {
		$_SESSION["flash"] = "<p style='color:red;'>No prediction has been entered, ".$_SESSION["name"].".</p>";
		header('Location: newprediction.php');
		return;
	}
	
	$stmt = $pdo->prepare("SELECT * FROM enc_keys1");
	$stmt->execute();
	$funkeys = $stmt->fetchAll();


	$key = $funkeys[0]["funkey"];
	$key_id = $funkeys[0]["key_id"];
	
	if (in_array($cipher, openssl_get_cipher_methods())) {
    	$ivlen = openssl_cipher_iv_length($cipher);
    	$iv = openssl_random_pseudo_bytes($ivlen);
    	$ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv, $tag);
    } else {
    	$_SESSION["flash"] = "<p style='color:red;'>Something went wrong, ".$_SESSION["name"].". I don't know what though. Try again?</p>";
    	header('Location: newprediction.php');
    	return;
    }
    
	$sql = "INSERT INTO predictions (user_id, subject, prediction, iv, tag, key_id, image) VALUES (:id, :sub, :pred, :iv, :tg, :kid, 0)";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(
		':id' => $_SESSION['user_id'],
		':sub' => $subject,
		':pred' => $ciphertext,
		':iv' => $iv,
		':tg' => $tag,
		':kid' => $key_id));

	$sql = "DELETE FROM enc_keys1 WHERE key_id=:kid";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(':kid' => $key_id));

	$_SESSION["flash"] = "<p style='color:green;'>Prediction added. Thank you, ".$_SESSION["name"].".</p>";
	header('Location: index.php');
	return;
}

?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>2021 Predictions: New Prediction</title>
		<link rel="icon" type="img/png" href="icon.png">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<h1>Enter a new 2021 prediction</h1>
		<form method='POST'>
			<p>New Prediction:<br>
				<textarea name="prediction" rows="8" cols="80"></textarea>
			</p>
			<p>
				Subject (optional):<br>
				<input type="text" name="subject">
			</p>
			<p>
				<input type="submit" value="Add">
				<input type="submit" name="cancel" value="Cancel">
			</p>
		</form>
		<?php echo $flash; ?>
	</body>
</html>