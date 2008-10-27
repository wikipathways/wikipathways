<?php
/*
Move the pathway pages to stable identifier pages
*/

require_once("Maintenance.php");

$pathways = Pathway::getAllPathways();
shuffle($pathways);

foreach($pathways as $pathway) {
	$species = $pathway->species();
	$title = $pathway->getTitleObject();
	
	echo("Processing " . $title->getFullText() . "<BR>\n");

	if(substr($title->getDbKey(), 0, 2) == "WP") {
		echo("\tSkipping, already identifier<BR>\n");
		continue; //Already an identifier
	}
	
	$id = generateUniqueId();
	
	$newTitle = Title::newFromText($id, NS_PATHWAY);
	echo "\tNew title: {$newTitle->getFullText()}<br>\n";
	if($doit) {
		$moved = $title->moveTo($newTitle, true, "Moved to stable identifier", true);
		$moved = var_export($moved, true);
		echo("\tMoving...$moved<BR>\n");
	}
}

function generateUniqueId() {
		//Get the highest identifier
		$dbr = wfGetDB( DB_SLAVE );
		$ns = NS_PATHWAY;
		$prefix = Pathway::$ID_PREFIX;
		$query = "SELECT page_title FROM page " .
			"WHERE page_namespace =$ns " .
			"AND page_is_redirect =0 " .
			"AND page_title LIKE '{$prefix}_%' " .
			"ORDER BY length(page_title) DESC, page_title DESC " .
			"LIMIT 0 , 1 ";
		$res = $dbr->query($query);
		$row = $dbr->fetchObject( $res );
		if($row) {
			$lastid = $row->page_title;
		} else {
			$lastid = Pathway::$ID_PREFIX . "0";
		}
		$dbr->freeResult( $res );
		
		$lastidNum = substr($lastid, 2);
		$newidNum = $lastidNum + 1;
		$newid = Pathway::$ID_PREFIX . $newidNum;
		return $newid;
}

?>
