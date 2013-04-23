<?php

registerTask('usageFrequencies', 'UsageFrequencies::run');

class UsageFrequencies {
	/**
	 * Frequency of number of views and number of edits.
	 */
	static function run($file, $times) {
		$tsCurr = array_pop($times);

		$viewCounts = array();
		$editCounts = array();

		$i = 0;
		$pathways = StatPathway::getSnapshot($tsCurr);
		$total = count($pathways);
		foreach($pathways as $p) {
			if(($i % 100) == 0) logger("Processing $i out of $total");
			$i++;

			array_push($viewCounts, $p->getNrViews());
			array_push($editCounts, $p->getNrRevisions());
		}

		$fout = fopen($file . ".edits", 'w');
		fwrite($fout, "string\tnumber\n");
		fwrite($fout, "Pathway rank (by number of edits)\tNumber of edits\n");

		WikiPathwaysStatistics::writeFrequencies($fout, $viewCounts);

		fclose($fout);

		$fout = fopen($file . ".views", 'w');
		fwrite($fout, "string\tnumber\n");
		fwrite($fout, "Pathway rank (by number of views)\tNumber of views\n");

		WikiPathwaysStatistics::writeFrequencies($fout, $viewCounts);

		fclose($fout);
	}
}
