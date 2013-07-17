<?php
/**
 * @package MediaWiki
 * @subpackage SpecialPage
 */

class PullPages extends SpecialPage {
	public $puller;
	public $pullPage = "MediaWiki:PullPageList";

	function __construct( $empty = null ) {
		global $wgOut, $wgRequest, $wgUser;

		parent::__construct('PullPages', 'pullpage');
	}

	static function initMsg( ) {
		# Need this called in hook early on so messages load... maybe
		# a bug in old MW?
		wfLoadExtensionMessages( 'PullPages' );
	}

	public function execute( $par ) {
		global $wgUser, $wgRequest, $wgOut;

		$wgOut->setPagetitle( wfMsg( 'pullpages' ) );

		if (  !$this->userCanExecute( $wgUser )  ) {
			$this->displayRestrictionError();
			return;
		}

		$this->showForm();
		if( $wgRequest->wasPosted() ) {
			$this->processPullRequest();
		}
	}

	public function processPullRequest() {
		global $wgRequest;

		if( $wgRequest->getVal("sourceWiki", "") != "" &&
			$wgRequest->getVal("sourcePage", "") != "" ) {

			$this->puller = new PagePuller( $wgRequest->getVal("sourceWiki"),
				$wgRequest->getVal("sourcePage") );
			$this->puller->setUser( $wgRequest->getVal("sourceUser"), $wgRequest->getVal("sourcePass") );
			$this->puller->useImgAuth( $wgRequest->getVal("useImgAuth") );

			$this->pullPage = $wgRequest->getVal("sourcePage");
		} else {
			$wgOut->addWikiText( wfMsg('pullpage-no-wiki') );
		}

		if( $this->puller ) {
			$this->startProgress();
			$ret = $this->puller->getPageList();
			if( is_array( $ret ) ) {
				foreach( $ret as $page ) {
					$this->puller->getPage( $page,
						array( $this, "showProgress" ) );
				}
			} else {
				global $wgOut;
				$wgOut->addWikiMsg( 'pullpage-no-pages', $ret );
			}
			$this->finishProgress();
		}
	}

	public function showForm() {
		global $wgOut, $wgRequest;
		$wgOut->addWikiMsg( 'pullpage-intro' );

		$wgOut->addHtml(
			Xml::openElement( 'form', array(
				'method' => 'post',
				'action' => $wgRequest->getRequestURL() ) ) .
			'<fieldset>' .
			Xml::inputLabel( wfMsg( 'pullpage-source-wiki' ),
				'sourceWiki', 'sourceWiki', 40,
				$wgRequest->getVal( "sourceWiki" ) ) . '<br>' .
			Xml::inputLabel( wfMsg( 'pullpage-source-page' ),
				'sourcePage', 'sourcePage', 20, $this->pullPage ) . '<br>' .
			Xml::inputLabel( wfMsg( 'pullpage-source-user' ),
				'sourceUser', 'sourceUser', 20,
				$wgRequest->getVal( "sourceUser" ) ) . '<br>' .
			Xml::inputLabel( wfMsg( 'pullpage-source-pass' ),
				'sourcePass', 'sourcePass', 20, "",
				array('type' => 'password') ) . '<br>' .
			Xml::checkLabel( wfMsg( 'pullpage-use-imgauth' ),
				'useImgAuth', 'useImgAuth',
				$wgRequest->getVal( "useImgAuth" ) ) . '<br>' .
			Xml::submitButton( wfMsg( 'pullpage-submit' ) ) .
			'</fieldset>' .
			'</form>' );
	}

	public function startProgress() {
		global $wgOut;

		$wgOut->addWikiText( wfMsg( "pullpage-progress-start" ) );
	}

	public function showProgress( $pageName, $status ) {
		global $wgOut;

		if( $status->isGood() ) {
			$wgOut->addWikiText( wfMsg( "pullpage-progress-page-good",
					$pageName ) );
		} else {
			$wgOut->addWikiText( wfMsg( "pullpage-progress-page-error",
					$pageName, $status->getWikiText() ) );
		}
	}

	public function finishProgress( ) {
		global $wgOut;

		$wgOut->addWikiText( wfMsg( "pullpage-progress-end", $this->pullPage,
				sprintf( "%0.2f", memory_get_usage() / 1024 / 1024 ),
				sprintf( "%0.2f", memory_get_peak_usage() / 1024 / 1024 ) ) );
	}
}
