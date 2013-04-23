<?php

require_once("Maintenance.php");

## Removes all pathways in the Pw_old namespace (100);

$dbr =& wfGetDB(DB_SLAVE);
$res = $dbr->select( "page", array("page_title"), array("page_namespace" => 100));
$np = $dbr->numRows( $res );
echo 'nrow: ' . $np . '<br>';
$i = 0;
while( $row = $dbr->fetchRow( $res )) {
	$title = $row[0];
	$title = Title::newFromText($title, 100);
	echo("Removing {$title->getFullText()}<br>\n");

	if($doit) {
		Pathway::deleteArticle($title, "cleaning up old pathway pages");
	}
}
