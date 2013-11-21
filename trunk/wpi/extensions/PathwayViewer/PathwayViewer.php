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
		$gpml = $pathway->getFileURL(FILETYPE_GPML);

		$script = "<script type=\"{$wgJsMimeType}\">window.onload = function() {var img = $('#pwImage');if (img.get(0).nodeName.toLowerCase()!= 'img') {img = $('#pwImage img');}if (img.parent().is('a')){var oldParent=img.parent();var newParent=oldParent.parent();oldParent.after(img);oldParent.remove();}var container = $('<div />').attr('id', 'pwImage_container').css({width: '100%', height: '500px'});var parent = img.parent();img.after(container);img.remove();var layout=$('<div/>').attr('id', 'pwImage_layout').css({width:'100%',height:'100%'});var viewer=$('<div/>').addClass('ui-layout-center').css({border:'1px solid #BBBBBB','background-color':'#FFFFFF'});layout.append(viewer);container.append(layout);var pvjs=$('<div/>').attr('id','pwImage_pvjs');viewer.append(pvjs);pathvisiojs.load({target: '#pwImage_pvjs', data: \"$gpml\",hiddenElements: ['find','wikipathways-link']});}</script>";
		$script = $script . "
			<link rel=\"stylesheet\" href=\"http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css\" media=\"screen\" type=\"text/css\" />
			<link rel=\"stylesheet\" href=\"http://wikipathways.github.io/pathvisiojs/src/css/pathvisio-js.css\" media=\"screen\" type=\"text/css\" />
  			<link rel=\"stylesheet\" href=\"http://wikipathways.github.io/pathvisiojs/src/css/annotation.css\" media=\"screen\" type=\"text/css\" />
  			<link rel=\"stylesheet\" href=\"http://wikipathways.github.io/pathvisiojs/src/css/pan-zoom.css\" media=\"screen\" type=\"text/css\" />
  			<link rel=\"stylesheet\" href=\"http://wikipathways.github.io/pathvisiojs/src/css/pathway-template.css\" media=\"screen\" type=\"text/css\" />\n";
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
			"$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js",
                        "$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.3.0.js",
                        "$wgScriptPath/wpi/lib/js/rgb-color.min.js",    
                        "$wgScriptPath/wpi/lib/js/case-converter.min.js", 
                        "$wgScriptPath/wpi/lib/js/async.js",
                        "$wgScriptPath/wpi/lib/js/d3.min.js", 
                        "$wgScriptPath/wpi/lib/js/jquery.min.js", 
                        "$wgScriptPath/wpi/lib/js/typeahead.min.js", 
                        "$wgScriptPath/wpi/lib/js/openseadragon.min.js", 
                        "$wgScriptPath/wpi/lib/js/modernizr.js",   
                        "$wgScriptPath/wpi/lib/js/screenfull.min.js", 
                        "$wgScriptPath/wpi/lib/js/svg-pan.js",  
                        "$wgScriptPath/wpi/lib/js/pathfinding-browser.min.js",  
                        "$wgScriptPath/wpi/lib/js/pathvisio.min.js"            
                );  

		//Do not load svgweb when using HTML5 version of svg viewer (IE9)
//		if(browser_detection('ie_version') != 'ie9x') {
//			array_unshift($scripts, $jsSvgWeb);
//		}

		return $scripts;
	}
}
