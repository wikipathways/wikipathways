<?php

require_once("Maintenance.php");

## Replace all underscores in categories with spaces
$pathways = Pathway::getAllPathways();

foreach($pathways as $pathway) {
	$species = $pathway->species();
	$name = $pathway->name();

	$gpml = $pathway->getGPML();
	$gpmlNew = str_replace('_', ' ', $gpml);
	if($gpmlNew !== $gpml) {
		echo "Updating $name ($species)<br>\n";
		if($doit) {
			$pathway->updatePathway($gpmlNew, "fixed category names");
		}
	} else {
		echo "No update needed for $name ($species)<br>\n";
	}
}
