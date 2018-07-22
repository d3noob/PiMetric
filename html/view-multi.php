<?php

if ($_GET) {

	// Do some validation/sanitisation here
	$parent = $_GET['parent'];

	if ($parent != preg_replace("/[^A-Za-z0-9\_]/", '', $parent)) {
		// Go to the fail page
		// Perhaps send a error message to a log?
		header("Location: fail.php?error_message=The+parent+has+disallowed+characters.");
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
		header("Location: fail.php?error_message=I+couldn't+find+the+parent+of+the+metric+to+display.");
		exit();
	}
	$bingo = 0;
	
} else {
//	header("Location: fail.php?error_message=Something+weird+happened+while+trying+to+get+the+metric+information.");
//	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Multi View</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet" type="text/css">
    <!-- jquery dependency -->
    <script src="js/jquery-2.1.4.min.js"></script>

    <style>

		body { font: 12px Arial;}

		path { 
			stroke: steelblue;
			stroke-width: 2;
			fill: none;
		}
		.axis path,
		.axis line {
			fill: none;
			stroke: #A6A5A5;
			stroke-width: 1;
			shape-rendering: crispEdges;
		}
		.tick text {
			fill: #A6A5A5
		}
		.legend {
			font-size: 16px;
			font-weight: bold;
			text-anchor: middle;
		}

		.centertext {text-align: center;}
		.righttext {text-align: right;}

		.row{
		  width:100%;
		  margin:0;
		  padding:0;
		  display:flex;
		}

    </style>

  </head>

  <body style="padding-top: 50px">

	<?php include 'navbar.php'; ?>

<div class="container" style="width: 100%; margin-top: 10px">

	<div class="row"> <!-- Row -->

	<div class="col-xs-3"> <!-- Left Colunm -->
		<div class="container-fluid   pull-right" style="width: 100%; margin-top:30px;" >
			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='create.php' class='btn btn-success' style='width: 100%'>Create New Metric</a>
			</div>

			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='main.php?parent=<?php echo $parent;?>' class='btn btn-info' style='width: 100%'>Operating page</a>
			</div>
			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='read.php' class='btn btn-info' style='width: 100%'>View Metrics Table</a>
			</div>
			
		</div>
	</div> <!-- Left Colunm -->

	<div class="col-xs-6" style="min-width: 780px; margin-top:30px;"> <!-- Graph Colunm -->

		<div class='centertext'> <!-- Centre the graph in the svg -->

		<!-- load the d3.js library -->	
		<script src="js/d3.v4.min.js"></script>

		<svg width="780" height="410" style='background-color: #211F1F;'></svg>

		<script>

		// Set the dimensions of the canvas / graph
		var margin = {top: 30, right: 20, bottom: 70, left: 50},
			width = 770 - margin.left - margin.right,
			height = 410 - margin.top - margin.bottom;

		// Parse the date / time
		var parseDate = d3.utcParse("%Y-%m-%d %H:%M:%S");

		// Set the ranges
		var x = d3.scaleTime().range([0, width]);  
		var y = d3.scaleLinear().range([height, 0]);

		// Define the line
		var priceline = d3.line()	
			.x(function(d) { return x(d.date); })
			.y(function(d) { return y(d.price); });
			
		// Adds the svg canvas
		var svg = d3.select("svg")
			.append("svg")
				.attr("width", width + margin.left + margin.right)
				.attr("height", height + margin.top + margin.bottom)
			.append("g")
				.attr("transform", 
					  "translate(" + margin.left + "," + margin.top + ")");

		// Get the data
		d3.json(<?php echo '"'.'datamulti.php?parent='.$parent.'"'; ?>, function(error, data) {
			data.forEach(function(d) {
				d.date = parseDate(d.dtg);
				d.price = +d.value;
				d.symbol = d.name;
			});

			// Scale the range of the data
			x.domain(d3.extent(data, function(d) { return d.date; }));

			// Nest the entries by symbol
			var dataNest = d3.nest()
				.key(function(d) {return d.symbol;})
				.entries(data);

			// set the colour scale
			var color = d3.scaleOrdinal(d3.schemeCategory10);

			legendSpace = width/dataNest.length; // spacing for the legend

			// Loop through each symbol / key
			dataNest.forEach(function(d,i) { 

			var innerValues = dataNest[i]['values'];

			y.domain([d3.min(innerValues, function(d) { return d.price; }), 
					  d3.max(innerValues, function(d) { return d.price; })
					 ]);

				svg.append("path")
					.attr("class", "line")
					.style("stroke", function() { // Add the colours dynamically
						return d.color = color(d.key); })
					.attr("id", 'tag'+d.key.replace(/\s+/g, '')) // assign an ID
					.attr("d", priceline(d.values));

				// Add the Legend
				svg.append("text")
					.attr("x", (legendSpace/2)+i*legendSpace)  // space legend
					.attr("y", height + (margin.bottom/2)+ 5)
					.attr("class", "legend")    // style the legend
					.style("fill", function() { // Add the colours dynamically
						return d.color = color(d.key); })
					.on("click", function(){
						// Determine if current line is visible 
						var active   = d.active ? false : true,
						newOpacity = active ? 0 : 1; 
						// Hide or show the elements based on the ID
						d3.select("#tag"+d.key.replace(/\s+/g, ''))
							.transition().duration(100) 
							.style("opacity", newOpacity); 
						// Update whether or not the elements are active
						d.active = active;
						})  
					.text(d.key); 

			});

		  // Add the X Axis
		  svg.append("g")
			  .attr("class", "axis")
			  .attr("transform", "translate(0," + height + ")")
			  .call(d3.axisBottom(x));

		});

		</script>

		</div> <!-- centertext Column -->

	</div> <!-- Graph Column -->

	<div class="col-xs-3"> <!-- Right Column -->
		<div class="container-fluid">
			<div class="row band">
				<div class="table-responsive">
					<table class="table table-condensed" style="margin-top:30px;">
						<tbody>
							<tr>
								<td align='right'><b>Label: </b></td>
								<td><?php echo $label; ?></td>
							</tr>
							<tr>
								<td align='right'><b>Name: </b></td>
								<td><?php echo $name; ?></td>
							</tr>
							<tr>
								<td align='right'><b>Date/Time: </b></td>
								<td><?php echo $dtg; ?></td>
							</tr>
							<tr>
								<td align='right'><b>Script: </b></td>
								<td><?php echo $script; ?></td>
							</tr>
							<tr>
								<td align='right'><b>Schedule: </b></td>
								<td><?php echo $schedule; ?></td>
							</tr>
							<tr>
								<td align='right'><b>Parent metric: </b></td>
								<td><?php echo $parent; ?></td>
							</tr>
							<tr>
								<td align='right'><b>Measured: </b></td>
								<td><?php if ($measured >= 1) { echo 'Yes';} else {echo 'No';} ?></td>
							</tr>
							<tr>
								<td align='right'><b>Monitored: </b></td>
								<td><?php if ($monitored >= 1) { echo 'Yes';} else {echo 'No';} ?></td>
							</tr>
							<tr>
								<td align='right'><b>Acknowledged: </b></td>
								<td><?php if ($acknowledged >= 1) { echo 'Yes';} else {echo 'No';} ?></td>
							</tr>
							<tr>
								<td align='right'><b>Value: </b></td>
								<td><?php echo $value; ?></td>
							</tr>
							<tr>
								<td align='right'><b>Metric state: </b></td>
								<td><?php echo $level_actual; ?></td>
							</tr>
							<tr>
								<td align='right'><b>Acknowledged state: </b></td>
								<td><?php echo $level_ack; ?></td>
							</tr>
							<tr>
								<td colspan="2" align='left'><b>Help URL: </b><?php echo $help_url; ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div> <!-- Right Colunm -->

	</div> <!-- /row -->
	
</div> <!-- /container   -->

	<?php include 'footer.php'; ?>
      
    <!-- Bootstrap core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Placed at the end of the document so the pages load faster -->
  </body>
</html>
