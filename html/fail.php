<?php

if ($_GET) {

	// Do some validation/sanitisation here
	$error_message = $_GET['error_message'];

//	if ($error_message != preg_replace("/[^A-Za-z0-9/+\_]/", '', $error_message)) {
//		$error_message = preg_replace("/[^A-Za-z0-9/+\_]/", '', $error_message);
//	}
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Failure</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet" type="text/css">
    <!-- jquery dependency -->
    <script src="js/jquery-2.1.4.min.js"></script>

	<style>
		.centertext {text-align: center;}

		.center {
			display: block;
			margin-left: auto;
			margin-right: auto;
			width: 50%;
		}
	</style>

  </head>

  <body style="padding-top: 40px">

	<?php include 'navbar.php'; ?>

	<div class="container" style="width: 100%; margin-top: 10px">

		<div class="page-header">
			<p class="lead centertext">Yikes! <?php echo urldecode($error_message); ?></p>
		</div>

		 <!-- Snippet Code -->
		<div>
			<img src="tbrun1.png" class="img-responsive center" alt="Responsive image">
			<p class="lead centertext">Sorry about that. Shall we try again?</p>		  
		</div>
	   <!-- Snipet Code end -->

	</div> <!-- /container -->

	<?php include 'footer.php'; ?>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>

  </body>
</html>
