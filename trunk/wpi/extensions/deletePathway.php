<?php
require_once('wpi/wpi.php');

/**
 * Special user permissions once a pathway is deleted.
 * TODO: Disable this hook for running script to transfer to stable ids
 */
$wgHooks['userCan'][] = 'checkDeleted';

function checkDeleted($title, $user, $action, $result) {
	if($action == 'edit' && $title->getNamespace() == NS_PATHWAY) {
		$pathway = Pathway::newFromTitle($title);
		if($pathway->isDeleted()) {
			if(MwUtils::isOnlyAuthor($user, $title->getArticleId())) {
				//Users that are sole author of a pathway can always revert deletion
				$result = true;
				return false;
			} else {
				//Only users with 'delete' permission can revert deletion
				//So disable edit for all other users
				$result = $title->getUserPermissionsErrors('delete', $user) == array();
				return false;
			}
		}
	}
	$result = null;
	return true;
}

/*
 * Special delete permissions for pathways if user is sole author
 */
$wgHooks['userCan'][] = 'checkSoleAuthor';

function checkSoleAuthor($title, $user, $action, $result) {
	//Users are allowed to delete their own pathway
	if($action == 'delete' && $title->getNamespace() == NS_PATHWAY) {
		if(MWUtils::isOnlyAuthor($user, $title->getArticleId()) && $title->userCan('edit')) {
			$result = true;
			return false;
		}
	}
	$result = null;
	return true;
}

