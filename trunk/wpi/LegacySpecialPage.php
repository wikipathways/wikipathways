<?php

class LegacySpecialPage extends SpecialPage {

	protected $legacyDestination = null;

	function __construct( $oldName, $newName ) {
		parent::__construct( $oldName );
		$this->legacyDestination = Title::newFromText( $newName, NS_SPECIAL );
		$this->mListed = false;

	}

	function execute( $par ) {
		global $wgRequest;

		if( !( $this->legacyDestination instanceOf Title ) ) {
			throw new MWException( "legacy-no-destination" );
		}

		if( isset( $wgRequest->data['title'] ) ) unset( $wgRequest->data['title'] );

		$query = array();
		foreach( $wgRequest->data as $k => $v) {
			$query[] = urlencode( $k ) . '=' . urlencode( $v );
		}

		$wgRequest->response()->header( "HTTP/1.1 301 Moved Permanently" );
		$wgRequest->response()->header( "Content-Type: text/html; charset=utf-8" );
		$wgRequest->response()->header( "Location: ". $this->legacyDestination->getLocalURL( implode( "&", $query ) ) );
	}
}