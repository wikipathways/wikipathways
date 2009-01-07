<?php
chdir(dirname(realpath(__FILE__)) . "/../");
require_once('wpi.php');
try {
	require_once('search.php');
} catch(Exception $e) {
	wfDebug("Webservice: Unable to connect to lucene index!\n");
}
chdir($dir);

//Prevent errors/warnings from messing up the xml response
ini_set("display_errors", "0");

$operations = array(
	"listOrganisms",
	"listPathways", 
	"getPathway",
	"getPathwayInfo",
	"getRecentChanges",
	"login",
	"getPathwayAs",
	"updatePathway",
	"findPathwaysByText",
	"findPathwaysByXref",
	"removeCurationTag",
	"saveCurationTag",
	"getCurationTags",
	"getCurationTagHistory",
	"getColoredPathway",
	"findInteractions",
);
$opParams = array(
	"listOrganisms" => "MIXED",
	"listPathways" => "MIXED", 
	"getPathway" => "MIXED",
	"getPathwayInfo" => "MIXED",
	"getRecentChanges" => "MIXED",
	"login" => "MIXED",
	"getPathwayAs" => "MIXED",
	"updatePathway" => "MIXED",
	"findPathwaysByText" => "MIXED",
	"findPathwaysByXref" => "MIXED",
	"removeCurationTag" => "MIXED",
	"saveCurationTag" => "MIXED",
	"getCurationTags"=> "MIXED",
	"getCurationTagHistory" => "MIXED",
	"getColoredPathway" => "MIXED",
	"findInteractions" => "MIXED",
);

$classmap = array(); //just let the engine know you prefer classmap mode

$restmap = array(
	"getPathwayInfo" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getPathwayInfo/id/{pwId}"
	),
	"listOrganisms" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "listOrganisms"
	),
	"listPathways" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "listPathways"
	),
	"findPathwaysByXref" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "findPathwaysByXref/id/{id}/code/{code}"
	),
	"findPathwaysByText" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "findPathwaysByText/query/{query}/species/{species}"
	),
	"findInteractions" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "findInteractions/query/{query}"
	),
);

$svr = new WSService(array(
	"operations" => $operations,
	"classmap" => $classmap,
	"opParams" => $opParams,
	"serviceName" => "WikiPathways",
	"RESTMapping" => $restmap,
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
 * @param string $pwId The pathway identifier
 * @param int $revision The revision number of the pathway (use 0 for most recent)
 * @return object WSPathway $pathway The pathway
 **/
function getPathway($pwId, $revision = 0) {
	try {
		$pathway = new Pathway($pwId);
		$pwi = new WSPathway($pathway);
		return array("pathway" => $pwi);
	} catch(Exception $e) {
		wfDebug("ERROR: $e");
		throw new WSFault("Receiver", $e);
	}
}

/**
 * Get some general info about the pathway, such as the name, species
 * and latest revision
 * @param string $pwId The pathway identifier
 * @return object WSPathwayInfo $pathwayInfo The pathway info
 **/
function getPathwayInfo($pwId) {
	try {
		$pathway = new Pathway($pwId);
		$pwi = new WSPathwayInfo($pathway);
		return array("pathwayInfo" => $pwi);
	} catch(Exception $e) {
		wfDebug(__METHOD__ . " (ERROR): $e\n");
		throw new WSFault("Receiver", $e);
	}
}

/**
 * Update the GPML code of a pathway on the wiki
 * @param string $pwId The pathway identifier
 * @param string $description A description of the modifications
 * @param string $gpml The updated GPML code
 * @param int $revision The revision the GPML code is based on
 * @param object WSAuth $auth The authentication info
 * @return boolean $success Whether the update was successful
 **/
function updatePathway($pwId, $description, $gpml, $revision, $auth = NULL) {
	global $wgUser;
	
	try {
		//Authenticate first, if token is provided
		if($auth) {
			authenticate($auth['user'], $auth['key'], true);
		}

		$pathway = new Pathway($pwId);
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
 * @param string $pwId The pathway identifier
 * @param int $revision The revision number of the pathway (use 0 for most recent)
 * @return base64Binary $data The converted file data (base64 encoded)
 **/
function getPathwayAs($fileType, $pwId, $revision = 0) {
	try {
		$p = new Pathway($pwId);
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
			if(!$p->getTitleObject()->isRedirect()) {
				$objects[] = new WSPathwayInfo($p);			
			}
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

/**
 * Find interactions.
 * @param string $query The name of an entity to find interactions for (e.g. 'P53')
 * @return array of object WSSearchResult $result Array of WSSearchResult objects
 **/
function findInteractions($query) {
	$objects = array();
	$results= PathwayIndex::searchInteractions($query);
	foreach($results as $r) {
		$objects[] = new WSSearchResult($r);
	}
	return array("result" => $objects);
}

/**
 * Apply a curation tag to a pahtway. This operation will
 * overwrite any existing tag with the same name.
 * @param string $pwId The pathway identifier
 * @param string $tagName The name of the tag to apply
 * @param string $tagText The tag text (optional)
 * @param int $revision The revision this tag applies to
 * @param object WSAuth $auth The authentication info
 * @return boolean $success
 */
function saveCurationTag($pwId, $tagName, $text, $revision, $auth) {
	if($auth) {
		authenticate($auth['user'], $auth['key'], true);
	}
	
	try {
		$pathway = new Pathway($pwId);
		if($pathway->exists()) {
			$pageId = $pathway->getTitleObject()->getArticleId();
			CurationTag::saveTag($pageId, $tagName, $text, $revision);
		}
	} catch(Exception $e) {
		wfDebug("ERROR: $e");
		throw new WSFault("Receiver", $e);
	}
	return array("success" => true);
}

/**
 * Remove a curation tag from a pathway.
 * @param string $pwId The pathway identifier
 * @param string $tagName The name of the tag to apply
 * @param object WSAuth $auth The authentication data
 * @return boolean $success
 **/
function removeCurationTag($pwId, $tagName, $auth) {
	if($auth) {
		authenticate($auth['user'], $auth['key'], true);
	}
	
	try {
		$pathway = new Pathway($pwId);
		if($pathway->exists()) {
			$pageId = $pathway->getTitleObject()->getArticleId();
			CurationTag::removeTag($tagName, $pageId);
		}
	} catch(Exception $e) {
		wfDebug("ERROR: $e");
		throw new WSFault("Receiver", $e);
	}
	return array("success" => true);
}

/**
 * Get all curation tags for the given pathway.
 * @param string $pwId The pathway identifier
 * @return array of object WSCurationTag $tags The curation tags.
 **/
function getCurationTags($pwId) {
	$pw = new Pathway($pwId);
	$pageId = $pw->getTitleObject()->getArticleId();
	$tags = CurationTag::getCurationTags($pageId);
	$wstags = array();
	foreach($tags as $t) {
		$wstags[] = new WSCurationTag($t);
	}
	return array("tags" => $wstags);
}

/**
 * Get the curation tag history for the given pathway.
 * @param string $pwId The pathway identifier
 * @param string $timestamp Only include history from after the given date
 * @return array of object WSCurationTagHistory $history The history
 **/
function getCurationTagHistory($pwId, $timestamp = 0) {
	$pw = new Pathway($pwId);
	$pageId = $pw->getTitleObject()->getArticleId();
	$hist = CurationTag::getHistory($pageId, $timestamp);
	$wshist = array();
	foreach($hist as $h) {
		$wshist[] = new WSCurationTagHistory($h);
	}
	return array("history" => $wshist);
}

/**
 * Get a colored image version of the pahtway.
 * @param string $pwId The pathway identifier
 * @param string $revision The revision of the pathway (use '0' for most recent)
 * @param array of string $graphId An array with graphIds of the objects to color
 * @param array of string $color An array with colors of the objects (should be the same length as $graphId)
 * @param string $fileType The image type (One of 'svg', 'pdf' or 'png').
 * @return base64Binary $data The image data (base64 encoded)
 **/
function getColoredPathway($pwId, $revision, $graphId, $color, $fileType) {
	try {
		$p = new Pathway($pwId);
		$p->setActiveRevision($revision);
		$gpmlFile = realpath($p->getFileLocation(FILETYPE_GPML));
		
		$outFile = WPI_TMP_PATH . "/" . $p->getTitleObject()->getDbKey() . '.' . $fileType;

		if(count($color) != count($graphId)) {
			throw new Exception("Number of colors doesn't match number of graphIds");
		}
		$colorArg = '';
		for($i = 0; $i < count($color); $i++) {
			$colorArg .= " -c '{$graphId[$i]}' '{$color[$i]}'";
		}
		
		$basePath = WPI_SCRIPT_PATH;
		$cmd = "java -jar $basePath/bin/pathvisio_color_exporter.jar '$gpmlFile' '$outFile' $colorArg 2>&1";
		wfDebug("COLOR EXPORTER: $cmd\n");
		exec($cmd, $output, $status);
		
		foreach ($output as $line) {
			$msg .= $line . "\n";
		}
		if($status != 0 ) {
			throw new Exception("Unable to convert to $outFile:\nStatus:$status\nMessage:$msg");
		}
		$data = file_get_contents($outFile);
		$data = base64_encode($data);
	} catch(Exception $e) {
		throw new WSFault("Receiver", "Unable to get pathway: " . $e);
	}
	return array("data" => $data);
}

//Non ws functions
function authenticate($username, $token, $write = false) {
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
	if($write) { //Also check for write access
		$rights = $user->getRights();
		if(!in_array('webservice_write', $rights)) {
			throw new WSFault("Sender", "Account doesn't have write access for the web service. \n".
			"Contact the site administrator to request write permissions.");
		}
	}
}

function formatXml($xml) {
	return preg_replace("/\&/", "&amp;", $xml);
}

//Class definitions
 /**
 * @namespace http://www.wikipathways.org/webservice
 */
class WSPathwayInfo {
	function __construct($pathway) {
		$this->id = $pathway->getIdentifier();
		$this->revision = $pathway->getLatestRevision();
		$this->species = $pathway->species();
		$this->name = formatXml($pathway->name());
		$this->url = $pathway->getTitleObject()->getFullURL();
		
		//Hack to make response valid in case of missing revision
		if(!$this->revision) $this->revision = 0;
	}
	
	/**
	 * @var string $id - the pathway identifier
	 */
	public $id;
	
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
		$this->values = formatXml($this->values);
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
		$this->gpml = formatXml($pathway->getGPML());
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

/**
 * @namespace http://www.wikipathways.org/webservice
 **/
class WSCurationTag {
	public function __construct($metatag) {
		$this->name = $metatag->getName();
		$this->displayName = CurationTag::getDisplayName($this->name);
		$this->pathway = new WSPathwayInfo(
			Pathway::newFromTitle(Title::newFromId($metatag->getPageId()))
		);
		$this->revision = $metatag->getPageRevision();
		$this->text = $metatag->getText();
		$this->timeModified = $metatag->getTimeMod();
		$this->userModified = User::newFromId($metatag->getUserMod())->getName();
	}
	
	/**
	 * @var string $name The internal tag name
	 **/
	public $name;
	
	/**
	 * @var string $displayName The display name of the tag
	 */
	public $displayName;
	
	/**
	 * @var object WSPathwayInfo $pathway The pathway this tag applies to
	 */
	public $pathway;
	
	/**
	 *@var string $revision The revision this tag applies to. '0' is used for tags that apply to all revisions.
	 */
	public $revision;
	
	/**
	 *@var string $text The tag text.
	 */
	public $text;
	
	/**
	 *@var long $timeModified The timestamp of the last modified date
	 */
	public $timeModified;
	
	/**
	 *@var string $userModified The username of the user that last modified the tag
	 */
	public $userModified;
}


/**
 * @namespace http://www.wikipathways.org/webservice
 **/
class WSCurationTagHistory {
	public function __construct($histRow) {
		$this->tagName = $histRow->getTagName();
		$this->pathwayId = Title::newFromId($histRow->getPageId())->getText();
		$this->action = $histRow->getAction();
		$this->user = User::newFromId($histRow->getUser())->getName();
		$this->time = $histRow->getTime();
		$this->text = $histRow->getText();
	}
	
	/**
	 *@var string $tagName The name of the tag that was affected
	 */
	public $tagName;
	
	/**
	 *@var string $text The text of the tag at time this action was performed
	 */
	public $text;
	
	/**
	 *@var string $pathwayId The id of the pathway this tag applies to
	 */
	public $pathwayId;
	
	/**
	 *@var string $action The action that was performed on the tag
	 */
	public $action;
	
	/**
	 *@var string $user The name of the user that performed the action
	 */
	public $user;
	
	/**
	 *@var string $time The timestamp of the date the action was performed
	 */
	public $time;
}
?>
