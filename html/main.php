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

	// At this point the parent name should be valid

	// Get the parent of the parent's label for the 'Go Back' button. 
	
		// Get all the metric data from the status database
		$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');

		$query = "SELECT * FROM status WHERE 
				  name = :parent";
		$statement = $db->prepare($query);
		$statement->bindValue(':parent', $parent);
		$statement->execute();
		
		// compile the returned data
		$values = $statement->fetchAll(PDO::FETCH_ASSOC);

		// print the data
		$parent_parent = $values[0]['parent'];

		/* close connection */
		$db = null;

		$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');

		$query = "SELECT * FROM status WHERE 
				  name = :parent";
		$statement = $db->prepare($query);
		$statement->bindValue(':parent', $parent_parent);
		$statement->execute();
		
		// compile the returned data
		$values = $statement->fetchAll(PDO::FETCH_ASSOC);

		// print the data
		$parent_label = $values[0]['label'];

		/* close connection */
		$db = null;
	
} else {
	
	// Set the prent to the top value
	$parent = 'root';
	$parent_label = 'Root';
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Operating Page</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/justified-nav.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet" type="text/css">

    <script src="js/jquery-2.1.4.min.js"></script>

    <style>
		text.shadow {
		    stroke: #211F1F;
		    stroke-width: 3px;
		    opacity: 0.8;
		}
    </style>

  </head>

  <body style="padding-top: 50px">

	<?php include 'navbar.php'; ?>

<div class="container" style="width: 100%; margin-top: 10px">
	<div class="row" >
		<div class="col-md-12">

<!-- load the d3.js library -->	
<script src="https://d3js.org/d3.v4.min.js"></script>

<script>
// Colours mid = opacity .4 dark = opacity .1 ack = opacity .8

var	green = 	 "#5CB85C",
	orange = 	 "#F0AD4E",
	red = 		 "#D9534F",
	blue = 		 "#5BC0DE",
	white = 	 "#D3D2D2",
	ackgreen = 	 "#509950",
	ackorange =  "#C79145",
	ackred = 	 "#B44945",
	ackblue = 	 "#4FA0B8",
	midgreen = 	 "#395C37",
	midorange =  "#745832",
	midred = 	 "#6B3432",
	midblue = 	 "#385F6B",
	midwhite = 	 "#7A7979",
	darkgreen =  "#272E25",
	darkorange = "#352D24",
	darkred = 	 "#332424",
	darkblue = 	 "#272F32",
	darkwhite =  "#373535";

var bodybackground =  "#141414",
	graphbackground = "#211F1F";
	
</script>

<div id="area1"></div>
<div id="area2"></div>
<div id="area3"></div>

<script>

var width = 220;
var height = 80;
var border = 15;

var area2 = d3.select("#area2");

d3.json("statusdata.php?parent=<?php echo $parent;?>", function(error, data) {
    if (error) throw error;

	var metricGroups = d3.select('#area2')
						 .selectAll('svg')
						 .data(data);

	// format the data
	data.forEach(function(d) { 
		// actual colours are slightly muted
		if (d.level_ack == "green") { d.level_ack = darkgreen;}	
		if (d.level_ack == "orange") { d.level_ack = darkorange;}	
		if (d.level_ack == "red") { d.level_ack = red;}	
		if (d.level_ack == "blue") { d.level_ack = midblue;}		
		if (d.level_actual == "green") { d.level_actual = midgreen;}	
		if (d.level_actual == "orange") { d.level_actual = midorange;}	
		if (d.level_actual == "red") { d.level_actual = red;}	
		if (d.level_actual == "blue") { d.level_actual = blue;}	
		if (d.level_actual == midgreen && d.acknowledged >=1) { d.level_actual = ackgreen;}	
		if (d.level_actual == midorange && d.acknowledged >=1) { d.level_actual = ackorange;}	
		if (d.level_actual == red && d.acknowledged >=1) { d.level_actual = ackred;}	
		if (d.level_actual == blue && d.acknowledged >=1) { d.level_actual = ackblue;}	
	});

	// ****************  Individual metric  *********************

	var metricGroupsEnter = metricGroups.enter()
										.append('svg')
										.attr("height", height+(2*border))
										.attr("width", width+(2*border));

	metricGroupsEnter.append("rect");
	metricGroupsEnter.append("text");
	metricGroupsEnter.append("a");

	//update
	metricGroups.merge(metricGroupsEnter)
			.select("a")
				.style("cursor", "pointer")
				.attr("xlink:href", 
					function(d) {if (d.measured == 0) {
						return "main.php?parent="+d.name} else {return "info.php?name="+d.name}; }
				)
			.append("rect")
			.attr("transform", "translate("+border+","+border+")")
			.attr("height", height)
			.attr("width", width)
			.style("fill", function(d) { return d.level_ack} )
			.style("stroke-width", 4.5)
			.style("stroke",  function(d) { return  d.level_actual} )
			.attr("rx", 10)
			.attr("ry", 10);

	// Add in the metric label
	metricGroups.merge(metricGroupsEnter).append("text")
//			.attr("transform", "translate("+(border+(width/2))+","+(border+10)+")")
			.attr("transform",
				function(d) {if (d.measured == 0) {
					return "translate("+(border+(width/2))+","+(border+24)+")"} else {
					return "translate("+(border+(width/2))+","+(border+10)+")"};
				} )			.attr("dy", ".71em")
			.attr("text-anchor", "middle")
			.style("fill", white)
			.style("font-weight", "bold")
			.style("pointer-events", "none")
			.text(function(d) {return d.label;}); 
					
	// Add in the metric value and date / time
	metricGroups.merge(metricGroupsEnter).append("text")
			.attr("transform", "translate("+(border+(width/2))+","+(border+30)+")")
			.attr("dy", ".71em") 
			.attr("text-anchor", "middle")
			.style("fill", white) 
			.style("pointer-events", "none")
			.text( function(d) {if (d.measured == 0) {
				return ""} else {
				return d.value+" @ "+d.dtg}; }
				); 

	// Add in the acknowledge rectangle if required
	metricGroups.merge(metricGroupsEnter).append("a")
			.style("cursor", "pointer")
			.attr("xlink:href", function(d) { return "acknowledge.php?name="+d.name})
			.append("rect")
			.attr("display", function(d) {  // do not display if not required
				// if (d.acknowledged == 0 && d.level_actual != "lightgreen" && d.measured == 1) {return "show"}
				if (d.acknowledged == 0 && d.level_ack != darkgreen && d.measured == 1) {return "show"}
				else {return "none"} })
			.attr("transform", "translate("+(border)+","+(border+height-18)+")")
			.attr("height", 30)
			.attr("width", width)
			.style("fill", midwhite)
			.style("stroke-width", 5)
			.style("stroke", function(d) {return d.level_actual;})
			.attr("rx", 10)
			.attr("ry", 10); 

	// Add in the metric label
	metricGroups.merge(metricGroupsEnter).append("text")
			.attr("display", function(d) { // do not display if not required
				// if (d.acknowledged == 0 && d.level_actual != "lightgreen" && d.measured == 1) {return "show"}
				if (d.acknowledged == 0 && d.level_ack != darkgreen && d.measured == 1) {return "show"}
				else {return "none"} })
			.attr("transform", "translate("+(border+(width/2))+","+(border+height-8)+")")
			.attr("dy", ".71em")
			.attr("text-anchor", "middle")
			.style("fill", white)
			.style("font-weight", "bold")
			.style("pointer-events", "none")
			.text("Acknowledge "); 

});


var inter = setInterval(function() {
		updateData();
//		updateParentGoBack(); // This is to update the parent button (see monitor.php)
	}, 10000);

function updateData() {

	var area2 = d3.select("#area2");

	d3.json("statusdata.php?parent=<?php echo $parent;?>", function(error, data) {
		if (error) throw error;

		var metricGroups = d3.select('#area2')
							 .selectAll('svg')
							 .remove();

		var metricGroups = d3.select('#area2')
							 .selectAll('svg')
							 .data(data);

		// format the data
		data.forEach(function(d) { 
			// actual colours are slightly muted
			if (d.level_ack == "green") { d.level_ack = darkgreen;}	
			if (d.level_ack == "orange") { d.level_ack = darkorange;}	
			if (d.level_ack == "red") { d.level_ack = red;}	
			if (d.level_ack == "blue") { d.level_ack = midblue;}
			if (d.level_actual == "green") { d.level_actual = midgreen;}	
			if (d.level_actual == "orange") { d.level_actual = midorange;}	
			if (d.level_actual == "red") { d.level_actual = red;}	
			if (d.level_actual == "blue") { d.level_actual = blue;}	
			if (d.level_actual == midgreen && d.acknowledged >=1) { d.level_actual = ackgreen;}	
			if (d.level_actual == midorange && d.acknowledged >=1) { d.level_actual = ackorange;}	
			if (d.level_actual == red && d.acknowledged >=1) { d.level_actual = ackred;}	
			if (d.level_actual == blue && d.acknowledged >=1) { d.level_actual = ackblue;}	
		});

		var metricGroupsEnter = metricGroups.enter()
			.append('svg')
			.attr("height", height+(2*border))
			.attr("width", width+(2*border));

		metricGroupsEnter.append("rect");
		metricGroupsEnter.append("text");
		metricGroupsEnter.append("a");

		//update
		metricGroups.merge(metricGroupsEnter)
			.select("a")
				.style("cursor", "pointer")
				.attr("xlink:href", 
					function(d) {if (d.measured == 0) {
						return "main.php?parent="+d.name} else {return "info.php?name="+d.name}; }
				)
			.append("rect")
			.attr("transform", "translate("+border+","+border+")")
			.attr("height", height)
			.attr("width", width)
			.style("fill", function(d) { return d.level_ack} )
			.style("stroke-width", 4.5)
			.style("stroke",  function(d) { return  d.level_actual} )
			.attr("rx", 10)
			.attr("ry", 10); 

		// Add in the metric label
		metricGroups.merge(metricGroupsEnter).append("text")
			.attr("transform",
				function(d) {if (d.measured == 0) {
					return "translate("+(border+(width/2))+","+(border+24)+")"} else {
					return "translate("+(border+(width/2))+","+(border+10)+")"};
				} )			.attr("dy", ".71em")
			.attr("text-anchor", "middle")
			.style("fill", white)
			.style("font-weight", "bold")
			.style("pointer-events", "none")
			.text(function(d) {return d.label;}); 
				
		// Add in the metric value and date / time
		metricGroups.merge(metricGroupsEnter).append("text")
			.attr("transform", "translate("+(border+(width/2))+","+(border+30)+")")
			.attr("dy", ".71em") 
			.attr("text-anchor", "middle")
			.style("fill", white) 
			.style("pointer-events", "none")
			.text( function(d) {if (d.measured == 0) {
				return ""} else {
				return d.value+" @ "+d.dtg}; }
				); 

		// Add in the acknowledge rectangle if required
		metricGroups.merge(metricGroupsEnter).append("a")
			.style("cursor", "pointer")
			.attr("xlink:href", function(d) { return "acknowledge.php?name="+d.name})
			.append("rect")
			.attr("display", function(d) {  // do not display if not required
				// if (d.acknowledged == 0 && d.level_actual != "lightgreen" && d.measured == 1) {return "show"}
				if (d.acknowledged == 0 && d.level_ack != darkgreen && d.measured == 1) {return "show"}
				else {return "none"} })
			.attr("transform", "translate("+(border)+","+(border+height-18)+")")
			.attr("height", 30)
			.attr("width", width)
			.style("fill", midwhite)
			.style("stroke-width", 5)
			.style("stroke", function(d) {return d.level_actual;})
			.attr("rx", 10)
			.attr("ry", 10); 

		// Add in the metric label
		metricGroups.merge(metricGroupsEnter).append("text")
			.attr("display", function(d) { // do not display if not required
				// if (d.acknowledged == 0 && d.level_actual != "lightgreen" && d.measured == 1) {return "show"}
				if (d.acknowledged == 0 && d.level_ack != darkgreen && d.measured == 1) {return "show"}
				else {return "none"} })
			.attr("transform", "translate("+(border+(width/2))+","+(border+height-8)+")")
			.attr("dy", ".71em")
			.attr("text-anchor", "middle")
			.style("fill", white)
			.style("font-weight", "bold")
			.style("pointer-events", "none")
			.text("Acknowledge "); 


	}); // end of the d3.json call


d3.json("metricdata.php?name=<?php echo $parent;?>", function(error, headerData) {


	var area1 = d3.select("#area1"); 

	var metricGroups1 = d3.select('#area1')
			 .selectAll('svg')
			 .data(headerData);

	// format the data
	headerData.forEach(function(d) { 
		// actual colours are slightly muted
		if (d.level_ack == "green") { d.level_ack = darkgreen;}	
		if (d.level_ack == "orange") { d.level_ack = darkorange;}	
		if (d.level_ack == "red") { d.level_ack = red;}	
		if (d.level_ack == "blue") { d.level_ack = midblue;}		
		if (d.level_actual == "green") { d.level_actual = midgreen;}	
		if (d.level_actual == "orange") { d.level_actual = midorange;}	
		if (d.level_actual == "red") { d.level_actual = red;}	
		if (d.level_actual == "blue") { d.level_actual = blue;}	
		if (d.level_actual == midgreen && d.acknowledged >=1) { d.level_actual = ackgreen;}	
		if (d.level_actual == midorange && d.acknowledged >=1) { d.level_actual = ackorange;}	
		if (d.level_actual == red && d.acknowledged >=1) { d.level_actual = ackred;}	
		if (d.level_actual == blue && d.acknowledged >=1) { d.level_actual = ackblue;}	
	});

	var metricGroupsEnter1 = metricGroups1.enter()
			.append('svg')
			.attr("height", height+(2*border))
			.attr("width", (width*2)+(4*border));

	metricGroupsEnter1.append("rect");
	metricGroupsEnter1.append("text");
	metricGroupsEnter1.append("a");

	metricGroups1.merge(metricGroupsEnter1).select("rect")
		.attr("transform", "translate("+border+","+border+")")
		.attr("height", height)
		.attr("width", (width*2)+(border*2))
		.style("fill", function(d) { return d.level_ack} )
		.style("stroke-width", 5)
		.style("stroke",  function(d) { return  d.level_actual} )
		.attr("rx", 10)
		.attr("ry", 10); 

	// Add in the metric label
	metricGroups1.merge(metricGroupsEnter1).append("text")
		.attr("transform", "translate("+((border*2)+(width))+","+(border+24)+")")
		.attr("dy", ".71em") 
		.attr("text-anchor", "middle")
		.style("fill", white) 
		.style("font-weight", "bold") 
		.style("font-size", "20px") 
		.text(function(d) {return d.label}); 

	}); // end of the d3.json call


} // end of updateData


// ************ Parent heading and go up a level button  ***************

// Get the data for the parent of the metrics being displayed
d3.json("metricdata.php?name=<?php echo $parent;?>", function(error, headerData) {

// ****************  Display the Parent rectangle  *********************
var area1 = d3.select("#area1"); 

	var metricGroups1 = d3.select('#area1')
			 .selectAll('svg')
			 .data(headerData);

	// format the data
	headerData.forEach(function(d) { 
		// actual colours are slightly muted
		if (d.level_ack == "green") { d.level_ack = darkgreen;}	
		if (d.level_ack == "orange") { d.level_ack = darkorange;}	
		if (d.level_ack == "red") { d.level_ack = red;}	
		if (d.level_ack == "blue") { d.level_ack = midblue;}		
		if (d.level_actual == "green") { d.level_actual = midgreen;}	
		if (d.level_actual == "orange") { d.level_actual = midorange;}	
		if (d.level_actual == "red") { d.level_actual = red;}	
		if (d.level_actual == "blue") { d.level_actual = blue;}	
		if (d.level_actual == midgreen && d.acknowledged >=1) { d.level_actual = ackgreen;}	
		if (d.level_actual == midorange && d.acknowledged >=1) { d.level_actual = ackorange;}	
		if (d.level_actual == red && d.acknowledged >=1) { d.level_actual = ackred;}	
		if (d.level_actual == blue && d.acknowledged >=1) { d.level_actual = ackblue;}	
	});

	var metricGroupsEnter1 = metricGroups1.enter()
			.append('svg')
			.attr("height", height+(2*border))
			.attr("width", (width*2)+(4*border));

	metricGroupsEnter1.append("rect");
	metricGroupsEnter1.append("text");
	metricGroupsEnter1.append("a");

	metricGroups1.merge(metricGroupsEnter1).select("rect")
		.attr("transform", "translate("+border+","+border+")")
		.attr("height", height)
		.attr("width", (width*2)+(border*2))
		.style("fill", function(d) { return d.level_ack} )
		.style("stroke-width", 5)
		.style("stroke",  function(d) { return  d.level_actual} )
		.attr("rx", 10)
		.attr("ry", 10); 

	// Add in the metric label
	metricGroups1.merge(metricGroupsEnter1).append("text")
		.attr("transform", "translate("+((border*2)+(width))+","+(border+24)+")")
		.attr("dy", ".71em") 
		.attr("text-anchor", "middle")
		.style("fill", white) 
		.style("font-weight", "bold") 
		.style("font-size", "20px") 
		.text(function(d) {return d.label}); 

});

// ******************* Display the Go back rectangle *******************
// Get the data for the parent of the metrics being displayed
d3.json("metricdata.php?name=<?php echo $parent;?>", function(error, footerData) {

	if (footerData[0].parent != "null") { // should be root

		var metricGroups3 = d3.select('#area3')
				 .selectAll('svg')
				 .data(footerData);

		var metricGroupsEnter3 = metricGroups3.enter()
				.append('svg')
				.attr("height", height+(2*border))
				.attr("width", width+(2*border));

		metricGroupsEnter3.append("rect");
		metricGroupsEnter3.append("text");
		metricGroupsEnter3.append("a");
								
		// Add in the rectangle
		metricGroups3.merge(metricGroupsEnter3).select("a")
			.style("cursor", "pointer")
			.attr("xlink:href", "main.php?parent="+footerData[0].parent)
			.append("rect")
			.attr("transform", "translate("+border+","+border+")")
			.attr("height", height)
			.attr("width", (width))
			.style("fill", darkwhite)
			.style("stroke-width", 5)
			.style("stroke",  midwhite)
			.attr("rx", 10)
			.attr("ry", 10); 

		// Add the return message
		metricGroups3.merge(metricGroupsEnter3).append("text")
			.attr("transform", "translate("+((border)+(width/2))+","+(border+20)+")")
			.attr("dy", ".71em")
			.attr("text-anchor", "middle")
			.style("fill", white)
			.style("font-weight", "bold")
			.style("font-size", "16px")
			.style("pointer-events", "none")
			.text("Go up a level to"); 

		// Add in the metric label
		metricGroups3.merge(metricGroupsEnter3).append("text")
			.attr("transform", "translate("+((border)+(width/2))+","+(border+40)+")")
			.attr("dy", ".71em") 
			.attr("text-anchor", "middle")
			.style("fill", white) 
			.style("font-weight", "bold") 
			.style("font-size", "20px") 
			.style("pointer-events", "none")
			.text("<?php echo $parent_label;?>"); 

	};

});

</script>


		</div> <!-- /column -->
	</div> <!-- /row -->
</div> <!-- /container   -->

	<?php include 'footer.php'; ?>
      
    <!-- Bootstrap core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Placed at the end of the document so the pages load faster -->
  </body>
</html>
