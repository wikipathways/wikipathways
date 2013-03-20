<?php

## Does some preparations for all maintenance scripts

$dir = getcwd();
require_once(dirname(dirname(__FILE__)).'/wpi.php');
set_time_limit(0);

global $wgUser; // extra safe

//Do a dry run by default, only write database
//when called with doit=true!
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    $doit = isset( $_GET['doit'] ) && $_GET['doit'] == 'true';
    echo "<pre>\n";
    if(!($wgUser->getName() == USER_MAINT_BOT)) {
        echo "WRONG USER {$wgUser->getName()}! Please log in as " . USER_MAINT_BOT . "\n";
        exit();
    }
} else {
    $doit = false;
    $wgUser = User::newFromName( USER_MAINT_BOT );
    foreach($argv as $v) {
        if ( $v == "--force" || $v == "-f" ) {
            $doit = true;
        }
    }
}
if($doit) {
	echo "WRITE MODE\n";
} else {
	echo "DRY RUN\n";
}
