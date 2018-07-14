<?php

 if ($_GET) {

	$parent = $_GET['parent'];
	$data= array();
	
	// Need to validate $parent as being one of the possible metrics. Otherwise return error.

	// Get all the metrics from the stored db that have `parent` as a parent from the status database
	$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');

	$query = "SELECT * FROM stored WHERE 
			  dtg>DATETIME('now','localtime', '-36 hours')
				AND name IN (
				SELECT name from status 
				WHERE monitored=1 
				AND measured=1 
				AND parent = :parent
				) 
			  ORDER BY dtg DESC";

	$statement = $db->prepare($query);
	$statement->bindValue(':parent', $parent);

	$statement->execute();
	

	// compile the returned data
	$values = $statement->fetchAll(PDO::FETCH_ASSOC);
	array_push($data, $values);

	// print the data
	echo json_encode($values);


	/* close connection */
	$db = null;
		
	// echo $result['name'];
	
} else {
	
	
	// Probably should have something else here.
	echo "failed";
}



?>
