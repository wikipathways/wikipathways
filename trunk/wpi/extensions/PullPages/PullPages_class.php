<?php
/**
 * @package MediaWiki
 * @subpackage SpecialPage
 */

class PullPages extends SpecialPage {
	public $puller;
	public $defaultPullPage = "MediaWiki:PullPageList";

	function __construct( $empty = null ) {
		global $wgOut, $wgRequest, $wgUser;

		if( !$wgUser->isAllowed( 'pullpages' ) ) {
			$wgOut->permissionRequired( 'pullpages' );
			return;
		}
		parent::__construct();

		if( $wgRequest->wasPosted() ) {
			$this->puller = new PagePuller( $wgRequest->get("sourceWiki"), $wgRequest->get("sourcePage") );
		}
	}

	function execute( $par ) {
		if( $this->puller ) {
			$this->showForm();
			$this->startProgress();
			foreach( $this->puller->getPageList() as $page ) {
				$this->puller->getPage( $page, array( $this, "showProgress" ) );
			}
			$this->finishProgress();
		} else {
			$this->showForm();
		}
	}

	function showForm() {
		global $wgOut, $wgScript;
		$wgOut->addWikiMsg( 'pullpage-form-start' );

		$wgOut->addHtml(
			Xml::openElement( 'form', array(
				'method' => 'post',
				'action' => $wgScript ) ) .
			'<fieldset>' .
			Xml::inputLabel( wfMsg( 'pullpage-source-wiki' ),
				'sourceWiki', 'sourceWiki', 40 ) .
			Xml::inputLabel( wfMsg( 'pullpage-source-page' ),
				'sourcePage', 'sourcePage', 20, $this->defaultPullPage ) .
			Xml::submitButton( wfMsg( 'pullpage-pull-submit' ) ) .
			'</fieldset>' .
			'</form>' );
	}

	function startProgress() {
		global $wgOut;

		$wgOut->addWikiText( "pullpage-progress-start" );
	}

	function showProgress( $pageName ) {
		global $wgOut;

		$wgOut->addHTML( wfMsg( "pullpage-progress-page", $pageName ) );
	}

	function finishProgress( $pageName ) {
		global $wgOut;

		$wgOut->addHTML( wfMsg( "pullpage-progress-end", $pageName ) );
	}

}
