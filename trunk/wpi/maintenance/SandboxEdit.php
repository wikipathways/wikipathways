<?php
/**
 * Adds or updates the sandbox pathway (WP4) with a timestamp.
 * Can be used to test editing via the web service, or email notifications.
 */

/* Abort if called from a web server */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	print "This script must be run from the command line\n";
	exit();
}

if(count($_SERVER['argv']) < 3) {
	$help = <<<HELP
Wrong number of arguments, 2 or 3 expected. Usage:
	php SandboxEdit.php user pass [url]

	- user: The username of a WikiPathways bot account
	- pass: The password of a WikiPathways bot account
	- url: The url to the webservice WSDL. Defaults to http://www.wikipathways.org/wpi/webservice/webservice.php?wsdl
HELP;
	exit($help);
}

$sandbox = 'WP4';

$user = $_SERVER['argv'][1];
$pass = $_SERVER['argv'][2];
$url = 'http://www.wikipathways.org/wpi/webservice/webservice.php?wsdl';
if(count($_SERVER['argv']) > 3) $url = $_SERVER['argv'][3];

$client = new SoapClient($url);

echo("Logging in as $user\n");
$key = $client->login(array('name' => $user, 'pass' => $pass))->auth;

$pw = $client->getPathway(array('pwId' => $sandbox, 'revision' => 0))->pathway;
$rev = $pw->revision;
$gpml = $pw->gpml;

$ts = date('YmdHis');
$comment = "<Comment Source=\"BotTest\">$ts</Comment>";

if(preg_match('/Source="BotTest"/', $gpml)) {
	//Update timestamp
	$gpml = preg_replace('/<Comment Source="BotTest">[0-9]{14}/s',
		'<Comment Source="BotTest">' . $ts, $gpml);
} else {
	$gpml = preg_replace('/(<Pathway (?U).+)(<(Graphics|BiopaxRef))/s',
		"$1$comment\n$2", $gpml);
}

$client->updatePathway(array(
	'pwId' => $sandbox, 'description' => 'Bot edit test',
	'revision' => $rev, 'gpml' => $gpml,
	'auth' => array('key' => $key, 'user' => $user)
));
