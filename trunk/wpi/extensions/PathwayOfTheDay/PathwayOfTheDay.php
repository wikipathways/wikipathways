<?php

require_once("$IP/wpi/wpi.php");

/*
Pathway of the day generator

We need:
	- a randomized list of all pathways
	- remove pathway that is used
	- randomize again when we're at the end!
	- update list when new pathways are added....randomize every time (but exclude those we've already had)

Concerning MediaWiki:
	- create a new SpecialPage: Special:PathwayOfTheDay
	- create an extension that implements above in php

We need:
	- to pick a random pathway everyday (from all articles in namespace pathway)
	- remember this pathway and the day it was picked, store that in cache
	- on a new day, pick a new pathway, replace cache and update history
*/

#### DEFINE EXTENSION
# Define a setup function
$wgExtensionFunctions[] = 'wfPathwayOfTheDay';
# Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]  = 'wfPathwayOfTheDay_Magic';

function wfPathwayOfTheDay() {
		global $wgParser;
		# Set a function hook associating the "example" magic word with our function
		$wgParser->setFunctionHook( 'pathwayOfTheDay', 'getPathwayOfTheDay' );
}

function wfPathwayOfTheDay_Magic( &$magicWords, $langCode ) {
		# Add the magic word
		# The first array element is case sensitive, in this case it is not case sensitive
		# All remaining elements are synonyms for our parser function
		$magicWords['pathwayOfTheDay'] = array( 0, 'pathwayOfTheDay' );
		# unless we return true, other parser functions extensions won't get loaded.
		return true;
}

function getPathwayOfTheDay( &$parser, $date, $listpage = 'FeaturedPathways', $isTag = false) {
	$parser->disableCache();
	wfDebug("GETTING PATHWAY OF THE DAY for date: $date\n");
	try {
		if($isTag) {
			$potd = new TaggedPathway($listpage, $date, $listpage);
		} else {
			$potd = new FeaturedPathway($listpage, $date, $listpage);
		}
		$out =  $potd->getWikiOutput();
		wfDebug("END GETTING PATHWAY OF THE DAY for date: $date\n");
	} catch(Exception $e) {
		$out = "Unable to get pathway of the day: {$e->getMessage()}";
		wfDebug("Couldn't make pathway of the day: {$e->getMessage()}");
	}
	$out = $parser->recursiveTagParse( $out );
	return array( $out, 'isHTML' => true, 'noparse' => true, 'nowiki' => true );
}

$wgAutoloadClasses['TaggedPathway'] = dirname( __FILE__ ) . "/TaggedPathway.php";
$wgAutoloadClasses['FeaturedPathway'] = dirname( __FILE__ ) . "/FeaturedPathway.php";
$wgAutoloadClasses['PathwayOfTheDay'] = dirname( __FILE__ ) . "/PathwayOfTheDay_class.php";
