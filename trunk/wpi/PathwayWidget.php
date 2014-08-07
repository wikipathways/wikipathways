<?php
/**
Entry point for a pathway viewer widget that can be included in other pages.

This page will display the interactive pathway viewer for a given pathway. It takes the following parameters:
- id: the pathway id (e.g. WP4)
- rev: the revision number of a specific version of the pathway (optional, leave out to display the newest version)

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
#pwImage_pvjs {
/*
  position:fixed;
//*/
  top:0;
  left:0;
  font-size:12px;
  width:100%;
  height:100%;
}
</style>
<?php
//    echo '<link rel="stylesheet" href="' . $cssJQueryUI . '" type="text/css" />' . "\n";
          echo "<link rel=\"stylesheet\" href=\"http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css\" media=\"screen\" type=\"text/css\">
    <link rel=\"stylesheet\" href=\"$wgScriptPath/wpi/lib/pathvisiojs/css/pathvisiojs.bundle.css\" media=\"screen\" type=\"text/css\" />
                        \n";
//Initialize javascript
echo '<script type="text/javascript" src="' . $jsJQuery . '"></script>' . "\n";

//Needed for xrefinfo buttons in External References section
$jsSnippets = XrefPanel::getJsSnippets();
foreach($jsSnippets as $js) {
  echo "<script type=\"text/javascript\">$js</script>\n";
}

$imgPath = "$wgServer/$wgScriptPath/skins/common/images/";
echo "<script type=\"text/javascript\">XrefPanel_imgPath = '$imgPath';</script>";

$jsSrc = PathwayViewer::getJsDependencies();
$jsSrc = array_merge($jsSrc, XrefPanel::getJsDependencies());
foreach($jsSrc as $js) {
  echo '<script type="text/javascript" src="' . $js . '"></script>' . "\n";
}
$id = $_REQUEST['id'];
$rev = $_REQUEST['rev'];
$label = $_REQUEST['label'];
$xref = $_REQUEST['xref'];
$colors = $_REQUEST['colors'];

$highlights = " ";
if ((!is_null($label) || !is_null($xref)) && !is_null($colors)){
$highlights = ", highlights: [";
$selectors = array();
if (!is_null($label)){
  if (is_array($label)){
    foreach ($label as $l) {
  array_push($selectors, "{\"label\":\"$l\",");
    }
  } else {
    array_push($selectors, "{\"label\":\"$label\",");
  }
}
if (!is_null($xref)){
  if (is_array($xref)){
    foreach ($xref as $x) {
  $xParts = explode(",", $x);
        array_push($selectors, "{\"xref\":\"".$xParts[0]."-".$xParts[1]."\",");
    }
  } else {
    $xrefParts = explode(",", $xref);
    array_push($selectors, "{\"xref\":\"".$xrefParts[0]."-".$xrefParts[1]."\",");
  }
}

$colorArray = explode(",",$colors);
$firstColor = $colorArray[0];
if (count($selectors) != count($colorArray)){ //if color list doesn't match selector list, then just use first color
  for($i=0; $i <count($selectors); $i++){
  $colorArray[$i] = $firstColor;
  }
}

for($i=0; $i <count($selectors); $i++){
  $highlights .= $selectors[$i]."\"style\":{\"fill\":\"".$colorArray[$i]."\",\"stroke\":\"".$colorArray[$i]."\"}},";
}
$highlights .= "]";
}//if highlight params received

$pathway = Pathway::newFromTitle($id);
if($rev) {
        $pathway->setActiveRevision($rev);
}

$svg = $pathway->getFileURL(FILETYPE_IMG);
$png = $pathway->getFileURL(FILETYPE_PNG);
echo "<script>pngFilePath = \"$png\"</script>";
$gpml = $pathway->getFileURL(FILETYPE_GPML);
echo "<script>gpmlFilePath = \"$gpml\"</script>";
?>
<title>WikiPathways Pathway Viewer</title>
</head>
<body>
<div id="pwImage_pvjs">
  <?php
    if(preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT'])) {
      echo "<img src=\"$png\" alt='View SVG' width='600' height='420' class='thumbimage>";
    }
  ?>
</div>
<div style="position:absolute;height:0px;overflow:visible;bottom:0;left:15px;">
  <div id="logolink">
    <?php
      echo "<a id='wplink' target='top' href='{$pathway->getFullUrl()}'>View at ";
      echo "<img style='border:none' src='$wgScriptPath/skins/common/images/wikipathways_name.png' /></a>";
    ?>
  </div>
</div>
<script>
if (Modernizr.inlinesvg) {
  $(function(){

    var colors;
    if (!!queryStringParameters.colors) {
      colors = queryStringParameters.colors.split(',');
    }

    var xrefs = queryStringParameters.xref;
    if (!!xrefs && (typeof(xrefs) === 'string')) {
      xrefs = [xrefs];
    }
    var xrefHighlights = [];
    var xrefIndex = 0;
    _.forEach(xrefs, function(xref) {
      var xrefHighlight = {};
      xrefHighlight.id = xref.split(',')[0];
      xrefHighlight.color = colors[xrefIndex] || colors[0];
      xrefHighlights.push(xrefHighlight);
      xrefIndex += 1;
    });

    var labels = queryStringParameters.label;
    if (!!labels && (typeof(labels) === 'string')) {
      labels = [labels];
    }
    var labelIndex = 0;
    var labelHighlights = [];
    _.forEach(labels, function(label) {
      var labelHighlight = {};
      labelHighlight.id = label;
      labelHighlight.color = colors[labelIndex] || colors[0];
      labelHighlights.push(labelHighlight);
      labelIndex += 1;
    });

    $('#pwImage_pvjs').pathvisiojs({
      fitToContainer: true,
      manualRender: true,
      sourceData: [{uri:gpmlFilePath, fileType:'gpml'},{uri:pngFilePath, fileType:'png'}]
    });

    var pathInstance = $('#pwImage_pvjs').pathvisiojs('get').pop();

    pathvisiojsNotifications(pathInstance, {displayErrors: true, displayWarnings: false});

    pathInstance.on('rendered', function(){
      var hi = pathvisiojsHighlighter(pathInstance);

      if (!!labelHighlights && labelHighlights.length > 0) {
        labelHighlights.forEach(function(labelHighlight) {
          hi.highlight(labelHighlight.id, null, {backgroundColor: labelHighlight.color, borderColor: labelHighlight.color});
        });
      }

      if (!!xrefHighlights && xrefHighlights.length > 0) {
        xrefHighlights.forEach(function(xrefHighlight) {
          hi.highlight('xref:id:' + xrefHighlight.id, null, {backgroundColor: xrefHighlight.color, borderColor: xrefHighlight.color});
        });
      }
    });

    pathInstance.render();
  });
}




/*
if (Modernizr.inlinesvg) {
  $(function(){

    var colors;
    if (!!queryStringParameters.colors) {
      colors = queryStringParameters.colors.split(',');
    }
    var xrefHighlights = [];
    var xrefIndex = 0;
    _.forEach(queryStringParameters.xref, function(xref) {
      var xrefHighlight = {};
      xrefHighlight.id = xref.split(',')[0];
      xrefHighlight.color = colors[xrefIndex] || colors[0];
      xrefHighlights.push(xrefHighlight);
      xrefIndex += 1;
    });

    var labelIndex = 0;
    var labelHighlights = [];
    _.forEach(queryStringParameters.label, function(label) {
      var labelHighlight = {};
      labelHighlight.id = label;
      labelHighlight.color = colors[labelIndex] || colors[0];
      labelHighlights.push(labelHighlight);
      labelIndex += 1;
    });

    $('#pwImage_pvjs').pathvisiojs({
      fitToContainer: true,
      manualRender: true,
      sourceData: [{uri:gpmlFilePath, fileType:'gpml'},{uri:pngFilePath, fileType:'png'}]
    });

    var pathInstance = $('#pwImage_pvjs').pathvisiojs('get').pop();

    pathvisiojsNotifications(pathInstance, {displayErrors: true, displayWarnings: true});

    pathInstance.on('rendered', function(){
      var hi = pathvisiojsHighlighter(pathInstance);

      if (!!labelHighlights && labelHighlights.length > 0) {
        labelHighlights.forEach(function(labelHighlight) {
          hi.highlight(labelHighlight.id, null, {fill: labelHighlight.color, stroke: labelHighlight.color});
        });
      }

      if (!!xrefHighlights && xrefHighlights.length > 0) {
        xrefHighlights.forEach(function(xrefHighlight) {
          hi.highlight('xref:' + xrefHighlight.id, null, {fill: xrefHighlight.color, stroke: xrefHighlight.color});
        });
      }
    });

    pathInstance.render();
  });
}
//*/
<script>
</body>
</html>
