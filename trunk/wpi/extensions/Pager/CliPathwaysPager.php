<?php

/**
 *  So you have a pager class... that could easily be used as an iterator
 */

class CliPathwaysPager extends BasePathwaysPager {

	function nextPager( $offset ) {
		self::$myOffset = $offset;
		self::$myLimit = 50;
		self::$myBackwards = false;
		self::$myOrder = null;

		return new self( );
	}

	static function initPager() {
		self::$myOffset = null;
		self::$myLimit = 50;
		self::$myBackwards = false;
		self::$myOrder = null;

		return new self( );
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
		throw new MWException( "You shouldn't see this!" );
	}

	function getKey( $res ) {
		return $res->page_title;
	}

	function getValue( $res ) {
		return $res->tag_text;
	}

}
