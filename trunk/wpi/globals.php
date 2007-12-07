<?php
global $wgScriptPath;

//File types
define("FILETYPE_IMG", "svg");
define("FILETYPE_GPML", "gpml");
define("FILETYPE_MAPP", "mapp");
define("FILETYPE_PNG", "png");

//Script info
$wpiPathName = 'wpi'; //pathname containing wpi script
$wpiTmpName = 'tmp'; //temp path name
$wpiScriptFile = 'wpi.php';

$wpiScriptPath = realpath(dirname(__FILE__));
$wpiScript = "$wpiScriptPath/$wpiScriptFile"; 
$wpiTmpPath = "$wpiScriptPath/$wpiTmpName";
$siteURL = "http://{$_SERVER['HTTP_HOST']}/$wgScriptPath";
$wpiURL = "$siteURL/$wpiPathName";

define("WPI_SCRIPT_PATH", $wpiScriptPath);
define("WPI_SCRIPT", realpath($wpiScriptFile));
define("WPI_TMP_PATH", realpath($wpiTmpPath));
define("SITE_URL", $siteURL);
define("WPI_URL",  $wpiURL);
define("WPI_SCRIPT_URL", WPI_URL . '/' . $wpiScriptFile);
define("WPI_TMP_URL", WPI_URL . '/' . $wpiPathName . '/' . $wpiTmpName);

//JS info
define("JS_SRC_EDITAPPLET", $wgScriptPath . "/wpi/js/editapplet.js");
define("JS_SRC_RESIZE", $wgScriptPath . "/wpi/js/resize.js");
define("JS_SRC_PROTOTYPE", $wgScriptPath . "/wpi/js/prototype.js");

//Users
define("USER_MAINT_BOT", "MaintBot"); //User account for maintenance scripts

?>
