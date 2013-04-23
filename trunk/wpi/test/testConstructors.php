<?php

set_include_path(get_include_path().PATH_SEPARATOR.realpath('../../includes').PATH_SEPARATOR.realpath('../../').PATH_SEPARATOR.realpath('../').PATH_SEPARATOR);
$dir = realpath(getcwd());
chdir("../../");
require_once ( 'WebStart.php');
require_once( 'Wiki.php' );
chdir($dir);

/* Test newFromTitle with url */
$pathway = Pathway::newFromTitle("http://www.wikipathways.org/index.php?title=Pathway:WP1");
testConstructed($pathway);
echo("OK<BR>\n");

$pathway = Pathway::newFromTitle("http://www.wikipathways.org/index.php/Pathway:WP1");
testConstructed($pathway);
echo("OK<BR>\n");

$pathway = Pathway::newFromTitle("http://www.wikipathways.org/index.php/Pathway:WP112");
testConstructed($pathway);
echo("OK<BR>\n");

$pathway = Pathway::newFromTitle("Pathway:WP566");
testConstructed($pathway);
echo("OK<BR>\n");

$pathway = Pathway::newFromTitle("WP566");
testConstructed($pathway);
echo("OK<BR>\n");

try {
	$pathway = Pathway::newFromTitle("http://www.wikipathways.org/index.php/Pathway");
	//Should throw exception, fail if it end up here
	throw new Exception("Invalid pathway title accepted");
} catch(Exception $e) {
	echo("OK<BR>\n");
}

function testConstructed($pathway) {
	if(!$pathway->getName()) {
		var_dump($pathway);
		throw new Exception("Pathway doesn't exist");
	}
}
