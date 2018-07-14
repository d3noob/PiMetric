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

    <title>Operating Page</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/justified-nav.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet" type="text/css">

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
			stroke: grey;
			stroke-width: 1;
			shape-rendering: crispEdges;
		}
		.legend {
			font-size: 16px;
			font-weight: bold;
			text-anchor: middle;
		}
    </style>

  </head>

  <body style="padding-top: 50px">

	<?php include 'navbar.php'; ?>

<div class="container" style="width: 100%; margin-top: 10px">
	<div class="row" >
		<div class="col-md-12">
		<div class='centertext'>

<!-- load the d3.js library -->	
<script src="js/d3.v4.min.js"></script>


<!-- ****************** Start of first graph ***************** -->
<div class="row" id="graph1">
<script>

// Set the dimensions of the canvas / graph
var margin = {top: 30, right: 20, bottom: 70, left: 50},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

// Parse the date / time
var parseDate = d3.timeParse("%Y-%m-%d %H:%M:%S");

// Set the ranges
var x = d3.scaleTime().range([0, width]);  
var y = d3.scaleLinear().range([height, 0]);


// Adds the svg canvas
var svg = d3.select("body")
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

//console.log(dataNest);
//console.log(dataNest[0].values); // This looks like the right range!

    // set the colour scale
    var color = d3.scaleOrdinal(d3.schemeCategory10);

    legendSpace = width/dataNest.length; // spacing for the legend

//https://gist.github.com/benjchristensen/2579619

    // Loop through each symbol / key
    dataNest.forEach(function(d,i) { 

//console.log(d.values[0]);

    // Loop through each symbol / key
    dataNest.forEach(function(d,i) { 
		total = dataNest.values;
		
		// Loop through each symbol / key
		total.forEach(function(d,i) { 
			
			console.log(key);
			

		});	

	});



		var yI = d3.scaleLinear().domain(d3.extent(data, function(d) { return d.price; })).range([height, 0]);

		data.forEach(function(d) {
			d.price = yI(d.price);
		});
		
		// Define the line
		var priceline = d3.line()	
			.x(function(d) { return x(d.date); })
			.y(function(d) { return yI(d.price); });

// console.log(d.values);

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

  // Add the Y Axis
  svg.append("g")
      .attr("class", "axis")
      .call(d3.axisLeft(y));

});

</script>

</div> <!-- /row -->
<!-- **************** End of second graph ***************** -->

		</div> <!-- /centertext -->
		</div> <!-- /column -->
	</div> <!-- /row -->
</div> <!-- /container   -->

	<?php include 'footer.php'; ?>
      
    <!-- Bootstrap core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Placed at the end of the document so the pages load faster -->
  </body>
</html>
