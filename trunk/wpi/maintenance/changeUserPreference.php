<?php

require_once("Maintenance.php");

//Iterate over users
$dbr =& wfGetDB(DB_SLAVE);

$res = $dbr->query("SELECT user_id FROM user");

$np = $dbr->numRows( $res );
$i = 0;
while( $row = $dbr->fetchRow( $res )) {
	$user = User::newFromId($row[0]);
	if(!$user->isAnon()) {
		echo("Processing user: " . $user->getName() . "<BR>\n");
		if($doit) {
			$user->setOption('enotifwatchlistpages', 1);
			$user->saveSettings();
		}
	}
}

?>
