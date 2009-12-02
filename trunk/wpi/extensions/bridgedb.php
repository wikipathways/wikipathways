<?php
require_once('../../pass.php'); //Load user variables

/**
 * Simple proxy to support remote bridgedb web service calls
 * from javascript.
 */
if(!isset($wpiBridgeUrl)) $wpiBridgeUrl = 'http://webservice.bridgedb.org';

header('Content-type: text/plain');

if (preg_match('/bridgedb.php(\/.+)/', $_SERVER['REQUEST_URI'], $m)) {
	$url = $wpiBridgeUrl;
	if(strrpos($url, '/') == strlen($url) - 1) {
		$url = substr($url, 0, -1);
	}
	
	$url .= $m[1];
}

ini_set("error_reporting", 0);

$handle = fopen($url, "r");

if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        echo $buffer;
    }
    fclose($handle);
} else {
	header('HTTP/1.1 500 Internal Server Error', true, 500);
	echo("Error getting data from " . $url);
}

?>
