<?php
/**
 * Calculate several statistics for WikiPathways.
 *
 * Run using PHP cli:
 *
 * php statistics.php [task]
 *
 * Tasks will be loaded from php files in the tasks directory.
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

$allTasks = array();
function registerTask($name, $functionName) {
	global $allTasks;
	
	$allTasks[$name] = $functionName;
}

//Load available tasks
$base = dirname(__FILE__) . '/tasks';
foreach(scandir($base) as $f) {
	if(preg_match('/\.php$/', $f)) {
		require_once($base . '/' . $f); //Load the file, it will register itself to the available tasks.
	}
}

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
	call_user_func($allTasks[$task], $f, $times);
}

/**
 * Implements functions to extract the statistics.
 */
class WikiPathwaysStatistics {
	static $excludeTags = array(
		"Curation:Tutorial"
	);
	
	
	static function writeFrequencies($fout, $counts, $includeKey = 0) {
		arsort($counts);
		$i = 0;
		foreach(array_keys($counts) as $u) {
			$row = array($i, $counts[$u]);
			if($includeKey) array_unshift($row, $u);
			fwrite($fout, implode("\t", $row) . "\n");
			$i += 1;
		}
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
