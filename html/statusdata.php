<?php

 if ($_GET) {

	$parent = $_GET['parent'];
	
	// Need to validate $name as being one of the possible metrics. Otherwise return error.
	if ($parent != preg_replace("/[^A-Za-z0-9\_]/", '', $parent)) {
		// Go to the fail page
		// Perhaps send a error message to a log?
		header("Location: fail.php?error_message=The+metric+name+has+disallowed+characters.");
		exit();
	}

	// Check the parent against all the possible parents to ensure validity
	$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');
	$result = $db->query('SELECT parent FROM status');

	$bingo = 0;
	foreach($result as $row) {
		if ($row['parent'] == $parent) {
			$bingo = 1;
		}
	}

	if ($bingo == 0) {
		// Go to the fail page
		// Perhaps send a error message to a log?
		header("Location: fail.php?error_message=I+couldn't+find+the+name+of+the+metric.");
		exit();
	}
	$bingo = 0;


	// Get all the metric names from the status database
	$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');

	$query = "SELECT * FROM status WHERE 
			  parent = :parent";
	$statement = $db->prepare($query);
	$statement->bindValue(':parent', $parent);
	$statement->execute();
	
	// compile the returned data
	$values = $statement->fetchAll(PDO::FETCH_ASSOC);

	// print the data
	echo json_encode($values);


	/* close connection */
	$db = null;
		
	// echo $result['name'];
	
} else {
	
	$parent = 'root';
	
	// Get all the metric names from the status database
	$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');

	$query = "SELECT * FROM status WHERE 
			  parent = 'root'";
	$statement = $db->prepare($query);
	$statement->execute();
	
	// compile the returned data
	$values = $statement->fetchAll(PDO::FETCH_ASSOC);

	// print the data
	echo json_encode($values);


	/* close connection */
	$db = null;
		
}











?>
