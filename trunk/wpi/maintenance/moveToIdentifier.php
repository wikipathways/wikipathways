<?php
/*
Move the pathway pages to stable identifier pages
*/

require_once("Maintenance.php");

$spName2Code = array(
	'Homo sapiens' => 'Hs',
	'Rattus norvegicus' => 'Rn',
	'Mus musculus' => 'Mm',
	'Drosophila melanogaster' => 'Dm',
	'Caenorhabditis elegans' => 'Ce',
	'Saccharomyces cerevisiae' => 'Sc',
	'Danio rerio' => 'Dr',
);

$pathways = array();
$dbr =& wfGetDB(DB_SLAVE);
$ns = NS_PATHWAY;
$query = "SELECT page_title FROM page
		WHERE page_namespace = $ns AND page_is_redirect = 0";
$res = $dbr->query($query, __METHOD__);
while( $row = $dbr->fetchRow( $res )) {
	$pathways[] = $row[0];
}
$dbr->freeResult($res);

shuffle($pathways);

foreach($pathways as $page_title) {
	$title = Title::newFromText($page_title, NS_PATHWAY);
	$talk = Title::newFromText($page_title, NS_PATHWAY_TALK);

	echo("Processing " . $title->getFullText() . "<BR>\n");

	if($title->isDeleted()) {
		//Hack to make sure the pathway was deleted
		//This is needed for pathways that were undeleted
		//(MW keeps them marked as deleted)
		$rev = Revision::newFromTitle($title);
		if(!$rev || !$rev->getText()) {
			echo("\tSkipping, title was deleted<BR>\n");
			continue; //Deleted
		}
	}
	if(substr($title->getDbKey(), 0, 2) == "WP") {
		echo("\tSkipping, already identifier<BR>\n");
		continue; //Already an identifier
	}

	$id = generateUniqueId();

	$newTitle = Title::newFromText($id, NS_PATHWAY);
	$newTalk = Title::newFromText($id, NS_PATHWAY_TALK);
	echo "\tNew title: {$newTitle->getFullText()}<br>\n";
	echo "\tNew talk: {$newTalk->getFullText()}<br>\n";
	if($doit) {
		$moved = $title->moveTo($newTitle, true, "Moved to stable identifier", true);
		echo("\Moved...". var_dump($moved, true) . "<BR>\n");
		if($talk->exists()) {
			$moved = $talk->moveTo($newTalk, true, "Moved to stable identifier", true);
			echo("\Moved talk...". var_dump($moved, true) . "<BR>\n");
		}
	}
	echo("\tRemoving cache...<BR>\n");
	$name = nameFromTitle($page_title);
	$species = speciesFromTitle($page_title);
	$species = $spName2Code[$species];

	$imgTitle = Title::newFromText($species . "_" . $name . ".svg", NS_IMAGE);
	echo($imgTitle->getFullText() . ": " . $imgTitle->exists() . "<BR>\n");

	if($doit) {
		//Remove svg cache
		Pathway::deleteArticle($imgTitle, "removed old cache");
		$img = new Image($imgTitle);
		$img->delete("removed old cache");
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


function nameFromTitle($title) {
	$parts = explode(':', $title);

	if(count($parts) < 2) {
		throw new Exception("Invalid pathway article title: $title");
	}
	return array_pop($parts);
}

function speciesFromTitle($title) {
	$parts = explode(':', $title);

	if(count($parts) < 2) {
		throw new Exception("Invalid pathway article title: $title");
	}
	$species = array_slice($parts, -2, 1);
	$species = array_pop($species);
	$species = str_replace('_', ' ', $species);
	return $species;
}
