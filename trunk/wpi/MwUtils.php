<?php

/**
 * Set of utility functions to handle MediaWiki data.
 */
class MwUtils {
	/**
	 * Find out if the given user is the only author of the page
	 * @param $user The user or user id
	 * @param $pageId The article id
	 */
	public static function isOnlyAuthor($user, $pageId) {
		$userId = $user;
		if($user instanceof User) {
			$userId = $user->getId();
		}
		foreach(self::getAuthors($pageId) as $author) {
			if($userId != $author) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Get all authors for a page
	 * @param $pageId The article id
	 * @return An array with the user ids of the authors
	 */
	public static function getAuthors($pageId) {
		$users = array();
		$dbr = wfGetDB( DB_SLAVE );
		$query = "SELECT DISTINCT(rev_user) FROM revision WHERE " .
			"rev_page = {$pageId}";
		$res = $dbr->query($query);
		while($row = $dbr->fetchObject( $res )) {
			$users[] = $row->rev_user;
		}
		$dbr->freeResult($res);
		return $users;
	}
	
	/**
	 * Get the timestamp of the latest edit
	 */
	public static function getLatestTimestamp($namespace = '') {
		$revision = Revision::newFromId(self::getLatestRevision($namespace));
		return $revision->getTimestamp();
	}
	
	/**
	 * Get the latest revision for all pages.
	 * @param $namespace Only include pages for the given namespace
	 */
	public static function getLatestRevision($namespace = '') {
		$dbr = wfGetDB( DB_SLAVE );
		$ns = array();
		if($namespace) {
			$ns['page_namespace'] = $namespace;
		}
		$res = $dbr->select("page", "MAX(page_latest) as latest", $ns);
		$row = $dbr->fetchObject($res);
		$rev = $row->latest;
		$dbr->freeResult($res);
		return $rev;
	}
}
?>
