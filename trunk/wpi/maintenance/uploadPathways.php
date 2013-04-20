<?php
/*
Uploads pathways in from the path './upload/' to wikipathways
The subdirectory will the category the pathway has to be placed in, e.g.

pathway0.gpml 			will not be placed in any category
foo/pathway1.gpml 		will be placed in category 'foo'

Run this script as user 'MaintBot' and with parameter 'doit=true' in the url to upload the pathways.
If this parameter is not present, the script will do a 'dry run' and only list
the pathways but don't actually upload them.
*/

require_once("Maintenance.php");
$startdir = realpath(dirname(__FILE__)) . "/upload";
process($startdir);

function process($file, $category = '') {
	global $startdir;
	global $doit;

	echo("Processing " . $file . "<BR>\n");
	if(is_dir($file)) {
		foreach(scandir($file) as $dir) {
			//Skip . and ..
			if($dir != '.' && $dir != '..') {
				//Append top directory to categories
				process(realpath($file . '/' . $dir), $category);
			}
		}
	} else {
		$nm = basename($file);
		echo("\tUploading pathway $nm<BR>\n");
		if( $category !== '' ) {
			echo "\t\t(Ignoring category)<br>\n";
		}
		if($doit) {
			$pathway = uploadPathway($file, $pathway);
			echo("\t\=> Success, uploaded to {$pathway->getIdentifier()}<BR>\n");
		}
	}
}

function uploadPathway($file, $pathway) {
	$gpml = file_get_contents($file);
	return Pathway::createNewPathway($gpml);
}
