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
$db = new PDO('sqlite:/srv/monitoring/monitoring');
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
		
		// Jump to the editing page
		header("Location: edit_metric.php?name=".$name);

		exit();
	} 
} 

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Edit Metric</title>

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
		<div class="col-xs-3"> <!-- left col buttons -->
			<div class="container-fluid   pull-right" style="width: 100%; margin-top:10px;" >
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='create.php' class='btn btn-success' style='width: 100%'>Create a New Metric</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='main.php' class='btn btn-info' style='width: 100%'>Operating Page</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='read.php' class='btn btn-info' style='width: 100%'>View Metrics Table</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px;  margin-top: 30px;">
					<a href='delete.php' class='btn btn-danger' style='width: 100%'>Delete Metric...</a>
				</div>			
			</div>
		</div> <!-- col -->
		
		<div class="col-xs-9">
			<!-- *** The start of the form *** -->
			<form class="form-horizontal" role="form" action="edit.php" method="post">
				<!--  name field  -->
				<div class="row">
					<label for="name" class="col-sm-1 control-label">Select Metric</label>
					<div class="col-sm-4">
						<select class="form-control" id="name" name="name" multiple size="20">
						  <?php
							for ($x = 0; $x < count($resultslist); $x++) { ?>
							  <option value="<?php echo $resultslist[$x]; ?>"><?php echo $resultslist[$x]; ?></option>
						  <?php
							} ?>
						</select>
						<button type="submit" class="btn btn-lg btn-success" style="margin-top: 20px;">Start Editing</button>
					</div>
					<div class="col-sm-7 control-label" style="text-align: left">
						<span style="font-size: 10px; color: grey" >Metric name</span>
					</div>
				</div> <!-- row -->
			</form>
		</div> <!-- col -->
	</div> <!-- row -->

</div> <!-- /container -->

	<?php include 'footer.php'; ?>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

  </body>
</html>
