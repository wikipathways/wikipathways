<?php

require_once("wpi/wpi.php");
require_once("wpi/Pathway.php");
require_once("wpi/PathwayData.php");
require_once("wpi/StatisticsCache.php");

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
	$nrPathways = StatisticsCache::howManyPathways('total');
	$output = "* There are '''{$nrPathways}''' pathways";
	$table = <<<EOD

* Number of '''pathways''' ''(and unique genes)'' per species:
{| align="center" $tableAttr
EOD;
	foreach(Pathway::getAvailableSpecies() as $species) {
		$nr = StatisticsCache::howManyPathways($species);
		$genes = StatisticsCache::howManyUniqueGenes($species);
		if ($nr > 0) {  // skip listing species with 0 pathways
			$table .= <<<EOD

|-align="left"
|$species:
|'''$nr'''
|''($genes)''
EOD;
		}
	}
	$table .= "\n|}";
	$output .= $table;
	$output .= "\n* There are '''{{NUMBEROFUSERS}}''' registered users";
	//$output .= "\n* Active user [[Special:ContributionScores|statistics]]";

	$output = $parser->recursiveTagParse( $output );
	return array( $output, 'isHTML' => true, 'noparse' => true, 'nowiki' => true );
}


function getSpecies() {
	return Pathway::getAvailableSpecies();
}

?>
