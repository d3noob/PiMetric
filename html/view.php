<?php

if ($_GET) {

	// Do some validation/sanitisation here
	$name = $_GET['name'];

	if ($name != preg_replace("/[^A-Za-z0-9\_]/", '', $name)) {
		// Go to the fail page
		// Perhaps send a error message to a log?
		header("Location: fail.php?error_message=The+name+has+disallowed+characters.");
		exit();
	}

	// Check the name against all the possible names to ensure validity
	$db = new PDO('sqlite:/srv/monitoring/monitoring');
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
		header("Location: fail.php?error_message=I+couldn't+find+the+name+of+the+metric+to+display.");
		exit();
	}
	$bingo = 0;

	// Get all the metric details from the status database
	$db = new PDO('sqlite:/srv/monitoring/monitoring');
	$statement = $db->prepare("SELECT * FROM status WHERE name=:name;");
	$statement->bindValue(':name', $name);
	$statement->execute();
	
	// Get the result.
	$result = $statement->fetch();

	/* close connection */
	$db = null;
		
	// echo $result['name'];
	
	// Load the metric parameters into the current operating set
	$name = $result['name'];
	$value = $result['value'];
	$dtg = $result['dtg'];
	$script = $result['script'];
	$schedule = $result['schedule'];
	$parent = $result['parent'];
	$priority = $result['priority'];
	$measured = $result['measured'];
	$monitored = $result['monitored'];
	$alert_lower = $result['alert_lower'];
	$caution_lower = $result['caution_lower'];
	$normal_lower = $result['normal_lower'];
	$normal_upper = $result['normal_upper'];
	$caution_upper = $result['caution_upper'];
	$alert_upper = $result['alert_upper'];
	$level_actual = $result['level_actual'];
	$level_ack = $result['level_ack'];
	$help_url = $result['help_url'];
	$description = $result['description']; 	
	$label = $result['label']; 	
	
} else {
	header("Location: fail.php?error_message=Something+weird+happened+while+trying+to+get+the+metric+information.");
	exit();
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
	<link href="css/custom.css" rel="stylesheet" type="text/css">
    <!-- Custom styles for this template -->
    <link href="css/justified-nav.css" rel="stylesheet">
    <script src="js/jquery-2.1.4.min.js"></script>

    <style>
		body { background: #141414 !important; }

		.centertext {text-align: center;}
		.righttext {text-align: right;}

		body {
			font: 12px Arial;
			}

		.axis text{
			font: 12px sans-serif;
			fill: #A6A5A5;
		}

		.axis path,
		.axis line {
			fill: none;
			stroke: #A6A5A5;
			stroke-width: 1;
			shape-rendering: crispEdges;
		}

		.line {
			stroke: steelblue;
			stroke-width: 2;
			fill: none;
		}

		text.shadow {
		    stroke: #211F1F;
		    stroke-width: 3px;
		    opacity: 0.8;
		}

		.overlay {
		    fill: none;
		    pointer-events: all;
		}
		.overlay1 {
		    fill: none;
		    pointer-events: all;
		}
		.focus circle {
			stroke: #A6A5A5;
			stroke-width: 1;
			fill: none;
		}
		.focus1 circle {
			stroke: #A6A5A5;
			stroke-width: 1;
			fill: none;
		}		  
		.hover-line {
			stroke: #A6A5A5;
			stroke-width: 1;
		    stroke-dasharray: 3,3;
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

<script>
// Colours mid = opacity .4 dark = opacity .1

var	green = 	 "#5CB85C",
	orange = 	 "#F0AD4E",
	red = 		 "#D9534F",
	blue = 		 "#5BC0DE",
	white = 	 "#D3D2D2",
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

<!-- ****************** Start of first graph ***************** -->
<div class="row" id="graph1">

<svg width="960" height="410" style='background-color: #211F1F;'></svg>



<script>
var graph1 = d3.select("#graph1");

var svg1 = graph1.select("svg"),
    margin = {top: 40, right: 40, bottom: 30, left: 50},
    width = +svg1.attr("width") - margin.left - margin.right,
    height = +svg1.attr("height") - margin.top - margin.bottom;

var parseTime = d3.utcParse("%Y-%m-%d %H:%M:%S");
    formatDate = d3.timeFormat("%H:%M"),
    bisectDate1 = d3.bisector(function(d) { return d.date; }).left;

var g1x = d3.scaleTime().range([0, width]);
var g1y = d3.scaleLinear().range([height, 0]);

// Bring in the limits
var alertLower = <?php echo $alert_lower; ?>;
var cautionLower = <?php echo $caution_lower; ?>;
var normalLower = <?php echo $normal_lower; ?>;
var normalUpper = <?php echo $normal_upper; ?>;
var cautionUpper = <?php echo $caution_upper; ?>;
var alertUpper = <?php echo $alert_upper; ?>;

// Define the line
var valueline1 = d3.line()
    .x(function(d) { return g1x(d.date); })
    .y(function(d) { return g1y(d.value); });

var g1 = svg1.append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

d3.json(<?php echo '"'.'data.php?name='.$name.'"'; ?>, function(error, data) {
    if (error) throw error;

//console.log(data);

	// format the data
    data.forEach(function(d) {
        d.date = parseTime(d.dtg);
        d.value = +d.value;
    });

    data.sort((a, b) => a.date - b.date);

	// Determine the vertical upper and lower limits
	var graphMax = d3.max(data, function(d) {return Math.max(d.value, normalUpper); });
	var graphMin = d3.min(data, function(d) {return Math.min(d.value, normalLower); });
	var buffer = (graphMax - graphMin) * .05; // gets 5% of the range as a graphical buffer
	
	var limitMax = graphMax + buffer;
	var limitMin = graphMin - buffer;

    g1x.domain(d3.extent(data, function(d) { return d.date; }));
    g1y.domain([limitMin, limitMax]);

	// Set the upper and lower values so that they arrange correctly on the graph
	if (alertUpper >= limitMax) {alertUpper = limitMax};
	if (cautionUpper >= limitMax) {cautionUpper = limitMax};
	if (normalUpper >= limitMax) {normalUpper = limitMax};
	if (normalLower <= limitMin) {normalLower = limitMin};
	if (cautionLower <= limitMin) {cautionLower = limitMin};
	if (alertLower <= limitMin) {alertLower = limitMin};

    g1.append("rect")
       .style("fill", darkblue) // blue		
       .attr("x", 0)				
       .attr("y", 0)
       .attr("height", height)	
       .attr("width", width);

    g1.append("rect")
       .style("fill", darkred) // red		
       .attr("x", 0)				
       .attr("y", g1y(alertUpper))
       .attr("height", g1y(alertLower) - g1y(alertUpper))	
       .attr("width", width);

    g1.append("rect")
       .style("fill", darkorange) // orange
       .attr("x", 0)				
       .attr("y", g1y(cautionUpper))
       .attr("height", g1y(cautionLower) - g1y(cautionUpper))	
       .attr("width", width);

    g1.append("rect")
       .style("fill", darkgreen) // green
       .attr("x", 0)				
       .attr("y", g1y(normalUpper))
       .attr("height", g1y(normalLower) - g1y(normalUpper))	
       .attr("width", width);


    g1.append("line")
       .style("stroke", midblue) // blue
       .attr("x1", 0)				
       .attr("y1", 0)
       .attr("x2", width)				
       .attr("y2", 0)
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g1.append("line")
       .style("stroke", midblue) // blue
       .attr("x1", 0)				
       .attr("y1", height)
       .attr("x2", width)				
       .attr("y2", height)
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g1.append("line")
       .style("stroke", midred) // red
       .attr("x1", 0)				
       .attr("y1", g1y(alertUpper))
       .attr("x2", width)				
       .attr("y2", g1y(alertUpper))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g1.append("line")
       .style("stroke", midred) // red
       .attr("x1", 0)				
       .attr("y1", g1y(alertLower))
       .attr("x2", width)				
       .attr("y2", g1y(alertLower))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g1.append("line")
       .style("stroke", midorange) // orange
       .attr("x1", 0)				
       .attr("y1", g1y(cautionUpper))
       .attr("x2", width)				
       .attr("y2", g1y(cautionUpper))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g1.append("line")
       .style("stroke", midorange) // orange
       .attr("x1", 0)				
       .attr("y1", g1y(cautionLower))
       .attr("x2", width)				
       .attr("y2", g1y(cautionLower))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g1.append("line")
       .style("stroke", midgreen) // green
       .attr("x1", 0)				
       .attr("y1", g1y(normalUpper))
       .attr("x2", width)				
       .attr("y2", g1y(normalUpper))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g1.append("line")
       .style("stroke", midgreen) // green
       .attr("x1", 0)				
       .attr("y1", g1y(normalLower))
       .attr("x2", width)				
       .attr("y2", g1y(normalLower))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g1.append("g")
        .attr("class", "axis axis--x")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(g1x));

    g1.append("g")
        .attr("class", "axis axis--y")
        .style("stroke-width", "1px")
        .style("opacity", 1)
        .call(d3.axisLeft(g1y).ticks(6));

    g1.append("path")
        .datum(data)
        .attr("class", "line")
        .attr("d", valueline1);

    g1.append("text")
       .attr("x", (width / 2))				
       .attr("y", -10 )
       .style("fill", white)
       .attr("text-anchor", "middle")	
       .style("font-size", "20px") 
       .text(<?php echo '"'.$label.'"' ?>+": 36 hours");


    var focus1 = g1.append("g")
        .attr("class", "focus1")
        .style("display", "none");

    focus1.append("line")
        .attr("class", "x-hover-line hover-line");

    focus1.append("line")
        .attr("class", "y-hover-line hover-line");

    focus1.append("circle")
        .attr("r", 5);

   // value on cursor
    focus1.append("text")
	    .attr("class", "y2")	
        .style("stroke", graphbackground)
        .style("stroke-width", "3.5px")
        .style("opacity", 0.8)
        .attr("x", 10)
        .attr("y", -10)
      	.attr("dy", ".31em");
   focus1.append("text")
	    .attr("class", "y1")	
        .style("fill", white)
        .style("stroke-width", "1px")
        .style("opacity", 1)
      	.attr("dy", ".31em");

   // time on cursor
    focus1.append("text")
	    .attr("class", "y4")	
        .style("stroke", graphbackground)
        .style("stroke-width", "3.5px")
        .style("opacity", 0.8)
      	.attr("dy", ".31em");
   focus1.append("text")
	    .attr("class", "y3")	
        .style("fill", white)
        .style("stroke-width", "1px")
        .style("opacity", 1)
      	.attr("dy", ".31em");

    svg1.append("rect")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
        .attr("class", "overlay1")
        .attr("width", width)
        .attr("height", height)
        .on("mouseover", function() { focus1.style("display", null); })
        .on("mouseout", function() { focus1.style("display", "none"); })
        .on("mousemove", mousemove1);

    function mousemove1() {
      var g1x0 = g1x.invert(d3.mouse(this)[0]),
          g1i = bisectDate1(data, g1x0, 1),
          g1d0 = data[g1i - 1],
          g1d1 = data[g1i],
          g1d = g1x0 - g1d0.date > g1d1.date - g1x0 ? g1d1 : g1d0;
      focus1.attr("transform", "translate(" + g1x(g1d.date) + "," + g1y(g1d.value) + ")");
      focus1.select("text.y1")
           .attr("x", 10)
           .attr("y", -10)
           .text(function() { return g1d.value; });
      focus1.select("text.y2")
           .attr("x", 10)
           .attr("y", -10)
           .text(function() { return g1d.value; });
      focus1.select("text.y3")
           .attr("x", 10)
           .attr("y", +10)    
		   .text(function() { return formatDate(g1d.date); });
      focus1.select("text.y4")
           .attr("x", 10)
           .attr("y", +10)    
		   .text(function() { return formatDate(g1d.date); });
      focus1.select(".x-hover-line").attr("y1", 0);
      focus1.select(".x-hover-line").attr("y2", height - g1y(g1d.value));
      focus1.select(".y-hover-line").attr("x1", width-g1x(g1d.date));
      focus1.select(".y-hover-line").attr("x2", -g1x(g1d.date));
    }
});

</script>

</div> <!-- /row -->
<!-- ****************** End of first graph ***************** -->

<!-- **************** Start of second graph ***************** -->
<div class="row" id="graph2">

<svg width="960" height="410" style='background-color: #211F1F;'></svg>

<script>
var graph2 = d3.select("#graph2");
	
var svg = graph2.select("svg"),
    margin = {top: 40, right: 40, bottom: 30, left: 50},
    width = +svg.attr("width") - margin.left - margin.right,
    height = +svg.attr("height") - margin.top - margin.bottom;

var parseTime = d3.utcParse("%Y-%m-%d %H:%M:%S");
    formatDate = d3.timeFormat("%H:%M"),
    bisectDate = d3.bisector(function(d) { return d.date; }).left;

var x = d3.scaleTime().range([0, width]);
var y = d3.scaleLinear().range([height, 0]);

// Bring in the limits
var alertLower = <?php echo $alert_lower; ?>;
var cautionLower = <?php echo $caution_lower; ?>;
var normalLower = <?php echo $normal_lower; ?>;
var normalUpper = <?php echo $normal_upper; ?>;
var cautionUpper = <?php echo $caution_upper; ?>;
var alertUpper = <?php echo $alert_upper; ?>;

// Define the line
var valueline = d3.line()
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.value); });

var g = svg.append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

d3.json(<?php echo '"'.'datafull.php?name='.$name.'"'; ?>, function(error, datafull) {
    if (error) throw error;

	// format the data
    datafull.forEach(function(d) {
        d.date = parseTime(d.dtg);
        d.value = +d.value;
    });

    datafull.sort((a, b) => a.date - b.date);

	// Determine the vertical upper and lower limits
	var graphMax = d3.max(datafull, function(d) {return Math.max(d.value, normalUpper); });
	var graphMin = d3.min(datafull, function(d) {return Math.min(d.value, normalLower); });
	var buffer = (graphMax - graphMin) * .05; // gets 5% of the range as a graphical buffer
	
	var limitMax = graphMax + buffer;
	var limitMin = graphMin - buffer;

    x.domain(d3.extent(datafull, function(d) { return d.date; }));
    y.domain([limitMin, limitMax]);

	// Set the upper and lower values so that they arrange correctly on the graph
	if (alertUpper >= limitMax) {alertUpper = limitMax};
	if (cautionUpper >= limitMax) {cautionUpper = limitMax};
	if (normalUpper >= limitMax) {normalUpper = limitMax};
	if (normalLower <= limitMin) {normalLower = limitMin};
	if (cautionLower <= limitMin) {cautionLower = limitMin};
	if (alertLower <= limitMin) {alertLower = limitMin};

    g.append("rect")
       .style("fill", darkblue) // blue		
       .attr("x", 0)				
       .attr("y", 0)
       .attr("height", height)	
       .attr("width", width);

    g.append("rect")
       .style("fill", darkred) // red		
       .attr("x", 0)				
       .attr("y", y(alertUpper))
       .attr("height", y(alertLower) - y(alertUpper))	
       .attr("width", width);

    g.append("rect")
       .style("fill", darkorange) // orange
       .attr("x", 0)				
       .attr("y", y(cautionUpper))
       .attr("height", y(cautionLower) - y(cautionUpper))	
       .attr("width", width);

    g.append("rect")
       .style("fill", darkgreen) // green
       .attr("x", 0)				
       .attr("y", y(normalUpper))
       .attr("height", y(normalLower) - y(normalUpper))	
       .attr("width", width);

    g.append("line")
       .style("stroke", midblue) // blue
       .attr("x1", 0)				
       .attr("y1", 0)
       .attr("x2", width)				
       .attr("y2", 0)
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g.append("line")
       .style("stroke", midblue) // blue
       .attr("x1", 0)				
       .attr("y1", height)
       .attr("x2", width)				
       .attr("y2", height)
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g.append("line")
       .style("stroke", midred) // red
       .attr("x1", 0)				
       .attr("y1", y(alertUpper))
       .attr("x2", width)				
       .attr("y2", y(alertUpper))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g.append("line")
       .style("stroke", midred) // red
       .attr("x1", 0)				
       .attr("y1", y(alertLower))
       .attr("x2", width)				
       .attr("y2", y(alertLower))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g.append("line")
       .style("stroke", midorange) // orange
       .attr("x1", 0)				
       .attr("y1", y(cautionUpper))
       .attr("x2", width)				
       .attr("y2", y(cautionUpper))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g.append("line")
       .style("stroke", midorange) // orange
       .attr("x1", 0)				
       .attr("y1", y(cautionLower))
       .attr("x2", width)				
       .attr("y2", y(cautionLower))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g.append("line")
       .style("stroke", midgreen) // green
       .attr("x1", 0)				
       .attr("y1", y(normalUpper))
       .attr("x2", width)				
       .attr("y2", y(normalUpper))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g.append("line")
       .style("stroke", midgreen) // green
       .attr("x1", 0)				
       .attr("y1", y(normalLower))
       .attr("x2", width)				
       .attr("y2", y(normalLower))
       .style("shape-rendering", "crispEdges")
       .style("stroke-width", "1px");

    g.append("g")
        .attr("class", "axis axis--x")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(x));

    g.append("g")
        .attr("class", "axis axis--y")
        .call(d3.axisLeft(y).ticks(6));

    g.append("path")
        .datum(datafull)
        .attr("class", "line")
        .attr("d", valueline);

    g.append("text")
       .attr("x", (width / 2))				
       .attr("y", -10 )
       .style("fill", white)
       .attr("text-anchor", "middle")	
       .style("font-size", "20px") 
       .text(<?php echo '"'.$label.'"' ?>+": All data");


    var focus = g.append("g")
        .attr("class", "focus")
        .style("display", "none");

    focus.append("line")
        .attr("class", "x-hover-line hover-line");

    focus.append("line")
        .attr("class", "y-hover-line hover-line");

    focus.append("circle")
        .attr("r", 5);

   // value on cursor
    focus.append("text")
	    .attr("class", "y2")	
        .style("stroke", graphbackground)
        .style("stroke-width", "3.5px")
        .style("opacity", 0.8)
        .attr("x", 10)
        .attr("y", -10)
      	.attr("dy", ".31em");
   focus.append("text")
	    .attr("class", "y1")	
        .style("fill", white)
        .style("stroke-width", "1px")
        .style("opacity", 1)
      	.attr("dy", ".31em");

   // time on cursor
    focus.append("text")
	    .attr("class", "y4")	
        .style("stroke", graphbackground)
        .style("stroke-width", "3.5px")
        .style("opacity", 0.8)
      	.attr("dy", ".31em");
   focus.append("text")
	    .attr("class", "y3")	
        .style("fill", white)
        .style("stroke-width", "1px")
        .style("opacity", 1)
      	.attr("dy", ".31em");

    svg.append("rect")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
        .attr("class", "overlay")
        .attr("width", width)
        .attr("height", height)
        .on("mouseover", function() { focus.style("display", null); })
        .on("mouseout", function() { focus.style("display", "none"); })
        .on("mousemove", mousemove);

    function mousemove() {
      var x0 = x.invert(d3.mouse(this)[0]),
          i = bisectDate(datafull, x0, 1),
          d0 = datafull[i - 1],
          d1 = datafull[i],
          d = x0 - d0.date > d1.date - x0 ? d1 : d0;
      focus.attr("transform", "translate(" + x(d.date) + "," + y(d.value) + ")");
      focus.select("text.y1")
           .attr("x", 10)
           .attr("y", -10)
           .text(function() { return d.value; });
      focus.select("text.y2")
           .attr("x", 10)
           .attr("y", -10)
           .text(function() { return d.value; });
      focus.select("text.y3")
           .attr("x", 10)
           .attr("y", +10)    
		   .text(function() { return formatDate(d.date); });
      focus.select("text.y4")
           .attr("x", 10)
           .attr("y", +10)    
		   .text(function() { return formatDate(d.date); });
      focus.select(".x-hover-line").attr("y1", 0);
      focus.select(".x-hover-line").attr("y2", height - y(d.value));
      focus.select(".y-hover-line").attr("x1", width-x(d.date));
      focus.select(".y-hover-line").attr("x2", -x(d.date));
    }
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
