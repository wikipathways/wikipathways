<?php
class ListPrivatePathways extends SpecialPage {
	function __construct() {
		parent::__construct('ListPrivatePathways', 'list_private_pathways');
		wfLoadExtensionMessages('ListPrivatePathways');
	}

	function execute($par) {
		global $wgOut, $wgUser, $wgLang;
		
		if ( !$this->userCanExecute($wgUser) ) {
			$this->displayRestrictionError();
			return;
		}

		$this->setHeaders();
		
		$wgOut->addWikiText(wfMsg("listprivatepathways-desc"));
		$permissions = MetaTag::getTags(PermissionManager::$TAG);
		
		$table = "<TABLE class='prettytable sortable'>";
		$table .= "<TH>Pathway<TH>Read<TH>Write<TH>Manage permissions<TH>Expires";
		
		foreach($permissions as $ps) {
			$tr = "<TR>";
			$pp = unserialize($ps->getText());
			if($pp) {
				$title = Title::newFromId($pp->getPageId());
				if(!$title instanceof Title) { continue;}
				if(!Pathway::parseIdentifier($title->getText())) {
					continue;
				}
				$pathway = Pathway::newFromTitle($title);
				$tr .= "<TD><A href='{$title->getFullURL()}'>{$pathway->getName()} ({$pathway->getSpecies()})</A>";
			
				$p = $pp->getPermissions();
				$tr .= "<TD>" . self::createUserString($p['read']);
				$tr .= "<TD>" . self::createUserString($p['edit']);
				$tr .= "<TD>" . self::createUserString($p[PermissionManager::$ACTION_MANAGE]);
				$tr .= "<TD>" . $wgLang->date($pp->getExpires(), true);
				$table .= $tr;
			}
		}
		
		$table .= "</TABLE>";
		$wgOut->addHTML($table);
	}
	
	public static function createUserString($array = array()) {
		global $wgUser;
		
		$us = "";
		foreach($array as $uid) {
			$u = User::newFromId($uid);
			$us .= $wgUser->getSkin()->userLink( $uid, $u->getName() ) . "; ";
		}
		return $us;
	}
}
?>
