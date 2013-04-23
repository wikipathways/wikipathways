<?php
/*
 * Loads javascript required for statistics.
 */
$wgExtensionFunctions[] = 'wfStatistics';
$wgHooks['LanguageGetMagic'][]  = 'wfStatistics_Magic';

function wfStatistics() {
	global $wgParser;
	$wgParser->setFunctionHook( "Statistics", "loadStatistics" );
}

function wfStatistics_Magic( &$magicWords, $langCode ) {
	$magicWords['Statistics'] = array( 0, 'Statistics' );
	return true;
}

function loadStatistics(&$parser) {
	global $wgOut;

	$src = WPI_URL . "/statistics/statistics.js";
	$parser->mOutput->addHeadItem(
		"<script src=\"https://www.google.com/jsapi\" type=\"text/javascript\"></script>\n");
	$parser->mOutput->addHeadItem(
		"<script src=\"$src\" type=\"text/javascript\"></script>\n");
	$css = WPI_URL . "/statistics/statistics.css";
	$parser->mOutput->addHeadItem(
		"<link rel=\"stylesheet\" href=\"$css\" type=\"text/css\"/>");

	return '';
}
