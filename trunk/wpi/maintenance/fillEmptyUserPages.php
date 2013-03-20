<?php

require_once("Maintenance.php");

function doUsage() {
?>
fillEmptyUserPages.php [options] [User:Name] [User:Name] ...

    --force - Actually fill the pages.
    --quiet - Do not print every name.
    --help  - This message.

This script fills out user's pages using the [[Template:UserPage]] and [[Template:UserTalkPage]].

Without --force, it just prints out what it is going to do.

Usernames can be specified on the command line.  If they are not then all of the wiki's users will be checked.

<?php
    exit;
}


$quiet = false;
$fetchUser = array();
if ( !isset( $_SERVER ) || !isset( $_SERVER['REQUEST_METHOD'] ) ) {
    foreach($argv as $v) {
        if( substr( $v, 0, 5 ) == "User:" ) {
            $fetchUser[] = substr( $v, 5 );
        }
        if( $v === "--help" ) {
            doUsage();
        }
        if( $v === "--quiet" ) {
            $quiet = true;
        }
    }
}

$dbr =& wfGetDB(DB_SLAVE);
if ( count( $fetchUser ) > 0 ) {
    $res = $dbr->select("user", array("user_id"), "user_name in ('". implode("','", $fetchUser) ."')");
} else {
    $res = $dbr->select("user", array("user_id"));
}
while($row = $dbr->fetchRow($res)) {
	try {
		$user = User::newFromId($row[0]);
                if( ! $quiet ) {
                    echo "Processing user: " . $user->getName() . "\n";
                }

		$userPageTitle = $user->getUserPage();
		$userTalkTitle = $user->getTalkPage();

		if(!$userPageTitle->exists()) {
			echo "\tNo user page, creating [[User:{$user->getName()}]] from template\n";
			if($doit) {
				$tempCall = "{{subst:Template:UserPage|{$user->getName()}|{$user->getRealName()}}}";

				$userPage = new Article($userPageTitle, 0);
				$succ = true;
				$succ =  $userPage->doEdit($tempCall, "Initial user page");
			}
		}
		if(!$userTalkTitle->exists()) {
			echo "\tNo user talk page, creating [[User_talk:{$user->getName()}]] from template<br>\n";
			if($doit) {
				$tempCall = "{{subst:Template:TalkPage|{$user->getName()}}}";

				$userPage = new Article($userTalkTitle, 0);
				$succ = true;
				$succ =  $userPage->doEdit($tempCall, "Initial user page");
			}
		}
	} catch(Exception $e) {
		echo "Exception: {$e->getMessage()}\n";
	}
}
