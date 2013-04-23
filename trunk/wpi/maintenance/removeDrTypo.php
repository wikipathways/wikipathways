<?php

require_once("Maintenance.php");

//Removes all pathways having the Danio Rero typo
$dbr =& wfGetDB(DB_SLAVE);
$ns = NS_PATHWAY;
$query = "SELECT page_title FROM page
						WHERE page_namespace = $ns
						AND page_title LIKE 'Danio_rero%'";

echo $query . "<BR>\n";

$res = $dbr->query($query);

$np = $dbr->numRows( $res );
echo 'nrow: ' . $np . '<br>';
$i = 0;
while( $row = $dbr->fetchRow( $res )) {
	$title = $row[0];
	$pathway = Pathway::newFromTitle($title);
	echo("Removing {$pathway->getTitleObject()->getFullText()}<br>\n");

	if($doit) {
		$pathway->delete();
	}
}
