<?php

session_start();
require_once "pdo.php";
require_once "vars.php";

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

if (!isset($_SESSION['user_id'])) {
	die("ACCESS DENIED");
}



$sql = "SELECT name, turned FROM users JOIN turnkeys ON users.user_id = turnkeys.user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$whoturned = $stmt->fetchAll();

$today = getdate();

$keysturned = 0;
$report = "<p>";
for ($i=0; $i < count($whoturned); $i++) {
	if ($whoturned[$i]['turned'] == 1) {
		$report = $report.$whoturned[$i]['name']." has turned their key. ";
		$keysturned++;
	}
}

if ($keysturned == 0) {
	$report = "<p>No keys have been turned.</p>";
} else {
	$remaining_keys = count($key_holders) - $keysturned;
	if ($remaining_keys == 0) {
		$report = "<p>All keys have now been turned. Here are the predictions.</p>";
	} else {
		$report = $report.$remaining_keys." more keys must be turned to reveal the predictions.</p>";
	}
}

if (time()<$revealdate) {
	$report = "<p>It is not time to reveal the predictions.</p>";
	$time = 0;
} else {
	$time = 1;
}

$reveal = $time + $keysturned;

if ($reveal < count($key_holders) + 1) {
	$sql = "SELECT name, image, subject, date(date_submitted) AS thedate FROM users JOIN predictions ON users.user_id = predictions.user_id ORDER BY predictions.key_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$predictions = $stmt->fetchAll();
	if (count($predictions)==0) {
		$content = "<p>There are no predictions yet.</p>";
	} else {
		$content = "<ul>";
		foreach ($predictions as $prediction) {
			$name = $prediction["name"];
			$date = $prediction["thedate"];
			if ($prediction['image']==='0') {
				$image_or_not = "a";
			} else {
				$image_or_not = "an image";
			}
			if ($prediction['subject']) {
				$subject = " about " . $prediction['subject'];
			} else {
				$subject = "";
			}
			$content = $content . "<li>". $name . " made " . $image_or_not . " prediction" . $subject . " on " . date('F j, Y', strtotime($date)) .".</li>";
		}
		$content = $content . "</ul>";
	}
} else {
	$sql = "SELECT key_id, name, subject, image, prediction, iv, tag, date(date_submitted) AS thedate FROM users JOIN predictions ON users.user_id = predictions.user_id ORDER BY key_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$predictions = $stmt->fetchAll();
	$content = "";
	foreach ($predictions as $prediction) {
		$iv = $prediction["iv"];
		$tag = $prediction["tag"];
		$key = file_get_contents("keysForLater/".$prediction["key_id"]);
		$date = $prediction["thedate"];
		$name = $prediction["name"];
		$image_path = $prediction['image'];
		$ciphertext = $prediction["prediction"];
		$id = $prediction["key_id"];
		if ($prediction['subject']) {
			$subject = " about " . $prediction['subject'];
		} else {
			$subject = "";
		}
		if ($image_path==='0') {
				$original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv, $tag);
				$content = $content . "<details><summary>" . $name . " made a prediction" . $subject . " on " . date('F j, Y', strtotime($date)) .".</summary><p>" . $original_plaintext . "</p></details>";
			} else {
				if ($ciphertext == "encrypted file") {
					$ciphertext = file_get_contents($image_path);
					$original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv, $tag);
					file_put_contents($image_path,$original_plaintext);
					$sql = "UPDATE predictions SET prediction = 0 WHERE key_id = :id";
					$stmt = $pdo->prepare($sql);
					$stmt->execute(array(':id' => $id));
				}
				
				$content = $content . "<details><summary>" . $name . " made an image prediction" . $subject . " on " . date('F j, Y', strtotime($date)) .".</summary><img width='50%' src='" . $image_path . "'></details>";
			}
	}
}

?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>2021 Predictions</title>
		<link rel="icon" type="img/png" href="icon.png">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<h1>2021 Predictions</h1>
		<?php
		echo $report;
		echo $content;
		echo $flash;
		?>
		<p><a href="index.php">Go Back</a></p>
	</body>
</html>