<?php
require_once('DetectBrowserOS.php');

/*
 * Loads an interactive pathway viewer using svgweb.
 */

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
	global $wgOut, $wgStylePath, $wpiJavascriptSources, $wgScriptPath,
		$wpiJavascriptSnippets, $jsRequireJQuery, $wgRequest, $wgJsMimeType; 

	$jsRequireJQuery = true;

	try {
		$parser->disableCache();

		//Add javascript dependencies
		XrefPanel::addXrefPanelScripts();
		$wpiJavascriptSources = array_merge($wpiJavascriptSources, PathwayViewer::getJsDependencies());

		$revision = $wgRequest->getval('oldid');
		$pvPwAdded[] = $pwId . '@' . $revision;

		$pathway = Pathway::newFromTitle($pwId);
		if($revision) {
			$pathway->setActiveRevision($revision);
		}
		$png = $pathway->getFileURL(FILETYPE_PNG);
                $gpml = $pathway->getFileURL(FILETYPE_GPML);

                $script = "<script type=\"{$wgJsMimeType}\">window.onload = function() {pathvisiojs.load({container: '#pwImage_pvjs', sourceData: [{uri:\"$gpml\", fileType:\"gpml\"},{uri:\"$png\", fileType:\"png\"}],fitToContainer:'true',hiddenElements: ['find','wikipathways-link']});}</script>";
		$script = $script . "
			<link rel=\"stylesheet\" href=\"http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css\" media=\"screen\" type=\"text/css\" />
			<link rel=\"stylesheet\" href=\"$wpScriptPath/wpi/lib/css/pathvisiojs.css\" media=\"screen\" type=\"text/css\" />
  			<link rel=\"stylesheet\" href=\"$wpScriptPath/wpi/lib/css/annotation.css\" media=\"screen\" type=\"text/css\" />
  			<link rel=\"stylesheet\" href=\"$wpScriptPath/wpi/lib/css/pathway-diagram.css\" media=\"screen\" type=\"text/css\" />
			\n";
		return array($script, 'isHTML'=>1, 'noparse'=>1);
	} catch(Exception $e) {
		return "invalid pathway title: $e";
	}
	return true;
}

class PathwayViewer {
	static function getJsDependencies() {
		global $wgScriptPath; 

                $scripts = array(   
			"$wgScriptPath/wpi/extensions/PathwayViewer/pathwayviewer.js",
			"$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js",
                        "$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.3.0.js",
			// pvjs libs
                        "$wgScriptPath/wpi/lib/js/aight.min.js",
			"$wgScriptPath/wpi/lib/js/aight.d3.min.js",
                        "$wgScriptPath/wpi/lib/js/async.js",
                        "$wgScriptPath/wpi/lib/js/load-image.min.js",
                        "$wgScriptPath/wpi/lib/js/d3.min.js", 
                        "$wgScriptPath/wpi/lib/js/es5-sham.min.js",
                //        "$wgScriptPath/wpi/lib/js/jquery.min.js", //NOTE: careful, this can cause conflicts and break xrefinfo and edit functions
                        "$wgScriptPath/wpi/lib/js/jsonld.js",
                        "$wgScriptPath/wpi/lib/js/Promise.js",
                        "$wgScriptPath/wpi/lib/js/modernizr.js",   
                        "$wgScriptPath/wpi/lib/js/uuid.js",
                        "$wgScriptPath/wpi/lib/js/rgb-color.min.js",
                        "$wgScriptPath/wpi/lib/js/strcase.min.js",
                        "$wgScriptPath/wpi/lib/js/svg-pan-zoom.js",   
                        "$wgScriptPath/wpi/lib/js/typeahead.min.js",
                        // pvjs
                        "$wgScriptPath/wpi/lib/js/pathvisio.min.js",
                );  

		//Do not load svgweb when using HTML5 version of svg viewer (IE9)
//		if(browser_detection('ie_version') != 'ie9x') {
//			array_unshift($scripts, $jsSvgWeb);
//		}

		return $scripts;
	}
}
