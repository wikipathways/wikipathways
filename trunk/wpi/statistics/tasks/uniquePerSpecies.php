<?php

registerTask('uniquePerSpecies', 'UniquePerSpecies::run');

class UniquePerSpecies {
	static function run($file, $times) {
		$last = array_pop($times);

		//Create histogram of in how many species each pathway title occurs
		$speciesPerTitle = array();
		$pathways = StatPathway::getSnapshot($last);
		foreach($pathways as $p) {
			$wp = new Pathway($p->getPwId());
			$name = $wp->getName();
			$species = $wp->getSpecies();

			if(array_key_exists($name, $speciesPerTitle)) {
				$speciesPerTitle[$name][$species] = 1;
			} else {
				$speciesPerTitle[$name] = array($species => 1);
			}
		}

		$countsPerTitle = array();
		foreach(array_keys($speciesPerTitle) as $name) {
			$countsPerTitle[$name] = count(array_keys($speciesPerTitle[$name]));
		}

		$hist = array();
		for($i = min($countsPerTitle); $i <= max($countsPerTitle); $i++) $hist[$i] = 0;

		foreach(array_keys($countsPerTitle) as $name) {
			$number = $countsPerTitle[$name];
			$hist[$number] = $hist[$number] + 1;
		}

		//Export historgram
		$fout = fopen($file, 'w');
		fwrite($fout, "string\tnumber\n");
		fwrite($fout, "Number of species\tNumber of pathway titles\n");

		foreach(array_keys($hist) as $number) {
			$row = array(
				$number, $hist[$number]
			);
			fwrite($fout, implode("\t", $row) . "\n");
		}

		fclose($fout);

		//Export individual titles and species
		$fout = fopen($file . '.titles', 'w');
		fwrite($fout, "string\tstring\tnumber\n");
		fwrite($fout, "Pathway title\tPresent in species\tNumber of species\n");

		foreach(array_keys($speciesPerTitle) as $title) {
			$species = array_keys($speciesPerTitle[$title]);
			sort($species);

			$row = array(
				$title, implode(", ", $species), $countsPerTitle[$title]
			);
			fwrite($fout, implode("\t", $row) . "\n");
		}

		fclose($fout);
	}
}
