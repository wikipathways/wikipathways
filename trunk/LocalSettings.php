<?php

# This file was automatically generated by the MediaWiki installer.
# If you make manual changes, please keep track in case you need to
# recreate them later.
#
# See includes/DefaultSettings.php for all configurable settings
# and their default values, but don't forget to make changes in _this_
# file, not there.

# If you customize your file layout, set $IP to the directory that contains
# the other MediaWiki files. It will be used as a base to locate files.
if( defined( 'MW_INSTALL_PATH' ) ) {
	$IP = MW_INSTALL_PATH;
} else {
	$IP = dirname( __FILE__ );
}

$path = array( $IP, "$IP/includes", "$IP/languages" );
set_include_path( implode( PATH_SEPARATOR, $path ) . PATH_SEPARATOR . get_include_path() );

require_once( "includes/DefaultSettings.php" );

# If PHP's memory limit is very low, some operations may fail.
ini_set( 'memory_limit', '128M' );
# Increase max shell memory for inkscape conversion of large pathways
$wgMaxShellMemory = 512 * 1024;

if ( $wgCommandLineMode ) {
	if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
		die( "This script must be run from the command line\n" );
	}
} elseif ( empty( $wgNoOutputBuffer ) ) {
	## Compress output if the browser supports it
	if( !ini_get( 'zlib.output_compression' ) ) @ob_start( 'ob_gzhandler' );
}

$wgSitename         = "WikiPathways";

## The URL base path to the directory containing the wiki;
## defaults for all runtime URL paths are based off of this.
$wgScriptPath       = '';

## For more information on customizing the URLs please see:
## http://www.mediawiki.org/wiki/Manual:Short_URL

$wgEnableEmail      = true;
$wgEnableUserEmail  = true;

$wgEmergencyContact = "webmaster@localhost";
$wgPasswordSender = "webmaster@localhost";

## Turn of httponly cookies, otherwise the applet will not
## have access to the authentication cookies
$wgCookieHttpOnly = false;

## For a detailed description of the following switches see
## http://meta.wikimedia.org/Enotif and http://meta.wikimedia.org/Eauthent
## There are many more options for fine tuning available see
## /includes/DefaultSettings.php
## UPO means: this is also a user preference option
$wgEnotifUserTalk = true; # UPO
$wgEnotifWatchlist = true; # UPO
$wgEmailAuthentication = true;

$wgDBtype           = "mysql";
$wgDBserver         = "localhost";
$wgDBport           = "5432";
$wgDBprefix         = "";

if(!$wpiJavascriptSnippets) $wpiJavascriptSnippets = array();
if(!$wpiJavascriptSources) $wpiJavascriptSources = array();

# Load organism registry
require_once('wpi/Organism.php');
# Load passwords/usernames
require('pass.php');
# Load globals
require_once('wpi/globals.php');

# Default javascript locations
if(!isset($jsJQuery)) $jsJQuery = "$wgScriptPath/wpi/js/jquery/jquery-1.5.1.js";
if(!isset($jsJQueryUI)) $jsJQueryUI = "$wgScriptPath/wpi/js/jquery-ui/jquery-ui-1.8.10.custom.min.js";
if(!isset($cssJQueryUI)) $cssJQueryUI = "$wgScriptPath/wpi/js/jquery-ui/jquery-ui-1.8.10.custom.css";
if(!isset($jsSvgWeb)) $jsSvgWeb = "$wgScriptPath/wpi/js/svgweb/svg-uncompressed.js\" data-path=\"$wgScriptPath/wpi/js/svgweb";
$jsRequireJQuery = false; //Only load jquery when required by extension

# Schemas for Postgres
$wgDBmwschema       = "mediawiki";
$wgDBts2schema      = "public";

# Experimental charset support for MySQL 4.1/5.0.
$wgDBmysql5 = false;

## Shared memory settings
$wgMainCacheType = CACHE_NONE;
$wgMemCachedServers = array();

## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
$wgEnableUploads       = true;
$wgUploadPath = $wgScriptPath."/img_auth.php";

##Extensions
$wgUseImageResize      = true;
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";

## If you want to use image uploads under safe mode,
## create the directories images/archive, images/thumb and
## images/temp, and make them all writable. Then uncomment
## this, if it's not already uncommented:
# $wgHashedUploadDirectory = false;

## User ID for UserLoginLog extension
$wgServerUser = 101;

## Enable AJAX
$wgUseAjax = true;

## If you have the appropriate support software installed
## you can enable inline LaTeX equations:
$wgUseTeX           = false;

$wgLocalInterwiki   = $wgSitename;

$wgLanguageCode = "en";

$wgProxyKey = "b748562511ea57982358c30cec614e30b52b75119e3df78ac554eec5f69343cf";

## Default skin: you can change the default skin. Use the internal symbolic
## names, ie 'standard', 'nostalgia', 'cologneblue', 'monobook':
$wgDefaultSkin = 'wikipathways';

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.
# $wgEnableCreativeCommonsRdf = true;
$wgRightsPage = "WikiPathways:License_Terms"; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl = "http://creativecommons.org/licenses/by/3.0/";
$wgRightsText = "our license terms";
$wgRightsIcon = "http://i.creativecommons.org/l/by/3.0/88x31.png";
# $wgRightsCode = ""; # Not yet used

$wgDiff3 = "/usr/bin/diff3";

# When you make changes to this configuration file, this will make
# sure that cached pages are cleared.
$configdate = gmdate( 'YmdHis', @filemtime( __FILE__ ) );
$wgCacheEpoch = max( $wgCacheEpoch, $configdate );

$wgGroupPermissions['user']['edit'] = true;
$wgGroupPermissions['user']['createtalk'] = true;

$wgGroupPermissions['*'    ]['createaccount']   = true;

//Disable read for all users, this will be handled by the private pathways extension
//$wgGroupPermissions['*'    ]['read']            = true;

$wgGroupPermissions['*'    ]['edit']            = false;
$wgGroupPermissions['*'    ]['createpage']      = false;
$wgGroupPermissions['*'    ]['createtalk']      = false;

#Non-defaults:

#Allow slow parser functions ({{PAGESINNS:ns}})
$wgAllowSlowParserFunctions = true;

#Logo
$wgLogo = "http://www.wikipathways.org/skins/common/images/earth-or-pathway_text3_beta.png";

#Allow gpml extension and larger image files
$wgFileExtensions = array( 'png', 'gif', 'jpg', 'jpeg', 'svg', 'gpml', 'mapp');
$wgUploadSizeWarning = 1024 * 1024 * 5;

## Better SVG converter
/** Pick one of the above */
$wgSVGConverter = 'inkscape';
$wgSVGConverters['inkscape'] = '$path/inkscape -z -b white -w $width -f $input -e $output';

##$wgMimeDetectorCommand = "file -bi"; #This doesn't work for svg!!!
##$wgCheckFileExtensions = false;

# Allow direct linking to external images (so we don't have to upload them to the wiki)
$wgAllowExternalImages = true;

# Ontology data

# Ontologies in JSON format for use in the Javascript
# Format : ["<Ontology Name>", <Ontology Id>, <Version Id>]
$wgOntologiesJSON = '[' . '["Pathway Ontology","PW:0000001",1035,46237]' . ',' . '["Disease","DOID:4",1009,46309]' . ',' . '["Cell Type","CL:0000000",1006,46163]]';
# Ontologies Array to be used in the PHP Code
$wgOntologiesArray = json_decode($wgOntologiesJSON);
# Email address for the User Identification parameter to be used while making REST calls to BioPortal
$wgOntologiesBioPortalEmail =  "apico@gladstone.ucsf.edu";
# Maximum number of search results returned while searching BioPortal
$wgOntologiesBioPortalSearchHits =  12;
# Time after which data in the cache is refreshed (in Seconds)
$wgOntologiesExpiryTime = 60*60*24*7;

##Custom namespaces
define("NS_PATHWAY", 102); //NS_PATHWAY is same as NS_GPML since refactoring
define("NS_PATHWAY_TALK", 103);
define("NS_GPML", 102);
define("NS_GPML_TALK", 103);
define("NS_WISHLIST", 104);
define("NS_WISHLIST_TALK", 105);
define("NS_PORTAL", 106);
define("NS_PORTAL_TALK", 107);

$wgExtraNamespaces =
	array(	NS_PATHWAY => "Pathway", NS_PATHWAY_TALK => "Pathway_Talk",
			100 => "Pw_Old", 101 => "Pw_Old_Talk", //Old namespace
			NS_WISHLIST => "Wishlist", NS_WISHLIST_TALK => "Wishlist_Talk",
			NS_PORTAL => "Portal", NS_PORTAL_TALK => "Portal_Talk"
		);
$wgNamespacesToBeSearchedDefault += 
	array( 	NS_PATHWAY => true, NS_PATHWAY_TALK => true,
			100 => false, 100 => false); //Old namespace
$wgContentNamespaces += array(NS_PATHWAY, NS_PATHWAY_TALK);

//AP20080328 - setting permissions for custom namespaces
$wgGroupPermissions[ '*' ][ 'ns102_read'] = true;
$wgGroupPermissions[ 'user' ][ 'ns102_edit'] = true;
$wgGroupPermissions[ 'user' ][ 'ns102_create'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns102_move'] = true;
$wgGroupPermissions[ 'sysop' ][ 'ns102_delete'] = true;
$wgGroupPermissions[ '*' ][ 'ns103_read'] = true;
$wgGroupPermissions[ 'user' ][ 'ns103_edit'] = true;
$wgGroupPermissions[ 'user' ][ 'ns103_create'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns103_move'] = true;
$wgGroupPermissions[ 'sysop' ][ 'ns103_delete'] = true;
$wgGroupPermissions[ '*' ][ 'ns104_read'] = true;
$wgGroupPermissions[ 'user' ][ 'ns104_edit'] = true;
$wgGroupPermissions[ 'user' ][ 'ns104_create'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns104_move'] = true;
$wgGroupPermissions[ 'sysop' ][ 'ns104_delete'] = true;
$wgGroupPermissions[ '*' ][ 'ns105_read'] = true;
$wgGroupPermissions[ 'user' ][ 'ns105_edit'] = true;
$wgGroupPermissions[ 'user' ][ 'ns105_create'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns105_move'] = true;
$wgGroupPermissions[ 'sysop' ][ 'ns105_delete'] = true;
$wgGroupPermissions[ '*' ][ 'ns106_read'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns106_edit'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns106_create'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns106_move'] = true;
$wgGroupPermissions[ 'sysop' ][ 'ns106_delete'] = true;
$wgGroupPermissions[ '*' ][ 'ns107_read'] = true;
$wgGroupPermissions[ 'user' ][ 'ns107_edit'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns107_create'] = true;
$wgGroupPermissions[ 'bureaucrat' ][ 'ns107_move'] = true;
$wgGroupPermissions[ 'sysop' ][ 'ns107_delete'] = true;
$wgGroupPermissions['usersnoop'   ]['usersnoop'] = true;
$wgGroupPermissions['sysop'       ]['usersnoop'] = true;
$wgGroupPermissions['bureaucrat'  ]['usersnoop'] = true;
$wgGroupPermissions['sysop']['list_private_pathways'] = true;
$wgGroupPermissions['webservice']['webservice_write'] = true;

//AP20071027 
# Reject user creation from specific domains
function abortOnBadDomain($user, $message) {

  global $wgRequest;
  $email = $wgRequest->getText( 'wpEmail' );
  $emailSplitList = split("@", $email, 2);
  if ( $emailSplitList[1] == "mail.ru" ||
       $emailSplitList[1] == "list.ru" ) {
    $message = "Your e-mail domain has been blocked";
    return false;
  }
  return true;
}

$wgHooks['AbortNewAccount'][] = 'abortOnBadDomain';

##Debug
$wgDebugLogFile = WPI_SCRIPT_PATH . '/tmp/wikipathwaysdebug.txt';
//$wgProfiling = true; //Set to true for debugging info

##Extensions
require_once('extensions/GoogleAnalytics/googleAnalytics.php'); //Google Analytics support
require_once('extensions/inputbox.php');
require_once('extensions/GoogleGroups.php');
//require_once('extensions/ParserFunctions.php');
//require_once('wpi/extensions/redirectImage.php'); //Redirect all image pages to file
require_once('wpi/extensions/PathwayOfTheDay.php');
require_once('wpi/extensions/siteStats.php');
require_once('wpi/extensions/pathwayInfo.php');
require_once('wpi/extensions/imageSize.php');
require_once('wpi/extensions/magicWords.php');
require_once('extensions/EmbedVideo.php');
require_once('wpi/extensions/PopularPathwaysPage2/PopularPathwaysPage.php');
require_once('wpi/extensions/MostEditedPathwaysPage/MostEditedPathwaysPage.php');
require_once('wpi/extensions/NewPathwaysPage/NewPathwaysPage.php');
require_once('wpi/extensions/CreatePathwayPage/CreatePathwayPage.php');
require_once('wpi/extensions/pathwayHistory.php');
require_once('wpi/extensions/DynamicPageList2.php');
require_once('wpi/extensions/LabeledSectionTransclusion/compat.php');
require_once('wpi/extensions/LabeledSectionTransclusion/lst.php');
require_once('wpi/extensions/LabeledSectionTransclusion/lsth.php');
require_once('wpi/extensions/SearchPathways/SearchPathways.php');
require_once('wpi/extensions/SearchPathways/searchPathwaysBox.php');
require_once('wpi/extensions/button.php');
require_once('wpi/extensions/pathwayThumb.php');
require_once('wpi/extensions/imageLink.php');
require_once('wpi/extensions/BrowsePathwaysPage2/BrowsePathwaysPage.php');
require_once('wpi/extensions/editApplet.php');
require_once('wpi/extensions/listPathways.php');
require_once('wpi/extensions/movePathway.php');
require_once('wpi/extensions/deletePathway.php');
require_once('wpi/batchDownload.php');
require_once('wpi/PathwayPage.php');
require_once('wpi/extensions/SpecialWishList/SpecialWishList.php');
require_once('wpi/extensions/SpecialWishList/TopWishes.php');
require_once('wpi/extensions/DiffAppletPage/DiffAppletPage.php');
require_once('wpi/extensions/RecentPathwayChanges/RecentPathwayChanges.php');
require_once('wpi/extensions/ParserFunctions/ParserFunctions.php' );
require_once('wpi/extensions/NamespacePermissions.php' );
require_once('wpi/extensions/CheckGpmlOnSave.php' );
require_once('wpi/extensions/CreateUserPage.php' );
require_once('wpi/extensions/CurationTags/CurationTags.php');
require_once('wpi/extensions/UserSnoop.php');
require_once('wpi/extensions/AuthorInfo/AuthorInfo.php');
require_once('wpi/extensions/CurationTags/SpecialCurationTags/SpecialCurationTags.php');
require_once('wpi/extensions/UserLoginLog/UserLoginLog.php');
require_once('extensions/LiquidThreads/LiquidThreads.php');
require_once('extensions/SocialRewarding/SocialRewarding.php');
require_once('wpi/extensions/DeletePathway/DeletePathway.php');
require_once('wpi/extensions/ShowError/ShowError.php');
require_once('wpi/extensions/pathwayParserFunctions.php');
require_once('extensions/UserMerge/UserMerge.php');
require_once('extensions/parseViewRedirect.php');
require_once('wpi/extensions/PrivatePathways/PrivatePathways.php' );
require_once('wpi/extensions/PrivatePathways/ListPrivatePathways.php' );
require_once('wpi/extensions/PrivatePathways/PrivateContributions.php' );
require_once('wpi/extensions/recentChangesBox.php');
require_once('wpi/extensions/pathwayBibliography.php');
require_once('wpi/extensions/otag/otags_main.php');
require_once('wpi/extensions/ontologyindex/ontologyindex.php');
require_once('wpi/extensions/PathwayViewer/PathwayViewer.php');
require_once('wpi/extensions/StubManager/StubManager.php');
require_once('wpi/extensions/ParserFunctionsHelper/ParserFunctionsHelper.php');
require_once('wpi/extensions/SecureHTML/SecureHTML.php');
require_once('wpi/extensions/RSS/rss.php');
require_once('wpi/extensions/Relations/Relations.php');
require_once('wpi/extensions/XrefPanel.php');
require_once('wpi/statistics/StatisticsHook.php');
require_once( "extensions/ConfirmEdit/ConfirmEdit.php" );
require_once('wpi/extensions/PageEditor/PageEditor.php');

require_once( "extensions/ConfirmEdit/FancyCaptcha.php" );
$wgCaptchaClass = 'FancyCaptcha';
$wgCaptchaDirectory = "captcha";

//Load captcha keyphrase
require("pass.php");

require_once( "wpi/extensions/ContributionScores/ContributionScores.php" );
$contribScoreIgnoreBots = true;  //Set to true if you want to exclude Bots from the reporting - Can be omitted.
 
//Each array defines a report - 7,50 is "past 7 days" and "LIMIT 50" - Can be omitted.
$contribScoreReports = array(
    array(7,50),
    array(30,50),
    array(0,50));

/* Biblio extension
Isbndb account: thomas.kelder@bigcat.unimaas.nl / BigC0w~wiki
*/
$isbndb_access_key = 'BR5539IJ'; 
require_once('extensions/Biblio.php');

//Interwiki extension
require_once('wpi/extensions/Interwiki/SpecialInterwiki.php');
$wgGroupPermissions['*']['interwiki'] = false;
$wgGroupPermissions['sysop']['interwiki'] = true;

//UserMerge settings
$wgGroupPermissions['bureaucrat']['usermerge'] = true;

//Google analytics settings (key should be in pass.php)
$wgGoogleAnalyticsIgnoreSysops = false;

//Set enotif for watch page changes to true by default
$wgDefaultUserOptions ['enotifwatchlistpages'] = 1;

##Cascading Style Sheets
#Default is {$wgScriptPath}/skins

$wgShowExceptionDetails = true;
$wgShowSQLErrors = true;

$wgReadOnlyFile = "readonly.enable";

//Increase recent changes retention time
$wgRCMaxAge = 60 * 24 * 3600;

//Lastly, include javascripts (that may have been added by other extensions)
require_once('wpi/Javascript.php');
?>
