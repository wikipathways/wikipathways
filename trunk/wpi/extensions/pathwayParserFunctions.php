<?php

$wgExtensionFunctions[] = 'pathwayParserFunctions_Setup';

$wgHooks['LanguageGetMagic'][] = 'pathwayParserFunctions_Magic';

function pathwayParserFunctions_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook('pathwayName', 'pp_pathwayName');
	$wgParser->setFunctionHook('pathwaySpecies', 'pp_pathwaySpecies');
}

function pathwayParserFunctions_Magic(&$magicWords, $langCode) {
	$magicWords['pathwayName'] = array(0, 'pathwayName');
	$magicWords['pathwaySpecies'] = array(0, 'pathwaySpecies');
	return true;
}

function pp_pathwayName(&$parser, $id = '') {
	if(!$id) {
		$id = $parser->mTitle->getDbKey();
	}
	$p = new Pathway($id);
	return $p->getName();
}

function pp_pathwaySpecies(&$parser, $id = '') {
	if(!$id) {
		$id = $parser->mTitle->getDbKey();
	}
	$p = new Pathway($id);
	return $p->getName();
}
?>

