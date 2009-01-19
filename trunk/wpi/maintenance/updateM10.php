<?php
/* Abort if called from a web server */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	print "This script must be run from the command line\n";
	exit();
}
chdir("../");
require_once('wpi.php');
chdir(dirname(__FILE__));

//Create new tables
require_once('createTables.php');

//Modify metatag table
$dbw =& wfGetDB(DB_MASTER);
$dbw->immediateBegin();

$dbw->query(
	"ALTER TABLE tag_history ADD COLUMN text varchar(500)"
);

$dbw->immediateCommit();


?>
