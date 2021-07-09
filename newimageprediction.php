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




if(isset($_POST["submit"]) && isset($_FILES["fileToUpload"])) {
	
	$subject = htmlentities($_POST["subject"]);
	$sql = "SELECT key_id FROM predictions WHERE user_id = :id AND image != '0'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(':id' => $_SESSION['user_id']));
	$sofar = $stmt->fetchAll();
	$number = count($sofar) + 1;


	$target_dir = "uploads/";
	$target_file = $target_dir . $_SESSION['user_id'] . "_" . $number . "_" . basename($_FILES["fileToUpload"]["name"]);
	$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  	if ($_FILES["fileToUpload"]["size"] > 10485760) {
  		$_SESSION['flash'] = "<p style='color:red;'>Sorry, your file is too large.</p>";
  		header('Location: newimageprediction.php');
		return;
	}
	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
		$_SESSION['flash'] = "<p style='color:red;'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</p>";
  		header('Location: newimageprediction.php');
		return;
	}
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		$stmt = $pdo->prepare("SELECT * FROM enc_keys1");
		$stmt->execute();
		$funkeys = $stmt->fetchAll();
		$key = $funkeys[0]["funkey"];
		$key_id = $funkeys[0]["key_id"];

		$plaintext = file_get_contents($target_file);

		if (in_array($cipher, openssl_get_cipher_methods())) {
    		$ivlen = openssl_cipher_iv_length($cipher);
    		$iv = openssl_random_pseudo_bytes($ivlen);
    		$ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv, $tag);
    		file_put_contents($target_file,$ciphertext);
    	} else {
    		$_SESSION["flash"] = "<p style='color:red;'>Something went wrong, ".$_SESSION["name"].". I don't know what though. Try again?</p>";
    		header('Location: newimageprediction.php');
    		return;
    	}

		$sql = "INSERT INTO predictions (user_id, subject, prediction, iv, tag, key_id, image) VALUES (:id, :sub, :pred, :iv, :tg, :kid, :im)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(
			':id' => $_SESSION['user_id'],
			':sub' => $subject,
			':pred' => "encrypted file",
			':iv' => $iv,
			':tg' => $tag,
			':kid' => $key_id,
			':im' => $target_file));

		$sql = "DELETE FROM enc_keys1 WHERE key_id=:kid";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(':kid' => $key_id));

		$_SESSION["flash"] = "<p style='color:green;'>Prediction added. Thank you, ".$_SESSION["name"].".</p>";
		header('Location: index.php');
		return;
	} else {
		$_SESSION['flash'] = "<p style='color:red;'>Something went wrong. Please try again.</p>";
		header('Location: newimageprediction.php');
		return;
	}
} else if (isset($_POST["submit"])) {
	$_SESSION['flash'] = "<p style='color:red;'>Please upload a file.</p>";
	header('Location: newimageprediction.php');
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
		<h1>Enter a new 2021 image prediction</h1>
		<form method="POST" enctype="multipart/form-data">
			<p>
				Subject (optional):<br>
				<input type="text" name="subject">
			</p>
			<p>
  				Select image to upload (10mb limit):<br>
  				<input type="file" name="fileToUpload" id="fileToUpload">
  			</p>
  			<p>
  				<input type="submit" value="Upload Image" name="submit">
  				<input type="submit" value="Cancel" name="cancel">
  			</p>
		</form>
		<?php echo $flash; ?>
	</body>
</html>