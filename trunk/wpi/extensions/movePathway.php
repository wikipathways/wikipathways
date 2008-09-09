<?php
require_once('wpi/wpi.php');

$wgHooks['AbortMove'][] = 'checkMoveAllowed';$wgHooks['SpecialMovepageAfterMove'][] = 'movePathwayPages';

/**
 * Handles actions before move: check if the pathway name is valid and
 * return error message if not
 */
function checkMoveAllowed($oldtitle, $newtitle, $usser, &$error) {
	if($oldtitle->getNamespace() == NS_PATHWAY ||
	 	$newtitle->getNamespace() == NS_PATHWAY) {
		try {
			$pwNew = Pathway::newFromTitle($newtitle);
		} catch(Exception $e) {
			$error = $e->getMessage();
			return false;
		}
	}
	return true;
}

/**
 * Handles actions needed after moving a page in the pathway namespace
 */
function movePathwayPages(&$movePageForm , &$ot , &$nt) {
	if($ot->getNamespace() == NS_PATHWAY) {
		$pwOld = Pathway::newFromTitle($ot);		
		//Clean up old cache and update for the new page
		$pwOld->clearCache(null, true);
	
		$pwNew = Pathway::newFromTitle($nt);
		$pwNew->updateCache();
		$pwNew->updateCategories();
	}
	return(true);
}
