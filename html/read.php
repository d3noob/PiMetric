<?php

// Quick rough sanitize function
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Get all the metric names from the status database
$db = new PDO('sqlite:/srv/monitoring/monitoring');
$result = $db->query('SELECT * FROM status ORDER BY parent');
$resultslist = array();
$i = 0;
foreach($result as $row) {
	// convert timezone
	$dt = new DateTime($row['dtg']);
	$tz = new DateTimeZone('Pacific/Auckland'); // or whatever zone you're after
	$dt->setTimezone($tz);
	$row['dtg'] = $dt->format('Y-m-d H:i:s');

	// assign array variables
	$resultslist[$i]['label'] = $row['label'];
	$resultslist[$i]['name'] = $row['name'];
	$resultslist[$i]['parent'] = $row['parent'];
	$resultslist[$i]['value'] = $row['value'];
	$resultslist[$i]['dtg'] = $row['dtg'];
	$resultslist[$i]['schedule'] = $row['schedule'];
	$resultslist[$i]['measured'] = $row['measured'];
	$resultslist[$i]['monitored'] = $row['monitored'];
	$resultslist[$i]['acknowledged'] = $row['acknowledged'];
	$resultslist[$i]['alert_lower'] = $row['alert_lower'];
	$resultslist[$i]['caution_lower'] = $row['caution_lower'];
	$resultslist[$i]['normal_lower'] = $row['normal_lower'];
	$resultslist[$i]['normal_upper'] = $row['normal_upper'];
	$resultslist[$i]['caution_upper'] = $row['caution_upper'];
	$resultslist[$i]['alert_upper'] = $row['alert_upper'];
	$i++;
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Read Metrics</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet" type="text/css">
    <!-- jquery dependency -->
    <script src="js/jquery-2.1.4.min.js"></script>

	<style>
		.centertext {text-align: center;}
		.righttext {text-align: right;}

		div.band {
		  width: 100%;
		}

	</style>

  </head>

  <body style="padding-top: 50px">

	<?php include 'navbar.php'; ?>

<div class="container-fluid">
<div class="row band">
<div class="table-responsive">
	<table class="table table-hover table-condensed">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th>Label</th>
				<th>Name</th>
				<th>Parent</th>
				<th>Value</th>
				<th>Date / Time</th>
				<th>Schedule</th>
				<th>Meas</th>
				<th>Mon</th>
				<th>Ack</th>
				<th>Alert</th>
				<th>Caution</th>
				<th colspan="2" class="centertext">Normal</th>
				<th class="righttext">Caution</th>
				<th class="righttext">Alert</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($resultslist as $key=>$row) {
				echo "<tr>";
				if (
				     ($row['value'] > $row['alert_upper'] ) ||
				     ($row['value'] < $row['alert_lower'] )
				   )
				     { $value_highlight = " bgcolor='#385F6B' ";}
				else if (
				     ($row['value'] > $row['alert_lower'] && $row['value'] < $row['caution_lower']) ||
				     ($row['value'] < $row['alert_upper'] && $row['value'] > $row['caution_upper'])
				   )
				     { $value_highlight = " bgcolor='#6B3432' ";}
				else if (
				     ($row['value'] > $row['caution_lower'] && $row['value'] < $row['normal_lower']) ||
				     ($row['value'] < $row['caution_upper'] && $row['value'] > $row['normal_upper'])
				   )
				     { $value_highlight = " bgcolor='#745832' ";}
				else {$value_highlight = "";}
					echo "<td class='centertext'><a href='edit_metric.php?name=".$row['name']."' class='btn btn-xs btn-success'>Edit</a> </td>";
					echo "<td class='centertext'><a href='duplicate.php?name=".$row['name']."' class='btn btn-xs btn-info'>Duplicate</a> </td>";
					foreach($row as $key2=>$row2){
						if ($key2 == 'name') {
							echo "<td><a href='info.php?name=".$row['name']."'>".$row['name']."</a> </td>"; 
						} else if ($key2 == 'parent') {
							echo "<td><a href='info.php?name=".$row['parent']."'>".$row['parent']."</a> </td>"; 
						} else if ($key2 == 'value') {
							echo "<td".$value_highlight.">" .$row2 ." </td>"; 
						} else if ($key2 == 'alert_lower') {
							echo "<td bgcolor='#6B3432' >" . $row2 . "</td>";
						} else if ($key2 == 'caution_lower') {
							echo "<td bgcolor='#745832' >" . $row2 . "</td>";
						} else if ($key2 == 'normal_lower') {
							echo "<td bgcolor='#395C37' >" . $row2 . "</td>";
						} else if ($key2 == 'normal_upper') {
							echo "<td bgcolor='#395C37'"." align='right'>" . $row2 . "</td>";
						} else if ($key2 == 'caution_upper') {
							echo "<td bgcolor='#745832'"." align='right'>" . $row2 . "</td>";
						} else if ($key2 == 'alert_upper') {
							echo "<td bgcolor='#6B3432'"." align='right'>" . $row2 . "</td>";
						} else {					;
							echo "<td >" . $row2 . "</td>";
						}
					}
					echo "<td class='centertext'><a href='delete.php?name=".$row['name']."' class='btn btn-xs btn-danger'>Delete...</a> </td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>
</div>
</div>

    <div class="container">

    </div> <!-- /container -->

	<?php include 'footer.php'; ?>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

  </body>
</html>
