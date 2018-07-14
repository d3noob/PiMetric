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
		header("Location: fail.php?error_message=I+couldn't+find+the+name+of+the+metric+to+graph.");
		exit();
	}
	$bingo = 0;

	// Get all the metric details from the status database
	$db = new PDO('sqlite:/srv/PiMetric/monitoring/monitoring');
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
	header("Location: fail.php?error_message=Something+weird+happened+while+trying+to+draw+the+graph.");
	exit();
}


?>

<!DOCTYPE html>
<meta charset="utf-8">

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <!--  <link rel="icon" href="../../favicon.ico"> -->

    <title>Read Metrics</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/justified-nav.css" rel="stylesheet">

	<style>
		.centertext {text-align: center;}
		.righttext {text-align: right;}

		body { font: 12px Arial;}

		.axis {
			font: 12px sans-serif;
		}

		.axis path,
		.axis line {
			fill: none;
			stroke: grey;
			stroke-width: 1;
			shape-rendering: crispEdges;
		}

		.line {
			stroke: steelblue;
			stroke-width: 2;
			fill: none;
		}

		text.shadow {
		    stroke: white;
		    stroke-width: 3px;
		    opacity: 0.8;
		}

		.overlay {
		    fill: none;
		    pointer-events: all;
		}

		.focus circle {
			stroke: darkslategrey;
			stroke-width: 1;
			fill: none;
		}
		  
		.hover-line {
			stroke: darkslategrey;
			stroke-width: 1;
		    stroke-dasharray: 3,3;
		}
	</style>

    <script src="js/jquery-2.1.4.min.js"></script>

  </head>

<body>

    <div class="container">

      <!-- The justified navigation menu is meant for single line per list item.
           Multiple lines will require custom code not provided by Bootstrap. -->
      <div class="masthead">
        <h3 class="text-muted">PiMetric Configuration Management</h3>
        <nav>
          <ul class="nav nav-justified">
            <li><a href="create.php">Create</a></li>
            <li class="active"><a href="read.php">Read</a></li>
            <li><a href="edit.php">Edit</a></li>
            <li><a href="delete.php">Delete</a></li>
            <li><a href="faqs.php">FAQs</a></li>
            <li><a href="main.php">Operating</a></li>
          </ul>
        </nav>
      </div>

	<p></p>

<div class='centertext'> <!-- Centre the graph in the svg -->

<svg width="960" height="500"></svg>
<script src="js/d3.v4.min.js"></script>
<script>
var svg = d3.select("svg"),
    margin = {top: 40, right: 40, bottom: 30, left: 40},
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

d3.json(<?php echo '"'.'data.php?name='.$name.'"'; ?>, function(error, data) {
    if (error) throw error;
console.log(data);

	// format the data
    data.forEach(function(d) {
        d.date = parseTime(d.dtg);
        d.value = +d.value;
    });

    data.sort((a, b) => a.date - b.date);

	// Determine the vertical upper and lower limits
	var graphMax = d3.max(data, function(d) {return Math.max(d.value, normalUpper); });
	var graphMin = d3.min(data, function(d) {return Math.min(d.value, normalLower); });
	var limitMax = graphMax * 1.05;
	var limitMin = graphMin * .95;

    x.domain(d3.extent(data, function(d) { return d.date; }));
    y.domain([limitMin, limitMax]);

	// Set the upper and lower values so that they arrange correctly on the graph
	if (alertUpper>= graphMax) {alertUpper = limitMax};
	if (cautionUpper>= graphMax) {cautionUpper = limitMax};
	if (normalUpper>= graphMax) {normalUpper = limitMax};
	if (normalLower <= graphMin) {normalLower = limitMin};
	if (cautionLower <= graphMin) {cautionLower = limitMin};
	if (alertLower <= graphMin) {alertLower = limitMin};

    g.append("rect")
       .style("fill", "#d9edf7") // blue		
       .attr("x", 0)				
       .attr("y", 0)
       .attr("height", height)	
       .attr("width", width);

    g.append("rect")
       .style("fill", "#f2dede") // red		
       .attr("x", 0)				
       .attr("y", y(alertUpper))
       .attr("height", y(alertLower) - y(alertUpper))	
       .attr("width", width);

    g.append("rect")
       .style("fill", "fcf8e3") // orange
       .attr("x", 0)				
       .attr("y", y(cautionUpper))
       .attr("height", y(cautionLower) - y(cautionUpper))	
       .attr("width", width);

    g.append("rect")
       .style("fill", "#dff0d8") // green
       .attr("x", 0)				
       .attr("y", y(normalUpper))
       .attr("height", y(normalLower) - y(normalUpper))	
       .attr("width", width);

    g.append("g")
        .attr("class", "axis axis--x")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(x));

    g.append("g")
        .attr("class", "axis axis--y")
        .call(d3.axisLeft(y).ticks(6));

    g.append("path")
        .datum(data)
        .attr("class", "line")
        .attr("d", valueline);

    g.append("text")
       .attr("x", (width / 2))				
       .attr("y", -10 )
       .attr("text-anchor", "middle")	
       .style("font-size", "24px") 
       .text(<?php echo '"'.$label.'"' ?>);


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
        .style("stroke", "white")
        .style("stroke-width", "3.5px")
        .style("opacity", 0.8)
        .attr("x", 10)
        .attr("y", -10)
      	.attr("dy", ".31em");
   focus.append("text")
	    .attr("class", "y1")	
        .style("fill", "black")
        .style("stroke-width", "1px")
        .style("opacity", 1)
      	.attr("dy", ".31em");

   // time on cursor
    focus.append("text")
	    .attr("class", "y4")	
        .style("stroke", "white")
        .style("stroke-width", "3.5px")
        .style("opacity", 0.8)
      	.attr("dy", ".31em");
   focus.append("text")
	    .attr("class", "y3")	
        .style("fill", "black")
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
          i = bisectDate(data, x0, 1),
          d0 = data[i - 1],
          d1 = data[i],
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

    </div> <!-- SVG -->

<P></P>

      <div class='centertext'><!-- Limits presentation -->
     <!-- *** Alert limits fields *** -->
      <div class="row">
	    <div class="form-group">
          <div class="col-lg-2">
	      </div>
          <div class="col-lg-8">
			<div class="row">
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 15px">
				<p style="text-align: center; margin: 0 0 0px">Alert</p>
				<input type="text" class="form-control"
				       id="alert_lower"
				       name="alert_lower"
				       value="<?php echo $alert_lower; ?>"
				       style="text-align: center; border-color: red"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Caution</p>
				<input type="text" class="form-control"
				       id="caution_lower"
				       name="caution_lower"
				       value="<?php echo $caution_lower; ?>"
				       style="text-align: center; border-color: orange"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Normal</p>
				<input type="text" class="form-control"
				       id="normal_lower"
				       name="normal_lower"
				       value="<?php echo $normal_lower; ?>"
				       style="text-align: center; border-color: green"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Normal</p>
				<input type="text" class="form-control"
				       id="normal_upper"
				       name="normal_upper"
				       value="<?php echo $normal_upper; ?>"
				       style="text-align: center; border-color: green"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Caution</p>
				<input type="text" class="form-control"
				       id="caution_upper"
				       name="caution_upper"
				       value="<?php echo $caution_upper; ?>"
				       style="text-align: center; border-color: orange"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
              <div class="col-xs-2" style="padding-right: 15px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Alert</p>
				<input type="text" class="form-control"
				       id="alert_upper"
				       name="alert_upper"
				       value="<?php echo $alert_upper; ?>"
				       style="text-align: center; border-color: red"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
			</div>
          <div class="col-lg-2">
	      </div>
	      </div>
        </div>
      </div>


      </div>


      <!-- Site footer -->
      <footer class="footer">
		<img src="ee.png">
      </footer>

    </div> <!-- /container -->


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

</body>
