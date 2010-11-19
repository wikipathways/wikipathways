<?php
chdir(dirname(realpath(__FILE__)) . "/../");
require_once('wpi.php');
require_once('search.php');
require_once('relations.php');
chdir($dir);

## Log the request ##
//Try to find the called operation
$operation = '';
if(preg_match("/Body>(.*?)>/us", $HTTP_RAW_POST_DATA, $match)) {
	$operation = trim($match[1]);
#} else if(preg_match("/Body>(.*?)>/us", file_get_contents('php://input'), $match)){
#	$operation = trim($match[1]);
} else if(in_array("wsdl", array_keys($_REQUEST))) { //WSDL requests
	$operation = "wsdl";
} else if($_SERVER["PATH_INFO"]) {
	$operation = "rest: " . $_SERVER["PATH_INFO"];
}

$timestamp = wfTimestamp( TS_MW );
$ip = $_SERVER['REMOTE_ADDR'];
//write the log entry
$dbw =& wfGetDB(DB_MASTER);
$values = array(
	"ip" => $ip,
	"operation" => $operation,
	"request_timestamp" => $timestamp,
);
$dbw->insert("webservice_log", $values);
$dbw->immediateCommit();

//Prevent errors/warnings from messing up the xml response
ini_set("display_errors", "0");

$operations = array(
	"listOrganisms",
	"listPathways", 
	"getPathway",
	"getPathwayInfo",
	"getPathwayHistory",
	"getRecentChanges",
	"login",
	"getPathwayAs",
	"updatePathway",
	"createPathway",
	"findPathwaysByText",
	"findPathwaysByXref",
	"removeCurationTag",
	"saveCurationTag",
	"getCurationTags",
	"getCurationTagsByName",
	"getCurationTagHistory",
	"getColoredPathway",
	"findInteractions",
	"getXrefList",
	"findPathwaysByLiterature",
    "getOntologyTermsByPathway",
    "getOntologyTermsByOntology",
    "getPathwaysByOntologyTerm",
    "getPathwaysByParentOntologyTerm",
    "getRelations"
);
$opParams = array(
	"listOrganisms" => "MIXED",
	"listPathways" => "MIXED", 
	"getPathway" => "MIXED",
	"getPathwayInfo" => "MIXED",
	"getPathwayHistory" => "MIXED",
	"getRecentChanges" => "MIXED",
	"login" => "MIXED",
	"getPathwayAs" => "MIXED",
	"updatePathway" => "MIXED",
	"createPathway" => "MIXED",
	"findPathwaysByText" => "MIXED",
	"findPathwaysByXref" => "MIXED",
	"removeCurationTag" => "MIXED",
	"saveCurationTag" => "MIXED",
	"getCurationTags"=> "MIXED",
	"getCurationTagsByName" => "MIXED",
	"getCurationTagHistory" => "MIXED",
	"getColoredPathway" => "MIXED",
	"findInteractions" => "MIXED",
	"getXrefList" => "MIXED",
	"findPathwaysByLiterature" => "MIXED",
    "getOntologyTermsByPathway" => "MIXED",
    "getOntologyTermsByOntology" => "MIXED",
    "getPathwaysByOntologyTerm" => "MIXED",
    "getPathwaysByParentOntologyTerm" => "MIXED",
    "getRelations" => "MIXED"
);

$classmap = array(); //just let the engine know you prefer classmap mode

$restmap = array(
	"getPathway" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getPathway"
	),
	"getPathwayInfo" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getPathwayInfo"
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
		"RESTLocation" => "findPathwaysByXref"
	),
	"findPathwaysByText" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "findPathwaysByText"
	),
	"findInteractions" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "findInteractions"
	),
	"findPathwaysByLiterature" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "findPathwaysByLiterature"
	),
	"getXrefList" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getXrefList"
	),
	"getPathwayHistory" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getPathwayHistory"
	),
	"getRecentChanges" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getRecentChanges"
	),
	"getOntologyTermsByPathway" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getOntologyTermsByPathway"
	),
	"getOntologyTermsByOntology" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getOntologyTermsByOntology"
	),
	"getPathwaysByOntologyTerm" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getPathwaysByOntologyTerm"
	),
	"getPathwaysByParentOntologyTerm" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getPathwaysByParentOntologyTerm"
	),
	"getRelations" => array(
		"HTTPMethod" =>"GET",
		"RESTLocation" => "getRelations"
	)
);

$svr = new WSService(array(
	"operations" => $operations,
	"classmap" => $classmap,
	"opParams" => $opParams,
	"serviceName" => "WikiPathways",
	"RESTMapping" => $restmap,
	"cacheWSDL" => false,
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
 * @param string $organism The organism to filter by (optional)
 * @return array of object WSPathwayInfo $pathways Array of pathway info objects
 **/
function listPathways($organism = false) {
	try {
		$pathways = Pathway::getAllPathways($organism);
		$objects = array();
		foreach($pathways as $p) {
			$objects[] = new WSPathwayInfo($p);
		}
		return array("pathways" => $objects);
	} catch(Exception $e) {
		wfDebug("ERROR: $e");
		throw new WSFault("Receiver", $e);
	}
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
		if($revision) $pathway->setActiveRevision($revision);
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
 * Get the revision history of the pathway.
 * @param string $pwId The pathway identifier
 * @param string $timestamp Limit by time, only history items after the given
 * time will be included.
 * @return object WSPathwayHistory $history The pathway history
 **/
function getPathwayHistory($pwId, $timestamp) {
	try {
		$pathway = new Pathway($pwId);
		$id = $pathway->getTitleObject()->getArticleId();
		$dbr =& wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			"revision", 
			array("rev_id", "rev_user_text", "rev_timestamp", "rev_comment"),
			array('rev_page' => $id, 'rev_timestamp >= ' . $dbr->addQuotes($timestamp))
		);

		$hist = new WSPathwayHistory($pathway);
		
		while($row = $dbr->fetchObject( $res )) {
			$hr = new WSHistoryRow();
			$hr->revision = $row->rev_id;
			$hr->comment = $row->rev_comment;
			$hr->user = $row->rev_user_text;
			$hr->timestamp = $row->rev_timestamp;
			$hist->history[] = $hr;
		}
		
		$dbr->freeResult( $res );
		
		return array('history' => $hist);
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
 * Cteate a new pathway on the wiki
 * @param string $gpml The GPML code for the new pathway
 * @param object WSAuth $auth The authentication info
 * @return object WSPathwayInfo $pathwayInfo The pathway info of the created pathway
 **/
function createPathway($gpml, $auth) {
	try {
		//Authenticate first, if token is provided
		if($auth) {
			authenticate($auth['user'], $auth['key'], true);
		}
		
		$pathway = Pathway::createNewPathway($gpml, "New pathway");
		return array("pathwayInfo" => new WSPathwayInfo($pathway));
	} catch(Exception $e) {
		throw new WSFault("Receiver", $e);
		wfDebug("WSFAULT: $e");
	}
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
			if(!$p->getTitleObject()->isRedirect() && $p->isReadable()) {
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
	try {
		$objects = array();
		$results = PathwayIndex::searchByText($query, $species);
		foreach($results as $r) {
			$objects[] = new WSSearchResult($r, array());
		}
		return array("result" => $objects);
	} catch(Exception $e) {
		wfDebug("ERROR: $e");
		throw new WSFault("Receiver", $e);
	}
}

/**
 * Find pathways by a datanode xref.
 * @param array of string $ids The datanode identifier (e.g. 'P45985')
 * @param array of string $codes Optional, limit the query by database (e.g. 'S' for UniProt). Leave
 * blank to search on all databases
 * @return array of object WSSearchResult $result Array of WSSearchResult objects
 **/
function findPathwaysByXref($ids, $codes = '') {
	try {
		if($codes) {
			if(count($codes) == 1) { //One code for all ids
				$codes = array_fill(0, count($ids), $codes[0]);
			} else if(count($codes) != count($ids)) {
				throw new WSFault("Sender", "Number of supplied ids does not match number of system codes");
			}
		} else {
			$codes = array_fill(0, count($ids), '');
		}
		$xrefs = array();
		$xrefsStr = array();
		for($i = 0; $i < count($ids); $i += 1) {
			$x = new XRef($ids[$i], $codes[$i]);
			$xrefs[] = $x;
			$xrefsStr[] = (string)$x;
		}
		$objects = array();
		$results = PathwayIndex::searchByXref($xrefs, true);
		foreach($results as $r) {
			$wsr = new WSSearchResult($r);
			$objects[] = $wsr;
		}
		return array("result" => $objects);
	} catch(Exception $e) {
		wfDebug("ERROR: $e");
		throw new WSFault("Receiver", $e);
	}
}

/**
 * Find pathways by literature references.
 * @param string $query The query, can be a pubmed id, author name or title keyword.
 * @return array of object WSSearchResult $result Array of WSSearchResult objects
 */
function findPathwaysByLiterature($query) {
	try {
		$results = PathwayIndex::searchByLiterature($query);
		$combined = array();
		foreach($results as $r) {
			$nwsr = new WSSearchResult($r, array(
				PathwayIndex::$f_graphId,
				PathwayIndex::$f_literature_pubmed,
				PathwayIndex::$f_literature_title,
			));
			$source = $r->getFieldValue(PathwayIndex::$f_source);
			if($combined[$source]) {
				$wsr =& $combined[$source];
				foreach(array_keys($wsr->fields) as $fn) {
					if($nwsr->fields[$fn]) {
						$newvalues = array_merge(
							$nwsr->fields[$fn]->values, 
							$wsr->fields[$fn]->values
						);
						$newvalues = array_unique($newvalues);
						$wsr->fields[$fn]->values = $newvalues;
					}
				}
			} else {
				$combined[$source] = $nwsr;
			}
		}
		return array("result" => $combined);
	} catch(Exception $e) {
		wfDebug("ERROR: $e");
		throw new WSFault("Receiver", $e);
	}
}

/**
 * Find interactions.
 * @param string $query The name of an entity to find interactions for (e.g. 'P53')
 * @return array of object WSSearchResult $result Array of WSSearchResult objects
 **/
function findInteractions($query) {
	try {
		$objects = array();
		$results= PathwayIndex::searchInteractions($query);
		foreach($results as $r) {
			$objects[] = new WSSearchResult($r);
		}
		return array("result" => $objects);
	} catch(Exception $e) {
		throw new WSFault("Receiver", $e);
	}
}

/**
 * List the datanode xrefs of a pathway, translated to the given
 * identifier system. Note that the number of items may differ from
 * the number of datanodes on the pathway (due to a many-to-many mapping
 * between the different databases).
 * @param string $pwId The pathway identifier.
 * @param string $code The database code to translate to (e.g. 'S' for UniProt).
 * @return array of string $xrefs The translated xrefs.
 */
function getXrefList($pwId, $code) {
	try {
		$list = PathwayIndex::listPathwayXrefs(new Pathway($pwId), $code);
		return array("xrefs" => $list);
	} catch(Exception $e) {
		throw new WSFault("Receiver", "Unable to process request: " . $e);
	}
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
 * Get all curation tags for the given
 * tag name.
 * @param string $tagName The tag name
 * @return array of object WSCurationTag $tags The curation tags
 */
function getCurationTagsByName($tagName) {
	$tags = CurationTag::getCurationTagsByName($tagName);
	$wstags = array();
	foreach($tags as $t) {
		$wst = new WSCurationTag($t);
		if($wst->pathway) {
			$wstags[] = $wst;
		}
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
	} catch(Exception $e) {
		throw new WSFault("Receiver", "Unable to get pathway: " . $e);
	}
	return array("data" => $data);
}

/**
 * Get the relations for the given pathways.
 * @param string $type The type of relation for the score has to be fetched (optional).
 * @param string $pwId_1 The id of the Pathway for the relation has to be fetched (optional).
 * @param string $pwId_2 The id of the second Pathway for the relation has to be fetched (optional).
 * @param float $minScore The minimum score for which the relations are fetched (optional).
 * @param string $species Limit the query by species.
 * @param string $species Limit the query by species.
 * @return array of object WSRelation $relations The Relations
 **/
function getRelations($type = "", $pwId_1 = "", $pwId_2 = "", $minScore = 0, $species = "") {

        try{
            $relations = array();
            $relations = Relations::fetchRelations($type, $pwId_1, $pwId_2, $minScore, $species);

            $wsRelations = array();
            foreach($relations as $relation) {
                    $wsRelation = new WSRelation($relation);
                    $wsRelations[] = $wsRelation;
            }
            return array("relations" => $wsRelations);
            
	} catch(Exception $e) {
		wfDebug(__METHOD__ . " (ERROR): $e\n");
		throw new WSFault("Receiver", $e);
	}
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

/**
 * Get a list of ontology terms for a given pathway
 * @param string $pwId The pathway identifier
 * @return array of object WSOntologyTerm $terms The ontology terms
 **/
function getOntologyTermsByPathway($pwId) {
      try {
              $pw = new Pathway($pwId);
              $terms = array();
              $dbr = wfGetDB( DB_SLAVE );
              $res = $dbr->select(
                      'ontology',
                      array('*'),
                      array('pw_id = ' . $dbr->addQuotes($pwId))
              );

              $terms = array();
              $count = 0;
              while($row = $dbr->fetchObject( $res )) {
                      $term = new WSOntologyTerm();
                      $term->id = $row->term_id;
                      $term->name = $row->term;
                      $term->ontology = $row->ontology;
                      $terms[] = $term;
                      $count++;
              }
              $dbr->freeResult( $res );

              $termObjects = array();
  } catch(Exception $e) {
              throw new WSFault("Receiver", "Unable to get ontology
terms: " . $e);
      }
  return array("terms" => $terms);
}

/**
 * Get a list of ontology terms from a given ontology
 * @param string $ontology The Ontology name
 * @return array of object WSOntologyTerm $terms The ontology terms 
 **/
function getOntologyTermsByOntology($ontology) {
      try {
              $terms = array();
              $dbr = wfGetDB( DB_SLAVE );
              $res = $dbr->select(
                      'ontology',
                      array('*'),
                      array('ontology = ' . $dbr->addQuotes($ontology))
              );

              $terms = array();
              $count = 0;
              while($row = $dbr->fetchObject( $res )) {
                      $term = new WSOntologyTerm();
                      $term->id = $row->term_id;
                      $term->name = $row->term;
                      $term->ontology = $row->ontology;
                      $terms[] = $term;
                      $count++;
              }
              $dbr->freeResult( $res );

              $termObjects = array();
  } catch(Exception $e) {
              throw new WSFault("Receiver", "Unable to get ontology
terms: " . $e);
      }
  return array("terms" => $terms);
}

/**
 * Get a list of pathways tagged with a given ontology term
 * @param string $term The Ontology term
 * @return array of object WSPathwayInfo $pathways Array of pathway info objects
 **/
function getPathwaysByOntologyTerm($term) {
      try {
              $dbr = wfGetDB( DB_SLAVE );
              $res = $dbr->select(
                      'ontology',
                      array('*'),
                      array('term_id = ' . $dbr->addQuotes($term))
              );
              $objects = array();
              while($row = $dbr->fetchObject( $res )) {
                    $pathway = Pathway::newFromTitle($row->pw_id);
                    $objects[] = new WSPathwayInfo($pathway);
              }
              $dbr->freeResult( $res );
  } catch(Exception $e) {
              throw new WSFault("Receiver", "Unable to get Pathways: " . $e);
      }
      return array("pathways" => $objects);
}

/**
 * Get a list of pathways tagged with a ontology term which is the child of the given Ontology term
 * @param string $term The Ontology term
 * @return array of object WSPathwayInfo $pathways Array of pathway info objects
 **/
function getPathwaysByParentOntologyTerm($term) {
      try {
              $term = mysql_escape_string($term);
              $dbr = wfGetDB( DB_SLAVE );
              $query = "SELECT * FROM `ontology` " . "WHERE `term_path` LIKE '%$term%' ";
              $res = $dbr->query($query);
              $objects = array();
              while($row = $dbr->fetchObject( $res )) {
                    $pathway = Pathway::newFromTitle($row->pw_id);
                    $objects[] = new WSPathwayInfo($pathway);
              }
              $dbr->freeResult( $res );
  } catch(Exception $e) {
              throw new WSFault("Receiver", "Unable to get Pathways: " . $e);
      }
      return array("pathways" => $objects);
}

function formatXml($xml) {
	if(is_array($xml)) {
		return array_map(htmlentities, $xml);
	} else {
		return htmlentities($xml);
	}
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
class WSPathwayHistory extends WSPathwayInfo {
	public function __construct($pathway) {
		parent::__construct($pathway);
	}
	
	public function addRow($histRow) {
		$history[] = $histRow;
	}
	
	/**
	* @var array of object WSHistoryRow $history - The pathway history
	**/
	public $history = array();
}

 /**
 * @namespace http://www.wikipathways.org/webservice
 */
class WSHistoryRow {
	/**
	* @var string $revision - the revision number
	**/
	public $revision;
		/**
	* @var string $comment - the edit description
	**/
	public $comment;
	/**
	* @var string $revision - the username ofthe user that edited this revision
	**/
	public $user;
	/**
	* @var string $revision - the timestamp of this revision
	**/
	public $timestamp;
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
			$includeFields = $hit->getFieldNames();
		}
		$this->fields = array();
		foreach($includeFields as $fn) {
			if(in_array($fn, $hit->getFieldNames())) {
				$v = $hit->getFieldValues($fn);
				if($v && count($v) > 0) {
					$this->fields[$fn] = new WSIndexField($fn, $v);
				}
			}
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

/**
 * @namespace http://www.wikipathways.org/webservice
 **/
class WSCurationTag {
	public function __construct($metatag) {
		$this->name = $metatag->getName();
		$this->displayName = CurationTag::getDisplayName($this->name);
		$title = Title::newFromId($metatag->getPageId());
		if($title) {
			$this->pathway = new WSPathwayInfo(
				Pathway::newFromTitle($title)
			);
		}

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

 /**
 * @namespace http://www.wikipathways.org/webservice
 */
 class WSOntologyTerm {

      /**
       * @var string $ontology - the ontology to which the term belongs
       */
      public $ontology;
      /**
       * @var string $id - the ontology term identifier
       */
      public $id;

       /**
       * @var string $name - the ontology term name
       */
      public $name;
 }

 /**
 * @namespace http://www.wikipathways.org/webservice
 */
 class WSRelation {

	public function __construct($result) {
                if($result->pwId_1) {
                    $this->pathway1 = new WSPathwayInfo(
                            Pathway::newFromTitle($result->pwId_1)
                    );
		}
                if($result->pwId_2) {
                    $this->pathway2 = new WSPathwayInfo(
                            Pathway::newFromTitle($result->pwId_2)
                    );
		}
                $this->type = $result->type;
                $this->score = (float)$result->score;
	}

	/**
	 * @var object WSPathwayInfo $pathway1 for the first pathway
	 */
	public $pathway1;

	/**
	 * @var object WSPathwayInfo $pathway2 for the second pathway
	 */
	public $pathway2;

	/**
	 *@var string $type The type of the relation
	 */
	public $type;

	/**
	 *@var float $score The degree of relativeness(score) between the pair of pathways
	 */
	public $score;
}

?>
