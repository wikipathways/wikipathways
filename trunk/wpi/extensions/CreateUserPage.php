<?php
require_once('wpi/wpi.php');

//Register a hook that creates an initial
//user page every time a new user registers
$wgHooks['AddNewAccount'][] = 'wfCreateUserPage';

function wfCreateUserPage($user, $byEmail = false) {
	//Create user page
	$tempCall = "{{subst:Template:UserPage|{$user->getName()}|{$user->getRealName()}}}";
	$title = $user->getUserPage();
	$userPage = new Article($title, 0);
	$succ = true;
	$succ =  $userPage->doEdit($tempCall, "Initial user page");
	
	//Create talk page
	$tempCall = "{{subst:Template:TalkPage|{$user->getName()}}}";
	$title = $user->getTalkPage();
	$userPage = new Article($title, 0);
	$succ = true;
	$succ =  $userPage->doEdit($tempCall, "Initial user page");
	
	return true;
}
