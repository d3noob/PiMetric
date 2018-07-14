<?php

 if ($_GET) {

	$name = $_GET['name'];
	
	// Need to validate $name as being one of the possible metrics. Otherwise return error.
	if ($name != preg_replace("/[^A-Za-z0-9\_]/", '', $name)) {
		// Go to the fail page
		// Perhaps send a error message to a log?
		header("Location: fail.php?error_message=The+metric+name+has+disallowed+characters.");
		exit();
	}

	// Check the name against all the possible names to ensure validity
	$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');
	$result = $db->query('SELECT name FROM status');

	$bingo = 0;
	foreach($result as $row) {
		if ($row['name'] == $name) {
			$bingo = 1;
		}
	}

	if ($bingo == 0) {
		// Go to the fail page
		// Perhaps send a error message to a log?
		header("Location: fail.php?error_message=I+couldn't+find+the+name+of+the+metric+in+metricdata.php.");
		exit();
	}
	$bingo = 0;


	// Get all the metric data from the status database
	$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');

	$query = "SELECT * FROM status WHERE 
			  name = :name";
	$statement = $db->prepare($query);
	$statement->bindValue(':name', $name);
	$statement->execute();
	
	// compile the returned data
	$values = $statement->fetchAll(PDO::FETCH_ASSOC);

	// print the data
	echo json_encode($values);


	/* close connection */
	$db = null;
		
	// echo $result['name'];
	
} else {
	
	header("Location: fail.php?error_message=Attempt+to+gather+metrics+based+on+name+without+naming+the+metric.");
	exit();
		
}











?>
