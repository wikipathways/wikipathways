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

		// Option #1: do browser detection with PHP
		if(!preg_match('/(?i)msie [6-9]/',$_SERVER['HTTP_USER_AGENT'])) {
		    // if IE>8
			$script = "<script type=\"{$wgJsMimeType}\">window.addEventListener('load', function() {var pathvisiojsInstance = Object.create(pathvisiojs); pathvisiojsInstance.load({container: '#pwImage_pvjs', sourceData: [{uri:\"$gpml\", fileType:\"gpml\"},{uri:\"$png\", fileType:\"png\"}],fitToContainer:'true'});}, false);</script>";
		}

		// Option #2: do browser detection with JS
                //$script = "<script type=\"{$wgJsMimeType}\">window.addEventListener('load', function() { var isIE = function() { var myNav = navigator.userAgent.toLowerCase(); return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1], 10) : false; }; if (Modernizr.inlinesvg && (!pathvisiojs.utilities.isIE() || pathvisiojs.utilities.isIE() > 9)) { var pathvisiojsInstance = Object.create(pathvisiojs); pathvisiojsInstance.load({ container: '#pwImage_pvjs', sourceData: [{ uri: \"$gpml\", fileType: 'gpml' }, { uri: \"$png\", fileType: 'png' }], fitToContainer: 'true' }); } }, false);</script>";
		$script = $script . "<link rel=\"stylesheet\" href=\"http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css\" media=\"screen\" type=\"text/css\">
			<link rel=\"stylesheet\" href=\"$wgScriptPath/wpi/lib/pathvisiojs/css/pathvisiojs.css\" media=\"screen\" type=\"text/css\" />
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

		if(preg_match('/(?i)msie [6-9]/',$_SERVER['HTTP_USER_AGENT'])) {
		    // if IE<=8
			$scripts = array(   
/* It appears all of these are used for the pathway viewer. If pvjs won't render and we're just using the thumbnail, there's no need for them. -AR
				"$wgScriptPath/wpi/extensions/PathwayViewer/pathwayviewer.js",
				"$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js",
				"$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.3.0.js",
//*/
			);  
		}
		else {
		    // if IE>8
			$scripts = array(   
				"$wgScriptPath/wpi/extensions/PathwayViewer/pathwayviewer.js",
				"$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js",
				"$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.3.0.js",
				// pvjs libs
				"//cdnjs.cloudflare.com/ajax/libs/async/0.7.0/async.js",
				"$wgScriptPath/wpi/lib/he/js/he.js", 
				"$wgScriptPath/wpi/lib/d3/js/d3-with-aight.min.js", 
			//        "$wgScriptPath/wpi/lib/js/jquery.min.js", //NOTE: careful, this can cause conflicts and break xrefinfo and edit functions
				"//cdnjs.cloudflare.com/ajax/libs/modernizr/2.7.1/modernizr.min.js",   
				"//cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.9.3/typeahead.min.js",
				// pvjs
				"$wgScriptPath/wpi/lib/pathvisiojs/js/pathvisiojs.min.js",
			);  
		}

		//Do not load svgweb when using HTML5 version of svg viewer (IE9)
//		if(browser_detection('ie_version') != 'ie9x') {
//			array_unshift($scripts, $jsSvgWeb);
//		}

		return $scripts;
	}
}
