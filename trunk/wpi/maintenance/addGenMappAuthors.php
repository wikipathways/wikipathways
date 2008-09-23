<?php
/**
 * Script to add the authors in the GPML author field
 * to the MediaWiki history.
 * To run the script, first specify the $pass variable
 * (which is the password that will be used for accounts to
 * create).
 */
$mapFile = "author_mappings.txt";
$pass = '';

if(!$pass) {
	print("Please set a password first\n");
	exit();
}

/* Abort if called from a web server */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	print "This script must be run from the command line\n";
	exit();
}

chdir("../");
require_once('wpi.php');
chdir(dirname(__FILE__));

println("**** Reading author mapping file '" . $mapFile . "' ****");

$lines = file($mapFile);
$map = array();

foreach ($lines as $line_num => $line) {
	$cols = explode("\t", $line);
	$field = $cols[0];
	$ex_usr = explode(";", $cols[1]);
	$nw_usr = explode(";", $cols[2]);
	$nw_nm = explode(";", $cols[3]);
	
	$authors = array();
	foreach($ex_usr as $u) {
		if($u) {
			$authors[$u] = new GMAuthor(null, trim($u));
		}
	}
	$i = 0;
	foreach($nw_usr as $u) {
		if($u) {
			$authors[$u] = new GMAuthor(trim($nw_nm[$i]), trim($u));
		}
		$i++;
	}
	
	$map[$field] = $authors;
}

foreach(Pathway::getAllPathways() as $pathway) {
	println("* Processing " . $pathway->name() . " | " . $pathway->species());
	$rev = $pathway->getFirstRevision();
	$first_user = User::newFromId($rev->getUser());
	if(!$first_user || $first_user->isAnon() || $first_user->isBot()) {
		println("\tFirst revision user is bot/anonymous");
		//Get the GPML author
		$pd = $pathway->getPathwayData();
		$gpml = $pd->getGpml();
		$author = $gpml["Author"];
		if($author) {
			println("\tGPML author found: " . $author);
			$gmAuthors = $map[(string)$author];
			$first = true;
			foreach($gmAuthors as $gma) {
				if(!$gma->exists()) {
					println("\tAuthor " . $gma->real_name . " doesn't exist, creating account");
					$gma->create();
				}
				if($first) {
					$first = false;
					addFirstAuthor($pathway, $gma);
				} else {
					addAuthor($pathway, $gma);
				}
			}
		} else {
			println("\tSkipping, no GPML author found");
		}
	} else {
		println("\tSkipping, first revision not by bot or anonymous user: " . $first_user->getName());
	}
}



function addAuthor($pathway, $gma) {
	$dbw =& wfGetDB(DB_MASTER);
	$dbw->immediateBegin();
	
	println("\tAdding author " . $gma->real_name);

	$uid = $gma->getUser()->getId();
	$rev = $pathway->getFirstRevision()->getId();
	$pid = $pathway->getTitleObject()->getArticleId();
	
	//Check if user contributed already
	$dbr =& wfGetDB(DB_SLAVE);
	
	$query = "SELECT rev_id FROM revision WHERE rev_page = '$pid' AND rev_user = '$uid'";
	
	$res = $dbr->query($query);
	while($row = $dbr->fetchObject( $res )) {
		println("\t\tSkipping: author already contributed to this pathway");
		return;
	}
	
	$query = 
		"INSERT INTO revision (rev_page, rev_text_id, rev_comment, rev_user, " .
		"rev_user_text, rev_timestamp, rev_minor_edit, rev_deleted, rev_len, rev_parent_id) " .
		"SELECT rev_page, rev_text_id, rev_comment, $uid, '{$gma->user_name}', rev_timestamp, " .
		"rev_minor_edit, rev_deleted, rev_len, rev_parent_id " .
		"FROM revision WHERE rev_id = $rev";
	$dbw->query($query);
	$dbw->immediateCommit();
}

function addFirstAuthor($pathway, $gma) {
	$dbw =& wfGetDB(DB_MASTER);
	$dbw->immediateBegin();
	
	println("\tAdding first author " . $gma->real_name);
	//Replace user id in first revision
	$uid = $gma->getUser()->getId();
	$rev = $pathway->getFirstRevision()->getId();
	
	$dbw->query(
		"UPDATE revision " .
		"SET rev_user = " . $uid . ", rev_user_text ='" . 
		$gma->user_name . "' " .
		" WHERE rev_id = " . $rev
	);
	
	$dbw->immediateCommit();
}

class GMAuthor {
	public $real_name;
	public $user_name;
	
	function __construct($real_name, $user_name) {
		$this->real_name = $real_name;
		$this->user_name = $user_name;
	}
	
	function exists() {
		$user = $this->getUser();
		return $user && $user->getId() != 0;
	}
	
	function getUser() {
		return User::newFromName($this->user_name);
	}
	
	function create() {
		global $pass;
		$user = User::createNew($this->user_name, array(
			"password" => $pass,
			"real_name" => $this->real_name
		));
		wfRunHooks( 'AddNewAccount', array( $user ) );
	}
}

function println($str) {
	echo($str . "\n");
}
?>
