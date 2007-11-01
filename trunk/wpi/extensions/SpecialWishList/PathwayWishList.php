<?php

class PathwayWishList {	
	private $list_table = "wishlist";
	private $subscribe_table = "wishlist_subscribe";
	
	private $wishlist; //List of titles in domain wishlist
	
	function __construct() {
		$this->loadWishlist();
	}
	
	/**
	 * Get the array containing all page_ids of the whishlist pages
	 */
	function getWishlist() {
		if(!$this->wishlist) {
			$this->loadWishlist();
		}
		return $this->wishlist;
	}
	
	function addWish($name, $comments) {
		$wish = Wish::createNewWish($name, $comments);
		$this->wishlist[] = $wish;
	}
	
	/**
	 * Loads the wishlist from the database
	 */
	private function loadWishlist() {
		$this->wishlist = array();
		$dbr =& wfGetDB( DB_SLAVE );
		$res = $dbr->query(
			"SELECT page_id FROM page WHERE page_namespace = " . NS_WISHLIST
			);
		
		while ( $row = $dbr->fetchRow( $res ) ) {
			$wish =  new Wish($row[0]);
			if($wish->exists()) {
	        		$this->wishlist[] = $wish;
	        	}
		}
		$dbr->freeResult( $res );
	}
}

class Wish {
	private $id;
	private $title;
	private $article;
	private $firstRevision;
	private $voteArticle;
	
	function __construct($id) {
		$this->id = $id;
		$this->title = Title::newFromID($id);
		$this->article = new Article($this->title);
		$this->voteArticle = new Article($this->title->getTalkPage());
	}
	
	static function createNewWish($name, $comments) {
		global $wgLoadBalancer;
		
		if(!$name) throw new Exception("Please fill in the pathway name");
		
		$title = Title::newFromText($name, NS_WISHLIST);
		$wishArticle = new Article($title);		

		$succ = true;
		
		//Create the wish article, containing the comments
		$succ =  $wishArticle->doEdit($comments, "New wishlist item");
		if(!succ) {
			throw new Exception("Unable to create article $name");
		}
		//Create the talk page, containing the votes in a hidden section
		$voteArticle = new Article($title->getTalkPage());
		$succ =  $voteArticle->doEdit("<!--VOTES\n-->", "New wishlist item");
		if(!succ) {
			throw new Exception("Unable to create article $name");
		}
		//Commit the changes
		$wgLoadBalancer->commitAll();
		
		$wishArticle->doWatch();
		
		return new Wish($wishArticle->getID());		
	}
	
	function vote($userId) {
		global $wgLoadBalancer;
		
		//Add the user id to the talk page
		$votes = $this->getVotes();
		if(!in_array($userId, $votes)) {
			$votes[] = $userId;
			$this->saveVotes($votes);
		}
	}
	
	function unvote($userId) {
		global $wgLoadBalancer;
		
		//Remove the user id from the talk page
		$votes = $this->getVotes();
		if(in_array($userId, $votes)) {
			$votes = array_diff($votes, array($userId));
			$this->saveVotes($votes);
		}
	}
	
	private function saveVotes($votes) {
		global $wgLoadBalancer;
		//Save the votes to the talk page
		$voteText = implode("\n", $votes);
		$voteText = "<!--VOTES\n{$voteText}\n-->";
		$succ =  $this->voteArticle->doEdit($voteText, "Added user vote");
		if(!succ) {
			throw new Exception("Unable to update votes for $name");
		}
		$wgLoadBalancer->commitAll();
	}
	
	function countVotes() {
		return count($this->getVotes());
	}
	
	function getVotes() {
		$content = $this->voteArticle->getContent();
		//Find the <!--VOTES\s(.*)\s--> part
		$match = preg_match('/<!--VOTES\s(.*)\s-->/s', $content, $matches);
		if($match) {
			$votes = $matches[1];
		} else {
			$votes = "";
		}
		$votes = $votes ? explode("\n", $votes) : array();
		return $votes;
	}
		
	function exists() {
		return $this->title->exists();
	}
	
	function getId() {
		return $this->id;
	}
	 
	function getTitle() {
		return $this->title;
	}
	
	function getComments() {
		return $this->article->getContent();
	}
	
	function userIsWatching() {
		return $this->title->userIsWatching();
	}
	
	function watch() {
		$this->article->doWatch();
	}
	
	function unwatch() {
		$this->article->doUnwatch();
	}
	
	function remove() {
		Pathway::deleteArticle($this->title, "Removed wishlist item");
	}
	
	function isResolved() {
		return $this->article->isRedirect();
	}
	
	function getResolvedPathway() {
		if(!$this->isResolved()) {
			return false;
		}
		$title = Title::newFromRedirect($this->article->getContent());
		return Pathway::newFromTitle($title);
	}
	
	function markResolved($pathway) {
		global $wgLoadBalancer;
		//#REDIRECT [[pagename]]
		$this->article->doEdit("#REDIRECT [[{$pathway->getTitleObject()->getFullText()}]]",
					"Resolved wishlist item {$this->getTitle()->getText()}");
		$wgLoadBalancer->commitAll();		
	}
	
	private function getFirstRevision() {
		if(!$this->firstRevision) {
			$revs = Revision::fetchAllRevisions($this->getTitle());
			if($revs->numRows() > 0) {
				$revs->seek($revs->numRows() - 1);
			} else {
				return;
			}
			$row = $revs->fetchRow();
			$this->firstRevision = Revision::newFromId($row['rev_id']);
		}
		return $this->firstRevision;
	}
	
	function getRequestDate() {
		return $this->getFirstRevision()->getTimestamp();
	}
	
	function getResolvedDate() {
		if($this->isResolved()) {
			return $this->article->getTimestamp();
		}
	}
	
	function getRequestUser() {
		$rev = $this->getFirstRevision();
		return User::newFromId($rev->getUser());
	}
	
	function userCan($action) {
		global $wgUser;
		$uid = $wgUser->getId();
		
		switch($action) {
			case 'resolve':
				return $this->userCan('edit');
			case 'vote':
				return $this->userCan('edit') && 
				!in_array($uid, $this->getVotes()) && //Not allowed when already voted
				$uid != $this->getRequestUser()->getId(); //Don't vote on own request
			case 'unvote':
				return $this->userCan('edit') && in_array($uid, $this->getVotes());
			default:
				return $this->title->userCan($action) && $wgUser->isAllowed($action);
		}
	}
}
?>
