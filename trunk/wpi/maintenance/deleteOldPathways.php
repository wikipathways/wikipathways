<?php

/**
 * Deletes a batch of pages
 * Usage: php deleteBatch.php [-u <user>] [-r <reason>] [-i <interval>] <listfile>
 * where
 * 	<listfile> is a file where each line contains the title of a page to be deleted.
 *	<user> is the username
 *	<reason> is the delete reason
 *	<interval> is the number of seconds to sleep for after each delete
 *
 * @file
 * @ingroup Maintenance
 */

if( !isset($IP) ) $IP = dirname( dirname( dirname( __FILE__ ) ) );
require_once( "$IP/maintenance/commandLine.inc" );
require_once( "$IP/wpi/extensions/BrowsePathways/BrowsePathways_body.php");

# Setup complete, now start

class CliPathwaysPager extends BasePathwaysPager {

	function nextPager( $offset ) {
		self::$myOffset = $offset;
		self::$myLimit = 50;
		self::$myBackwards = false;
		self::$myOrder = null;

		return new self( '---', '---' );
	}

	static function initPager() {
		self::$myOffset = null;
		self::$myLimit = 50;
		self::$myBackwards = false;
		self::$myOrder = null;

		return new self( '---', '---' );
	}

	// set these directly for now.
	static $myOffset;
	static $myLimit;
	static $myBackwards;
	static $myOrder;

	public function getOffset() {
		return self::$myOffset;
	}
	public function getLimit() {
		return self::$myLimit;
	}
	public function isBackwards() {
		return self::$myBackwards;
	}
	public function getOrder() {
		return self::$myOrder;
	}
	public function formatRow( $row ) {
		var_dump( $row );
		echo "\n\nYou shouldn't see this!\n";
		exit;
	}

	function getKey( $res ) {
		return $res->page_title;
	}

	function getValue( $res ) {
		return $res->tag_text;
	}

}


class PagerIterator implements Iterator {
	protected $pager;
	protected $pagerClass;
	protected $offset;
	protected $rowsInQuery;
	protected $nextQueryOffset;
	protected $current;

	function __construct( $pagerClass ) {
		if( !class_exists( $pagerClass ) ) {
			throw new Exception( "Given pager class ($pagerClass) doesn't exist!" );
		}
		$this->pagerClass = $pagerClass;
	}

	function rewind() {
		$this->nextQueryOffset = null;
		return true;
	}

	function valid() {
		if( $this->nextQueryOffset === null )
			$this->next();

		return $this->current !== null;
	}

	function current() {
		if( $this->current )
			return $this->pager->getValue( $this->current );
		return null;
	}

	function key() {
		if( $this->current )
			return $this->pager->getKey( $this->current );
		return null;
	}

	function next() {

		if( $this->nextQueryOffset === false &&
			$this->offset == $this->rowsInQuery - 1 ) {
			$this->current = null;
			return false;
		} elseif( $this->nextQueryOffset !== false &&
			$this->offset >= $this->rowsInQuery ) {

			if( $this->pager ) {
				$this->pager = $this->pager->nextPager($this->nextQueryOffset );
				if( !$this->pager ) {
					return $this->pager;
				}
			} else {
				$class = $this->pagerClass;
				$this->pager = $class::initPager();
			}
			$this->offset = 0;
			$this->pager->doQuery();

			$res = $this->pager->mResult;
			if( $res->numRows() > $this->pager->mLimit ) {
				$res->seek( $res->numRows() - 1 );
				$this->nextQueryOffset = $this->pager->getValue( $res->fetchObject() );
			} else {
				$this->nextQueryOffset = false;
			}
			$this->rowsInQuery = min( $this->pager->mResult->numRows(),
				$this->pager->mLimit );
		} else {
			$this->offset++;
		}

		$this->pager->mResult->seek( $this->offset );
		$this->current = $this->pager->mResult->fetchObject();
	}
}

function needsDeletion( $pathway ) {
	$title = Title::newFromText( $pathway, NS_PATHWAY );
	$page = Revision::loadFromTitle( wfGetDB(), $title );
	if( $page && substr( $page->getText(), 0, 10 ) === "{{deleted|" ) {
		return $title;
	}
}

function main()  {
	$pager = new PagerIterator( 'CliPathwaysPager' );

	foreach( $pager as $k => $v ) {
		echo "$k\n";
		if( $title = needsDeletion( $k ) ){
			echo "Deleting $k\n";

			$article = new Article( $title );
			$article->doDeleteArticle( "bulk delete of ".
				"marked-deleted Pathways" );
		}
	}
}

main();