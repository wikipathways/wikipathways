<?php
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
		$wpiJavascriptSnippets, $wgJsMimeType, $jsJQuery, $jsSvgWeb, $jsJQueryUI, $jsJQueryXML;
	
	try {
		$parser->disableCache();

		//Force flash renderer
		//<meta name="svg.render.forceflash" content="true">
		$wgOut->addMeta('svg.render.forceflash', 'true');
		
		//Add javascript dependencies
		$wpiJavascriptSources[] = $jsSvgWeb;
		wpiAddXrefPanelScripts();
		$wpiJavascriptSources[] = "$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js";
		$wpiJavascriptSources[] = "$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.2.0.js";
		$wpiJavascriptSources[] = "$wfPathwayViewerPath/pathwayviewer.js";
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
			      
		$script = <<<SCRIPT
	var pwInfo = {
		imageId: "$imgId",
		svgUrl: "$svg",
		gpmlUrl: "$gpml"
	}
	PathwayViewer.pathwayInfo.push(pwInfo);
SCRIPT;
		$script = "<script type=\"{$wgJsMimeType}\">" . $script . "</script>\n";
		return array($script, 'isHTML'=>1, 'noparse'=>1);
	} catch(Exception $e) {
          return "invalid pathway title: $e";
     }
	return true;
}
?>
