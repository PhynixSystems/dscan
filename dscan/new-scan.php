<?php
	// require("session.php");
// 	ini_set('display_errors', 1);
// 	ini_set('display_startup_errors', 1);
// 	error_reporting(E_ALL);
	include("dscan-tool-script.php");
   // error_reporting(E_ALL);
   // ini_set("display_errors", 1);
?>

<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<title>D-Scan Aggregator</title>
	<link href="https://fonts.googleapis.com/css?family=Dosis|Quicksand" rel="stylesheet">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body class="landing content-page">
	<div id="page-wrapper">

<!-- ============================================================== -->
<!-- ============================ BODY ============================ -->
<!-- ============================================================== -->

		<article id="main">
			<header class="content-header">
				<h2 style="text-align: center">D-Scan Tool</h2>
				<a href="#"></a>
			</header>

			<section id="one" class="wrapper style5 no-padding wrapper-content-page">
				<div class="container">
					<div class="row f-s">
						<div class="col-md-12 col-sm-12">
							<div class="panel panel-default">
								<div class="panel-body">

<!-- ============================================================== -->
<!-- =========================== MARKUP =========================== -->
<!-- ============================================================== -->

<form role = "form" action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method = "post">
	<textarea class="form-control dscan-textarea" style="width:100%" name = "dscanInput" cols="70" rows="14" placeholder = "~~~~ Steps: ~~~~
		1) Scan using the ingame D-Scan Tool
		2) Click on one of the rows in the output
		3) Press: CTRL + A
		4) Press: CTRL + C
		5) Click inside this field
		6) Press: CTRL + V
		7) Click 'Submit'" required autofocus></textarea>
	<br/>
	<button type = "submit" name = "parseInput" class = "btn btn-primary content-submit-button">Submit</button>
</form>
								</div>
							</div>
						</div>
					</div>
						
<!-- =========================================================== -->
<!-- ================== OUTPUT FILE TEST AREA ================== -->
<!-- =========================================================== -->

<!-- This is for when you have the header line (at the bottom of dscan-tool-script.php) commented out -->
<!-- It prints what would normally go ona  stand alone page below the text area, and means the console and php errors won't clear upon navigation away (helpful) -->
<!-- Probably delete this before you actually use it, have a "dev" version of this project for debug including the stuff below -->

					<div class="row f-s dscan-output">
<?php
	if ( $returnHTML == true ) {
		echo "<div class='col-md-4 col-sm-4'>";
			echo "<b>All Items:</b> " . $allTotal;
			echo $allHTML;
			echo "<br/>";
		echo "</div>";

		echo "<div class='col-md-4 col-sm-4'>";
			echo "<b>Subcaps:</b> " . $subsTotal;
			echo $subsHTML;

			echo "<br/>";

			echo "<b>Caps:</b> " . $capsTotal;
			echo $capsHTML;

			echo "<br/>";

			echo "<b>Supers:</b> " . $supersTotal;
			echo $supersHTML;
			echo "<br/>";
		echo "</div>";

		echo "<div class='col-md-4 col-sm-4'>";
			echo "<b>Interesting:</b> " . $interestingTotal;
			echo $interestingHTML;

			echo "<br/>";

			echo "<b>Other:</b> " . $otherTotal;
			echo $otherHTML;
		echo "</div>";
	}
?>
						</div>
					</div>
				</div>
			</section>
		</article>
	</div>
</body>

</html>
