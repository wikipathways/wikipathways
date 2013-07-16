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

		parent::__construct('PullPages', 'pullpage');

		if( $wgRequest->wasPosted() ) {
			$this->puller = new PagePuller( $wgRequest->getVal("sourceWiki"),
				$wgRequest->getVal("sourcePage") );
		}
	}

	static function initMsg( ) {
		# Need this called in hook early on so messages load... maybe
		# a bug in old MW?
		wfLoadExtensionMessages( 'PullPages' );
	}

	function execute( $par ) {
		global $wgUser;

        if (  !$this->userCanExecute( $wgUser )  ) {
			$this->displayRestrictionError();
			return;
        }

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
		global $wgOut, $wgRequest;
		$wgOut->addWikiMsg( 'pullpage-intro' );

		$wgOut->addHtml(
			Xml::openElement( 'form', array(
				'method' => 'post',
				'action' => $wgRequest->getRequestURL() ) ) .
			'<fieldset>' .
			Xml::inputLabel( wfMsg( 'pullpage-source-wiki' ),
				'sourceWiki', 'sourceWiki', 40 ) . '<br>' .
			Xml::inputLabel( wfMsg( 'pullpage-source-page' ),
				'sourcePage', 'sourcePage', 20, $this->defaultPullPage ) .
			Xml::submitButton( wfMsg( 'pullpage-submit' ) ) .
			'</fieldset>' .
			'</form>' );
	}

	function startProgress() {
		global $wgOut;

		$wgOut->addWikiText( wfMsg( "pullpage-progress-start" ) );
	}

	function showProgress( $pageName ) {
		global $wgOut;

		$wgOut->addHTML( wfMsg( "pullpage-progress-page", $pageName ) );
	}

	function finishProgress( ) {
		global $wgOut;

		$wgOut->addHTML( wfMsg( "pullpage-progress-end" ) );
	}
}
