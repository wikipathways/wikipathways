<?php

$pullSite = "http://test.wikipathways.org/index.php";
$pullPages = "MediaWiki:PagesToPull";

#credit the extension
$wgExtensionCredits['other'][] = array(
	'name'=>'PullPages',
	'url'=>'http://www.mediawiki.org/wiki/Extension:PullPages',
	'author'=>'[[User:MarkAHershberger Mark A. Hershberger]]',
	'description'=>'Pull a selected list of on wiki pages from another wiki',
);

$wgSpecialPages['PullPages'] = "PullPages";
$wgSpecialPageGroups['PullPages'] = "pagetools";
$wgGroupPermissions['sysop']['pullpage'] = true;
$wgAvailableRights[] = 'pullpage';

$wgExtensionMessagesFiles['PullPages'] = dirname( __FILE__ ) . '/PullPages.i18n.php';
$wgAutoloadClasses['PullPages'] = dirname(__FILE__) . '/PullPages_class.php';
$wgAutoloadClasses['PagePuller'] = dirname(__FILE__) . '/PagePuller.php';
$wgExtensionFunctions[] = 'PullPages::initMsg';