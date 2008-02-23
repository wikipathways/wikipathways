<?php

require_once("wpi/wpi.php");

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

function getPathwayOfTheDay( &$parser, $date ) {
	$parser->disableCache();	
	wfDebug("GETTING PATHWAY OF THE DAY for date: $date\n");
	try {
		$potd = new FeaturedPathway($date);
		$out =  $potd->getWikiOutput();
		wfDebug("END GETTING PATHWAY OF THE DAY for date: $date\n");
	} catch(Exception $e) {
		$out = "Unable to get pathway of the day: {$e->getMessage()}";
	}
	return $out;
}

/**
Featured pathway is a slight modification on PathwayOfTheDay, it does get
pathways from a limited collection, kept on the FeaturedPathway wiki page
**/
class FeaturedPathway extends PathwayOfTheDay {
	private $listPage = "FeaturedPathways";
	
	/**
	Select a random pathway from the list
	on the wiki page FeaturedPathway
	**/
	protected function fetchRandomPathway() {
		wfDebug("Fetching random pathway...\n");
		
		$listRev = Revision::newFromTitle(Title::newFromText($this->listPage), 0);
		if($listRev != null) {
			$lines = explode("\n", $listRev->getText());
		} else {
			throw new Exception("[[{$this->listPage}]] doesn't exist!");
		}
		$pathwayList = array();
		
		//Try to parse a pathway from each line
		foreach($lines as $title) {
			//Regex to fetch title from "* [[title|...]]"
			// \*\ *\[\[(.*)\]\]
			$title = preg_replace('/\*\ *\[\[(.*)\]\]/', '$1', $title);
			try {
				//If pathway creation works and the pathway exists, add to array
				$pathway = Pathway::newFromTitle($title);
				if(!is_null($pathway) && $pathway->exists()) {
					$pathwayList[] = $title;
				}
			} catch(Exception $e) {
				//Ignore the pathway
			}
		}
		if(count($pathwayList) == 0) {
			throw new Exception("{$this->listPage} doesn't contain any valid pathway!");
		}
		return $pathwayList[rand(0, count($pathwayList) - 1)];
	}
}

class PathwayOfTheDay {
	private static $table = 'pathwayOfTheDay';
	
	var $todaysPw; //Todays pathway
	var $today; //Day todaysPw was marked as today's
		
	function __construct($date) {
		PathwayOfTheDay::setupDB(); //TODO: remove this after first use
		if($date) {
			$this->today = $date;
		} else {
			$this->today = date("l j F Y");
		}
		$this->todaysPw = $this->fetchTodaysPathway();
	}
	
	public function getWikiOutput() {
		if($this->today == '{{{date}}}') {//Template variable not set, use dummy return values
			$pw = "TemplatePathway";
			$date = "TemplateDate";
		} else {
			$pw = $this->todaysPathway();
			$name = $pw->name();
			$species = $pw->species();
			$article = $pw->getTitleObject()->getFullText();
			$image = $pw->getImageTitle()->getFullText();
			$date = $this->today;
		}
		return "{{Template:TodaysPathway|pwName=$name|pwSpecies=$species|article=$article|image=$image|date=$date}}";
	}
	
	private function fetchTodaysPathway() {
		$dbr =& wfGetDB(DB_SLAVE);
		$res = $dbr->select( PathwayOfTheDay::$table, array('pathway'), array('day' => $this->today));
		$row = $dbr->fetchRow( $res );
		$dbr->freeResult( $res );
		return $row[0];
	}
	
	//Get the pathway for today
	public function todaysPathway() {
		if(!$this->todaysPw) { //No pathway in history yet
			$this->brandNewDay();
		}
		try {
			$pathway = Pathway::newFromTitle(Title::newFromText($this->todaysPw, NS_PATHWAY));
		} catch(Exception $e) {
			//Fallback to default pathway
			$pathway = Pathway::newFromTitle("Pathway:Homo sapiens:Apoptosis");
		}
		return $pathway;
	}
	
	//Create and fill the tables
	private static function setupDB() {
		$tbl = PathwayOfTheDay::$table;
		$dbw =& wfGetDB(DB_MASTER);
		wfDebug("\tCreating tables\n");
		$dbw->query( "CREATE TABLE IF NOT EXISTS $tbl ( pathway varchar(255), day varchar(50) )", DB_MASTER );
		#Index...doesn't work yet
		/*$dbw->query( "IF NOT EXISTS (SELECT * from SYSINDEXES
						WHERE id=object_id('$tbl') AND name='$ind_pw')
						CREATE INDEX $ind_pw on $tbl(pathway)", 
					DB_MASTER );
		$dbw->query( "IF NOT EXISTS (SELECT * from SYSINDEXES
						WHERE id=object_id('$tbl') AND name='$ind_day')
						CREATE INDEX $ind_day on $tbl(pathway)", 
					DB_MASTER );
					*/
		wfDebug("\tDone!\n");
	}
	
	//A brand new day, fetch new random patwhay that we haven't had before
	private function brandNewDay() {
		wfDebug("\tA brand new day....refreshing pathway of the day\n");
		$this->findFreshPathway();
	}
	
	private function findFreshPathway() {
		wfDebug("\tSearching for fresh pathway\n");
		$pw = $this->fetchRandomPathway();
		wfDebug("\t\tPathway in cache: '$pw'\n");
		while($this->hadBefore($pw)) {
			//Keep on searching until we found one that we haven't had before 
			$pw = $this->fetchRandomPathway();	
			wfDebug("\t\tTrying: '$pw'\n");
			$tried++;
			wfDebug("\t\t\t$tried attempt\n");
			if($tried > 100) { 
				wfDebug("\tTried too often, clearing history\n");
				//However, if we tried too often, just pick a pathway and reset the pathway list
				//TODO: 'too often' needs to be the number of pathways...
				$this->clearHistory();
				$this->clearHistory();
				$tried = 0;
			}
		}
		$this->todaysPw = $pw;
		//We found  a new pathway, now update history
		$this->updateHistory();
	}
	
	private function hadBefore($pathway) {
		wfDebug("\tDid we have $pathway before? ");
		if(!$pathway) {
			wfDebug(" we don't have a pathway\n");
			return true;
		}
		$dbr =& wfGetDB(DB_SLAVE);
		$res = $dbr->select( PathwayOfTheDay::$table, array('pathway'), array('pathway' => $pathway) );
		$row = $dbr->fetchRow( $res );
		$dbr->freeResult( $res );
		$had = $row ? true : false;
		wfDebug(" $had\n");
		return $had;
	}
	
	private function clearHistory() {
		$dbw =& wfGetDB(DB_MASTER);
		wfDebug("\tClearing history\n");
		$dbw->query( "TRUNCATE TABLE " . PathwayOfTheDay::$table, DB_MASTER );
	}
		
	private function updateHistory() {
		$dbw =& wfGetDB(DB_MASTER);
		$dbw->insert(PathwayOfTheDay::$table, array('pathway' => $this->todaysPw, 'day' => $this->today));
	}
	
	//Select a random pathway
	protected function fetchRandomPathway() {
		wfDebug("Fetching random pathway...\n");
		$dbr =& wfGetDB(DB_SLAVE);
		//Pick a random pathway from all articles in namespace NS_PATHWAY
		$res = $dbr->query(
			"SELECT page_title FROM page WHERE page_namespace = " . NS_PATHWAY .
				" AND page_is_redirect = 0 ORDER BY RAND() LIMIT 1" ,DB_SLAVE ); //RAND() only works in MySQL?
		$row = $dbr->fetchRow($res);
		wfDebug("Resulting pathway: " . $row[0] . "\n");
		return $row[0];
	}
}
?>
