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
		header("Location: fail.php?error_message=I+couldn't+find+the+name+of+the+metric+to+display.");
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
	$acknowledged = $result['acknowledged'];
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

    <title>Metric Info Page</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet" type="text/css">
    <!-- jquery dependency -->
    <script src="js/jquery-2.1.4.min.js"></script>

	<style>
		.centertext {text-align: center;}
		.righttext {text-align: right;}
		body { font: 12px Arial;}
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
		.focus circle {
			stroke: #A6A5A5;
			stroke-width: 1;
			fill: none;
		}	  
		.hover-line {
			stroke: #A6A5A5;
			stroke-width: 1;
		    stroke-dasharray: 3,3;
		}
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
		<div class="container-fluid   pull-right" style="width: 100%; margin-top:40px;" >
			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='create.php' class='btn btn-success' style='width: 100%'>Create New Metric</a>
			</div>
			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='edit_metric.php?name=<?php echo $name;?>' class='btn btn-success' style='width: 100%'>Edit Current Metric</a>
			</div>
			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='duplicate.php?name=<?php echo $name;?>' class='btn btn-success' style='width: 100%'>Duplicate Current Metric</a>
			</div>
			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='view.php?name=<?php echo $name;?>' class='btn btn-info' style='width: 100%'>View Graphs</a>
			</div>
			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='main.php?parent=<?php echo $parent;?>' class='btn btn-info' style='width: 100%'>Operating page</a>
			</div>
			<div class="row" style="width: 100%; margin-bottom: 10px">
				<a href='read.php' class='btn btn-info' style='width: 100%'>View Metrics Table</a>
			</div>
			<?php If ($acknowledged >=1) {include 'unacknowledge-button.php';} ?>
			<?php If ($acknowledged < 1 && $level_actual != 'green') {include 'acknowledge-button.php';} ?>
			
			<div class="row" style="width: 100%; margin-bottom: 10px;  margin-top: 30px;">
				<a href='delete.php?name=<?php echo $name;?>' class='btn btn-danger' style='width: 100%'>Delete Metric...</a>
			</div>
		</div>
	</div> <!-- Left Colunm -->

<div class="col-xs-6" style="min-width: 780px"> <!-- Graph Colunm -->

<div class='centertext'> <!-- Centre the graph in the svg -->

<svg width="780" height="410" style='background-color: #211F1F;'></svg>
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

<script>
var svg = d3.select("svg"),
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

d3.json(<?php echo '"'.'data.php?name='.$name.'"'; ?>, function(error, data) {
    if (error) throw error;

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

    x.domain(d3.extent(data, function(d) { return d.date; }));
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
        .datum(data)
        .attr("class", "line")
        .attr("d", valueline);

    g.append("text")
       .attr("x", (width / 2))				
       .attr("y", -10 )
       .style("fill", white)
       .attr("text-anchor", "middle")	
       .style("font-size", "20px") 
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

          <div class="col-lg-12" style="padding-right: 50px; padding-left: 50px">
			<div class="row">
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 15px">
				<p style="text-align: center; margin: 0 0 0px">Alert</p>
				<input type="text" class="form-control"
				       id="alert_lower"
				       name="alert_lower"
				       value="<?php echo $alert_lower; ?>"
				       style="text-align: center; border-color: #6B3432; background: #332424; color: #D3D2D2;"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Caution</p>
				<input type="text" class="form-control"
				       id="caution_lower"
				       name="caution_lower"
				       value="<?php echo $caution_lower; ?>"
				       style="text-align: center; border-color: #745832; background: #352D24; color: #D3D2D2;"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Normal</p>
				<input type="text" class="form-control"
				       id="normal_lower"
				       name="normal_lower"
				       value="<?php echo $normal_lower; ?>"
				       style="text-align: center; border-color: #395C37; background: #272E25; color: #D3D2D2;"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Lower</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Normal</p>
				<input type="text" class="form-control"
				       id="normal_upper"
				       name="normal_upper"
				       value="<?php echo $normal_upper; ?>"
				       style="text-align: center; border-color: #395C37; background: #272E25; color: #D3D2D2;"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
              <div class="col-xs-2" style="padding-right: 5px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Caution</p>
				<input type="text" class="form-control"
				       id="caution_upper"
				       name="caution_upper"
				       value="<?php echo $caution_upper; ?>"
				       style="text-align: center; border-color: #745832; background: #352D24; color: #D3D2D2;"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
              <div class="col-xs-2" style="padding-right: 15px; padding-left: 5px">
				<p style="text-align: center; margin: 0 0 0px">Alert</p>
				<input type="text" class="form-control"
				       id="alert_upper"
				       name="alert_upper"
				       value="<?php echo $alert_upper; ?>"
				       style="text-align: center; border-color: #6B3432; background: #332424; color: #D3D2D2;"
				       READONLY >
				<p style="text-align: center; margin: 0 0 0px">Upper</p>
              </div>
			</div>

	      </div>
        </div>
      </div>


      </div>

</div> <!-- Graph Column -->

	<div class="col-xs-3"> <!-- Right Column -->
		<div class="container-fluid">
			<div class="row band">
				<div class="table-responsive">
					<table class="table table-condensed" style="margin-top:40px;">
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

</div> <!-- Row -->


    <!-- Another row -->

	<div class="row" >
		<div class="col-md-12">
		<div class='centertext'>

			<!-- ****************** Start of first graph ***************** -->
			<div class="row" id="graph1">


			</div> <!-- /row -->
			<!-- ****************** End of first graph ***************** -->

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

