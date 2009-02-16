<?php
require_once("wpi.php");
require_once("Pathway.php");
require_once("PathwayData.php");

error_reporting(0);

//Get species restriction
$restrictSpecies = $_REQUEST['species'];

//Get output format
$outputFormat = $_REQUEST['output'];
if(!$outputFormat){
	$outputFormat = 'tab'; //set default
}

//The displayed systems
$displaySystems = array(
	"Entrez Gene",
	"Ensembl",
	"SwissProt",
	"UniGene",
	"RefSeq",
	"MOD",
	"PubChem",
	"CAS",
	"ChEBI",
);

// Print header
//NOTE: Model Organism Databases = HUGO, MGI, RGD, ZFIN, FlyBase, WormBase, SGD
if ($outputFormat =='html'){
	$sysCols = '';
	foreach($displaySystems as $s) {
		$sysCols .= "<TD>$s</TD>";
	}
	
	print "<html><table border=1 cellpadding=3>
	<tr bgcolor=\"#CCCCFF\" font><td>Pathway Name</td><td>Organism</td><td>Gene Ontology</td><td>Url to WikiPathways</td><td>Last Changed</td><td>Last Revision</td><td>Author</td><td>Count</td>$sysCols</tr>\n";
	
} elseif ($outputFormat == 'excel'){
	//TODO (see Pear module for spreadsheet writer)
	print "Not available yet...\n";
} else {
	$sysCols = '';
	foreach($displaySystems as $s) {
		$sysCols .= "\t$s";
	}
	//set mime type and prompt for download
	$filename = "wikipathways_data_" . date('Ymd') . ".tab";
	header("Content-type: text/plain");
	header("Content-Disposition: attachment; filename=\"$filename\"");
	//print header
	print "Pathway Name\tOrganism\tGene Ontology\tUrl to WikiPathways\tLast Changed\tLast Revision\tAuthor\tCount$sysCols\n";
} 

$taggedIds = CurationTag::getPagesForTag('Curation:Tutorial');

$all_pathways = Pathway::getAllPathways();

//Stores looked up user names (key is user id)
$users = array();

foreach ($all_pathways as $pathway) {
	//Apply species restriction if necessary
	$species = $pathway->getSpecies();
	if($restrictSpecies) {
		if ($species != $restrictSpecies) continue; 	
	}

	//Exclude tutorial pathways
	$page_id = $pathway->getPageIdDB();
	if (in_array($page_id, $taggedIds)) continue;
	
	//Exclude deleted and private pathways
	if($pathway->isDeleted() || !$pathway->isPublic()) continue;
	
	try {
		$modTime = $pathway->getGpmlModificationTime();
		$url = $pathway->getFullUrl();
		$name = $pathway->getName();
		$authorIds = MwUtils::getAuthors($pathway->getTitleObject()->getArticleID());
		$authors = array();
		foreach($authorIds as $id) {
			$name = $users[$id];
			if(!$name) {
				$name = User::newFromId($id)->getName();
				$users[$id] = $name;
			}
			$authors[] = $name;
		}
		$author = implode(', ', $authors);
		$lastRevision = $pathway->getLatestRevision();

		$catArray = implode(", ", $pathway->getCategoryHandler()->getCategories());
		perlChop($categories);

		// Print pathways data
		if ($outputFormat =='html'){
			print "<tr><td>".$name."</td><td>".$species."</td><td>".$categories."&nbsp</td><td>".$url."</td><td>".$modTime."</td><td>".$lastRevision."</td><td>".$author."&nbsp</td><td>";
		}
		elseif ($outputFormat == 'excel'){
			//TODO
		}
		else {
			print $name."\t".$species."\t".$categories."\t".$url."\t".$modTime."\t".$lastRevision."\t".$author."\t";
		}

		$uniqueXrefs = $pathway->getUniqueXrefs();
		$count = 0;
		$xrefList = array();
		
		foreach($uniqueXrefs as $xref) {
			$xrefList[$xref->getSystem()] .= $xref->getId() . ',';
			$count++;
		}
		//Generate the MOD list
		$modSystems = array(
			'HUGO',
			'MGI',
			'RGD',
			'ZFIN',
			'FlyBase',
			'WormBase',
			'SGD',
		);
		foreach(array_keys($xrefList) as $system) {
			if(in_array($system, $modSystems)) {
				$xrefList['MOD'] .= $xrefList[$system];
			}
		}
		array_walk($xrefList, 'perlChop');
		
		//Print gene content data
		if ($outputFormat =='html') {
			print $count;
			foreach($displaySystems as $s) {
				//append with space character toprovide for empty cells in html table 
				print "<TD>{$xrefList[$s]}&nbsp</TD>";
			}
			print "</TR>";
		} elseif ($outputFormat == 'excel'){
			//TODO
		} else {
			print $count;
			foreach($displaySystems as $s) {
				//append with space character toprovide for empty cells in html table 
				print "\t{$xrefList[$s]}";
			}
			print "\n";
		}

	} catch (Exception $e) {
	 	// we can safely ignore exceptions
	 	// erroneous pathways simply won't get processed
	}
}

//Print footer
if ($outputFormat =='html'){
	print "</table></html>";
} elseif ($outputFormat == 'excel'){
	//TODO
} else {

}

function perlChop(&$string){
	$endchar = substr("$string", strlen("$string") - 1, 1);
	$string = substr("$string", 0, -1); 
	return $endchar;
}

?>
