<?php
require_once('wpi/wpi.php');

/**
 * Disables deletion for pathway pages.
 * TODO: Disable this hook for running script to transfer to stable ids
 */
$wgHooks['ArticleDelete'][] = 'checkForPathway';

function checkForPathway(&$article, &$user, &$reason, $error = '') {
	if($article && !$error && $article->getTitle()->getNamespace() == NS_PATHWAY) {
		//Prevent pathway page deletion, mark deprecated instead
		$pathway = Pathway::newFromTitle($article->getTitle());
		$pathway->markDeprecated($reason);
		return false;
	}
	return true;
}

/**
 * Special user permissions once a pathway is deprecated.
 * TODO: Disable this hook for running script to transfer to stable ids
 */
$wgHooks['userCan'][] = 'checkDeprecated';

function checkDeprecated($title, $user, $action, $result) {
	if($action == 'edit' && $title->getNamespace() == NS_PATHWAY) {
		$pathway = Pathway::newFromTitle($title);
		if($pathway->isDeprecated()) {
			//Only users with 'delete' permission can revert deprecation
			//So disable edit for all other users
			$result = $title->getUserPermissionsErrors('delete', $user) == array();
			return false;
		}
	}
	$result = null;
	return true;
}

?>
