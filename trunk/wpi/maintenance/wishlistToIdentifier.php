<?php
/*
Move the wishlist pages to stable identifier pages
*/

require_once("Maintenance.php");

//Get all resolved wishlist pages
$wishTitles = array();
$dbr =& wfGetDB(DB_SLAVE);
$ns = NS_WISHLIST;
$query = "SELECT page_id FROM page
		WHERE page_namespace = $ns AND page_is_redirect = 1";
$res = $dbr->query($query, __METHOD__);
while( $row = $dbr->fetchRow( $res )) {
	$wishTitles[] = Title::newFromId($row[0]);
}
$dbr->freeResult($res);

foreach($wishTitles as $wishTitle) {
	echo("Processing {$wishTitle->getFullText()}<BR>\n");

	//Find out if the resolved pathway is a redirect
	$rev = Revision::newFromTitle($wishTitle);
	$pwTitle = parseRedirect($rev->getText());
	$pwTitle = Title::newFromText($pwTitle);
	echo("\tResolved pathway: {$pwTitle->getFullText()}\n<BR>");
	if($pwTitle->isRedirect()) {
		$pwRevision = Revision::newFromTitle($pwTitle);
		$newTitle = Title::newFromText(parseRedirect($pwRevision->getText()));
		echo("<B>Pathway is redirect, moving to {$newTitle->getFullText()}</B>\n<BR>");
		if($doit) {
			echo("Changing redirect...<BR>\n");

			$txt = "#REDIRECT [[{$newTitle->getFullText()}]]";
			$txt_id = $rev->getTextID();

			$dbw =& wfGetDB( DB_MASTER );
			$dbw->immediateBegin();
			$sql = "UPDATE text SET old_text='$txt' WHERE old_id = $txt_id";
			$dbw->query($sql);

			$dbw->immediateCommit();
		}
	}
}

function parseRedirect($text) {
	$match = array();
	$exists = preg_match("/\#REDIRECT\ \[\[(.+)\]\]/", $text, $match);
	$title = $match[1];
	return $title;
}
