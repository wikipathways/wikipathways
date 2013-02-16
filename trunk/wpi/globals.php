<?php
global $wgScriptPath;

//File types
define("FILETYPE_IMG", "svg");
define("FILETYPE_GPML", "gpml");
define("FILETYPE_MAPP", "mapp");
define("FILETYPE_PNG", "png");
define("FILETYPE_PDF", "pdf");
define("FILETYPE_PWF", "pwf");
define("FILETYPE_TXT", "txt");
define("FILETYPE_BIOPAX", "owl");

//Script info
$wpiPathName = 'wpi'; //pathname containing wpi script
$wpiTmpName = 'tmp'; //temp path name
$wpiCacheName = 'cache'; //cache path name
$wpiScriptFile = 'wpi.php';

$host = isset( $_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "wikipathways.org";
$wpiScriptPath = realpath(dirname(__FILE__));
$wpiScript = "$wpiScriptPath/$wpiScriptFile"; 
$wpiTmpPath = "$wpiScriptPath/$wpiTmpName";
$siteURL = "http://$host/$wgScriptPath";
$wpiURL = "$siteURL/$wpiPathName";
$wpiCachePath = "$wpiScriptPath/$wpiCacheName";

define("WPI_SCRIPT_PATH", $wpiScriptPath);
define("WPI_SCRIPT", $wpiScript);
define("WPI_TMP_PATH", $wpiTmpPath);
define("SITE_URL", $siteURL);
define("WPI_URL",  $wpiURL);
define("WPI_SCRIPT_URL", WPI_URL . '/' . $wpiScriptFile);
define("WPI_TMP_URL", WPI_URL . '/' . $wpiTmpName);
define("WPI_CACHE_PATH", $wpiCachePath);
define("WPI_CACHE_URL", WPI_URL . '/' . $wpiCacheName);

//JS info
define("JS_SRC_EDITAPPLET", $wgScriptPath . "/wpi/js/editapplet.js");
define("JS_SRC_RESIZE", $wgScriptPath . "/wpi/js/resize.js");
define("JS_SRC_PROTOTYPE", $wgScriptPath . "/wpi/js/prototype.js");

//Users
define("USER_MAINT_BOT", "MaintBot"); //User account for maintenance scripts

?>
