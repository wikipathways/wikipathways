<?php
/*
Uploads pathways in from the path './upload/' to wikipathways
The subdirectory will the category the pathway has to be placed in, e.g.

pathway0.gpml 			will not be placed in any category
foo/pathway1.gpml 		will be placed in category 'foo'
*/

require_once("Maintenance.php");
$startdir = realpath(dirname(__FILE__)) . "/upload";
process($startdir);

function process($file, $category = '') {
	global $doit;
	
	echo("Processing " . $file . "<BR>\n");
	if(is_dir($file)) {
		$category = substr($file, strrpos($file, '/') + 1);
		foreach(scandir($file) as $dir) {
			//Skip . and ..
			if($dir != '.' && $dir != '..') {
				//Append top directory to categories
				process(realpath($file . '/' . $dir), $category);
			}
		}
	} else {
		$pathway = Pathway::newFromFileTitle(basename($file));
		echo("\tUploading pathway {$pathway->name()}<BR>\n");
		echo("\t\tSpecies: " . $pathway->species() . "<BR>\n");
		echo("\t\tCategory: $category<BR>\n");
		if($doit) {
//			uploadPathway($file, $pathway);
			$pathway->getCategoryHandler()->addToCategory($category);
		}
	}
}

function uploadPathway($file, $pathway) {
	$gpml = file_get_contents($file);
	$pathway->updatePathway($gpml, "Uploaded new pathway");
}

?>
