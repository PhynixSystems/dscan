<?php
//   Uncomment this to get errors to get propper error messages
//   ini_set('display_errors', 1);
//   ini_set('display_startup_errors', 1);
//   error_reporting(E_ALL);

  // Debug function
  function debug_to_console( $data, $context = 'Debug in Console' ) {
    ob_start();
    $output  = 'console.info( \'' . $context . ':\' );';
    $output .= 'console.log(' . json_encode( $data ) . ');';
    $output  = sprintf( '<script>%s</script>', $output );
    echo $output;
  }

  $msg      = "";
  $typeOut  = "";

  $itemArray = array();
  $typeArray = array();

  $outputFilePath   = '';
  $outputFileName   = '';
  $outputCode       = '';

  $returnHTML       = false;
  $allHTML          = "";
  $subsHTML         = "";
  $capsHTML         = "";
  $supersHTML       = "";
  $otherHTML        = "";
  $interestingHTML  = "";
  $outputHTML       = "";

  $allTotal         = 0;
  $subsTotal        = 0;
  $capsTotal        = 0;
  $supersTotal      = 0;
  $otherTotal       = 0;
  $interestingTotal = 0;

  // Array declarations
  $inputArray   = array();
  $ongridArray  = array();
  $offgridArray = array();

  // Check you navigated here from a valid submit form request and also it had something in the text field
  if (isset($_POST['parseInput']) && !empty($_POST['dscanInput'])) {
    // Pull the array from the posted text area value
    $inputRaw = isset($_POST['dscanInput'])?$_POST['dscanInput']:"";

    // Make sure there is input
    if (strlen($inputRaw)==0) {
      echo 'no input';
      exit;
    }

    // Delimit raw input array according to line breaks
    $rawArray = explode("\n", str_replace("\r", "", $inputRaw));
    debug_to_console($rawArray);

    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // =======================================================
    // --> --> --> --> CHANGE THE SHIT IN HERE <-- <-- <-- <--
    // =======================================================
    // FOR SERVER USE
    // CHANGE THIS TO WHEREVER YOUR SHIT IS STORED
    // $json     = file_get_contents("http://");
    // $jsonCats = file_get_contents("http://");
    //
    // FOR LOCAL USE
    $json = file_get_contents(__DIR__ . "/data/dscan-data.json");
    $jsonCats = file_get_contents(__DIR__ . "/data/dscan-categories.json");
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $json     = json_decode($json, true);
    $jsonCats = json_decode($jsonCats, true);
    
    // Declare our helper variables so we don't recreate them in each iteration
    $iCat = "";
    $iType = "";

    // =========================================================================
    // ================== INPUT PARSING AND ARRAY POPULATION ===================
    // =========================================================================

    // Begin parsing raw array
    foreach ( $rawArray as $str ) {
      // Delimit input by blank space, and unset the first element (date)
      // - "Blank Space" isn't a space bar character, but a tab-like character
      $pieces = explode("	", $str);
      $numPieces  = count($pieces);
      
      //  - Name / Item / Distance - directly pulled from dscan
      // - Category is broad classification (Ship, Deployable, etc.)
      //    - Default is "Unknown," so if it isn't found in the data array it uses this value
      // - Type is the more secpfic classification (Strategic Cruiser, Cal Tower, etc.)
      // - Default is the same as cetegories
				$iCat       = "Unknown";
      	$iType      = "Unknown";
//       $iName      = $pieces[0];
//       $iItem      = $pieces[1];
//       $iDistance  = $pieces[2];  
			
	// Count from end of array to prevent false positives from cleverly crafted ship names
	// - replaces code above
      $iDistance  = $numPieces >= 1 ? $pieces [$numPieces - 1] : "Invalid Input - No Distance"; 
      $iItem      = $numPieces >= 2 ? $pieces [$numPieces - 2] : "Invalid Input - No Item"; 
      $iName      = $numPieces >= 3 ? $pieces [$numPieces - 3] : "Invalid Input - No Name"; 
      // There's also an ID index at the start (it's the Item ID in game, nto a unqiue one per item in the dscan, so two Absolutions will have the same ID
			
      
      // Loop over array until a match is found
      // - 3 Levels
      //  - JSON      Object (array of categories)
      //  - Category  Object (array of types)
      //  - Type      Array  (array of items)
      //    - Check if the item matches the input item
      //    - If yes, set that item's category and type to the variables
      foreach ( $json as $category => $catKey) {
        foreach ( $catKey as $type => $typeKey) {
          foreach ( $typeKey as $item ) {
            if ( $item == $iItem ) {
              $iCat = $category;
              $iType = $type;
              break;
            }
          }
        }
      }
      
      // Populate Input Array
      // - If propper entries in json data file were found, add all the variables
      // - If not, parse unknowns (with distance, so we can do on grid / off grid)
      if ( $iCat != "Unknown" && $iType != "Unknown" ) {
        array_push($inputArray, array("name" => $iName, "item" => $iItem, "distance" => $iDistance, "category" => $iCat, "type" => $iType));
      }
      else {
				// decided not to use this, but left it in just in case I change my mind later
        //array_push($inputArray, array("name" => "Unknown", "item" => "Unknown", "distance" => $iDistance, "category" => "Unknown", "type" => "Unknown"));
      }
    }

    // =========================================================================
    // ============================== TYPE ARRAY ===============================
    // =========================================================================
    // Types & Items w/ Numbers
    // if in array, add one to count, otherwise add to array with count of 1
    foreach ( $inputArray as $item => $val ) {
      // Set output array checker to false
      $typeFound = false;
      $typeIndex = 0;

      foreach ( $typeArray as $type => $val2 ) {
        if ( $val["type"] == $val2["type"] ) {
          $typeIndex = $type;
          $typeFound = true;
          break;
        }
      }

      if ( $typeFound == true ) {
        $typeArray[$typeIndex]["count"] += 1;
      }
      elseif ( $typeFound == false) {
        array_push( $typeArray, array("type" => $val["type"], "count" => 1, "category" => $val["category"]) );
      }
    }

    // Sort Array by count (DESC), then by type alphabetically (ASC)
    $count = array();
    $type = array();
    
    foreach ( $typeArray as $key => $row ) {
      $count[$key] = $row["count"];
      $type[$key] = $row["type"];
    }
    
    array_multisort($count, SORT_DESC, $type, SORT_ASC, $typeArray);

    // =========================================================================
    // ============================== ITEM ARRAY ===============================
    // =========================================================================

    // loop INPUT array
    foreach ( $inputArray as $item => $val ) {
      // Set output array checker to false
      $itemFound = false;
      $itemIndex = 0;

      // Search OUTPUT array to see if the input val there
      foreach ( $itemArray as $item => $val2 ) {
        if ( $val["item"] == $val2["item"] ) {
          $itemIndex = $item;
          $itemFound = true;
          break;
        }
      }

      // If there is a match, udpate the OUTPUT count to +1
      if ( $itemFound == true ) {
        $itemArray[$itemIndex]["count"] += 1;
      }
      // if there is no match, push INPUT item to OUTPUT array
      elseif ( $itemFound == false) {
        array_push( $itemArray, array("item" => $val["item"], "count" => 1, "type" => $val["type"], "category" => $val["category"]) );
      }
    }

    // Sort Array by count (DESC), then by item alphabetically (ASC)
    $count  = array();
    $item   = array();
    $type   = array();
    foreach ( $itemArray as $key => $row ) {
      $count[$key]  = $row["count"];
      $item[$key]   = $row["item"];
      $type[$key]   = $row["type"];
    }
    // Sort by count, then by item
    array_multisort($count, SORT_DESC, $item, SORT_ASC, $itemArray);
    
    // =====================================================================
    // =====================================================================
    // ==================== HTML Table Generators ==========================
    // =====================================================================
    // =====================================================================
    
    // Shitty way of doing this, would like to figure out a more efficient way and re-adress color coding logic. We can definitely encapsulate a lot of what's in here, I'm just lazy and haven't done it
    
    // 1) Do color indexing (read below)
    // 2) Filter array (so color indexing works properly - we have to do it anyway when sorting display code, so it's fine to do it early)
    // 3) Iterate through new array, building up html (including color indexing) through the current item's count and type/item

    // All counts = 1 are green, so are found in the foreach loop with an if statement
    // The rest are divided by three (floor) and the number is returned
    // Thr foreach counts up, and each time it hits the number it changes color by comparin to a multiplied numnber (Red -> Orange -> Green)

    // You won't see any comments for a while because they all do the same thing, I just never bothered to write this properly and so it is anythign buy DRY. 
    // Sorry.
    
    function colorNumber ($iArray) {
      $i = 0;
      foreach ($iArray as $item) {
        if ( $item["count"] > 1 ) {
          $i = $i + 1;
        }
      }
      $i = $i / 3;
      $i = floor($i);
      
      if ( count($iArray) > 5 && $i < 2) {
        $i = 2;
      }
      elseif ( $i == 0 ) {
        $i = 1;
      }
      return $i;
    }
    
    // ======================== ALL ITEMS ==========================
    // Filtering not necessary because all items will be displayed
    $allHTML = "
		<php
		ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
	?>
		";
    $allHTML .= "
      <table class='table-dscan'>
    ";
    
    $colorNum = colorNumber($itemArray);
    $iNum = 1;
    
    foreach($itemArray as $key => $item){
      $allHTML .= "<tr>";        
      $allHTML .= "<td class='dscan-count'> <span class='label label-";
      
      if ( $item["count"] == 1 ) {
        $allHTML .= "success";
      }
      elseif ( $iNum <= $colorNum ) {
        $allHTML .= "danger";
      }
      elseif ( $iNum <= $colorNum * 2 ) {
        $allHTML .= "warning";
      }
      else {
        $allHTML .= "success";
      }
      
      $allHTML .= " label-dscan'>" . $item["count"] . "</span></td>";
      $allHTML .= "<td class='dscan-text'>" . $item["item"] . "</td>";
      $allHTML .= "</tr>";
      $iNum = $iNum + 1;
      $allTotal += intval($item["count"]);
    }
    $allHTML .= "</table>";

    // ======================== SUBS (type) ==========================
    // Filter Array
    $subsTypes = array();
    foreach($typeArray as $type) {
       if ( $type["category"] == "Ships" && $type["type"] != "Freighter" && $type["type"] != "Jump Freighter" && $type["type"] != "Carrier" && $type["type"] != "Dreadnought" && $type["type"] != "Faction Dreadnought" && $type["type"] != "Force Auxilliary" && $type["type"] != "Faction Force Auxilliary" && $type["type"] != "Super Carrier" && $type["type"] != "Faction Super Carrier" && $type["type"] != "Titan" && $type["type"] != "Faction Titan" && $type["type"] != "ORE Capital / Booster" ) {
         array_push( $subsTypes, $type );
       }
    }
    
    $subsHTML = "";
    $subsHTML .= "
      <table class='table-dscan'>
    ";
    $colorNum = colorNumber($subsTypes);
    $iNum = 1;
    foreach($subsTypes as $key => $type){
      $subsHTML .= "<tr>";        
      $subsHTML .= "<td class='dscan-count'><span class='label label-";

      if ( $type["count"] == 1 ) {
        $subsHTML .= "success";
      }
      elseif ( $iNum <= $colorNum ) {
        $subsHTML .= "danger";
      }
      elseif ( $iNum <= $colorNum * 2 ) {
        $subsHTML .= "warning";
      }
      else {
        $subsHTML .= "success";
      }

      $subsHTML .= " label-dscan'>" . $type["count"] . "</span></td>";
      $subsHTML .= "<td class='dscan-text'>" . $type["type"] . "</td>";
      $subsHTML .= "</tr>";
      $iNum = $iNum + 1;
      $subsTotal += intval($type["count"]);
    }
    $subsHTML .= "</table>";

    // ======================== CAPS (type) ==========================
    // Filter Array
    $capsTypes = array();
    foreach($typeArray as $type) {
       if ( $type["type"] == "Freighter" || $type["type"] == "Jump Freighter" || $type["type"] == "Carrier" || $type["type"] == "Dreadnought" || $type["type"] == "Faction Dreadnought" || $type["type"] == "Force Auxilliary" || $type["type"] == "Faction Force Auxilliary" || $type["type"] == "Super Carrier" || $type["type"] == "Faction Super Carrier" || $type["type"] == "Titan" || $type["type"] == "Faction Titan" || $type["type"] == "ORE Capital / Booster" ) {
         array_push( $capsTypes, $type );
       }
    }
    $capsHTML = "";
    $capsHTML .= "
      <table class='table-dscan'>
    ";
    
    $colorNum = colorNumber($capsTypes);
    debug_to_console($colorNum);
    $iNum = 1;
    foreach($capsTypes as $key => $type){
      $capsHTML .= "<tr>";        
      $capsHTML .= "<td class='dscan-count'><span class='label label-";

      if ( $type["count"] == 1 ) {
        $capsHTML .= "success";
      }
      elseif ( $iNum <= $colorNum ) {
        $capsHTML .= "danger";
      }
      elseif ( $iNum <= $colorNum * 2 ) {
        $capsHTML .= "warning";
      }
      else {
        $capsHTML .= "success";
      }

      $capsHTML .= " label-dscan'>" . $type["count"] . "</span></td>";
      $capsHTML .= "<td class='dscan-text'>" . $type["type"] . "</td>";
      $capsHTML .= "</tr>";
      $iNum = $iNum + 1;
      $capsTotal += intval($type["count"]);
    }
    $capsHTML .= "</table>";

    // ======================== SUPERS (type) ==========================
    $supersHTML = "";
    $supersHTML .= "
      <table class='table-dscan'>
    ";
    foreach($typeArray as $key => $type){
      if ( $type["type"] == "Super Carrier" || $type["type"] == "Faction Super Carrier" || $type["type"] == "Titan" || $type["type"] == "Faction Titan" ) {
        $supersHTML .= "<tr>";        
        $supersHTML .= "<td> <span class='label label-default label-dscan'>" . $type["count"] . "</span> <span class='dscan-text'>" . $type["type"]  . "</span></td>";
        $supersHTML .= "</tr>";
        
        $supersTotal += intval($type["count"]);
      }
    }
    $supersHTML .= "</table>";

    // ======================== OTHER (type) ==========================
    $otherHTML = "";
    $otherHTML .= "
      <table class='table-dscan'>
    ";
    foreach($typeArray as $key => $type){
      if ( $type["category"] != "Ships" ) {
        $otherHTML .= "<tr>";        
        $otherHTML .= "<td> <span class='label label-primary label-dscan'>" . $type["count"] . "</span> <span class='dscan-text'>" . $type["type"]  . "</span></td>";
        $otherHTML .= "</tr>";
        
        $otherTotal += intval($type["count"]);
      }
    }
    $otherHTML .= "</table>";
    
    // ======================== INTERESTING (item) ==========================
    $interestingHTML = "";
    $interestingHTML .= "
      <table class='table-dscan'>
    ";
    foreach($itemArray as $key => $item){
      if ( $item["type"] == "Special Edition Frigate" || $item["type"] == "Alliance Tournament Frigate" || $item["type"] == "Special Edition Cruiser" || $item["type"] == "Alliance Tournament Cruiser" || $item["type"] == "Super Carrier" || $item["type"] == "Faction Super Carrier" || $item["type"] == "Titan" || $item["type"] == "Faction Titan" ) {
        $interestingHTML .= "<tr>";        
        $interestingHTML .= "<td> <span class='label label-info label-dscan'>" . $item["count"] . "</span> <span class='dscan-text'>" . $item["item"]  . "</span></td>";
        $interestingHTML .= "</tr>";
        
        $interestingTotal += intval($item["count"]);
      }
    }
    $interestingHTML .= "</table>";
    $returnHTML = true;

    // =====================================================================
    // =========================== PAGE BUILDER ============================
    // =====================================================================
    // This is horrible. I need to do a propper templating engine.
    $outputHTML = '
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<title>D-Scan Aggregator</title>
	<link href="https://fonts.googleapis.com/css?family=Dosis|Quicksand" rel="stylesheet">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body class="landing content-page dscan-page">	
	<div id="page-wrapper">

		<!-- ============================================================== -->
		<!-- ============================ BODY ============================ -->
		<!-- ============================================================== -->

		<article id="main">
			<header class="content-header">
				<h2 style="text-align: center;">D-Scan Output</h2>
			</header>			
			<section id="one">
				<div class="container">
					<div class="row f-s scan">					
						<div class="col-md-4 col-sm-4">
							<div class="scan-panel">';
								$outputHTML .= "<b>All Items:</b> ";
								$outputHTML .= $allTotal;
								$outputHTML .= $allHTML;
                $outputHTML .= '
							<br/>
							</div>
						</div>						
						<div class="col-md-4 col-sm-4">
							<div class="scan-panel">';							
								$outputHTML .= "<b>Subcaps:</b> ";
								$outputHTML .= $subsTotal;
								$outputHTML .= $subsHTML;
								$outputHTML .= "<br/>";
								$outputHTML .= "<b>Caps:</b> ";
								$outputHTML .= $capsTotal;
								$outputHTML .= $capsHTML;
								$outputHTML .= "<br/>";
								$outputHTML .= "<b>Supers:</b> ";
								$outputHTML .= $supersTotal;
								$outputHTML .= $supersHTML;							
						    $outputHTML .= '
							<br/>
							</div>
						</div>						
						<div class="col-md-4 col-sm-4">
							<div class="scan-panel">';							
								$outputHTML .= "<b>Interesting:</b> ";
								$outputHTML .= $interestingTotal;
								$outputHTML .= $interestingHTML;
								$outputHTML .= "<br/>";
								$outputHTML .= "<b>Other:</b> ";
								$outputHTML .= $otherTotal;
								$outputHTML .= $otherHTML;
						    $outputHTML .= '
							</div>
						</div>						
					</div>
				</div>
			</section>
		</article>
	</div>
</body>

</html>';
		
		// the scripts up there are horrible too, I need to do something about that site wide and try cut down on the 3rd party crap
		
		// Various PHP methods to create the new page and link to it. 
    // Need to do link copying.

    // Creates a random file for our file name, and build up the path for it
    $rand = substr(str_shuffle(MD5(microtime())), 0, 10);
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // build this up however makes sense for your solution
    // SERVER
    // $outputFilePath = __DIR__ . '/scan/' . $rand . '.php'; 
    // LOCAL
    $outputFilePath = __DIR__ . '/scan/' . $rand . '.php';
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $outputFileName = $rand;
    $outputFile = fopen($outputFilePath, "w");
    fwrite($outputFile, $outputHTML);
    fclose($outputFile);
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // You'll have to change the bit in front of location for your enviuronment too
    // ~~~~~~ comment out header to print shit onto the original php page (this way you can torubleshoot)
    header('Location: /unpublished/dscan/scan/' . $outputFileName . ".php");
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
  }
?>
