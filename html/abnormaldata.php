<?php

// Get all the metric names from the status database
// which are measured and monitored and not green
$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');

$query = "SELECT * FROM status WHERE
		  measured >= 1 AND
		  monitored >= 1 AND
		  (level_actual <> 'green' OR
		  level_ack <> 'green')";
$statement = $db->prepare($query);
$statement->execute();

// compile the returned data
$values = $statement->fetchAll(PDO::FETCH_ASSOC);

// print the data
echo json_encode($values);

/* close connection */
$db = null;


?>
