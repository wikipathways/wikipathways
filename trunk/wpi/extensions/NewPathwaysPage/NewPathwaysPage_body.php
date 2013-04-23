<?php
require_once("QueryPage.php");

class NewPathwaysPage extends SpecialPage {
	function NewPathwaysPage() {
		SpecialPage::SpecialPage("NewPathwaysPage");
		self::loadMessages();
	}

	function execute( $par ) {
		global $wgRequest, $wgOut;

		$this->setHeaders();

		list( $limit, $offset ) = wfCheckLimits();

		$rcp = new RCQueryPage();

		return $rcp->doQuery( $offset, $limit );
	}

	static function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ( $messagesLoaded ) return true;
		$messagesLoaded = true;

		require( dirname( __FILE__ ) . '/NewPathwaysPage.i18n.php' );
		foreach ( $allMessages as $lang => $langMessages ) {
			$wgMessageCache->addMessages( $langMessages, $lang );
		}
		return true;
	}
}

class RCQueryPage extends QueryPage {

	function getName() {
		return "NewPathwaysPage";
	}

	function isExpensive() {
		# page_counter is not indexed
		return true;
	}
	function isSyndicated() { return false; }

	function getSQL() {
		$dbr =& wfGetDB( DB_SLAVE );
		$page = $dbr->tableName( 'page');
		$recentchanges = $dbr->tableName( 'recentchanges');

		return
			"SELECT DISTINCT 'Newpathwaypages' as type,
					rc_namespace as namespace,
					page_title as title,
				rc_user as user_id,
				rc_user_text as utext,
				rc_timestamp as value
			FROM $page, $recentchanges
			WHERE page_title=rc_title
			AND rc_new=1
			AND rc_bot=0
			AND rc_namespace=".NS_PATHWAY." ";
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang, $wgUser;
		$titleName = $result->title;
		try {
			$pathway = Pathway::newFromTitle($result->title);
			if(!$pathway->isReadable() || $pathway->isDeleted()) {
				return ''; //Don't display this title when user is not allowed to read
			}
			$titleName = $pathway->getSpecies().":".$pathway->getName();
		} catch(Exception $e) {}
		$title = Title::makeTitle( $result->namespace, $titleName );
		$id = Title::makeTitle( $result->namespace, $result->title );
		$link = $skin->makeKnownLinkObj( $id, htmlspecialchars( $wgContLang->convert( $title->getBaseText() ) ) );
		$nv = "<b>". $wgLang->date($result->value) . "</b> by <b>" . $wgUser->getSkin()->userlink($result->user_id, $result->utext) ."</b>";
		return wfSpecialList($link, $nv);
	}
}

