<?php
/**
 * Handles javascript dependencies for WikiPathways extensions
 */
$wgHooks['OutputPageParserOutput'][] = 'wpiAddJavascript';

function wpiAddJavascript(&$out, $parseroutput) {
	global $wgJsMimeType, $wpiJavascriptSnippets, $wpiJavascriptSources, $jsRequireJQuery, $jsJQuery, $wgRequest;

	//First add JQuery if required
	if($jsRequireJQuery) {
		$out->addScript("<script src=\"{$jsJQuery}\" type=\"{$wgJsMimeType}\"></script>\n");
	}

	//Array containing javascript source files to add
	if(!isset($wpiJavascriptSources)) $wpiJavascriptSources = array();
	$wpiJavascriptSources = array_unique($wpiJavascriptSources);

	//Array containing javascript snippets to add
	if(!isset($wpiJavascriptSnippets)) $wpiJavascriptSnippets = array();
	$wpiJavascriptSnippets = array_unique($wpiJavascriptSnippets);

	foreach($wpiJavascriptSnippets as $snippet) {
		$out->addScript("<script type=\"{$wgJsMimeType}\">$snippet</script>\n");
	}
	foreach($wpiJavascriptSources as $src) {
		$out->addScript("<script src=\"{$src}\" type=\"{$wgJsMimeType}\"></script>\n");
	}

	//Add firebug lite console if requested in GET
	$fb = $wgRequest->getval('firebug');
	if($fb) {
		$out->addScript("<script src=\"http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js\" type=\"{$wgJsMimeType}\"></script>\n");
	}
	return true;
}
?>
