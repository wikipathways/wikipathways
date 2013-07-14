<?php
/**
 * @file
 * @ingroup Pager
 */

/**
 * Iterator interface for pagers
 * @ingroup Pager
 */

/**
 * The Pager class is a core part of MediaWiki but it is tied very
 * tightly to the web interface -- see the getBody() and makeLink()
 * methods for example.
 *
 * This iterator class builds on that and allows the pagers to be used
 * in a command line script, for example.  This is useful to perform a
 * lot of work on the server that could previously only be performed
 * by bots.
 */
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
				$this->pager = eval("return $class::initPager();");
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
