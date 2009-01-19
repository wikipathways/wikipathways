<?php
/* Abort if called from a web server */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	print "This script must be run from the command line\n";
	exit();
}
chdir("../");
require_once('wpi.php');
chdir(dirname(__FILE__));

/* Tables for web service logging */
echo "*** Creating tables for web service logging ***\n";

$dbw =& wfGetDB(DB_MASTER);
$dbw->immediateBegin();


$dbw->sourceFile(realpath('./wslog.sql'), false, 'printSql');

$dbw->immediateCommit();

//Create metatag index
$dbw->immediateBegin();

$dbw->query(
	"CREATE INDEX tag_name ON tag (tag_name)"
);
$dbw->query(
	"CREATE INDEX tag_page ON tag (page_id)"
);
$dbw->query(
	"CREATE INDEX taghist_name ON tag_history (tag_name)"
);
$dbw->query(
	"CREATE INDEX taghist_page ON tag_history (page_id)"
);
$dbw->immediateCommit();

//Modify metatag table
$dbw->immediateBegin();

$dbw->query(
	"ALTER TABLE tag_history ADD COLUMN text varchar(500)"
);

$dbw->immediateCommit();
?>
