<?php
/* Abort if called from a web server */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	print "This script must be run from the command line\n";
	exit();
}

chdir("../");
require_once('wpi.php');
chdir(dirname(__FILE__));

function printUsage() {
	echo("Usage: php addWatchlistByTag.php {username} {tag}\n");
	echo("\t-username: The user for which you want to add pathways to the watchlist\n");
	echo("\t-tag: The tag that defines the pathway set you want to add to the watchlist (e.g. Curation:FeaturedPathway).\n");
	exit();
}

$user = $argv[1];
if(!$user) {
	echo("Please specify a user name as first argument!\n");
	printUsage();
}

$tag = $argv[2];
if(!$tag) {
	echo("Please specify a tag as second argument!\n");
	printUsage();
}

$user = User::newFromName($user);
$pages = CurationTag::getPagesForTag($tag);
echo("Adding " . count($pages) . " pathways to watchlist of user " . $user->getName() . "\n");

$dbw =& wfGetDB(DB_MASTER);
$dbw->immediateBegin();
foreach($pages as $p) {
	$title = Title::newFromId($p);
	echo("\tAdding " . $title->getFullText() . "\n");
	$user->addWatch($title);
	$user->addWatch($title->getTalkPage()); //Add the talk page as well
}
$dbw->immediateCommit();
?>
