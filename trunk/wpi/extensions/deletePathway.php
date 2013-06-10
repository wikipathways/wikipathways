<?php
require_once('wpi/wpi.php');

/**
 * Disables deletion for pathway pages.
 * TODO: Disable this hook for running script to transfer to stable ids
 */
/* $wgHooks['ArticleDelete'][] = 'checkForPathway';

function checkForPathway(&$article, &$user, &$reason, $error = '') {
	if($article && !$error && $article->getTitle()->getNamespace() == NS_PATHWAY) {
		//Prevent pathway page deletion, mark deleted instead of removing the page
		$pathway = Pathway::newFromTitle($article->getTitle());
		$pathway->delete($reason);
		return false;
	}
	return true;
}

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

/**
 * Removes deletion tab if needed
 */
/* $wgHooks['SkinTemplateContentActions'][] = 'deleteTab'; */

/* function deleteTab(&$content_actions) { */
/* 	global $wgTitle; */
/* 	$pathway = null; */

/* 	if($wgTitle->getNamespace() == NS_PATHWAY) { */
/* 		$pathway = Pathway::newFromTitle($wgTitle); */
/* 	} */

/* 	//Modify delete tab to use custom deletion for pathways */
/* 	if($pathway && $wgTitle->userCan('delete')) { */
/* 		if($pathway->isDeleted()) { */
/* 			//Remove delete tab if already deleted */
/* 			unset($content_actions['delete']); */
/* 		} */
/* 	} */
/* 	return true; */
/* } */
