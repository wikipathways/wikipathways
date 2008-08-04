<?php
$dir = getcwd();
chdir("../");
require_once('wpi.php');
require_once('search.php');
chdir($dir);

$operations = array(
	"listOrganisms",
	"listPathways", 
	"getPathway",
	"getRecentChanges",
	"login",
	"getPathwayAs",
	"updatePathway",
	"findPathwaysByText",
	"findPathwaysByXref",
	
);
$opParams = array(
	"listOrganisms" => "MIXED",
	"listPathways" => "MIXED", 
	"getPathway" => "MIXED",
	"getRecentChanges" => "MIXED",
	"login" => "MIXED",
	"getPathwayAs" => "MIXED",
	"updatePathway" => "MIXED",
	"findPathwaysByText" => "MIXED",
	"findPathwaysByXref" => "MIXED",
);

$classmap = array(); //just let the engine know you prefer classmap mode

$svr = new WSService(array(
	"operations" => $operations,
	"classmap" => $classmap,
	"opParams" => $opParams,
	"serviceName" => "WikiPathways",
));

$svr->reply();

/**
 * Get a list of all available organisms.
 * @return array of string $organisms Array with the names of all supported organisms
  **/
function listOrganisms() {
	return array("organisms" => Pathway::getAvailableSpecies());
}

/**
 * Get a list of all available pathways.
 * @return array of object WSPathwayInfo $pathways Array of pathway info objects
 **/
function listPathways() {
	$pathways = Pathway::getAllPathways();
	$objects = array();
	foreach($pathways as $p) {
		$objects[] = new WSPathwayInfo($p);
	}
	return array("pathways" => $objects);
}

/**
 * Get the GPML code for a pathway
 * @param string $pwName The pathway name
 * @param string $pwSpecies The pathway species
 * @param integer $revision The revision number of the pathway (use 0 for most recent)
 * @return object WSPathway $pathway The pathway
 **/
function getPathway($pwName, $pwSpecies, $revision = 0) {
	try {
		$pathway = new Pathway($pwName, $pwSpecies);
		$pwi = new WSPathway($pathway);
		return array("pathway" => $pwi);
	} catch(Exception $e) {
		wfDebug("ERROR: $e");
		throw new WSFault("Receiver", $e);
	}
}

/**
 * Update the GPML code of a pathway on the wiki
 * @param string $pwName The name of the pathway
 * @param string $pwSpecies The species of the pathway
 * @param string $description A description of the modifications
 * @param string $gpml The updated GPML code
 * @param int $revision The revision the GPML code is based on
 * @param object WSAuth $auth The authentication info
 * @return boolean $success Whether the update was successful
 **/
function updatePathway($pwName, $pwSpecies, $description, $gpml, $revision, $auth = NULL) {
	global $wgUser;
	
	try {
		//Authenticate first, if token is provided
		if($auth) {
			authenticate($auth['user'], $auth['key']);
		}

		$pathway = new Pathway($pwName, $pwSpecies);
		//Only update if the given revision is the newest
		//Or if this is a new pathway
		if(!$pathway->exists() || $revision == $pathway->getLatestRevision()) {
			$pathway->updatePathway($gpml, $description);
			$resp = $pathway->getLatestRevision();
		} else {
			throw new WSFault("Sender",
				"Revision out of date: your GPML code originates from " .
				"an old revision. This means somebody else modified the pathway " .
				"since you downloaded it. Please apply your changes on the newest version"
			);
		}
	} catch(Exception $e) {
		if($e instanceof WSFault) { 
			throw $e; 
		} else {
			throw new WSFault("Receiver", $e);
			wfDebug("ERROR: $e");
		}
	}
	ob_clean();
	return array("success" => true);
}

/**
 * Start a logged in session, using an existing WikiPathways account. 
 * This function will return an authentication code that can be used 
 * to excecute methods that need authentication (e.g. updatePathway)
 * @param string $name The usernameset_include_path(get_include_path().PATH_SEPARATOR.realpath('../includes').PATH_SEPARATOR.realpath('../').PATH_SEPARATOR);
 * @param string $pass The password
 * @return string $auth The authentication code
 **/
function login($name, $pass) {
	global $wgUser, $wgAuth;
	
	$user = User::newFromName( $name );
	if( is_null($user) || $user->getID() == 0) {
		//throw new Exception("Invalid user name");
		throw new WSFault("Sender", "Invalid user name");
	}
	$user->load();
	if ($user->checkPassword( $pass )) {
		$wgAuth->updateUser($user);
		$wgUser = $user;
		return array("auth" => $user->mToken);
	} else {
		//throw new Exception("Wrong password");
		throw new WSFault("Sender", "Wrong password");
	}
}

/**
 * Download a pathway in the specified file format.
 * @param string $fileType The file type to convert to, e.g.
 * 'svg', 'png' or 'txt'
 * @param string $pwName The pathway name
 * @param string $pwSpecies The pathway species
 * @param integer $revision The revision number of the pathway (use 0 for most recent)
 * @return base64Binary $data The converted file data (base64 encoded)
 **/
function getPathwayAs($fileType, $pwName, $pwSpecies, $revision = 0) {
	try {
		$p = new Pathway($pwName, $pwSpecies);
		$p->setActiveRevision($revision);
		$data = file_get_contents($p->getFileLocation($fileType));
		$data = base64_encode($data);
	} catch(Exception $e) {
		throw new WSFault("Receiver", "Unable to get pathway: " . $e);
	}
	return array("data" => $data);
}

/**
 * Get the recently changed pathways. Note: the recent changes table
 * only retains items for a limited time, so it's not guaranteed
 * that you will get all changes since the given timestamp.
 * @param string $timestamp Get the changes after this time
 * @return array of object WSPathwayInfo $pathways A list of the changed pathways
 **/
function getRecentChanges($timestamp)
{
	//check safety of $timestamp, must be exactly 14 digits and nothing else.
	if (!preg_match ("/^\d{14}$/", $timestamp))
	{
		throw new WSFault("Sender", "Invalid timestamp " . htmlentities ($timestamp));
	}

	$dbr =& wfGetDB( DB_SLAVE );
	$forceclause = $dbr->useIndexClause("rc_timestamp");
	$recentchanges = $dbr->tableName( 'recentchanges');

	$sql = "SELECT  
				rc_namespace, 
				rc_title, 
				MAX(rc_timestamp)
			FROM $recentchanges $forceclause
			WHERE 
				rc_namespace = " . NS_PATHWAY . "
				AND
				rc_timestamp > '$timestamp'
			GROUP BY rc_title
			ORDER BY rc_timestamp DESC
		";
		
	//~ wfDebug ("SQL: $sql");

	$res = $dbr->query( $sql, "getRecentChanges" );

	$objects = array();
	while ($row = $dbr->fetchRow ($res))
	{
		try {
				$ts = $row['rc_title'];
			$p = Pathway::newFromTitle($ts);
			$objects[] = new WSPathwayInfo($p);
		} catch(Exception $e) {
			wfDebug("Unable to create pathway object for recent changes: $e");
		}

	}
	return array("pathways" => $objects);
}

/**
 * Find pathways by a textual search.
 * @param string $query The query, e.g. 'apoptosis'
 * @param string $species Optional, limit the query by species. Leave
 * blank to search on all species
 * @return array of object WSSearchResult $result Array of WSSearchResult objects
 **/
function findPathwaysByText($query, $species = '') {
	$objects = array();
	$results = PathwayIndex::searchByText($query, $species);
	foreach($results as $r) {
		$objects[] = new WSSearchResult($r, array());
	}
	return array("result" => $objects);
}

/**
 * Find pathways by a datanode xref.
 * @param string $id The datanode identifier (e.g. 'P45985')
 * @param string $code Optional, limit the query by database (e.g. 'S' for UniProt). Leave
 * blank to search on all databases
 * @return array of object WSSearchResult $result Array of WSSearchResult objects
 **/
function findPathwaysByXref($id, $code = '', $indirect = true) {
	$xref = new XRef($id, $code);
	$objects = array();
	$results = PathwayIndex::searchByXref($xref, $indirect);
	foreach($results as $r) {
		$objects[] = new WSSearchResult($r, array(PathwayIndex::$f_graphId));
	}
	return array("result" => $objects);
}

//Non ws functions
function authenticate($username, $token) {
	global $wgUser, $wgAuth;
	$user = User::newFromName( $username );
	if( is_null($user) || $user->getID() == 0) {
		throw new WSFault("Sender", "Invalid user name");
	}
	$user->load();
	if ($user->mToken == $token) {
		$wgAuth->updateUser($user);
		$wgUser = $user;
	} else {
		throw new WSFault("Sender", "Wrong authentication token");
	}
}

//Class definitions
 /**
 * @namespace http://www.wikipathways.org/webservice
 */
class WSPathwayInfo {
	function __construct($pathway) {
		$this->revision = $pathway->getLatestRevision();
		$this->species = $pathway->species();
		$this->name = $pathway->name();
		$this->url = $pathway->getTitleObject()->getFullURL();
		
		//Hack to make response valid in case of missing revision
		if(!$this->revision) $this->revision = 0;
	}
	
	/**
	* @var string $url - the url to the pathway
	**/
	public $url;
	/**
	* @var string $name - the pathway name
	**/
	public $name;
	/**
	* @var string $species - the pathway species
	**/
	public $species;
	/**
	* @var string $revision - the revision number
	**/
	public $revision;
}

 /**
 * @namespace http://www.wikipathways.org/webservice
 */
class WSSearchResult extends WSPathwayInfo {
	/**
	 * @param $searchHit an object of class SearchHit
	 * @param $includeFields an array with the fields to include.
	 * Leave 'null' to include all fields.
	**/
	function __construct($hit, $includeFields = null) {
		parent::__construct($hit->getPathway());
		$this->score = $hit->getScore();
		if($includeFields === null) {
			$includeFields = $hit->getDocument()->getFieldNames();
		}
		$this->fields = array();
		$doc = $hit->getDocument();
		foreach($includeFields as $fn) {
			$this->fields[] = new WSIndexField($fn, $doc->getFieldValues($fn));
		}
	}
	
	/**
	* @var double $score - the score of the search result
	**/
	public $score;

	/**
	* @var array of object WSIndexField $fields - the url to the pathway
	**/
	public $fields;
}

 /**
 * @namespace http://www.wikipathways.org/webservice
 */
class WSIndexField {
	function __construct($name, $values) {
		$this->name = $name;
		$this->values = $values;
		$this->values = preg_replace("/\&/", "&amp;", $this->values);
	}
	
	/**
	* @var string $name - the name of the index field
	**/
	public $name;
	
	/**
	* @var array of string - the value(s) of the field
	**/
	public $values;
}

/**
 * @namespace http://www.wikipathways.org/webservice
 */
class WSPathway extends WSPathwayInfo {
	function __construct($pathway) {
		parent::__construct($pathway);
		$this->gpml = $pathway->getGPML();
	}
	/**
	* @var string $gpml - the GPML code
	**/
	public $gpml;
}

/**
 * @namespace http://www.wikipathways.org/webservice
 **/
class WSAuth {
	/**
	 * @var string $user The username
	 **/
	public $user;
	
	/**
	 * @var string $key The authentication key
	 **/
	public $key;
}
?>
