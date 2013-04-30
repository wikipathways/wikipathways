<?php

set_include_path(get_include_path().PATH_SEPARATOR.realpath('../../includes').PATH_SEPARATOR.realpath('../../').PATH_SEPARATOR.realpath('../').PATH_SEPARATOR);
$dir = realpath(getcwd());
chdir("../../");
require_once ( 'WebStart.php');
require_once( 'Wiki.php' );
chdir($dir);

$userRun = $wgUser; #The user that runs this script

$user1 = User::newFromName("ThomasKelder");
$user2 = User::newFromName("TestUser");
$admin = User::newFromName("Thomas");
$anon = User::newFromId(0);

$page_id = Title::newFromText('WP4', NS_PATHWAY)->getArticleId();
$title = Title::newFromId($page_id);

$mgr = new PermissionManager($page_id);

Test::echoStart("No permissions set");

#Remove all permissions from page
$mgr->clearPermissions();

##* can read
$wgUser = $anon;
$can = $title->userCanRead();
Test::assert("anonymous can read", $can, true);

##* can't edit
$wgUser = $anon;
$can = $title->userCan('edit');
Test::assert("anonymous can't edit", $can, false);

##users can read/edit
$wgUser = $user1;
$can = $title->userCanRead();
Test::assert("Users can read", $can, true);
$can = $title->userCan('edit');
Test::assert("Users can edit", $can, true);

#Set private for user1
Test::echoStart("Setting page private for user1");
$pms = new PagePermissions($page_id);
$pms->addReadWrite($user1->getId());
$pms->addManage($user1->getId());
$mgr->setPermissions($pms);

##user1 can read/write/manage
$wgUser = $user1;
$can = $title->userCanRead();
Test::assert("User1 can read", $can, true);
$can = $title->userCan('edit');
Test::assert("User1 can edit", $can, true);
$can = $title->userCan(PermissionManager::$ACTION_MANAGE);
Test::assert("User1 can manage", $can, true);

##admin can read/write/manage
$wgUser = $admin;
$can = $title->userCanRead();
Test::assert("User1 can read", $can, true);
$can = $title->userCan('edit');
Test::assert("User1 can edit", $can, true);
$can = $title->userCan(PermissionManager::$ACTION_MANAGE);
Test::assert("User1 can manage", $can, true);

##user2 cannot read/write/manage
$wgUser = $user2;
$can = $title->userCanRead();
Test::assert("User2 can't read", $can, false);
$can = $title->userCan('edit');
Test::assert("User2 can't read", $can, false);
$can = $title->userCan(PermissionManager::$ACTION_MANAGE);
Test::assert("User2 can't manage", $can, false);

##anonymous cannot read/write/manage
$wgUser = $anon;
$can = $title->userCanRead();
Test::assert("Anonymous can't read", $can, false);
$can = $title->userCan('edit');
Test::assert("Anonymous can't edit", $can, false);


#Add user2
Test::echoStart("Add user2 to read/write users");
$wgUser = $userRun;
$pms->addReadWrite($user2->getId());
$mgr->setPermissions($pms);

##user2 can read/write/manage
$wgUser = $user2;
$can = $title->userCanRead();
Test::assert("User2 can read", $can, true);
$can = $title->userCan('edit');
Test::assert("User1 can edit", $can, true);

##user1 can still read/write/manage
$wgUser = $user1;
$can = $title->userCanRead();
Test::assert("User1 can still read", $can, true);
$can = $title->userCan('edit');
Test::assert("User1 can still edit", $can, true);

#Remove read/write permissions for user2
Test::echoStart("Setting page private for user1");
$pms->clearPermissions($user2->getId());
$mgr->setPermissions($pms);

##user1 can read/write/manage
$wgUser = $user1;
$can = $title->userCanRead();
Test::assert("User1 can read", $can, true);
$can = $title->userCan('edit');
Test::assert("User1 can edit", $can, true);

##user2 cannot read/write/manage
$wgUser = $user2;
$can = $title->userCanRead();
Test::assert("User2 can't read", $can, false);
$can = $title->userCan('edit');
Test::assert("User2 can't read", $can, false);

#Set expiration date to past
##permissions should be removed
Test::echoStart("Simulating expiration");
$wgUser = $userRun;
$pms->setExpires(wfTimestamp(TS_MW) - 1);
$mgr->setPermissions($pms);

##user2 can read/edit again
$wgUser = $user2;
$can = $title->userCanRead();
Test::assert("User2 can read again", $can, true);
$can = $title->userCan('edit');
Test::assert("User2 can edit again", $can, true);


class Test {
	static function assert($test, $value, $expected) {
		echo("$test: ");
		if($value != $expected) {
			$e = new Exception();
			echo("\t<font color='red'>Fail!</font>: value '$value' doesn't equal expected: '$expected'" .
				"<BR>\n" . $e->getTraceAsString() . "<BR>\n");
		} else {
			echo("\t<font color='green'>Pass!</font><BR>\n");
		}
	}

	static function echoStart($case) {
		echo("<h3>Testing $case</h3>\n");
	}
}
