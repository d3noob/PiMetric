<?php

//Validate label
	if ($label == '') {
		$error_message_label = "The metric label is required";
	}
	if ($label != preg_replace("/[^A-Za-z0-9 \.\'\?\(\)\@\!\,\:\[\]]/", '', $label)) {
			$error_message_label = "Chatacters Removed. Alternative suggested.";
			$label = preg_replace("/[^A-Za-z0-9 \.\'\?\(\)\@\!\,\:\[\]]/", '', $label);
	}
	if (strlen($label) >= 50) {
			$error_message_label = "Sorry, the name is too long. 50 characters maximum.";
			$label = substr($label, 0, 50);
	}

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
//Validate value
	if ($value != preg_replace("/[^0-9\.]/", '', $value)) {
			$error_message_value = "Invalid character detected. Alternative suggested.";
			$value = preg_replace("/[^0-9\.]/", '', $value);
	}
//Validate dtg
		// The dtg value won't be entered here. Only when recorded
//Validate script
	if ($script == '') {
			$script = $name;
	}
	if ($script != preg_replace("/[^A-Za-z0-9\_]/", '', $script)) {
			$error_message_script = "Invalid character in script. Alternative suggested.";
			$script = preg_replace("/[^A-Za-z0-9\_]/", '', $script);
	}
	$url = '/srv/monitoring/metrics/'.$script.'.py';
	if ((!file_exists($url)) && ($measured == 1)) {
			$error_message_script = "Sorry, there isn't a metric checking script for that name and you have selected measured below.";
	}
	//Validate schedule
	if (!in_array($schedule, array(1,5,10,30,60,120,240,360,720,1440))) {
		$error_message_schedule = "Invalid value in schedule. Alternative suggested";
		$schedule = 60;
	}
//Validate parent
	//**** For the moment just the same validation as the name, but ultimately this needs to be checking other names from status. *****
	if ($parent == '') {
		$error_message_parent = "The parent metric name is required";
	}
	if ($parent != preg_replace("/[^A-Za-z0-9\_]/", '', $parent)) {
			$error_message_parent = "Invalid character in parent name. Alternative suggested.";
			$parent = preg_replace("/[^A-Za-z0-9\_]/", '', $parent);
	}
	if (strlen($parent) >= 50) {
			$error_message_parent = "Sorry, the parent name is too long. 50 characters maximum.";
			$name = substr($parent, 0, 50);
	}
//Validate priority
	if ($priority != preg_replace("/[^0-9]/", '', $priority)) {
			$error_message_priority = "Invalid character detected. Alternative suggested.";
			$priority = preg_replace("/[^0-9]/", '', $priority);
	}
	if ( (int)$priority != $priority || (int)$priority <= 0 ) {
			$error_message_priority = "Needs to be a positive, non-zero integer.";
	}
	if ( $priority == '') {
			$error_message_priority = "Priority Set to default value.";
			$priority = 5;
	}
//Validate alert limits
	// Check for foreign characters
	if ($alert_lower != preg_replace("/[^0-9\.\-]/", '', $alert_lower)) {
			$error_message_alert_lower = "Invalid character detected. Alternative suggested.";
			$alert_lower = preg_replace("/[^0-9\.\-]/", '', $alert_lower);
	}
	if ($caution_lower != preg_replace("/[^0-9\.\-]/", '', $caution_lower)) {
			$error_message_caution_lower = "Invalid character detected. Alternative suggested.";
			$caution_lower = preg_replace("/[^0-9\.\-]/", '', $caution_lower);
	}
	if ($normal_lower != preg_replace("/[^0-9\.\-]/", '', $normal_lower)) {
			$error_message_normal_lower = "Invalid character detected. Alternative suggested.";
			$normal_lower = preg_replace("/[^0-9\.\-]/", '', $normal_lower);
	}
	if ($normal_upper != preg_replace("/[^0-9\.\-]/", '', $normal_upper)) {
			$error_message_normal_upper = "Invalid character detected. Alternative suggested.";
			$normal_upper = preg_replace("/[^0-9\.\-]/", '', $normal_upper);
	}
	if ($caution_upper != preg_replace("/[^0-9\.\-]/", '', $caution_upper)) {
			$error_message_caution_upper = "Invalid character detected. Alternative suggested.";
			$caution_upper = preg_replace("/[^0-9\.\-]/", '', $caution_upper);
	}
	if ($alert_upper != preg_replace("/[^0-9\.\-]/", '', $alert_upper)) {
			$error_message_alert_upper = "Invalid character detected. Alternative suggested.";
			$alert_upper = preg_replace("/[^0-9\.\-]/", '', $alert_upper);
	}			
	// Check for correct sequencing
	if ($caution_lower < $alert_lower) {
			$error_message_caution_lower = "The Caution Lower value must be greater to (or equal to) the Alert Lower value";
	}
	if ($normal_lower < $caution_lower) {
			$error_message_normal_lower = "The Normal Lower value must be greater to (or equal to) the Caution Lower value";
	}	
	if ($normal_upper < $normal_lower) {
			$error_message_normal_upper = "The Normal Upper value must be greater to (or equal to) the Normal Lower value";
	}
	if ($caution_upper < $normal_upper) {
			$error_message_caution_upper = "The Caution Upper value must be greater to (or equal to) the Normal Upper value";
	}
	if ($alert_upper < $caution_upper) {
			$error_message_alert_upper = "The Alert Upper value must be greater to (or equal to) the Caution Upper value";
	}
	// Check for a value being in place if the metric is measured
	if ($measured >=1) {
		if ($alert_lower == '') {
				$error_message_alert_lower = "Value Required";
		}
		if ($caution_lower == '') {
				$error_message_caution_lower = "Value Required";
		}
		if ($normal_lower == '') {
				$error_message_normal_lower = "Value Required";
		}
		if ($normal_upper == '') {
				$error_message_normal_upper = "Value Required";
		}
		if ($caution_upper == '') {
				$error_message_caution_upper = "Value Required";
		}
		if ($alert_upper == '') {
				$error_message_alert_upper = "Value Required";
		}
	}
	
//Validate level_actual
	if (!in_array($level_actual, array("green","orange","red","blue"))) {
		$error_message_level_actual = "Invalid level value. Green suggested";
		$level_actual = "green";
	}
//Validate level_ack
	if (!in_array($level_ack, array("green","orange","red","blue"))) {
		$error_message_level_ack = "Invalid metric level. Green suggested";
		$level_ack = "green";
	}
//Validate help_url
	if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',$help_url)) {
		$error_message_help_url = "Invalid character(s) detected."; 
	}
	if ($help_url == '') {
		$error_message_help_url = ''; 
	}
//Validate description
	if ($description != preg_replace("/[^A-Za-z0-9 \.\'\?\(\)\@\!\,\:\[\]]/", '', $description)) {
			$error_message_description = "Chatacters Removed. Alternative suggested.";
			$description = preg_replace("/[^A-Za-z0-9 \.\'\?\(\)\@\!\,\:\[\]]/", '', $description);
	}
?>
