<?php

registerTask('pathwayCounts', 'PathwayCounts::run');

class PathwayCounts {
	public static function run($file, $times) {
		$exclude = WikiPathwaysStatistics::getExcludeByTag();

		$allSpecies = array();
		$allCounts = array();

		foreach($times as $tsCurr) {
			$date = date('Y/m/d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date . ":\t", "");
			$snapshot = StatPathway::getSnapshot($tsCurr);
			logger(count($snapshot));

			$total = 0;
			$counts = array();
			foreach($snapshot as $p) {
				if(in_array($p->getPageId(), $exclude)) continue;
				$s = $p->getSpecies();
				$allSpecies[$s] = 1;
				if(array_key_exists($s, $counts)) {
					$counts[$s] = $counts[$s] + 1;
				} else {
					$counts[$s] = 1;
				}
				$total += 1;
			}
			$counts['All species'] = $total;
			$allCounts[$date] = $counts;
		}

		unset($allSpecies['undefined']);
		$allSpecies = array_keys($allSpecies);
		sort($allSpecies);
		array_unshift($allSpecies, "All species");

		$fout = fopen($file, 'w');
		fwrite($fout, "date\t" .
			implode("\t", array_fill(0, count($allSpecies), "number")) . "\n");
		fwrite($fout, "Time\t" .
			implode("\t", $allSpecies) . "\n");

		foreach(array_keys($allCounts) as $date) {
			$counts = $allCounts[$date];
			$values = array();
			foreach($allSpecies as $s) {
				$v = 0;
				if(array_key_exists($s, $counts)) $v = $counts[$s];
				$values[] = $v;
			}
			fwrite($fout, $date . "\t" . implode("\t", $values) . "\n");
		}

		fclose($fout);
	}
}
