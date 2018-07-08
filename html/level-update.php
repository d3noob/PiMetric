<?php

# The only presumption is that the variable 'name' for a metric is
# passed to theis code block. From here it will work out 
# what the parent metric of 'name' is and discover all od the metrics
# that fall under that parent. It will then iterate through those
# metrics and establish what the highest alert level or acknowledged
# level should be applied to the parent. This is then applied to the
# parent and the parent becomes the new metric baseline. trhus the cycle
# repeats untill the parent of the metric is 'null'

# For testing
# $name = 'temp_inside';

# Get the parent of the current metric from the status database
$db = new PDO('sqlite:/srv/monitoring/monitoring');
$stmt = $db->prepare("SELECT parent FROM status WHERE name = :name");
$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
$stmt -> execute();
$result = $stmt -> fetch();
$db = null;

# Load the Current parent variable
$parent_C = $result[0];

#echo $name," ",$parent,"</br>";

# Enter the loop were we iterate upwards through the parents
while ($parent_C != 'null') {
	
    # Get the parent [P] of the current parent [C] 
	$db = new PDO('sqlite:/srv/monitoring/monitoring');
	$stmt = $db->prepare("SELECT * FROM status WHERE name = :parent");
	$stmt -> bindParam(':parent', $parent_C, PDO::PARAM_STR);
	$stmt -> execute();
	$result = $stmt -> fetch();
	$db = null;

	# Load the metric parameters into the current operating set
	$parent_P = $result['parent'];

    # Get all metrics with current parent
	$db = new PDO('sqlite:/srv/monitoring/monitoring');
	$stmt = $db->prepare("SELECT * FROM status WHERE parent = :parent and monitored >= 1");
	$stmt -> bindParam(':parent', $parent_C, PDO::PARAM_STR);
	$stmt -> execute();
	$result = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$db = null;
		
	# Set the default values to 'green'
	$highest_level_ack = 'green';
	$highest_level_actual = 'green';

	# Reset the index for the loop
	$i = 0;

	# loop through the metrics that belong to the current parent [C]
	# to work out what the highest level_actual and level_ack colours are
	foreach($result as $row) {
			
		$level_actual_M = $result[$i]['level_actual'];
		$level_ack_M = $result[$i]['level_ack'];

		# Compare the metric with the highest level_ack colour for the current parent [C]
		if ($level_ack_M == 'blue') {
			$highest_level_ack = 'blue';
		} else if ($level_ack_M == 'red' and $highest_level_ack != 'blue' and $highest_level_ack != 'red') {
			$highest_level_ack = 'red';
		} else if ($level_ack_M == 'orange' and $highest_level_ack != 'blue' and $highest_level_ack != 'red' and $highest_level_ack != 'orange') {
			$highest_level_ack = 'orange';
		}

		# Compare the metric with the highest level_actual colour for the current parent [C]
		if ($level_actual_M == 'blue') {
			$highest_level_actual = 'blue';
		} else if ($level_actual_M == 'red' and $highest_level_actual != 'blue' and $highest_level_actual != 'red') {
			$highest_level_actual = 'red';
		} else if ($level_actual_M == 'orange' and $highest_level_actual != 'blue' and $highest_level_actual != 'red' and $highest_level_actual != 'orange') {
			$highest_level_actual = 'orange';
		}

		$i++;

	}
    
    # The highest_level_ack at this point is the highest acknowledged alert level for that parent
	# The highest_level_actual at this point is the highest actual alert level for that parent

	# update the database to reflect the new alert levels for the current parent [C]
	$db = new PDO('sqlite:/srv/monitoring/monitoring');
	$sql = 	"UPDATE status SET level_ack = :highest_level_ack, level_actual = :highest_level_actual WHERE name = :parent_C";
	$stmt = $db -> prepare($sql);
	$stmt -> bindParam(':parent_C', $parent_C, PDO::PARAM_STR);
	$stmt -> bindParam(':highest_level_ack', $highest_level_ack, PDO::PARAM_STR);
	$stmt -> bindParam(':highest_level_actual', $highest_level_actual, PDO::PARAM_STR);
	$stmt -> execute();
	$db = null;

	# Make the current parent [C] the move up one level
	# If this makes the current parent 'null' the loop finishes
	$parent_C = $parent_P;

}


?>
