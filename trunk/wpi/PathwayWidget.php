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
?>
<HTML>
<HEAD>
<STYLE>
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
	opacity: 0.8;
}
</STYLE>
<meta name="svg.render.forceflash" content="true" />
<?php
	echo '<link rel="stylesheet" href="' . $wgScriptPath . '/wpi/js/jquery-ui/jquery-ui-1.7.2.custom.css" type="text/css" />' . "\n";
	echo '<script type="text/javascript" src="' . $jsJQuery . '"></script>' . "\n";
	echo '<script type="text/javascript" src="' . $jsJQueryUI . '"></script>' . "\n";	
	echo '<script type="text/javascript" src="' . $wgScriptPath . '/wpi/js/xrefpanel.js"></script>' . "\n";		

	//Initialize javascript
	foreach(PathwayViewer::getJsDependencies() as $js) {
		echo '<script type="text/javascript" src="' . $js . '"></script>' . "\n";
	}

	$search = '';
	if($wikipathwaysSearchUrl) {
		$search = 'XrefPanel_searchUrl = "' . $wikipathwaysSearchUrl . '/#type=id&text=$ID&system=$DATASOURCE";';
	}

	$bridge = "XrefPanel_dataSourcesUrl = '" . WPI_CACHE_URL . "/datasources.txt';\n";
	if($wpiBridgeUrl !== false) { //bridgedb web service support can be disabled by setting $wpiBridgeDb to false
		if(!isset($wpiBridgeUrl) || $wpiBridgeUseProxy) {
			//Point to bridgedb proxy by default
			$bridge .= "XrefPanel_bridgeUrl = '" . WPI_URL . '/extensions/bridgedb.php' . "';\n";
		} else {
			$bridge .= "XrefPanel_bridgeUrl = '$wpiBridgeUrl';\n";
		}
	}

	$id = $_REQUEST['id'];
	$rev = $_REQUEST['rev'];
	
	$pathway = Pathway::newFromTitle($id);
	if($rev) {
		$pathway->setActiveRevision($rev);
	}
	
	$svg = $pathway->getFileURL(FILETYPE_IMG);
	$gpml = $pathway->getFileURL(FILETYPE_GPML);
		
	echo <<<SCRIPT
<script type="text/javascript">
	PathwayViewer_basePath = '$wfPathwayViewerPath/';
	PathwayViewer.pathwayInfo.push({
		imageId: "pathwayImage",
		svgUrl: "$svg",
		gpmlUrl: "$gpml",
		start: true,
		width: '100%',
		height: '100%'
	});
	$search
	$bridge
</script>
SCRIPT;
?>
</HEAD>
<BODY>
<div id="pathwayImage" style="font-size:12px;"><img src="test" /></div>
<div style="position:relative;height:0px;overflow:visible;">
	<div id="logolink">
		<?php
			echo "<a id='wplink' target='top' href='{$pathway->getFullUrl()}'>View at "; 
			echo "<img style='border:none' src='$wgScriptPath/skins/common/images/wikipathways_name.png' /></a>"; 
		?>
	</div>
</div>
</BODY>
</HTML>
