<?php

require_once("Maintenance.php");

## Reset the MediaWiki categorylinks records for each pathway to contain only the GPML attributes
## And remove categories on images

$pathways = Pathway::getAllPathways();

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
?>
