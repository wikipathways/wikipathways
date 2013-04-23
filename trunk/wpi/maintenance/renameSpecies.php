<?php
/*
Renames the species names 'Human', 'Rat', 'Mouse' to the latin names
*/

require_once("Maintenance.php");

$convertSpecies = array(
	'Human' => 'Homo sapiens',
	'Mouse' => 'Mus musculus',
	'Rat' => 'Rattus norvegicus'
);

foreach(Pathway::getAllPathways() as $pathway) {
	$species = $pathway->species();
	if(array_key_exists($species, $convertSpecies)) {
		$title = $pathway->getTitleObject();
		echo("Processing " . $title->getFullText() . "<BR>\n");

		$newTitle = str_replace($species . ':', $convertSpecies[$species] . ':', $title->getFullText());

		echo "\tNew title: $newTitle<br>\n";
		if($doit) {
			$title->moveTo(Title::newFromText($newTitle), true, "Renaming species", true);
		}
	}
}
