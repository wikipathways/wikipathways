<?php
require_once('PrivatePathways.php');

//Adds a list of private pathways for the user
//to the my contributions page.
//NOTE: This hook is customly added and doesn't exist
//in the default MW installation.
$wgHooks['SpecialContributionsAfterForm'][] = 'PrivateContributions::privateContributionsList';

class PrivateContributions {
	public static function privateContributionsList($uid) {
		global $wgOut, $wgUser, $wgLang;

		if($uid != $wgUser->getId()) {
			return true; //Only show this section if we're looking at our own contributions
		}

		self::loadMessages();

		$title = wfMsg("pcontr_title");
		$wgOut->addWikiText("==$title==");

		$table = "<TABLE class='prettytable sortable'><TH>Pathway<TH>Allowed users<TH>Expires";
		$permissions = MetaTag::getTags(PermissionManager::$TAG);
		$rows = "";
		foreach($permissions as $ps) {
			$pp = unserialize($ps->getText());
			if($pp->userCan(PermissionManager::$ACTION_MANAGE, $uid)) {
				$tr = "<TR>";
				$title = Title::newFromId($pp->getPageId());
				if (!is_object($title))
					continue;
				if(!Pathway::parseIdentifier($title->getText())) 
					continue;
				$pathway = Pathway::newFromTitle($title);
				$tr .= "<TD><A href='{$title->getFullURL()}'>{$pathway->getName()} ({$pathway->getSpecies()})</A>";

				$p = $pp->getPermissions();
				$tr .= "<TD>" . ListPrivatePathways::createUserString($p['read']);
				$tr .= "<TD>" . $wgLang->date($pp->getExpires(), true);
				$rows .= $tr;
			}
		}
		if($rows) {
			$table .= $rows . "</TABLE>";
			$wgOut->addHTML($table);
		} else {
			$wgOut->addWikiText("<P>" . wfMsg("pcontr_empty") . "</P>");
		}

		$wgOut->addHTML("<H2>My contributions</H2>");
		return true;
	}

	static $messagesLoaded = false;

	public static function loadMessages() {
		global $wgMessageCache;
		if ( self::$messagesLoaded ) return true;
		self::$messagesLoaded = true;

		require( dirname( __FILE__ ) . '/PrivateContributions.i18n.php' );
		foreach ( $allMessages as $lang => $langMessages ) {
			$wgMessageCache->addMessages( $langMessages, $lang );
		}
		return true;
	}
}
