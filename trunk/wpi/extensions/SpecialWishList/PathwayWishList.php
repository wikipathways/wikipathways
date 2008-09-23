<?php

class PathwayWishList {	
	private $list_table = "wishlist";
	private $subscribe_table = "wishlist_subscribe";
	
	private $wishlist; //List of titles in domain wishlist
	private $byVotes;
	private $byDate;
	
	function __construct() {
		$this->loadWishlist();
	}
	
	/**
	 * Get the array containing all page_ids of the whishlist pages
	 */
	function getWishlist($sortKey = 'date') {
		switch($sortKey) {
			case 'votes':
				return $this->sortByVotes();
			case 'date':
				return $this->sortByDate();
			default:
				return $this->wishlist;
		}
	}
		
	private function sortByDate() {
		if(!$this->byDate) {
			$this->byDate = array_values($this->wishlist);
			usort($this->byDate, __CLASS__ . "::cmpDate");
		}
		return $this->byDate;
	}
	
	private function sortByVotes() {
		if(!$this->byVotes) {
			$this->byVotes = array_values($this->wishlist);
			usort($this->byVotes, __CLASS__ . "::cmpVotes");
		}
		return $this->byVotes;
	}
		
	static function cmpVotes($a, $b) {
		return $b->countVotes() - $a->countVotes();
	}
	
	static function cmpDate($a, $b) {
		return $b->getRequestDate() - $a->getRequestDate();
	}
	
	function addWish($name, $comments) {
		$wish = Wish::createNewWish($name, $comments);
		$this->wishlist[$wish->getRequestDate()] = $wish;
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
	        		$this->wishlist[$wish->getRequestDate()] = $wish;
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
	private $votes;
	
	function __construct($id) {
		$this->id = $id;
		$this->title = Title::newFromID($id);
		$this->article = new Article($this->title);
		$this->voteArticle = new Article($this->title->getTalkPage());
	}
	
	static function createNewWish($name, $comments) {
		if(!$name) throw new Exception("Please fill in the pathway name");

		$title = Title::newFromText($name, NS_WISHLIST);
		if(!$title->userCan('create')) {
			throw new Exception("User can not create new request, are you logged in?");
		}
		
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
		
		$wishArticle->doWatch();
		
		return new Wish($wishArticle->getID());
	}
	
	function vote($userId) {
		if(!$this->userCan('vote')) {
			throw new Exception("You have no permissions to vote");
		}
		
		//Add the user id to the talk page
		$votes = $this->getVotes();
		if(!in_array($userId, $votes)) {
			$votes[] = $userId;
			$this->saveVotes($votes);
		}
	}
	
	function unvote($userId) {
		if(!$this->userCan('unvote')) {
			throw new Exception("You have no permissions to remove a vote");
		}
		
		//Remove the user id from the talk page
		$votes = $this->getVotes();
		if(in_array($userId, $votes)) {
			$votes = array_diff($votes, array($userId));
			$this->saveVotes($votes);
		}
	}
	
	private function saveVotes($votes) {
		//Save the votes to the talk page
		$voteText = implode("\n", $votes);
		$voteText = "<!--VOTES\n{$voteText}\n-->";
		$succ =  $this->voteArticle->doEdit($voteText, "Added user vote");
		if(!succ) {
			throw new Exception("Unable to update votes for $name");
		}
		$this->votes = ''; //clear vote cache
	}
	
	function countVotes() {
		return count($this->getVotes());
	}
	
	function getVotes() {
		if(!$this->votes) {
			$content = $this->voteArticle->getContent();
			//Find the <!--VOTES\s(.*)\s--> part
			$match = preg_match('/<!--VOTES\s(.*)\s-->/s', $content, $matches);
			if($match) {
				$votes = $matches[1];
			} else {
				$votes = "";
			}
			$this->votes = $votes ? explode("\n", $votes) : array();
		}
		return $this->votes;
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
		if(!$this->userCan('delete')) {
			throw new Exception("You have no permissions to delete the item");
		}
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
		//#REDIRECT [[pagename]]
		if(!$this->userCan('resolve')) {
			throw new Exception("You have no permissions to resolve this item");
		}
		
		$this->article->doEdit("#REDIRECT [[{$pathway->getTitleObject()->getFullText()}]]",
					"Resolved wishlist item {$this->getTitle()->getText()}");
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
