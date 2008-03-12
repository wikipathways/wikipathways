<?php

require_once("wpi/wpi.php");
require_once("wpi/Pathway.php");
require_once("wpi/PathwayData.php");

/*
Statistics for main page
- how many pathways	{{PAGESINNS:NS_PATHWAY}}
- how many organisms
- how many pathways per organism
*/

#### DEFINE EXTENSION
# Define a setup function
$wgExtensionFunctions[] = 'wfSiteStats';
# Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]  = 'wfSiteStats_Magic';

function wfSiteStats() {
        global $wgParser;
        # Set a function hook associating the "example" magic word with our function
        $wgParser->setFunctionHook( 'siteStats', 'getSiteStats' );
}

function wfSiteStats_Magic( &$magicWords, $langCode ) {
        # Add the magic word
        # The first array element is case sensitive, in this case it is not case sensitive
        # All remaining elements are synonyms for our parser function
        $magicWords['siteStats'] = array( 0, 'siteStats' );
        # unless we return true, other parser functions extensions won't get loaded.
        return true;
}

function getSiteStats( &$parser, $tableAttr ) {
	$nrPathways = count(Pathway::getAllPathways());
	$output = "* There are '''{$nrPathways}''' pathways";
	$table = <<<EOD

* Number of '''pathways''' ''(and unique genes)'' per species:
{| align="center" $tableAttr
EOD;
	foreach(Pathway::getAvailableSpecies() as $species) {
		$nr = howManyPathways($species);
		$genes = howManyUniqueGenes($species);
		$table .= <<<EOD

|-align="left"
|$species:
|'''$nr'''
|''($genes)''
EOD;
	}
	$table .= "\n|}";
	$output .= $table;
	$output .= "\n* There are '''{{NUMBEROFUSERS}}''' registered users";
	//$output .= "\n* Active user [[Special:ContributionScores|statistics]]";

		return $output;
}

function howManyPathways($species) {
	$dbr =& wfGetDB(DB_SLAVE);
	//Fetch number of pathways for this species
	$species = Title::newFromText($species);
	$species = $species->getDbKey();
	$res = $dbr->query("SELECT COUNT(*) FROM page WHERE page_namespace=" . NS_PATHWAY . " AND page_title LIKE '$species%' AND page_is_redirect = 0");
	$row = $dbr->fetchRow($res);
	$dbr->freeResult($res);
	return $row[0];
}

function howManyUniqueGenes($species){
	$geneList = array();
	$all_pathways = Pathway::getAllPathways();
	foreach (array_keys($all_pathways) as $pathway) {
		$pathwaySpecies = $all_pathways[$pathway]->species();
		if ($pathwaySpecies != $species) continue;
		//$name = $all_pathways[$pathway]->getName();
                //echo "[" . $name . "]";
		try
		{
 			$xml = $all_pathways[$pathway]->getPathwayData();
			$nodes = $xml->getUniqueElements('DataNode', 'TextLabel');
			foreach ($nodes as $datanode){
				$xref = $datanode->Xref;
				if ($xref[ID] && $xref[ID] != '' && $xref[ID] != ' '){
					if ($xref[Database] == 'HUGO'
		                  	|| $xref[Database] == 'Entrez Gene'
                                  	|| $xref[Database] == 'Ensembl'
                                  	|| $xref[Database] == 'SwissProt'
                                  	|| $xref[Database] == 'UniGene'
                                  	|| $xref[Database] == 'RefSeq'
                                  	|| $xref[Database] == 'MGI'
                		  	|| $xref[Database] == 'RGD'
                		  	|| $xref[Database] == 'ZFIN'
                		  	|| $xref[Database] == 'FlyBase'
                		  	|| $xref[Database] == 'WormBase'
                		  	|| $xref[Database] == 'SGD'
                		  	){
						array_push($geneList, $xref[ID]);
					}
				}
			}
		}
		catch (Exception $e)
		{
			// we can safely ignore exceptions
			// errorneous pathways simply won't get counted
		}
	}
	$geneList = array_unique($geneList);
	return count($geneList);
}

function getSpecies() {
	return Pathway::getAvailableSpecies();
}

?>
