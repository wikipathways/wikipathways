<?php
/**
 * Calculate several statistics for WikiPathways.
 *
 * Run using PHP cli:
 *
 * php statistics.php [task]
 *
 * See $allTasks for a list of available tasks.
*/

/* Abort if called from a web server */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	print "This script must be run from the command line\n";
	exit();
}

set_time_limit(0);

/* Load MW classes */
$dir = getcwd();
chdir("../"); //Ugly, but we need to change to the MediaWiki install dir to include these files, otherwise we'll get an error
require_once('wpi.php');
chdir($dir);

/* Extract the statistics */
$basedir = dirname(__FILE__);

$startY = 2008;
$startM = 1;
$startD = 1;

$tsStart = date('YmdHis', mktime(0, 0, 0, $startM, $startD, $startY));
$tsEnd = date('YmdHis', mktime(0, 0, 0, date('m'), 1, date('Y')));

$times = WikiPathwaysStatistics::getTimeStampPerMonth($tsStart, $tsEnd);

//Available tasks
$allTasks = array(
	'pathwayCounts' => 'writePathwayCounts',
	'userCounts' => 'writeUserCounts',
	'collectionCounts' => 'writeCollectionCounts',
	'editCounts' => 'writeEditCounts',
	'userFrequencies' => 'writeUserFrequencies',
	'contentHistograms' => 'writeContentHistograms',
	'usageFrequencies' => 'writeUsageFrequencies',
	'summary' => 'writeSummary',
	'webservice' => 'writeWebserviceCounts',
	//'xrefCounts' => 'writeXrefCounts', //Disable, too slow using bridgedb WS
);

$tasks = $_SERVER['argv'];
array_shift($tasks);
if(count($tasks) == 0 || in_array('all', $tasks)) 
	$tasks = array_keys($allTasks);
foreach($tasks as $task) {
	if(!in_array($task, array_keys($allTasks))) {
		logger("ERROR: Unknown task: $task");
		logger("Please leave blank to run all tasks or choose from the following:");
		logger(implode("\n", array_keys($allTasks)));
		continue;
	}
	$f = $basedir . '/' . $task . '.txt';
	logger("Running task $task.");
	call_user_func(array('WikiPathwaysStatistics', $allTasks[$task]), $f, $times);
}

$file = '/pathway_count_species.txt';

/**
 * Implements functions to extract the statistics.
 */
class WikiPathwaysStatistics {
	static $excludeTags = array(
		"Curation:Tutorial"
	);
	
	static function writeSummary($file, $times) {
		$tsCurr = date('YmdHis');
		$date = date('Y-m-d', wfTimestamp(TS_UNIX, $tsCurr));
		
		$fout = fopen($file, 'w');
		
		//pathways
		$pwPublic = 0;
		$pwPrivate = 0;
		
		$pathways = StatPathway::getSnapshot($tsCurr);
		foreach($pathways as $p) {
			$wp = new Pathway($p->getPwId());
			if($wp->isPublic()) $pwPublic += 1;
			else $pwPrivate += 1;
		}
		
		fwrite($fout, "<p>Last update: $date</p>");
		$pws = <<<PATHWAYS
<h3>Number of pathways</h3><ul>
<li>Public pathways:<b> $pwPublic</b>
<li>Private pathways:<b> $pwPrivate</b>
</ul>
PATHWAYS;
		fwrite($fout, $pws);
	
		$uOne = 0;
		$uOneNoTest = 0;
		$uInactive = 0;
		
		$eTotal = 0;
		$eNoTest = 0;
		$eTotalBots = 0;

		$exclude = self::getExcludeByTag();
		$users = StatUser::getSnapshot($tsCurr);
			
		foreach($users as $u) {
			$edits = $u->getPageEdits($tsCurr);
			$editsNoTest = array_diff($edits, $exclude);
			
			if(count($edits) > 0) $uOne += 1;
			if(count($editsNoTest) > 0) $uOneNoTest += 1;
			if(count($edits) == 0) $uInactive += 1;
			
			$mwu = User::newFromId($u->getId());
			if($mwu->isBot()) {
				$eTotalBots += count($edits);
			} else {
				$eTotal += count($edits);
				$eNoTest += count($editsNoTest);
			}
		}
		
		$usr = <<<USERS
<h3>Number of users</h3><ul>
<li>Inactive users:<b> $uInactive</b>
<li>At least 1 edit:<b> $uOne</b>
<li>At least 1 edit (excluding test/tutorial pathways):<b> $uOneNoTest</b>
</ul>
USERS;
		$edt = <<<EDITS
<h3>Number of edits</h3><ul>
<li>User edits:<b> $eTotal</b>
<li>User edits (excluding test/tutorial pathways):<b> $eNoTest</b>
<li>Bot edits:<b> $eTotalBots</b>
</ul>
EDITS;

		fwrite($fout, $usr);
		fwrite($fout, $edt);
		
		fclose($fout);
	}
	
	/**
	 * Number of pathways over time.
	 */
	static function writePathwayCounts($file, $times) {
		$exclude = self::getExcludeByTag();
		
		$allSpecies = array();
		$allCounts = array();

		foreach($times as $tsCurr) {
			$date = date('Y-m-d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date . ":\t", "");
			$snapshot = StatPathway::getSnapshot($tsCurr);
			logger(count($snapshot));
	
			$counts = array();
			foreach($snapshot as $p) {
				if(in_array($p->getPageId(), $exclude)) continue;
				$s = $p->getSpecies();
				$allSpecies[$s] = 1;
				if(array_key_exists($s, $counts)) {
					$counts[$s] = $counts[$s] + 1;
				} else {
					$counts[$s] = 1;
				}
			}
	
			foreach(array_keys($counts) as $s) {
				$c = $counts[$s];
			}
			$allCounts[$date] = $counts;
		}

		unset($allSpecies['undefined']);
		$allSpecies = array_keys($allSpecies);

		$fout = fopen($file, 'w');
		fwrite($fout, "date\t" . 
			implode("\t", array_fill(0, count($allSpecies), "number")) . "\n");
		fwrite($fout, "Time\t" . 
			implode("\t", $allSpecies) . "\n");

		rsort($allSpecies);
		foreach(array_keys($allCounts) as $date) {
			$counts = $allCounts[$date];
			$values = array();
			foreach($allSpecies as $s) {
				$v = 0;
				if(array_key_exists($s, $counts)) $v = $counts[$s];
				$values[] = $v;
			}
			fwrite($fout, $date . "\t" . implode("\t", $values) . "\n");
		}

		fclose($fout);
	}
	
	/**
	 * Edit frequencies by user rank.
	 */
	static function writeUserFrequencies($file, $times) {
		$last = array_pop($times);
		$exclude = self::getExcludeByTag();
		$users = StatUser::getSnapshot($last);
		
		$editCounts = array();
		foreach($users as $u) {
			$mwu = User::newFromId($u->getId());
			if($mwu->isBot()) continue; //Skip bots
				
			$all = $u->getPageEdits();
			$edits = array_diff($all, $exclude);
			if(count($edits) > 0) $editCounts[$u->getName()] = count($edits);
		}
		
		arsort($editCounts);
		$fout = fopen($file, 'w');
		fwrite($fout, "string\tstring\tnumber\n");
		fwrite($fout, "User\tUser rank\tNumber of edits\n");

		$i = 0;
		foreach(array_keys($editCounts) as $u) {
			$row = array(
				$u, $i, $editCounts[$u]
			);
			fwrite($fout, implode("\t", $row) . "\n");
			$i += 1;
		}

		fclose($fout);
	}

	/**
	 * Number of registered and active users over time.
	 */
	static function writeUserCounts($file, $times) {
		$registered = array();
		$everActive = array();
		$intervalActive = array();
		
		$tsPrev = array_shift($times);
		foreach($times as $tsCurr) {
			$date = date('Y-m-d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);
			
			$users = StatUser::getSnapshot($tsCurr);
			
			$everCount = 0;
			$intervalCount = 0;
			
			$minEdits = 1;
			foreach($users as $u) {
				if(count($u->getPageEdits($tsCurr)) >= $minEdits)
					$everCount++;
				if(count($u->getPageEdits($tsCurr, $tsPrev)) >= $minEdits)
					$intervalCount++;
			}
			
			$everActive[$date] = $everCount;
			$intervalActive[$date] = $intervalCount;
			$registered[$date] = count($users) - $everCount;
			
			$tsPrev = $tsCurr;
		}
		
		$fout = fopen($file, 'w');
		fwrite($fout, "date\tnumber\tnumber\tnumber\n");
		fwrite($fout, "Time\tInactive\tActive\tActive in month\n");

		foreach(array_keys($registered) as $date) {
			$row = array(
				$registered[$date], $everActive[$date], $intervalActive[$date]
			);
			fwrite($fout, $date . "\t" . implode("\t", $row) . "\n");
		}

		fclose($fout);
	}
	
	static function writeWebserviceCounts($file, $times) {
		$ownIps = array(
			'/137\.120\.14\.[0-9]{1,3}/',
			'/137\.120\.89\.38/',
			'/137\.120\.89\.24/',
			'/137\.120\.17\.25/',
			'/137\.120\.17\.35/',
			'/137\.120\.17\.33/',
			'/169\.230\.76\.87/'
		);
		
		$dates = array();
		$own = array();
		$ext = array();
		
		$tsPrev = array_shift($times);
		foreach($times as $tsCurr) {
			$date = date('Y-m-d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);
			
			$ipCounts = StatWebservice::getCountsByIp($tsPrev, $tsCurr);			
			$ownCount = 0;
			$extCount = 0;
			
			foreach(array_keys($ipCounts) as $ip) {
				$isOwn = false;
				foreach($ownIps as $r) {
					if(preg_match($r, $ip)) {
						$isOwn = true;
						break;
					}
				}
				if($isOwn) $ownCount += $ipCounts[$ip];
				else $extCount += $ipCounts[$ip];
			}
			
			$own[$date] = $ownCount;
			$ext[$date] = $extCount;
			$dates[] = $date;
			$tsPrev = $tsCurr;
		}
		
		$fout = fopen($file, 'w');
		fwrite($fout, "date\tnumber\tnumber\n");
		fwrite($fout, "Time\tExternal\tInternal\n");

		foreach($dates as $date) {
			$row = array(
				$date, $ext[$date], $own[$date]
			);
			fwrite($fout, implode("\t", $row) . "\n");
		}

		fclose($fout);
	}
	
	static function writeEditCounts($file, $times) {
		//Number of edits in month, number of total edits
		//Exclude bot edits
		//Exclude test/tutorial edits
		
		$exclude = self::getExcludeByTag();
		
		$botEdits = array();
		$testEdits = array();
		$realEdits = array();
		$botEditsInt = array();
		$testEditsInt = array();
		$realEditsInt = array();
		
		$tsPrev = array_shift($times);
		foreach($times as $tsCurr) {
			$date = date('Y-m-d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);
			
			$users = StatUser::getSnapshot($tsCurr);
			
			$botCount = $testCount = $realCount = 0;
			$botCountInt = $testCountInt = $realCountInt = 0;
			
			foreach($users as $u) {
				$mwu = User::newFromId($u->getId());
				$bot = $mwu->isBot();
				
				$edits = $u->getPageEdits($tsCurr);
				$editsInt = $u->getPageEdits($tsCurr, $tsPrev);
				
				if($bot) {
					$botCount += count($edits);
					$botCountInt += count($editsInt);
				} else {
					//Remove test edits
					$rc = array_diff($edits, $exclude);
					$rcInt = array_diff($editsInt, $exclude);
					
					$testCount += count($edits) - count($rc);
					$testCountInt += count($editsInt) - count($rcInt);
					$realCount += count($rc);
					$realCountInt += count($rcInt);
				}
			}
			
			$botEdits[$date] = $botCount;
			$botEditsInt[$date] = $botCountInt;
			$testEdits[$date] = $testCount;
			$testEditsInt[$date] = $testCountInt;
			$realEdits[$date] = $realCount;
			$realEditsInt[$date] = $realCountInt;
			
			$tsPrev = $tsCurr;
		}
		
		$fout = fopen($file, 'w');
		fwrite($fout, "date\tnumber\tnumber\tnumber\tnumber\tnumber\tnumber\n");
		fwrite($fout, "Time\tUser edits\tUser edits in month\t" .
			"Test/tutorial edits\tTest/tutorial edits in month\t" .
			"Bot edits\tBot edits in month\n");

		foreach(array_keys($realEdits) as $date) {
			$row = array(
				$realEdits[$date], $realEditsInt[$date], 
				$testEdits[$date], $testEditsInt[$date],
				$botEdits[$date], $botEditsInt[$date]
			);
			fwrite($fout, $date . "\t" . implode("\t", $row) . "\n");
		}

		fclose($fout);
	}
	
	static function writeCollectionCounts($file, $times) {
		$collectionCounts = array();
		$collections = array(
			"Curation:FeaturedPathway",
			"Curation:AnalysisCollection",
			"Curation:GenMAPP_Approved",
			"Curation:CIRM_Related",
			"Curation:Wikipedia",
			"Curation:Reactome_Approved"
		);
		
		foreach($times as $tsCurr) {
			$date = date('Y-m-d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);
			
			$snapshot = StatTag::getSnapshot($tsCurr, $collections);
			$pathways = StatPathway::getSnapshot($tsCurr);
			
			//Remove tags on deleted pages
			$existPages = array();
			foreach($pathways as $p) $existPages[] = $p->getPageId();
			$removeTags = array();
			foreach($snapshot as $t) if(!in_array($t->getPageId(), $existPages)) {
				$removeTags[] = $t;
			}
			$snapshot = array_diff($snapshot, $removeTags);
			
			$counts = array();
			foreach($collections as $c) $counts[$c] = 0;
			foreach($snapshot as $tag) {
				$type = $tag->getType();
				if(array_key_exists($type, $counts)) {
					$counts[$type] = $counts[$type] + 1;
				}
			}
			$collectionCounts[$date] = $counts;
		}
		
		$collectionNames = array();
		foreach($collections as $c) {
			$collectionNames[] = CurationTag::getDisplayName($c);
		}
		
		$fout = fopen($file, 'w');
		fwrite($fout, "date\t" . 
			implode("\t", array_fill(0, count($collections), "number")) . "\n");
		fwrite($fout, "Time\t" . 
			implode("\t", $collectionNames) . "\n");

		foreach(array_keys($collectionCounts) as $date) {
			$values = $collectionCounts[$date];
			fwrite($fout, $date . "\t" . implode("\t", $values) . "\n");
		}

		fclose($fout);
	}

	/**
	 * Frequency of number of views and number of edits.
	 */
	static function writeUsageFrequencies($file, $times) {
		$tsCurr = array_pop($times);
		
		$viewCounts = array();
		$editCounts = array();
		
		$i = 0;
		$pathways = StatPathway::getSnapshot($tsCurr);
		$total = count($pathways);
		foreach($pathways as $p) {
			if(($i % 100) == 0) logger("Processing $i out of $total");
			$i++;
			
			array_push($viewCounts, $p->getNrViews());
			array_push($editCounts, $p->getNrRevisions());
		}
		
		arsort($editCounts);
		$fout = fopen($file . ".edits", 'w');
		fwrite($fout, "string\tnumber\n");
		fwrite($fout, "Pathway rank\tNumber of edits\n");

		$i = 0;
		foreach(array_keys($editCounts) as $u) {
			$row = array(
				$i, $editCounts[$u]
			);
			fwrite($fout, implode("\t", $row) . "\n");
			$i += 1;
		}

		fclose($fout);
		
		arsort($viewCounts);
		$fout = fopen($file . ".views", 'w');
		fwrite($fout, "string\tnumber\n");
		fwrite($fout, "Pathway rank\tNumber of views\n");

		$i = 0;
		foreach(array_keys($viewCounts) as $u) {
			$row = array(
				$i, $viewCounts[$u]
			);
			fwrite($fout, implode("\t", $row) . "\n");
			$i += 1;
		}

		fclose($fout);
	}
	
	/**
	 * Histogram of several patwhay statistics:
	 * - xrefs
	 * - literature references
	 * - linked lines (interactions)
	 */
	static function writeContentHistograms($file, $times) {
			$tsCurr = array_pop($times);
			
			$xrefCounts = array();
			$litCounts = array();
			$intCounts = array();
			
			$i = 0;
			$pathways = StatPathway::getSnapshot($tsCurr);
			$total = count($pathways);
			foreach($pathways as $p) {
				if(($i % 100) == 0) logger("Processing $i out of $total");
				$i++;
				
				$wp = new Pathway($p->getPwId());
				if(!$wp->isReadable()) continue;
				
				$wp->setActiveRevision($p->getRevision());
				$data = new PathwayData($wp);
				
				$xc = count($data->getUniqueXrefs());
				$lc = count($data->getPublicationXRefs());
				$ic = count($data->getInteractions());
				array_push($xrefCounts, $xc);
				array_push($litCounts, $lc);
				array_push($intCounts, $ic);
			}
			
			$xrefHist = self::histCounts($xrefCounts);
			$litHist = self::histCounts($litCounts);
			$intHist = self::histCounts($intCounts);
			
			$fout = fopen("$file.xrefs", 'w');
			fwrite($fout, "string\tnumber\n");
			fwrite($fout, "Number of xrefs\tNumber of pathways\n");
			array_walk($xrefHist, 'WikiPathwaysStatistics::kvpaste');
			fwrite($fout, implode("\n", $xrefHist));
			fclose($fout);
			
			$fout = fopen("$file.lit", 'w');
			fwrite($fout, "string\tnumber\n");
			fwrite($fout, "Number of literature references\tNumber of pathways\n");
			array_walk($litHist, 'WikiPathwaysStatistics::kvpaste');
			fwrite($fout, implode("\n", $litHist));
			fclose($fout);
			
			$fout = fopen("$file.int", 'w');
			fwrite($fout, "string\tnumber\n");
			fwrite($fout, "Number of linked lines\tNumber of pathways\n");
			array_walk($intHist, 'WikiPathwaysStatistics::kvpaste');
			fwrite($fout, implode("\n", $intHist));
			fclose($fout);
	}
	
	static function kvpaste(&$v, $k) {
		$v = "$k\t$v";
	}
	
	static function histCounts($values, $min = -1, $max = -1, $binSize = -1) {
		if($min < 0) $min = min($values);
		if($max < 0) $max = max($values);
		if($min == $max) return array($min => count($values));
		
		$range = $max - $min;
		
		$bins = intval($range);
		
		if($binSize > 0) {
			$bins = $range / $binSize;
		}
		
		$hist = array();
		
		$delta = $range / $bins;
		$lower = $min;
		while($lower <= $max) {
			$upper = $lower + $delta;
			$middle = $lower + ($upper - $lower) / 2;
			$count = 0;
			foreach($values as $v) {
				if($v >= $lower && $v < $upper) $count += 1;
			}
			$f_middle = intval($middle);
			if($f_middle >= 1000) 
				$f_middle = self::cutzero(sprintf("%e", $f_middle));
			$f_lower = intval($lower);
			if($f_lower >= 1000) 
				$f_lower = self::cutzero(sprintf("%e", $f_lower));
			$f_upper = intval($upper);
			if($f_upper >= 1000) 
				$f_upper = self::cutzero(sprintf("%e", $f_upper));
			
			if($bins == $range) $key = $f_middle;
			else $key = "$f_lower to $f_upper";
			$hist[$key] = $count;
			$lower = $upper;
		}
		
		return $hist;
	}
	
	static function cutzero($value) { 
		return preg_replace("/(\.\d+?)0+(e.+$)/", "$1$2", $value);
	}
	
	static function writeXrefCounts($file, $times) {
		$datasources = array(
			"HMDB" => "Worm", //Use worm database for metabolites (is small, so faster)
			"Ensembl Human" => "Homo sapiens",
		);
		
		$unmappable = array(
			"HMDB" => array(
				"Entrez Gene", "Ensembl Human", "MGI", 
				"SwissProt", "Ensembl", "RefSeq", "Other",
				"UniGene", "HUGO", "", "SGD", "RGD"
			),
			"Ensembl Human" => array(
				"ChEBI", "HMDB", "PubChem", "Other",
				"CAS", ""
			),
		);
		
		$datasourceCounts = array();
		
		$mappingCache = array();
		
		foreach($times as $tsCurr) {
			$date = date('Y-m-d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);
			
			$xrefsPerSpecies = array();
			
			$pathways = StatPathway::getSnapshot($tsCurr);
			foreach($pathways as $p) {
				$species = $p->getSpecies();
				$gpml = new SimpleXMLElement($p->getGpml());
				foreach($gpml->DataNode as $dn) {
					$id = (string)$dn->Xref['ID'];
					$system = (string)$dn->Xref['Database'];
					if(!$id || !$system) continue;
					$xrefsPerSpecies[$species][] = "$id||$system";
				}
			}
			foreach(array_keys($xrefsPerSpecies) as $s) {
				$xrefsPerSpecies[$s] = array_unique($xrefsPerSpecies[$s]);
			}
			
			$counts = array();
			foreach(array_keys($datasources) as $ds) {
				logger("Mapping $ds");
				
				if(!array_key_exists($ds, $mappingCache)) {
					$mappingCache[$ds] = array();
				}
				$myCache = $mappingCache[$ds];
				
				$mapped = array();
				$db = $datasources[$ds];
				
				$tomap = array();
				if(in_array($db, array_keys($xrefsPerSpecies))) {
					$tomap = $xrefsPerSpecies[$db];
				} else {
					foreach($xrefsPerSpecies as $x) $tomap += $x;
					$tomap = array_unique($tomap);
				}
				
				$i = 0;
				foreach($tomap as $x) {
					$idsys = explode('||', $x);
					$id = $idsys[0];
					$system = $idsys[1];
					if(in_array($system, $unmappable[$ds])) {
						continue;
					}
					if($system == $ds) {
						$mapped[] = $x;
						continue;
					}
					if(array_key_exists($x, $myCache)) {
						$mapped += $myCache[$x];
					} else {
						$xx = self::mapID($id, $system, $db, $ds);
						$myCache[$x] = $xx;
						$mapped += $xx;
					}
					$i += 1;
					if(($i % 10) == 0) logger("mapped $i out of " . count($tomap));
				}
				
				logger("Mapped: " . count($mapped));
				$counts[$ds] = count($mapped);
			}
			$datasourceCounts[$date] = $counts;
			logger(memory_get_usage() / 1000);
		}
		
		$fout = fopen($file, 'w');
		fwrite($fout, "date\t" . 
			implode("\t", array_fill(0, count($datasources), "number")) . "\n");
		fwrite($fout, "Time\t" . 
			implode("\t", array_keys($datasources)) . "\n");

		foreach(array_keys($datasourceCounts) as $date) {
			$values = $datasourceCounts[$date];
			fwrite($fout, $date . "\t" . implode("\t", $values) . "\n");
		}

		fclose($fout);
	}
		
	static function mapID($id, $system, $db, $ds) {
		global $wpiBridgeUrl;
		if(!$wpiBridgeUrl) $wpiBridgeUrl = 'http://webservice.bridgedb.org/';
		
		$mapped = array();
		
		if($db == "metabolites") $db = "Homo sapiens";
		$db_url = urlencode($db);
		$ds_url = urlencode($ds);
		$xd_url = urlencode($system);
		$xi_url = urlencode($id);
		$url = 
			"$wpiBridgeUrl$db_url/xrefs/$xd_url/$xi_url?dataSource=$ds_url";
		logger("opening $url");
		$handle = fopen($url, "r");

		if ($handle) {
			while(!feof($handle)) {
				$line = fgets($handle);
				$cols = explode("\t", $line);
				if(count($cols) == 2) {
					$mapped[] = $cols[0] . ':' . $cols[1];
				}
			}
			fclose($handle);
		} else {
			logger("Error getting data from " . $url);
		}
		return $mapped;
	}
	
	/**
	 * Get page ids to exclude based on a test/tutorial curation tag.
	 */
	static function getExcludeByTag() {
		$exclude = array();
		foreach(self::$excludeTags as $tag) {
			$exclude = array_merge($exclude, CurationTag::getPagesForTag($tag));
		}
		return $exclude;
	}
	
	/*
	 * Get an array of timestamps, one for each month from $tsStart
	 * to $tsEnd. Timestamps are in MW format.
	*/
	static function getTimeStampPerMonth($tsStart, $tsEnd) {
		$startD = (int)substr($tsStart, 6, 2);
		$startM = (int)substr($tsStart, 4, 2);
		$startY = (int)substr($tsStart, 0, 4);
		$ts = array();
		$tsCurr = $tsStart;
		$monthIncr = 0;
		while($tsCurr <= $tsEnd) {
			$ts[] = $tsCurr;
			$monthIncr += 1;
			$tsCurr = date('YmdHis', 
				mktime(0, 0, 0, $startM + $monthIncr, $startD, $startY));
		}
		$nm = count($ts);
		logger("Monthly interval from $tsStart to $tsEnd: $nm months.");
		return $ts;
	}
}

/**
 * Queries information about pathway entries.
 */
class StatPathway {
	static function getSnapshot($timestamp) {
		$snapshot = array();
		
		$ns_pathway = NS_PATHWAY;
		$q = <<<QUERY
				SELECT DISTINCT(r.rev_page) AS page_id, p.page_title as page_title 
				FROM revision AS r JOIN page AS p 
				ON r.rev_page = p.page_id
				WHERE r.rev_timestamp <= $timestamp
				AND p.page_is_redirect = 0
				AND p.page_namespace = $ns_pathway
QUERY;
		
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		while($row = $dbr->fetchObject($res)) {
			$p = new StatPathway($row->page_title, $row->page_id);
			$p->rev = $p->findClosestRevision($timestamp);
			$gpml = $p->getGpml();
			if(!$p->isDeleted($gpml) && !$p->isRedirect($gpml)) {
				$snapshot[] = $p;
			}
		}
		$dbr->freeResult($res);
		
		return $snapshot;
	}
	
	private $pwId;
	private $pageId;
	private $rev;
	
	function __construct($pwId, $pageId, $rev = 0) {
		$this->pwId = $pwId;
		$this->pageId = $pageId;
		$this->rev = $rev;
	}
	
	function getPageId() { return $this->pageId; }
	function getPwId() { return $this->pwId; }
	function getRevision() { return $this->rev; }
	
	function findClosestRevision($timestamp) {
		$q = <<<QUERY
				SELECT MAX(rev_id) FROM revision 
				WHERE rev_timestamp <= $timestamp AND rev_page = $this->pageId
QUERY;
		$rev = 0;
		
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		while($row = $dbr->fetchRow($res)) {
			$rev = $row[0];
		}
		$dbr->freeResult($res);
		return $rev;
	}
	
	function getGpml() {
		$q = <<<QUERY
				SELECT t.old_text FROM text AS t JOIN revision AS r
				WHERE r.rev_id = $this->rev AND t.old_id = r.rev_text_id
QUERY;
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		$row = $dbr->fetchRow($res);
		$gpml = $row[0];
		$dbr->freeResult($res);
		return $gpml;
	}
	
	function isDeleted($gpml) {
		return Pathway::isDeletedMark($gpml);	
	}
	
	function isRedirect($gpml) {
		return substr($gpml, 0, 9) == "#REDIRECT";
	}
	
	function getSpecies() {
		$gpml = $this->getGpml();
		$species = "undefined";
		$startTag = strpos($gpml, "<Pathway");
		if(!$startTag) throw new Exception("Unable to find start of '<Pathway ...>' tag.");
		$endTag = strpos($gpml, ">", $startTag);
		if(preg_match("/<Pathway.*Organism=\"(.*?)\"/us", substr($gpml, $startTag, $endTag - $startTag), $match)) {
			$species = $match[1];
		}
		return $species;
	}
	
	function getNrRevisions() {
		$q = <<<QUERY
SELECT COUNT(rev_id) FROM revision WHERE rev_page = $this->pageId
QUERY;
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		$row = $dbr->fetchRow($res);
		$count = $row[0];
		$dbr->freeResult($res);
		return $count;
	}
	
	function getNrViews() {
		$q = <<<QUERY
SELECT page_counter FROM page WHERE page_id = $this->pageId
QUERY;
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		$row = $dbr->fetchRow($res);
		$count = $row[0];
		$dbr->freeResult($res);
		return $count;
	}
}

/**
 * Queries information about curation tags.
 */
class StatTag {
	static function getSnapshot($timestamp, $tagTypes) {
		$snapshot = array();
		
		$q = <<<QUERY
SELECT tag_name, page_id FROM tag_history
WHERE tag_name LIKE 'Curation:%'
AND action = 'create' AND time <= $timestamp;
QUERY;
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		while($row = $dbr->fetchObject($res)) {
			$snapshot[] =  new StatTag($row->tag_name, $row->page_id);
		}
		$dbr->freeResult($res);
		
		array_unique($snapshot); //remove duplicates
		
		//Only include collection tags
		$removeTags = array();
		foreach($snapshot as $tag) if(!in_array($tag->getType(), $tagTypes)) {
			$removeTags[] = $tag;
		}
		$snapshot = array_diff($snapshot, $removeTags);
			
		$remove = array();
		foreach($snapshot as $tag) {
			//For each curation tag, find:
			//- the latest create before date
			//- the latest delete before date
			//Compare, if !delete or create > delete then exists
			$q_remove = <<<QUERY
SELECT time FROM tag_history
WHERE tag_name = '$tag->type' AND page_id = $tag->pageId
AND action = 'remove' AND time <= $timestamp
ORDER BY time DESC
QUERY;
			$res = $dbr->query($q_remove);
			$row = $dbr->fetchRow($res);
			$latest_remove = $row[0];
			$dbr->freeResult($res);
			
			if(!$latest_remove) continue;
			
			$q_create = <<<QUERY
SELECT time FROM tag_history
WHERE tag_name = '$tag->type' AND page_id = $tag->pageId
AND action = 'create' AND time <= $timestamp
ORDER BY time DESC
QUERY;
			$res = $dbr->query($q_create);
			$row = $dbr->fetchRow($res);
			$latest_create = $row[0];
			$dbr->freeResult($res);
			
			if($latest_remove > $latest_create) $remove[] = $tag;
		}
		
		$snapshot = array_diff($snapshot, $remove);
		return $snapshot;
	}
	
	private $type;
	private $pageId;
	
	function __construct($type, $pageId) {
		$this->type = $type;
		$this->pageId = $pageId;
	}
	
	function __toString() {
		return $this->type . $this->pageId;
	}
	
	function getPageId() { return $this->pageId; }
	function getType() { return $this->type; }
}

/**
 * Queries information about registered users.
 */
class StatUser {
	static function getSnapshot($timestamp) {
		$snapshot = array();
		
		$q = <<<QUERY
SELECT user_id, user_name, user_real_name FROM user 
WHERE user_registration <= $timestamp
QUERY;
		
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		while($row = $dbr->fetchObject($res)) {
			$u = new StatUser($row->user_id, $row->user_name, $row->user_real_name);
			$snapshot[] = $u;
		}
		$dbr->freeResult($res);
		
		return $snapshot;
	}
	
	private $id;
	private $name;
	private $realName;
	
	function __construct($id, $name, $realName) {
		$this->id = $id;
		$this->name = $name;
		$this->realName = $realName;
	}
	
	function getId() { return $this->id; }
	function getName() { return $this->name; }
	function getRealName() { return $this->realName; }
	
	function getPageEdits($tsTo = '', $tsFrom = '') {
		$pageEdits = array();
		
		$qto = '';
		$qfrom = '';
		if($tsTo) $qto = "AND r.rev_timestamp <= $tsTo ";
		if($tsFrom) $qfrom = "AND r.rev_timestamp > $tsFrom ";
		$ns_pathway = NS_PATHWAY;
		$q = <<<QUERY
SELECT r.rev_page FROM revision AS r JOIN page AS p
WHERE r.rev_user = $this->id AND p.page_namespace = $ns_pathway 
AND p.page_id = r.rev_page 
$qfrom $qto
QUERY;
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		while($row = $dbr->fetchRow($res)) {
			$pageEdits[] = $row[0];
		}
		$dbr->freeResult($res);
		
		return $pageEdits;
	}
}

class StatWebservice {
	static function getCountsByIp($tsFrom, $tsTo) {
		$q = <<<QUERY
SELECT ip, count(ip) FROM webservice_log 
WHERE request_timestamp >= $tsFrom AND request_timestamp < $tsTo
GROUP BY ip ORDER BY count(ip) DESC
QUERY;
		
		$counts = array();
		
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		while($row = $dbr->fetchRow($res)) {
			$counts[$row[0]] = $row[1];
		}
		$dbr->freeResult($res);

		return $counts;
	}
	
	static function getCounts($tsFrom, $tsTo) {
		$snapshot = array();
		
		$q = <<<QUERY
SELECT count(ip) FROM webservice_log 
WHERE request_timestamp >= $tsFrom AND request_timestamp < $tsTo
QUERY;
		
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->query($q);
		$row = $dbr->fetchRow($res);
		$count = $row[0];
		$dbr->freeResult($res);
		
		return $count;
	}
}

function logger($msg, $newline = "\n") {
	fwrite(STDOUT, $msg . $newline);
}
?>
