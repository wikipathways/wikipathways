<?php
require_once('wpi/wpi.php');

/**
 * Disables move for pathway pages.
 * TODO: Disable this hook for running script to transfer to stable ids
 */
$wgHooks['AbortMove'][] = 'checkMoveAllowed';

function checkMoveAllowed($oldtitle, $newtitle, $user, &$error) {
	if($oldtitle->getNamespace() == NS_PATHWAY ||
		$newtitle->getNamespace() == NS_PATHWAY) {
		$error = "Pathway pages can't be moved, rename the pathway in the editor instead.";
		return false;
	}
	return true;
}

/**
 * Handles actions needed after moving a page in the pathway namespace
 * TODO: This can be removed after the stable identifiers are in place
 */
$wgHooks['SpecialMovepageAfterMove'][] = 'movePathwayPages';

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
