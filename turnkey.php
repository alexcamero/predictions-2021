<?php

session_start();

require_once "vars.php";

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

if (!isset($_SESSION['user_id'])) {
	die("ACCESS DENIED");
}

if (!in_array($_SESSION['email'],$key_holders)) {
	$_SESSION['flash'] = "<p style='color:red;'>You have no key to turn.</p>";
	header('Location: index.php');
	return;
}

$today = getdate();

if (time()<$revealdate) {
	$_SESSION['flash'] = "<p style='color:red;'>It is not time yet. You may turn your key on ".date('F j, Y', $revealdate).".</p>";
	header('Location: index.php');
	return;
}

require_once 'pdo.php';

$sql = "UPDATE turnkeys SET turned = 1 WHERE user_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(':id' => $_SESSION['user_id']));
$_SESSION['flash'] = "<p style='color:green'>Your key has been turned.</p>";
header('Location: index.php');
return;

?>