<?php?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FAQs</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/justified-nav.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet" type="text/css">

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
            <li><a href="read.php">Read</a></li>
            <li><a href="edit.php">Edit</a></li>
            <li><a href="delete.php">Delete</a></li>
            <li class="active"><a href="faqs.php">FAQs</a></li>
            <li><a href="main.php">Operating</a></li>
          </ul>
        </nav>
      </div>

      <div class="page-header">
        <p class="lead">Just the FAQs.</p>
      </div>

		 <!-- Snippet Code -->
		<div class="accordion" id="accordion2">
				<!-- Each item should be enclosed inside the class "accordion-group". Note down the below markup. -->
									   
			   <div class="accordion-group">
				 <div class="accordion-heading">
				   <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#1">
					  <!-- Title. Don't forget the <i> tag. -->
					 <h5> What is the visit management web site trying to accomplish?</h5>
				   </a>
				 </div>
				 <div id="1" class="accordion-body collapse" style="height: 0px;">
				   <div class="accordion-inner">
					  <!-- Para -->
					 <p>Managing visitors in a consistent way is difficult. The aim of the site is to make the process of capturing the details of a visit easy and consistent for the purposes of knowing what is coming up and for making sure that the people who need to be advised are notified.</p>
				   </div>
				 </div>
			   </div>
									   
 			   <div class="accordion-group">
				 <div class="accordion-heading">
				   <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#2">
					  <!-- Title. Don't forget the <i> tag. -->
					 <h5> Why did you build the appication?</h5>
				   </a>
				 </div>
				 <div id="2" class="accordion-body collapse" style="height: 0px;">
				   <div class="accordion-inner">
					  <!-- Para -->
					 <p>I was looking for a small project to use as a training task to learn about the form submission and database structure. This builds on the work done with the Plus 1 Someone site helps solve a problem that had been identified with making tracking of visitors simpler. I was also looking for a project to do as part of the I.E.D Day and this fit the bill nicely :-)</p>
				   </div>
				 </div>
			   </div>                   
	   </div>
<!-- Snipet Code end -->

    </div> <!-- /container -->

	<?php include 'footer.php'; ?>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

  </body>
</html>
