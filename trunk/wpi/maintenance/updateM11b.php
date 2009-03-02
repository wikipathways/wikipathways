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
echo "*** Changing tag_text in tag to TEXT ***\n";

$dbw =& wfGetDB(DB_MASTER);
$dbw->immediateBegin();


$dbw->query("ALTER TABLE `tag` CHANGE `tag_text` `tag_text` TEXT NULL DEFAULT NULL");
$dbw->query("ALTER TABLE `tag_history` CHANGE `text` `text` TEXT NULL DEFAULT NULL");
$dbw->immediateCommit();
?>
