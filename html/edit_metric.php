<?php

// set errors to null
$error_message_name = '';
$error_message_value = '';
$error_message_dtg = '';
$error_message_script = '';
$error_message_schedule = '';
$error_message_parent = '';
$error_message_priority = '';
$error_message_measured = '';
$error_message_monitored = '';
$error_message_alert_lower = '';
$error_message_caution_lower = '';
$error_message_normal_lower = '';
$error_message_normal_upper = '';
$error_message_caution_upper = '';
$error_message_alert_upper = '';
$error_message_level_actual = '';
$error_message_level_ack = '';
$error_message_help_url = '';
$error_message_description = '';
$error_message_label = '';
$error_message_acknowledged = '';
	
// Set base Variables
$name = '';
$value = '';
$dtg = '';
$script = '';
$schedule = 30;
$parent = '';
$priority = '';
$measured = '';
$monitored = '';
$alert_lower = '';
$caution_lower = '';
$normal_lower = '';
$normal_upper = '';
$caution_upper = '';
$alert_upper = '';
$level_actual = '';
$level_ack = '';
$help_url = '';
$description = '';
$label = '';
$acknowledged = '';


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
	$original_name = isset($_POST['original_name']) ? test_input($_POST['original_name']) : '';
	$value = isset($_POST['value']) ? test_input($_POST['value']) : '';
	$dtg = isset($_POST['dtg']) ? test_input($_POST['dtg']) : '';
	$script = isset($_POST['script']) ? test_input($_POST['script']) : '';
	$schedule = isset($_POST['schedule']) ? test_input($_POST['schedule']) : '';
	$parent = isset($_POST['parent']) ? test_input($_POST['parent']) : '';
	$priority = isset($_POST['priority']) ? test_input($_POST['priority']) : '';
	$measured = isset($_POST['measured']) ? 1 : 0 ;
	$monitored = isset($_POST['monitored']) ? 1 : 0 ;
	$alert_lower = isset($_POST['alert_lower']) ? test_input($_POST['alert_lower']) : '';
	$caution_lower = isset($_POST['caution_lower']) ? test_input($_POST['caution_lower']) : '';
	$normal_lower = isset($_POST['normal_lower']) ? test_input($_POST['normal_lower']) : '';
	$normal_upper = isset($_POST['normal_upper']) ? test_input($_POST['normal_upper']) : '';
	$caution_upper = isset($_POST['caution_upper']) ? test_input($_POST['caution_upper']) : '';
	$alert_upper = isset($_POST['alert_upper']) ? test_input($_POST['alert_upper']) : '';
	$level_actual = isset($_POST['level_actual']) ? test_input($_POST['level_actual']) : '';
	$level_ack = isset($_POST['level_ack']) ? test_input($_POST['level_ack']) : '';
	$help_url = isset($_POST['help_url']) ? test_input($_POST['help_url']) : '';
	$description = isset($_POST['description']) ? test_input($_POST['description']) : '';
	$label = isset($_POST['label']) ? test_input($_POST['label']) : '';
	$acknowledged = isset($_POST['acknowledged']) ? 1 : 0 ;

	// Validate all the status variables (used in create and edit)
	include 'status_validation.php';

	// Special validation to check if the original name is being changed
	// and the new name belongs to yet another metric
	//(wow. confusing eh?)
	$bingo = 0;

	// Check the name against all the currently used names
	$db = new PDO('sqlite:/srv/monitoring/monitoring');
	$result = $db->query('SELECT name FROM status');

	foreach($result as $row) {
		if (($name != $original_name) && ($row['name'] == $name)) {
			$bingo = 1;
		}
	}
	// Set error if the name has been used before
	if ($bingo == 1) {
		$error_message_name = 'The metric name has already been used';
	}
	$bingo = 0;

	//Jump if no errors.
	if ($error_message_name == '' &&
		$error_message_value == '' &&
		$error_message_dtg == '' &&
		$error_message_script == '' &&
		$error_message_schedule == '' &&
		$error_message_parent == '' &&
		$error_message_priority == '' &&
		$error_message_measured == '' &&
		$error_message_monitored == '' &&
		$error_message_alert_lower == '' &&
		$error_message_caution_lower == '' &&
		$error_message_normal_lower == '' &&
		$error_message_normal_upper == '' &&
		$error_message_caution_upper == '' &&
		$error_message_alert_upper == '' &&
		$error_message_level_actual == '' &&
		$error_message_level_ack == '' &&
		$error_message_help_url == '' &&
		$error_message_description == '' &&
		$error_message_label == '' &&
		$error_message_acknowledged == ''
		) {


		# Change the levels to represent the value
		if ($measured >= 0 && $value != '') {
			if ($value >= $normal_lower && $value <= $normal_upper ) {
				$level_actual = 'green';
				$level_ack = 'green';			
			} else if ($value >= $caution_lower && $value <= $caution_upper) {
				$level_actual = 'orange';
				$level_ack = 'orange';			
			}
			 else if ($value >= $alert_lower && $value <= $alert_upper) {
				$level_actual = 'red';
				$level_ack = 'red';		
			} else {
				$level_ack = 'blue';
			}
		}
		
		# if the metric is acknowledged, the level_ack needs to be green
		if ($acknowledged >= 1) {
			$level_ack = 'green';
		}
		
		# write our information to the database
		$db = new PDO('sqlite:/srv/monitoring/monitoring');

		# Create a prepared statement
		$sql = 	"UPDATE status SET ".
				"name = :name, ".
				"value = :value, ".
				"dtg = :dtg, ".
				"script = :script, ".
				"schedule = :schedule, ".
				"parent = :parent, ".
				"priority = :priority, ".
				"measured = :measured, ".
				"monitored = :monitored, ".
				"alert_lower = :alert_lower, ".
				"caution_lower = :caution_lower, ".
				"normal_lower = :normal_lower, ".
				"normal_upper = :normal_upper, ".
				"caution_upper = :caution_upper, ".
				"alert_upper = :alert_upper, ".
				"level_actual = :level_actual, ".
				"level_ack = :level_ack, ".
				"help_url = :help_url, ".
				"description = :description, ".
				"label = :label, ".
				"acknowledged = :acknowledged ".
				"WHERE name = :original_name";
		
		$stmt = $db -> prepare($sql);
		
		/* bind params */
		$stmt -> bindParam(':original_name', $original_name, PDO::PARAM_STR);
		$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
		$stmt -> bindParam(':value', $value);
		$stmt -> bindParam(':dtg', $dtg, PDO::PARAM_STR);
		$stmt -> bindParam(':script', $script, PDO::PARAM_STR);
		$stmt -> bindParam(':schedule', $schedule, PDO::PARAM_INT);
		$stmt -> bindParam(':parent', $parent, PDO::PARAM_STR);
		$stmt -> bindParam(':priority', $priority, PDO::PARAM_INT);
		$stmt -> bindParam(':measured', $measured, PDO::PARAM_INT);
		$stmt -> bindParam(':monitored', $monitored, PDO::PARAM_INT);
		$stmt -> bindParam(':alert_lower', $alert_lower);
		$stmt -> bindParam(':caution_lower', $caution_lower);
		$stmt -> bindParam(':normal_lower', $normal_lower);
		$stmt -> bindParam(':normal_upper', $normal_upper);
		$stmt -> bindParam(':caution_upper', $caution_upper);
		$stmt -> bindParam(':alert_upper', $alert_upper);
		$stmt -> bindParam(':level_actual', $level_actual, PDO::PARAM_STR);
		$stmt -> bindParam(':level_ack', $level_ack, PDO::PARAM_STR);
		$stmt -> bindParam(':help_url', $help_url, PDO::PARAM_STR);
		$stmt -> bindParam(':description', $description, PDO::PARAM_STR);
		$stmt -> bindParam(':label', $label, PDO::PARAM_STR);
		$stmt -> bindParam(':acknowledged', $acknowledged, PDO::PARAM_INT);

		/* execute the query */
		$stmt -> execute();
		
		/* close connection */
		$db = null;

		# Update the levels for this metric's group and up the chain
		include 'level-update.php';

		// Jump to the info page
		header("Location: info.php?name=".$name);

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

	// Set the $name as the original name in case it changes
	// (this is then included in a hidden POST field in the form below) 
	$original_name = $name;
	
	// Get all the metric parameters from the status database
	$db = new PDO('sqlite:/srv/monitoring/monitoring');
	$statement = $db->prepare("SELECT * FROM status WHERE name=:name;");
	$statement->bindValue(':name', $name);
	$statement->execute();
	
	// Get the result.
	$result = $statement->fetch();

	/* close connection */
	$db = null;
			
	// Load the metric parameters into the current operating set
	$name = $result['name'];
	$value = $result['value'];
	$dtg = $result['dtg'];
	$script = $result['script'];
	$schedule = $result['schedule'];
	$parent = $result['parent'];
	$priority = $result['priority'];
	$measured = $result['measured'];
	$monitored = $result['monitored'];
	$alert_lower = $result['alert_lower'];
	$caution_lower = $result['caution_lower'];
	$normal_lower = $result['normal_lower'];
	$normal_upper = $result['normal_upper'];
	$caution_upper = $result['caution_upper'];
	$alert_upper = $result['alert_upper'];
	$level_actual = $result['level_actual'];
	$level_ack = $result['level_ack'];
	$help_url = $result['help_url'];
	$description = $result['description']; 	
	$label = $result['label']; 	
	$acknowledged = $result['acknowledged']; 	
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
					<a href='view.php?name=<?php echo $name;?>' class='btn btn-info' style='width: 100%'>View Graphs</a>
				</div>

				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='main.php?parent=<?php echo $parent;?>' class='btn btn-info' style='width: 100%'>Operating page</a>
				</div>
				<div class="row" style="width: 100%; margin-bottom: 10px">
					<a href='read.php' class='btn btn-info' style='width: 100%'>View Metrics Table</a>
				</div>
				<?php If ($acknowledged >=1) {include 'unacknowledge-button.php';} ?>
				<?php If ($acknowledged < 1 && $level_actual != 'green') {include 'acknowledge-button.php';} ?>
				<div class="row" style="width: 100%; margin-bottom: 10px;  margin-top: 30px;">
					<a href='delete.php' class='btn btn-danger' style='width: 100%'>Delete Metric...</a>
				</div>			
			</div>
		</div> <!-- col -->
		
		<div class="col-xs-10">


    <!-- *** The start of the form *** -->
	<form class="form-horizontal" role="form" action="edit_metric.php" method="post">

      <!-- *** label field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="label" class="col-sm-2 control-label">Metric label</label>
		<div class="col-sm-4">
		  <input type="text" 
		         class="form-control" 
		         id="label" 
		         name="label" 
		         placeholder="Metric label"
		         value="<?php echo $label; ?>"
		    <?php if ( $error_message_label != '' ) {echo "style=\"border-color: red\"";} ?>
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_label != '' ) {
			  echo "<font style=\"color: red\">$error_message_label</font>";
			  } else { echo "Keep the fancey characters to a minimum please."; }
			?>
		  </span>
	    </div>
	  </div>
	  </div>

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
		    <?php if ( $error_message_name != '' ) {echo "style=\"border-color: red\"";} ?>
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_name != '' ) {
			  echo "<font style=\"color: red\">$error_message_name</font>";
			  } else { echo "Lowercase alphanumerics and underscore only"; }
			?>
		  </span>
	    </div>
	  </div>
	  </div>

	  <input type="hidden" name="original_name" value="<?php echo $original_name; ?>">

      <!-- *** value field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="value" class="col-sm-2 control-label">Value</label>
		<div class="col-sm-4">
		  <input type="text" 
		         class="form-control" 
		         id="value" 
		         name="value" 
		         placeholder="metric value"
		         value="<?php echo $value; ?>"
		    <?php if ( $error_message_value != '' ) {echo "style=\"border-color: red\""; } ?>
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_value != '' ) {
			  echo "<font style=\"color: red\">$error_message_value</font>";
			  } else { echo "(defaults to 0 for new metrics)"; } 
			?>
		  </span>
	    </div>
	  </div>
	  </div>

      <!-- *** script field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="script" class="col-sm-2 control-label">Script name</label>
		<div class="col-sm-4">
		  <input type="text" 
		         class="form-control" 
		         id="script" 
		         name="script" 
		         placeholder="Script name"
		         value="<?php echo $script; ?>"
		    <?php if ( $error_message_script != '' ) {echo "style=\"border-color: red\"";} ?>
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_script != '' ) {
			  echo "<font style=\"color: red\">$error_message_script</font>";
			  } else { echo "Leave blank to use the metric name by default"; }
			?>
		  </span>
	    </div>
	  </div>
	  </div>


      <!-- *** dtg field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="dtg" class="col-sm-2 control-label">Date Time</label>
		<div class="col-sm-4">
		  <input type="text" 
		         class="form-control" 
		         id="dtg" 
		         name="dtg" 
		         placeholder="<?php echo $dtg; ?>"
		         value="<?php echo $dtg; ?>"
		         readonly
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php  echo "Not editable"; ?>
		  </span>
	    </div>
	  </div>
	  </div>

      <!-- *** schedule field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="schedule" class="col-sm-2 control-label">Schedule</label>
		<div class="col-sm-4">
		  <select class="form-control" id="schedule" name="schedule">
		    <option <?php if ($schedule == 1) {echo "selected";} ?>>1</option>
		    <option <?php if ($schedule == 5) {echo "selected";} ?>>5</option>
		    <option <?php if ($schedule == 10) {echo "selected";} ?>>10</option>
		    <option <?php if ($schedule == 30) {echo "selected";} ?>>30</option>
		    <option <?php if ($schedule == 60) {echo "selected";} ?>>60</option>
		    <option <?php if ($schedule == 120) {echo "selected";} ?>>120</option>
		    <option <?php if ($schedule == 240) {echo "selected";} ?>>240</option>
		    <option <?php if ($schedule == 360) {echo "selected";} ?>>360</option>
		    <option <?php if ($schedule == 720) {echo "selected";} ?>>720</option>
		    <option <?php if ($schedule == 1440) {echo "selected";} ?>>1440</option>
		  </select>
		</div>
		<div class="col-sm-4 control-label" style="text-align: left">
		  <span style="font-size: 10px; color: grey" > Metric check period in minutes</span>
		</div>
	  </div>
	  </div>

      <!-- *** parent field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="parent" class="col-sm-2 control-label">Parent metric</label>
		<div class="col-sm-4">
		  <input type="text" 
		         class="form-control" 
		         id="parent" 
		         name="parent" 
		         placeholder="Parent metric name"
		         value="<?php echo $parent; ?>"
		    <?php if ( $error_message_parent != '' ) {echo "style=\"border-color: red\"";} ?>
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_parent != '' ) {
			  echo "<font style=\"color: red\">$error_message_parent</font>";
			  } else { echo "Lowercase alphanumerics and underscore only"; }
			?>
		  </span>
	    </div>
	  </div>
	  </div>

      <!-- *** priority field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="priority" class="col-sm-2 control-label">Priority</label>
		<div class="col-sm-4">
		  <input type="text" 
		         class="form-control" 
		         id="priority" 
		         name="priority" 
		         placeholder="metric priority"
		         value="<?php echo $priority; ?>"
		    <?php if ( $error_message_priority != '' ) {echo "style=\"border-color: red\""; } ?>
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_priority != '' ) {
			  echo "<font style=\"color: red\">$error_message_priority</font>";
			  } else { echo "Lower integer = highest priority"; } 
			?>
		  </span>
	    </div>
	  </div>
	  </div>

      <!-- *** measured and monitored fields *** -->
      <div class="row">
	  <div class="form-group">
        <label class="col-sm-2 control-label">Measured</label>
        <div class="checkbox col-sm-1">
            <label for="measured" >
			  <input type="checkbox"
		             id="measured" 
		             name="measured" 
					 value="<?php echo $measured; ?>"
		             <?php if ( $measured != '0' ) {echo " checked"; } ?>			  
			  >
			</label>
        </div>

        <label class="col-sm-1 control-label">Monitored</label>
        <div class="checkbox col-sm-1">
            <label for="monitored" >
			  <input type="checkbox"
		             id="monitored" 
		             name="monitored" 
		             <?php if ( $monitored != '0' ) {echo " checked"; } ?>			  
			  >
			</label>
        </div>

        <label class="col-sm-1 control-label">Acknowledged</label>
        <div class="checkbox col-sm-1">
            <label for="acknowledged" >
			  <input type="checkbox"
		             id="acknowledged" 
		             name="acknowledged" 
		             <?php if ( $acknowledged != '0' ) {echo " checked"; } ?>			  
			  >
			</label>
        </div>

        <div class="col-sm-5 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php
		      if ( $error_message_measured != '' ) {
				echo "<font style=\"color: red\">$error_message_measured</font>";
			  } else { echo "Is it measured? "; } 
			  if ( $error_message_monitored != '' ) {
				echo "<font style=\"color: red\">$error_message_monitored</font>";
			  } else { echo "Does it get checked? "; } 
			  if ( $error_message_acknowledged != '' ) {
				echo "<font style=\"color: red\">$error_message_acknowledged</font>";
			  } else { echo "Is it acknowledged?"; } 
			?>
		  </span>
	    </div>
	  </div>
	  </div>

      <!-- *** Alert limits fields *** -->
      <div class="row">
	    <div class="form-group">
          <label class="col-sm-2 control-label">Alert limits</label>
          <div class="col-lg-6" style="margin: 0 0 10px">
			<div class="row">
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 15px">
				<p style="text-align: center; margin: 0 0 0px">Alert</p>
				<input type="text" class="form-control"
				       id="alert_lower"
				       name="alert_lower"
				       value="<?php echo $alert_lower; ?>"
				       style="text-align: center; border-color: red">
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Caution</p>
				<input type="text" class="form-control"
				       id="caution_lower"
				       name="caution_lower"
				       value="<?php echo $caution_lower; ?>"
				       style="text-align: center; border-color: orange">
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Normal</p>
				<input type="text" class="form-control"
				       id="normal_lower"
				       name="normal_lower"
				       value="<?php echo $normal_lower; ?>"
				       style="text-align: center; border-color: green">
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Normal</p>
				<input type="text" class="form-control"
				       id="normal_upper"
				       name="normal_upper"
				       value="<?php echo $normal_upper; ?>"
				       style="text-align: center; border-color: green">
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Caution</p>
				<input type="text" class="form-control"
				       id="caution_upper"
				       name="caution_upper"
				       value="<?php echo $caution_upper; ?>"
				       style="text-align: center; border-color: orange">
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
              <div class="col-xs-2" style="padding-right: 15px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Alert</p>
				<input type="text" class="form-control"
				       id="alert_upper"
				       name="alert_upper"
				       value="<?php echo $alert_upper; ?>"
				       style="text-align: center; border-color: red">
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
			</div>
          </div>
		  <div class="col-sm-4">
			<?php 
			//Any alert level messages to display
			if ( $error_message_alert_lower != '' ) {
				echo "<p style=\"font-size: 10px; color: red\">$error_message_alert_lower</p>";
			} 
			if ( $error_message_caution_lower != '' ) {
				echo "<p style=\"font-size: 10px; color: red\">$error_message_caution_lower</p>";
			} 
			if ( $error_message_normal_lower != '' ) {
				echo "<p style=\"font-size: 10px; color: red\">$error_message_normal_lower</p>";
			} 
			if ( $error_message_normal_upper != '' ) {
				echo "<p style=\"font-size: 10px; color: red\">$error_message_normal_upper</p>";
			} 
			if ( $error_message_caution_upper != '' ) {
				echo "<p style=\"font-size: 10px; color: red\">$error_message_caution_upper</p>";
			} 
			if ( $error_message_alert_upper != '' ) {
				echo "<p style=\"font-size: 10px; color: red\">$error_message_alert_upper</p>";
			} 
			?>
		  </div>	  
        </div>
      </div>

      <!-- *** level_actual field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="level_actual" class="col-sm-2 control-label">Actual level</label>
		<div class="col-sm-4">
		  <select class="form-control" id="level_actual" name="level_actual">
		    <option <?php if ($level_actual == "green") {echo "selected";} ?>>green</option>
		    <option <?php if ($level_actual == "orange") {echo "selected";} ?>>orange</option>
		    <option <?php if ($level_actual == "red") {echo "selected";} ?>>red</option>
		    <option <?php if ($level_actual == "blue") {echo "selected";} ?>>blue</option>
		  </select>
		</div>
		<div class="col-sm-4 control-label" style="text-align: left">
		  <span style="font-size: 10px; color: grey" >The actual level of the metric</span>
		</div>
	  </div>
	  </div>

      <!-- *** level_ack field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="level_ack" class="col-sm-2 control-label">Acknowledged level</label>
		<div class="col-sm-4">
		  <select class="form-control" id="level_ack" name="level_ack">
		    <option <?php if ($level_ack == "green") {echo "selected";} ?>>green</option>
		    <option <?php if ($level_ack == "orange") {echo "selected";} ?>>orange</option>
		    <option <?php if ($level_ack == "red") {echo "selected";} ?>>red</option>
		    <option <?php if ($level_ack == "blue") {echo "selected";} ?>>blue</option>
		  </select>
		</div>
		<div class="col-sm-4 control-label" style="text-align: left">
		  <span style="font-size: 10px; color: grey" >The acknowledged level of the metric</span>
		</div>
	  </div>
	  </div>

      <!-- *** help_url field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="help_url" class="col-sm-2 control-label">Help URL</label>
		<div class="col-sm-4">
		  <input type="text" 
		         class="form-control" 
		         id="help_url" 
		         name="help_url" 
		         placeholder="URL"
		         value="<?php echo $help_url; ?>"
		    <?php if ( $error_message_help_url != '' ) {echo "style=\"border-color: red\"";} ?>
		  >
		</div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_help_url != '' ) {
			  echo "<font style=\"color: red\">$error_message_help_url</font>";
			  } else { echo "Valid URL's only"; }
			?>
		  </span>
	    </div>
	  </div>
	  </div>

      <!-- *** description field *** -->
      <div class="row">
	  <div class="form-group">
		<label for="description" class="col-sm-2 control-label">Description</label>
		<div class="col-sm-6">
          <textarea class="form-control"
                   id="description"
                   name="description"
                   rows="2"
                   placeholder="Description"
		           <?php if ( $error_message_description != '' ) {echo "style=\"border-color: red\"";} ?>
		           ><?php echo $description; ?></textarea>
			<button type="submit" class="btn btn-lg btn-success" style="margin-top: 20px;">Edit Metric</button>
	    </div>
        <div class="col-sm-4 control-label" style="text-align: left">
	      <span style="font-size: 10px; color: grey "> 
		    <?php if ( $error_message_description != '' ) {
			  echo "<font style=\"color: red\">$error_message_description</font>";
			  } else { echo "General text area"; }
			?>
		  </span>
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
