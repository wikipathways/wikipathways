<?php

$wgExtensionFunctions[] = 'pathwayParserFunctions_Setup';

$wgHooks['LanguageGetMagic'][] = 'pathwayParserFunctions_Magic';

function pathwayParserFunctions_Setup() {
	global $wgParser;
	$wgParser->setFunctionHook('pathwayName', 'pp_pathwayName');
	$wgParser->setFunctionHook('pathwaySpecies', 'pp_pathwaySpecies');
	$wgParser->setFunctionHook('isPathway', 'pp_isPathway');
	$wgParser->setFunctionHook('isPathwayNS', 'pp_isPathwayNS');
}

function pathwayParserFunctions_Magic(&$magicWords, $langCode) {
	$magicWords['pathwayName'] = array(0, 'pathwayName');
	$magicWords['pathwaySpecies'] = array(0, 'pathwaySpecies');
	$magicWords['isPathway'] = array(0, 'isPathway');
	$magicWords['isPathwayNS'] = array(0, 'isPathwayNS');
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

function pp_isPathway(&$parser, $title) {
	try {
		$p = Pathway::newFromTitle($title);
		if($p && $p->exists()) {
			return 1;
		} else {
			return 0;
		}
	} catch(Exception $e) {
		return 0;
	}
}

function pp_isPathwayNS(&$parser, $title) {
	$t = Title::newFromText($title);
	return $t && $t->getNamespace() == NS_PATHWAY;
}
?>
