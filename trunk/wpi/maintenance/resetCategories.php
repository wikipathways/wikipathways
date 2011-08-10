<?php

require_once("Maintenance.php");

## Reset the MediaWiki categorylinks records for each pathway to contain only the GPML attributes
## And remove categories on images

# ini_set('memory_limit', '512M');

$pathways = Pathway::getAllPathways();


echo "Number of pathways: " . count($pathways) . "<BR>\n";

foreach($pathways as $pathway) {
	try {
		echo("PROCESSING: {$pathway->getTitleObject()->getFullText()}<BR>\n");
				
		if($doit) {		
			//Set pathway categories
			$pathway->getCategoryHandler()->setGpmlCategories();
		}
	} catch(Exception $e) {
		echo "ERROR: $e<BR>\n";
		continue;
	}
}

echo "DONE.<BR>\n";
?>
