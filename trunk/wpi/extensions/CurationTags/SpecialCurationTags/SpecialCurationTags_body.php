<?php
class SpecialCurationTags extends SpecialPage {
	function __construct() {
		parent::__construct("SpecialCurationTags");
		self::loadMessages();
	}

	private $tagNames;

	function execute($par) {
		global $wgOut, $wgUser, $wgLang, $wgRequest;
		$url = SITE_URL . '/index.php?title=Special:SpecialCurationTags';
		$this->setHeaders();

		if($tagName = $wgRequest->getVal( 'showPathwaysFor' ) ) {
			$disp = htmlentities(CurationTag::getDisplayName($tagName));
			$wgOut->setPageTitle( wfMsgExt( 'curation-tag-show', array( 'parsemsg' ), $disp ) );
			$def = CurationTag::getTagDefinition();
			$useRev = $def->xpath('Tag[@name="' . $tagName . '"]/@useRevision');

			$pages = CurationTag::getPagesForTag($tagName);
			$nr = 0;
			$table = "";
			foreach($pages as $pageId) {
				try {
					$t = Title::newFromId($pageId);
					if($t->getNamespace() == NS_PATHWAY) {
						$p = Pathway::newFromTitle($t);
						if($p->isDeleted()) continue; //Skip deleted pathways

						$nr = $nr + 1;

						$table .= "<tr><td><a href='{$p->getFullUrl()}'>{$p->name()}</a>".
													"<td>{$p->species()}";
						$tag = new MetaTag($tagName, $pageId);
						$umod = User::newFromId($tag->getUserMod());
						$tmod = "<i style='display: none'>{$tag->getTimeMod()}</i>".
							$wgLang->timeanddate( $tag->getTimeMod(), true );
						$lmod = $wgUser->getSkin()->userLink( $umod->getId(), $umod->getName() );

						if($useRev) {
							$latest = "<td>";
							if( $p->getLatestRevision() == $tag->getPageRevision() ) {
								$latest .= "<font color='green'>yes</font>";
							} else {
								$ts = $p->getFirstRevisionAfterRev( $tag->getPageRevision() )->getTimestamp();
								$latest .= "<font color='red'><i style='display:none'>$ts</i>".
									$wgLang->timeAndDate( $ts ) ."</font>";
							}
						}
						$table .= "<td>$lmod<td>$tmod$latest";
					}
				} catch(Exception $e) {
					wfDebug("SpecialCurationTags: unable to create pathway object for page " . $pageId);
				}
			}

			$wgOut->addWikiText(
				"The table below shows all $nr pathways that are tagged with curation tag: " .
				"'''$disp'''. "
			);
			$wgOut->addHTML("<p><a href='$url'>back</a></p>");
			$wgOut->addHTML("<table class='prettytable sortable'><tbody>");
			$wgOut->addHTML("<tr><th>Pathway name<th>Organism<th>Tagged by<th>Date tagged");
			if($useRev) {
				$wgOut->addHTML("<th>Applies to latest revision");
			}
			$wgOut->addHTML($table);
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
