<?php
class ShowError extends SpecialPage {
	function ShowError() {
		SpecialPage::SpecialPage("ShowError");
		self::loadMessages();
	}

	function execute($par) {
		global $wgOut, $wgUser, $wgLang;
		$this->setHeaders();
		$error = htmlentities($_REQUEST['error']);
		$wgOut->addWikiText("<pre><nowiki>$error</nowiki></pre>");
	}

	static function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ( $messagesLoaded ) return true;
		$messagesLoaded = true;

		require( dirname( __FILE__ ) . '/ShowError.i18n.php' );
		foreach ( $allMessages as $lang => $langMessages ) {
			$wgMessageCache->addMessages( $langMessages, $lang );
		}
		return true;
	}
}
