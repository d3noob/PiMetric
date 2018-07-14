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
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <!--  <link rel="icon" href="../../favicon.ico"> -->

    <title>Failure</title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/justified-nav.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="../js/ie-emulation-modes-warning.js"></script>

    <script src="../js/jquery-2.1.4.min.js"></script>

	<style>
		.centertext {text-align: center;}
	</style>

  </head>




  <body>

    <div class="container">

      <!-- The justified navigation menu is meant for single line per list item.
           Multiple lines will require custom code not provided by Bootstrap. -->
      <div class="masthead">
        <h3 class="text-muted">PiMetric Configuration Management</h3>
        <nav>
          <ul class="nav nav-justified">
            <li><a href="create.php">Create</a></li>
            <li><a href="read.php">Read</a></li>
            <li><a href="edit.php">Edit</a></li>
            <li><a href="delete.php">Delete</a></li>
            <li><a href="faqs.php">FAQs</a></li>
            <li><a href="main.php">Operating</a></li>
          </ul>
        </nav>
      </div>

      <div class="page-header">
        <p class="lead centertext">Yikes! <?php echo urldecode($error_message); ?></p>
      </div>

		 <!-- Snippet Code -->
		<div>

		<img src="tbrun1.png" class="img-responsive" alt="Responsive image">
        <p class="lead centertext">Sorry about that. Shall we try again?</p>

                  
	   </div>
	   <!-- Snipet Code end -->


      <!-- Site footer -->
      <footer class="footer">
		<img src="ee.png">
      </footer>

    </div> <!-- /container -->


    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>


    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="../js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
