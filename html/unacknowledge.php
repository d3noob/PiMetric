<?php

// set errors to null
$error_message_name = '';
$error_message_dtg = '';
$error_message_ack_text = '';
$error_message_ack_state = '';
	
// Set base Variables
$name = '';
$dtg = '';
$ack_text = '';
$ack_state = '';

// Quick rough sanitize function
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_POST) {

	// recieve information from a POST and if it exists, do a rough sanitize
	// and where there isn't any information set it to ''
	$name = isset($_POST['name']) ? test_input($_POST['name']) : '';
	$ack_text = isset($_POST['ack_text']) ? test_input($_POST['ack_text']) : '';
	$ack_state = isset($_POST['ack_state']) ? 1 : 0 ;

	$dtg = date("Y-m-d H:i:s", time()); 


	//Validate name
	if ($name == '') {
		$error_message_name = "The metric name is required";
	}
	if ($name != preg_replace("/[^A-Za-z0-9\_]/", '', $name)) {
			$error_message_name = "Invalid character in Name. Alternative suggested.";
			$name = preg_replace("/[^A-Za-z0-9\_]/", '', $name);
	}
	if (strlen($name) >= 50) {
			$error_message_name = "Sorry, the name is too long. 50 characters maximum.";
			$name = substr($name, 0, 50);
	}
	//Validate ack_text
	if ($ack_text != preg_replace("/[^A-Za-z0-9 \.\'\?\(\)\@\!\,\:\[\]]/", '', $ack_text)) {
			$error_message_ack_text = "Chatacters Removed. Alternative suggested.";
			$ack_text = preg_replace("/[^A-Za-z0-9 \.\'\?\(\)\@\!\,\:\[\]]/", '', $ack_text);
	}	
	

	//Jump if no errors.
	if ($error_message_name == '' &&
		$error_message_dtg == '' &&
		$error_message_ack_text == '' &&
		$error_message_ack_state == ''
		) {
		
		// Insert the unacknowledge information into the sqlite db
		$db = new PDO('sqlite:/srv/monitoring/monitoring');
		$stmt = $db -> prepare("INSERT INTO acknowledge (name, dtg, ack_text, ack_state) VALUES (:name, :dtg, :ack_text, :ack_state)");
		$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
		$stmt -> bindParam(':dtg', $dtg, PDO::PARAM_STR);
		$stmt -> bindParam(':ack_text', $ack_text, PDO::PARAM_STR);
		$stmt -> bindParam(':ack_state', $ack_state, PDO::PARAM_INT);
		$stmt -> execute();
		$db = null;

		// Get the new level_ack metric parameter from the status database
		$db = new PDO('sqlite:/srv/monitoring/monitoring');
		$stmt = $db -> prepare("SELECT level_actual FROM status WHERE name = :name");
		$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
		$stmt -> execute();
		$result = $stmt -> fetch();
		$db = null;

		// The new level_ack is the current level_actual
		$level_ack = $result['level_actual'];

		// Update the acknowledged state and level_ack in the sqlite db
		$db = new PDO('sqlite:/srv/monitoring/monitoring');
		$sql = 	"UPDATE status SET acknowledged = 0, level_ack = :level_ack WHERE name = :name";
		$stmt = $db -> prepare($sql);
		$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
		$stmt -> bindParam(':level_ack', $level_ack, PDO::PARAM_STR);
		$stmt -> execute();
		$db = null;

		# Update the levels for this metric's group and up the chain
		include 'level-update.php';

		# Get the parent of the current metric from the status database
		$db = new PDO('sqlite:/srv/monitoring/monitoring');
		$stmt = $db->prepare("SELECT parent FROM status WHERE name = :name");
		$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
		$stmt -> execute();
		$result = $stmt -> fetch();
		$db = null;

		# Load the Current parent variable
		$parent = $result[0];
		
		# Jump to the read page
		header("Location: main.php?parent=".$parent);

		exit();
	} 
} else if ($_GET) {

	$name = $_GET['name'];

	if ($name != preg_replace("/[^A-Za-z0-9\_]/", '', $name)) {
		// Go to the fail page
		// Perhaps send a error message to a log?
		header("Location: fail.php?error_message=The+name+has+disallowed+characters.");
		exit();
	}

	// Special validation to check if the name is valid
	$bingo = 0;

	// Check the name against all the currently used names
	$db = new PDO('sqlite:/srv/monitoring/monitoring');
	$result = $db->query('SELECT name FROM status');

	foreach($result as $row) {
		if ($row['name'] == $name) {
			$bingo = 1;
		}
	}
	// Set error if the name has been used before
	if ($bingo != 1) {
		// Go to the fail page
		// Perhaps send a error message to a log?
		header("Location: fail.php?error_message=The+name+does+not+exist.");
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

    <title>Unacknowledge Metric</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet" type="text/css">
    <!-- jquery dependency -->
    <script src="js/jquery-2.1.4.min.js"></script>

  </head>

  <body style="padding-top: 50px">

    <div class="container">

	<?php include 'navbar.php'; ?>

<p></p>

    <!-- *** The start of the form *** -->
	<form class="form-horizontal" role="form" action="unacknowledge.php" method="post">

      <!-- *** name field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="name" class="col-sm-2 control-label">Metric name</label>
		<div class="col-sm-4">
		  <input type="text" 
		         class="form-control" 
		         id="name" 
		         name="name" 
		         placeholder="Metric name"
		         value="<?php echo $name; ?>"
		         readonly
		    <?php if ( $error_message_name != '' ) {echo "style=\"border-color: red\"";} ?>
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_name != '' ) {
			  echo "<font style=\"color: red\">$error_message_name</font>";
			  } else { echo "Not editable"; }
			?>
		  </span>
	    </div>
	  </div>
	  </div>

	  <input type="hidden" name="ack_state" value="1">


      <!-- *** un-ack_text field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="ack_text" class="col-sm-2 control-label">Unacknowledge Reason</label>
		<div class="col-sm-4">
          <textarea class="form-control"
                   id="ack_text"
                   name="ack_text"
                   rows="5"
                   placeholder="Why the metric is being unacknowledged"
		           <?php if ( $error_message_ack_text != '' ) {echo "style=\"border-color: red\"";} ?>
		           ><?php echo $ack_text; ?></textarea>
	    </div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_ack_text != '' ) {
			  echo "<font style=\"color: red\">$error_message_ack_text</font>";
			  } else { echo "General text area"; }
			?>
		  </span>
	    </div>
	  </div>
	  </div>



	  <div class="form-group">
		<div class="col-sm-3">
		  <button type="submit" class="btn btn-lg btn-success">Unacknowledge</button>
		</div>
	  </div>

	</form>

    </div> <!-- /container -->

	<?php include 'footer.php'; ?>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>


    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!-- <script src="js/ie10-viewport-bug-workaround.js"></script> -->
  </body>
</html>
