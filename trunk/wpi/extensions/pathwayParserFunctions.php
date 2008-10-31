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
	try {
		if(!$id) {
			$id = $parser->mTitle->getDbKey();
		}
		$p = Pathway::newFromTitle($id);
		return $p->getName();
	} catch(Exception $e) {
		return "ERROR: Couldn't create pathway '$id'\n" . $e;
	}
}

function pp_pathwaySpecies(&$parser, $id = '') {
	try {
		if(!$id) {
			$id = $parser->mTitle->getDbKey();
		}
		$p = Pathway::newFromTitle($id);
		return $p->getName();
	} catch(Exception $e) {
		return "ERROR: Couldn't create pathway '$id'\n" . $e;
	}
}
?>

