<?php
require_once('DetectBrowserOS.php');

/*
 * Loads an interactive pathway viewer using svgweb.
 */
$wfPathwayViewerPath = WPI_URL . "/extensions/PathwayViewer";

$wgExtensionFunctions[] = 'wfPathwayViewer';
$wgHooks['LanguageGetMagic'][]  = 'wfPathwayViewer_Magic';

function wfPathwayViewer() {
	global $wgParser;
	$wgParser->setFunctionHook( "PathwayViewer", "displayPathwayViewer" );
}

function wfPathwayViewer_Magic( &$magicWords, $langCode ) {
	$magicWords['PathwayViewer'] = array( 0, 'PathwayViewer' );
	return true;
}

function displayPathwayViewer(&$parser, $pwId, $imgId) {
	global $wgOut, $wgStylePath, $wfPathwayViewerPath, $wpiJavascriptSources, $wgScriptPath,
		$wpiJavascriptSnippets, $jsRequireJQuery, $wpiAutoStartViewer;

	$jsRequireJQuery = true;

	try {
		$parser->disableCache();

		//Force flash renderer
		//<meta name="svg.render.forceflash" content="true">
		$wgOut->addMeta('svg.render.forceflash', 'true');

		//Add javascript dependencies
		XrefPanel::addXrefPanelScripts();
		$wpiJavascriptSources = array_merge($wpiJavascriptSources, PathwayViewer::getJsDependencies());

		$script = "PathwayViewer_basePath = '" . $wfPathwayViewerPath . "/';";
		$wpiJavascriptSnippets[] = $script;

		$revision = $_REQUEST['oldid'];
		$pvPwAdded[] = $pwId . '@' . $revision;

		$pathway = Pathway::newFromTitle($pwId);
		if($revision) {
			$pathway->setActiveRevision($revision);
		}
		$svg = $pathway->getFileURL(FILETYPE_IMG);
		$gpml = $pathway->getFileURL(FILETYPE_GPML);

		$dostart = ',start: true';
		$start = $dostart; //Autostart by default

		//First switch by variable in pass.php
		if(!$wpiAutoStartViewer) $start = $dostart;

		//Allow override via get parameter
		$getStart = $_GET['startViewer'];
		if($getStart == 'false' || $getStart == '0') $start = '';
		if($getStart == 'true' || $getStart == '1') $start = $dostart;

		$script = <<<SCRIPT
			var viewer = new PathwayViewer({
				  imageId: "$imgId",
						svgUrl: "$svg",
						gpmlUrl: "$gpml"$start
						});
		PathwayViewer_viewers.push(viewer);
SCRIPT;
		$script = "<script type=\"{$wgJsMimeType}\">" . $script . "</script>\n";
		return array($script, 'isHTML'=>1, 'noparse'=>1);
	} catch(Exception $e) {
		return "invalid pathway title: $e";
	}
	return true;
}

class PathwayViewer {
	static function getJsDependencies() {
		global $jsSvgWeb, $wgScriptPath, $wfPathwayViewerPath;

		$scripts = array(
			"$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js",
			"$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.3.0.js",
			"$wfPathwayViewerPath/pathwayviewer.js",
			"$wfPathwayViewerPath/highlightByElement.js"
		);

		//Do not load svgweb when using HTML5 version of svg viewer (IE9)
		if(browser_detection('ie_version') != 'ie9x') {
			array_unshift($scripts, $jsSvgWeb);
		}

		return $scripts;
	}
}
