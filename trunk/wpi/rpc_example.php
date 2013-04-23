<?php
/*
This is an example of how to use the xml-rpc interface to
retrieve and modify pathway data. See:
http://phpxmlrpc.sourceforge.net/

This example uses the php-xmlrpc extension. You can also use
the xml-rpc interface from programming languages other than php

For an overview of the available methods, see:
http://www.wikipathways.org/wpi/wpi_rpc.php

Please test your scripts on the test site first:
http://137.120.14.24/wikipathways-test/wpi/wpi_rpc.php
*/

//Authentication data
$user = "YourUserName";
$pass = "YourPassword";

//Load XML-RCP libraries
require("includes/xmlrpc.inc");

//Setup a connection to the xml-rpc server (on the test site)
$c = new xmlrpc_client("/wikipathways-test/wpi/wpi_rpc.php", "137.120.14.24", 80);
$c->setDebug(0);
$c->setAcceptedCompression(null);

//Get all pathways
$m = new xmlrpcmsg("WikiPathways.getPathwayList",
	array()
);

$r = $c->send($m);
$v = getvalue($r);
echo "<H2>Fetched list of {$v->arraysize()} pathways:</H2>";
for($i = 0; $i < $v->arraysize(); $i++) {
	echo($v->arraymem($i)->scalarval() . "<BR>\n");
}

//Get an authentication token
$m = new xmlrpcmsg("WikiPathways.login",
	array(
		new xmlrpcval($user),
		new xmlrpcval($pass)
	)
);
$r = $c->send($m);
$v = getvalue($r);
$token = $v->scalarval();

echo "<H2>Fetched authentication code</H2><P>User: $user<P>Token: $token";


//Get the latest GPML and revision id
$m = new xmlrpcmsg("WikiPathways.getPathway",
	array(
		new xmlrpcval("Sandbox"),
		new xmlrpcval("Homo sapiens")
	)
);
$r = $c->send($m);
$v = getvalue($r);
$data = php_xmlrpc_decode($v);
$gpml = base64_decode($data['gpml']);
$revision = $data['revision'];

echo "<H2>Downloaded GPML</H2><P>Fetched GPML, revision $revision</P>";

//Make changes to the GPML
//E.g. change all colors to purple
//Using regex: /Color=".{6}"/Color="a020f0"
$gpmlMod = preg_replace('/Color=".{6}"/', 'Color="a020f0"', $gpml);
//Encode the gpml code for sending it along with the xml-rpc response
$gpmlMod64 = base64_encode($gpmlMod);

//Update the pathway
$m = new xmlrpcmsg("WikiPathways.updatePathway",
	array(
		new xmlrpcval("Sandbox"),
		new xmlrpcval("Homo sapiens"),
		new xmlrpcval("All colors to purple"),
		new xmlrpcval($gpmlMod64, "base64"),
		new xmlrpcval($revision, "int"),
		php_xmlrpc_encode(array("user" => $user, "token" => $token))
	)
);

$r = $c->send($m);
$v = getvalue($r);
if($v->scalarval()) {
	echo "<H2>GPML updated!</H2>";
} else {
	echo "<H2>Update failed!</H2>";
}

/**
 * Get the xml-rpc response value and while checking for errors and throw an exception
 * if an error is found
 **/
function getvalue($r) {
	if($r->faultCode()) {
		throw new Exception("xml-rpc error ({$r->faultCode()}): {$r->faultString()}");
	} else {
		return $r->value();
	}
}
