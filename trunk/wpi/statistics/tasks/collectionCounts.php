<?php

registerTask('collectionCounts', 'CollectionCounts::run');

class CollectionCounts {
		static function run($file, $times) {
		$collectionCounts = array();
		$collections = array(
			"Curation:FeaturedPathway",
			"Curation:AnalysisCollection",
			"Curation:CIRM_Related",
			"Curation:Wikipedia",
			"Curation:Reactome_Approved"
		);

		foreach($times as $tsCurr) {
			$date = date('Y/m/d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);

			$snapshot = StatTag::getSnapshot($tsCurr, $collections);
			$pathways = StatPathway::getSnapshot($tsCurr);

			//Remove tags on deleted pages
			$existPages = array();
			foreach($pathways as $p) $existPages[] = $p->getPageId();
			$removeTags = array();
			foreach($snapshot as $t) if(!in_array($t->getPageId(), $existPages)) {
				$removeTags[] = $t;
			}
			$snapshot = array_diff($snapshot, $removeTags);

			$counts = array();
			foreach($collections as $c) $counts[$c] = 0;
			foreach($snapshot as $tag) {
				$type = $tag->getType();
				if(array_key_exists($type, $counts)) {
					$counts[$type] = $counts[$type] + 1;
				}
			}
			$collectionCounts[$date] = $counts;
		}

		$collectionNames = array();
		foreach($collections as $c) {
			$collectionNames[] = CurationTag::getDisplayName($c);
		}

		$fout = fopen($file, 'w');
		fwrite($fout, "date\t" .
			implode("\t", array_fill(0, count($collections), "number")) . "\n");
		fwrite($fout, "Time\t" .
			implode("\t", $collectionNames) . "\n");

		foreach(array_keys($collectionCounts) as $date) {
			$values = $collectionCounts[$date];
			fwrite($fout, $date . "\t" . implode("\t", $values) . "\n");
		}

		fclose($fout);
	}
}
