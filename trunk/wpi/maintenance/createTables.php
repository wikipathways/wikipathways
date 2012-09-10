<?php

/* Abort if called from a web server */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	print "This script must be run from the command line\n";
	exit();
}

chdir("../");
require_once('wpi.php');
chdir(dirname(__FILE__));

/* Tables for MetaTags */
echo "*** Creating table for metatags ***\n";

$dbw =& wfGetDB(DB_MASTER);
$dbw->immediateBegin();


$dbw->sourceFile(realpath('./metatags.sql'), false, 'printSql');
$dbw->sourceFile(realpath('./usersnoop.sql'), false, 'printSql');

$dbw->immediateCommit();

/* Tables for LiquidThreads */
echo "*** Creating tables for LiquidThreads ***\n";

$dbw =& wfGetDB(DB_MASTER);
$dbw->immediateBegin();


$dbw->sourceFile(realpath('./lqt.sql'), false, 'printSql');

$dbw->immediateCommit();

/* Tables for SocialRewarding */
echo "*** Creating tables for SocialRewarding ***\n";

$dbw =& wfGetDB(DB_MASTER);
$dbw->immediateBegin();


$dbw->sourceFile(realpath('./SocialRewardingTables.sql'), false, 'printSql');

$dbw->immediateCommit();

/* Tables for web service logging */
echo "*** Creating tables for web service logging ***\n";

$dbw =& wfGetDB(DB_MASTER);
$dbw->immediateBegin();


$dbw->sourceFile(realpath('./wslog.sql'), false, 'printSql');

$dbw->immediateCommit();

function printSql($txt) {
	echo "SQL> $txt\n";
}
?>
