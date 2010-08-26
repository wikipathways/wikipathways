<?php
# to activate the extension, include it from your LocalSettings.php
# with: include("extensions/YourExtensionName.php");

$wgExtensionFunctions[] = "wfwebsiteFrame";
 
function wfwebsiteFrame() {
global $wgParser;
 
$wgParser->setHook( "websiteFrame", "websiteFrame" );
}
 
# the callback function for converting the input text to HTML output
function websiteFrame($input) {
# set default arguments
$allParams['height'] = 800;
$allParams['width'] = 800;
$allParams['scroll'] = "no";
$allParams['border'] = "0";
$allParams['name'] = "Page1";
$allParams['align'] = "middle";
 
 
# get input args
$aParams = explode("\n", $input); # ie 'website=http://www.whatever.com'
foreach($aParams as $sParam) {
$aParam = explode("=", $sParam, 2); # ie $aParam[0] = 'website' and $aParam[1] = 'http://www.whatever.com'
if( count( $aParam ) < 2 ) # no arguments passed
continue;
 
$sType = $aParam[0]; # ie 'website'
$sArg = $aParam[1]; # ie 'http://www.whatever.com'

switch ($sType) {
case 'website':
# clean up
$sType = trim($sType);
$sArg = trim($sArg);
$allParams['website'] = $sArg; # http://www.whatever.com
break;
case 'height':
# clean up
$sType = trim($sType);
$sArg = trim($sArg);
$allParams['height'] = $sArg; # 80
break;
case 'width':
# clean up
$sType = trim($sType);
$sArg = trim($sArg);
$allParams['width'] = $sArg; # 100
break;
 
case 'scroll':
# clean up
$sType = trim($sType);
$sArg = trim($sArg);
$allParams['scroll'] = $sArg; # yes
break;
 
case 'border':
# clean up
$sType = trim($sType);
$sArg = trim($sArg);
$allParams['border'] = $sArg; # yes
break;
 
case 'name':
# clean up
$sType = trim($sType);
$sArg = trim($sArg);
$allParams['name'] = $sArg; # my iFrame
break;
 
case 'align':
# clean up
$sType = trim($sType);
$sArg = trim($sArg);
$allParams['align'] = $sArg; # my iFrame
break;
 
 
}
}
 
# THIS SHOULD FIX XSS VULNERABILITY
foreach ( array_keys($allParams) as $key ) {
$allParams[$key] = htmlentities($allParams[$key], ENT_QUOTES);
}
# END FIX

# build output
$output = "<iframe src=\"".$allParams['website']."\" align=\"".$allParams['align']."\" name=\"".$allParams['name']."\" frameborder=\"".$allParams['border']."\" height=\"".$allParams['height']."\" scrolling=\"".$allParams['scroll']."\" width=\"".$allParams['width']."\"></iframe>";
 
# return the output
return $output;
 
}
?>

