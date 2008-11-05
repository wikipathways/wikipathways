<?php
class SpecialCurationTags extends SpecialPage {
	function SpecialCurationTags() {
		SpecialPage::SpecialPage("SpecialCurationTags");
		self::loadMessages();
	}

	private $tagNames;
	
	function execute($par) {
		global $wgOut, $wgUser, $wgLang;
		$url = SITE_URL . '/index.php?title=Special:SpecialCurationTags';
		$this->setHeaders();
		
		if($tagName = $_REQUEST['showPathwaysFor']) {
			$disp = htmlentities(CurationTag::getDisplayName($tagName));
			$pages = CurationTag::getPagesForTag($tagName);
			
			$nr = count($pages);
			$wgOut->addWikiText(
				"The table below shows all $nr pathways that are tagged with curation tag: " .
				"'''$disp'''. "
			);
			$wgOut->addHTML("<p><a href='$url'>back</a></p>");
			$wgOut->addHTML("<table class='prettytable sortable'><tbody>");
			$wgOut->addHTML("<th>Pathway name<th>Organism<th>Created by<th><th>Last modified by<th>");
			
			foreach($pages as $pageId) {
				try {
					$t = Title::newFromId($pageId);
					if($t->getNamespace() == NS_PATHWAY) {
						$p = Pathway::newFromTitle($t);
						if($p->isDeleted()) continue; //Skip deleted pathways
						
						$wgOut->addHTML(
							"<tr><td><a href='{$p->getFullUrl()}'>{$p->name()}</a><td>{$p->species()}"
						);
						$tag = new MetaTag($tagName, $pageId);
						$ucreate = User::newFromId($tag->getUserAdd());
						$tcreate = $wgLang->timeanddate( $tag->getTimeAdd(), true );
						$umod = User::newFromId($tag->getUserMod());
						$tmod = $wgLang->timeanddate( $tag->getTimeMod(), true );
						$lcreate = $wgUser->getSkin()->userLink( $ucreate->getId(), $ucreate->getName() );
						$lmod = $wgUser->getSkin()->userLink( $umod->getId(), $umod->getName() );
						$wgOut->addHTML("<td>$lcreate<td>$tcreate<td>$lmod<td>$tmod");
					}
				} catch(Exception $e) {
					wfDebug("SpecialCurationTags: unable to create pathway object for page " . $pageId);
				}
			}
		} else {
			$wgOut->addWikiText("This page lists all available curation tags. " .
				"See the [[Help:CurationTags|help page]] for instructions on how to use curation tags.");
			$wgOut->addHTML("<table class='prettytable sortable'><tbody>");
			$wgOut->addHTML("<th>Name<th>Template<th>Description");
			$this->tagNames = CurationTag::getTagNames();
			foreach($this->tagNames as $tagName) {
				$tmp = htmlentities("Template:$tagName");
				$disp = htmlentities(CurationTag::getDisplayName($tagName));
				$descr = htmlentities(CurationTag::getDescription($tagName));
				$wgOut->addHTML("<tr><td>$disp<td>");
				$wgOut->addWikiText("[[$tmp|$tagName]]");
				$wgOut->addHTML("<td>$descr");
				$urlName = htmlentities($tagName);
				$wgOut->addHTML("<td><a href='$url&showPathwaysFor=$urlName'>Show pathways</a>");
			}
		}
		$wgOut->addHTML("</tbody></table>");
	}

	function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ( $messagesLoaded ) return true;
		$messagesLoaded = true;

		require( dirname( __FILE__ ) . '/SpecialCurationTags.i18n.php' );
		foreach ( $allMessages as $lang => $langMessages ) {
			$wgMessageCache->addMessages( $langMessages, $lang );
		}
		return true;
	}
}
?>
