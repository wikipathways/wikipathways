<?php
/**
Provide an information and cross-reference panel for xrefs on a wiki page.

<Xref id="1234" datasource="L" species="Homo sapiens">Label</Xref>

**/

$wgExtensionFunctions[] = "wpiXref";

function wpiXref() {
	global $wgParser;
	$wgParser->setHook( "Xref", "renderXref" );
	
	wpiAddXrefPanelScripts();
}

function wpiXrefRender($input, $argv, &$parser) {
	return wpiXrefHTML($argv['id'], $argv['datasource'], $input, $argv['species']);
}

function wpiXrefHTML($id, $datasource, $label, $text, $species) {
	$url = SITE_URL . '/skins/common/images/info.png';
	$fun = "XrefPanel.registerTrigger(this, '$id', '$datasource', '$species', '$label');";
	$html = $text . " <img title='Show additional info and linkouts' style='cursor:pointer;' onload=\"$fun\" src='$url'/>";
	return $html;
}

?>
