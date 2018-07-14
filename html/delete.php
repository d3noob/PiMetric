<?php

// set errors to null
$error_message_name = '';
	
// Set base Variables
$name = '';

// Quick rough sanitize function
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Get all the metric names from the status database
$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');
$result = $db->query('SELECT name FROM status');
$resultslist = array();
$i = 0;
foreach($result as $row) {
   $resultslist[$i] = $row['name'];
   $i++;
}


if ( $_POST ) {
	
	// recieve information from a POST and if it exists, do a rough sanitize
	// and where there isn't any information set it to ''
	$name = isset($_POST['name']) ? test_input($_POST['name']) : '';


	//Validate name
	if ($name == '') {
		$error_message_name = "The metric name is required";
	}
	if ($name != preg_replace("/[^A-Za-z0-9\_]/", '', $name)) {
			$error_message_name = "Invalid character in Name. Alternative suggested.";
			$name = preg_replace("/[^A-Za-z0-9\_]/", '', $name);
	}
	
	//Jump if no errors.
	if ($error_message_name == '') {

		echo "No Errors. Deleting metric";
		
		// Connect to the sqlite db (this needs to have the right permissions
		$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');

		/* Create a prepared statement */
		$sql = "DELETE FROM status WHERE name = :name";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);   
		$stmt->execute();

		/* close connection */
		$db = null;

		// Jump to the read page
		header("Location: read.php");

		exit();
	} 
} else if ($_GET) {

	$name = $_GET['name'];

	$bingo = 0;

	// Check the name against all the currently used names to ensure validity
	$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');
	$result = $db->query('SELECT name FROM status');

	foreach($result as $row) {
		if ($row['name'] == $name) {
			$bingo = 1;
		}
	}
	// Set error if the name has been used before
	if ($bingo != 1) {
		header("Location: fail.php?error_message=The+name+selected+to+be+deleted+is+not+valid.");
		exit();	}
	$bingo = 0;
	
} 

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Delete Metric</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet" type="text/css">
    <!-- jquery dependency -->
    <script src="js/jquery-2.1.4.min.js"></script>

  </head>

  <body style="padding-top: 60px">

	<?php include 'navbar.php'; ?>

<div class="container" style="width: 100%; margin-top: 10px">

	<div class="row">

		<div class="col-xs-2"> <!-- left col -->
			<div class="container-fluid   pull-right" style="width: 100%; margin-top:10px;" >
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='create.php' class='btn btn-success' style='width: 100%'>Create New Metric</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='edit.php' class='btn btn-success' style='width: 100%'>Edit a New Metric</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='duplicate.php?name=<?php echo $name;?>' class='btn btn-success' style='width: 100%'>Duplicate Current Metric</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='info.php?name=<?php echo $name;?>' class='btn btn-info' style='width: 100%'>View Metric Info</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='main.php?parent=<?php echo $parent;?>' class='btn btn-info' style='width: 100%'>Operating page</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='read.php' class='btn btn-info' style='width: 100%'>View Metrics Table</a>
				</div>
		
			</div>
		</div> <!-- col -->
		
		<div class="col-xs-10">

    <!-- *** The start of the form *** -->
	<form class="form-horizontal" role="form" action="delete.php" method="post">

      <!-- *** name field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="name" class="col-sm-2 control-label">Metric to be deleted:</label>
		<div class="col-sm-4">
		  <select class="form-control" id="name" name="name" multiple size="20">
		  <?php
			for ($x = 0; $x < count($resultslist); $x++) { ?>
			  <option <?php if ($resultslist[$x] == $name) {echo "selected ";} ?> value="<?php echo $resultslist[$x]; ?>"><?php echo $resultslist[$x]; ?></option>
		  <?php
			} ?>
		  </select>
		  <button type="submit" class="btn btn-lg btn-danger" style="margin-top: 20px;">Delete Metric</button>
		</div>
		<div class="col-sm-4 control-label" style="text-align: left">
		  <span style="font-size: 10px; color: grey" >Metric name</span>
		</div>
	  </div>
	  </div>

	</form>

		</div> <!-- col -->

	</div> <!-- row -->

    </div> <!-- /container -->

	<?php include 'footer.php'; ?>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

  </body>
</html>
