<?php
/**
Entry point for a pathway viewer widget that can be included in other pages.

This page will display the interactive pathway viewer for a given pathway. It takes the following parameters:
- identifier: the pathway identifier (e.g. WP4)
- version: the version (revision) number of a specific version of the pathway (optional, leave out to display the newest version)

You can include a pathway viewer in another website using an iframe:

<iframe src ="http://www.wikipathways.org/wpi/PathwayWidget.php?id=WP4" width="500" height="500" style="overflow:hidden;"></iframe>

 */
	require_once('wpi.php');
	require_once('extensions/PathwayViewer/PathwayViewer.php');
	header("X-XSS-Protection: 0");
?>
<!DOCTYPE HTML>
<html>
<head>
<style  type="text/css">
	a#wplink {
	text-decoration:none;
	font-family:serif;
	color:black;
	font-size:12px;
	}
	#logolink {
		float:right;
		top:-20px;
		left: -10px;
		position:relative;
		z-index:2;
		opacity: 0.5;
	}
	html, body {
		width:100%;
		height:100%;
	}
	#pvjs-widget {
		top:0;
		left:0;
		font-size:12px;
		width:100%;
		height:inherit;
	}
</style>
<?php
//Initialize javascript
echo '<script type="text/javascript" src="' . $jsJQuery . '"></script>' . "\n";

$imgPath = "$wgServer/$wgScriptPath/skins/common/images/";

$jsSrc = PathwayViewer::getJsDependencies();
foreach($jsSrc as $js) {
	echo '<script type="text/javascript" src="' . $js . '"></script>' . "\n";
}
$identifier = $_REQUEST['id'];
$version = isset($_REQUEST['rev']) ? $_REQUEST['rev'] : 0;
$label = isset($_REQUEST['label']) ? $_REQUEST['label'] : null;
$xref = isset($_REQUEST['xref']) ? $_REQUEST['xref'] : null;
$colors = isset($_REQUEST['colors']) ? $_REQUEST['colors'] : null;

$highlights = " ";
if ((!is_null($label) || !is_null($xref)) && !is_null($colors)){
$highlights = "[";
$selectors = array();
if (!is_null($label)){
  if (is_array($label)){
	foreach ($label as $l) {
	array_push($selectors, "{\"selector\":\"$l\",");
	}
  } else {
	array_push($selectors, "{\"selector\":\"$label\",");
  }
}
if (!is_null($xref)){
  if (is_array($xref)){
	foreach ($xref as $x) {
	$xParts = explode(",", $x);
		array_push($selectors, "{\"selector\":\"xref:id:".$xParts[0].",".$xParts[1]."\",");
	}
  } else {
	$xrefParts = explode(",", $xref);
	array_push($selectors, "{\"selector\":\"xref:id:".$xrefParts[0].",".$xrefParts[1]."\",");
  }
}

$colorArray = explode(",",$colors);
$firstColor = $colorArray[0];
if (count($selectors) != count($colorArray)){ //if color list doesn't match selector list, then just use first color
  for($i=0; $i <count($selectors); $i++){
	$colorArray[$i] = $firstColor;
  }
}

//if highlight params received
for($i=0; $i <count($selectors); $i++){
  $highlights .= $selectors[$i]."\"backgroundColor\":\"".$colorArray[$i]."\",\"borderColor\":\"".$colorArray[$i]."\"},";
}
$highlights .= "]";
}

if (!isset($highlights) || empty($highlights) || $highlights == " ") {
	$highlights = "[]";
}

$pathway = Pathway::newFromTitle($identifier);
if($version) {
		$pathway->setActiveRevision($version);
}

$svg = $pathway->getFileURL(FILETYPE_IMG);
$png = $pathway->getFileURL(FILETYPE_PNG);
echo "<script>kaavioHighlights = " . $highlights . "</script>";
$gpml = $pathway->getFileURL(FILETYPE_GPML);
?>
<title>WikiPathways Pathway Viewer</title>
</head>
<body>
	<wikipathways-pvjs
		id="pvjs-widget"
		src="<?php echo $gpml ?>"
		display-errors="true"
		display-warnings="true"
		fit-to-container="true"
		editor="disabled">'
			<img src="<?php echo $png ?>" alt="Diagram for pathway <?php echo $identifier ?>" width="600" height="420" class="thumbimage">
	</wikipathways-pvjs>
	<div style="position:absolute;height:0px;overflow:visible;bottom:0;left:15px;">
		<div id="logolink">
			<?php
				echo "<a id='wplink' target='top' href='{$pathway->getFullUrl()}'>View at ";
				echo "<img style='border:none' src='$wgScriptPath/skins/common/images/wikipathways_name.png' /></a>";
			?>
		</div>
	</div>
</body>
</html>
