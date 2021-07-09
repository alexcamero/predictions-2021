<?php

session_start();

require_once "vars.php";

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

$content = "<p>";

if (isset($_SESSION['user_id'])) {
	$content = $content."Welcome, ".$_SESSION['name'].". You may <a href='newprediction.php'>enter a new prediction</a>, <a href='newimageprediction.php'>enter a new image prediction</a>, <a href='logout.php'>log out</a>, or <a href='reveal.php'>view the results.</a></p>";
} else {
	$content = $content."Hello. Please <a href='login.php'>log in</a> or <a href='register.php'>register for an account</a> to make or view predictions for 2021!</p>";
}

$email = $_SESSION['email'] ?? "";

if (in_array($email,$key_holders)) {
	$content = $content."<p>When the time is right, you may also <a href='turnkey.php'>turn your key</a> to unlock the results. All four keys must be turned to reveal the predictions.</p>";
}

?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>2021 Predictions</title>
		<link rel="icon" type="img/png" href="icon.png">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-D84G81Z7VC"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-D84G81Z7VC');
</script>
	</head>
	<body>
		<h1>2021 Predictions</h1>
		<div id="countdownClock" style="color:red;"></div>
		<?php echo $flash; ?>
		<?php echo $content; ?>

		

<script>
var countDownDate = new Date("Jan 1, 2022 00:00:00").getTime();
var now = new Date().getTime();
var distance = countDownDate - now;
if (distance < 0) {
	document.getElementById("countdownClock").innerHTML = "HAPPY NEW YEAR!";
} else {
	countdown();
	var x = setInterval(countdown, 1000);
}

function countdown() {
  var now = new Date().getTime();
  var distance = countDownDate - now;
  if (distance < 0) {
  	document.getElementById("countdownClock").innerHTML = "HAPPY NEW YEAR!";
  } else {
  	var days = Math.floor(distance / (1000 * 60 * 60 * 24));
  	var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  	var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  	var seconds = Math.floor((distance % (1000 * 60)) / 1000);
  	document.getElementById("countdownClock").innerHTML = "<h2>" + days + "d " + hours + "h " + minutes + "m " + seconds + "s </h2>";
  }
}
</script>
	</body>
</html>