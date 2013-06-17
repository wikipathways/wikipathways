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

$oldCwd = getcwd();
require_once( 'commandLine.inc' );
require_once( "$IP/wpi/extensions/BrowsePathways/BrowsePathways_body.php");
chdir( $oldCwd );

# Setup complete, now start

class CliPathwaysPager extends BasePathwaysPager {
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
}


class PagerIterator implements Iterator {
	protected $pager;
	protected $offset;
	protected $rowsInQuery;
	protected $nextQueryOffset;
	protected $current;


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
			return $this->current->tag_text;
		return null;
	}

	function key() {
		if( $this->current )
			return $this->current->page_title;
		return null;
	}

	function next() {

		if( $this->nextQueryOffset === false &&
			$this->offset == $this->rowsInQuery - 1 ) {
			$this->current = null;
			return false;
		} elseif( $this->nextQueryOffset !== false &&
			$this->offset >= $this->rowsInQuery ) {

			if( $this->nextQueryOffset !== null )
				CliPathwaysPager::$myOffset = $this->nextQueryOffset;
			CliPathwaysPager::$myLimit = 50;
			CliPathwaysPager::$myBackwards = false;
			CliPathwaysPager::$myOrder = null;

			$this->pager = new CLIPathwaysPager( '---', 'Curation:ProposedDeletion' );
			$this->offset = 0;
			$this->pager->doQuery();

			$res = $this->pager->mResult;
			if( $res->numRows() > $this->pager->mLimit ) {
				$res->seek( $res->numRows() - 1 );
				$this->nextQueryOffset = $res->fetchObject();
				$this->nextQueryOffset = $this->nextQueryOffset->tag_text;
				# $res->seek( 0 ); don't seek back to beginning here, we'll do that below.
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
	$pager = new PagerIterator( );

	foreach( $pager as $k => $v ) {
#	foreach( array("WP686") as $k ) {
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