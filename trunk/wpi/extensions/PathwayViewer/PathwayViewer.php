<?php
require_once('DetectBrowserOS.php');

/*
 * Enable pvjs (interactive pathway viewer/editor)
 * Used in both pathway page and widget.
 */

$wgExtensionFunctions[] = 'wfPathwayViewer';
$wgHooks['LanguageGetMagic'][]  = 'wfPathwayViewer_Magic';

function wfPathwayViewer() {
	global $wgParser;
	$wgParser->setFunctionHook( "PathwayViewer", "PathwayViewer::enable" );
}

function wfPathwayViewer_Magic( &$magicWords, $langCode ) {
	$magicWords['PathwayViewer'] = array( 0, 'PathwayViewer' );
	return true;
}

class PathwayViewer {
	static function getJsDependencies() {
		global $wgScriptPath;

		if(preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT'])) {
			// if IE<=8
			$scripts = array(
			);
		}
		else {
			// if IE>8
			$scripts = array(
				// What are these for?
				"$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js",
				"$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.3.0.js",
				// pvjs and dependencies
				"//cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js",
				"//mithril.js.org/archive/v0.2.2-rc.1/mithril.min.js",
				// TODO remove the polyfill bundle below once the autopolyfill
				// work is complete. Until then, leave it as-is.
				"$wgScriptPath/wpi/lib/pvjs/release/polyfills.bundle.min.js",
				//"$wgScriptPath/wpi/lib/pvjs/dev/pvjs.core.js",
				//"$wgScriptPath/wpi/lib/pvjs/dev/pvjs.custom-element.js",
				"$wgScriptPath/wpi/lib/pvjs/release/pvjs.core.min.js",
				"$wgScriptPath/wpi/lib/pvjs/release/pvjs.custom-element.min.js",
			);
		}

		return $scripts;
	}

	static function enable(&$parser, $pwId, $imgId) {
		global $wgOut, $wgStylePath, $wpiJavascriptSources, $wgScriptPath,
			$wpiJavascriptSnippets, $jsRequireJQuery, $wgRequest, $wgJsMimeType;

		$jsRequireJQuery = true;

		try {
			$wpiJavascriptSources = array_merge($wpiJavascriptSources, PathwayViewer::getJsDependencies());

			$revision = $wgRequest->getval('oldid');

			$pathway = Pathway::newFromTitle($pwId);

			if($revision) {
				$pathway->setActiveRevision($revision);
			}
		} catch(Exception $e) {
			return "invalid pathway title: $e";
		}
	}

}
