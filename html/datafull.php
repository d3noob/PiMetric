<?php

 if ($_GET) {

	$name = $_GET['name'];
	$data= array();
	
	// Need to validate $name as being one of the possible metrics. Otherwise return error.

	// Get all the metric names from the status database
	$db = new PDO('sqlite:/srv/monitoring/monitoring');

	$query = "SELECT * FROM stored WHERE 
			  name = :name";
	$statement = $db->prepare($query);
	$statement->bindValue(':name', $name);

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
