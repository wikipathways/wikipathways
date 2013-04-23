<?php

registerTask('contentFrequencies', 'ContentFrequencies::run');

class ContentFrequencies {
	/**
	 * Frequencies of several pathway statistics:
	 * - xrefs
	 * - literature references
	 * - linked lines (interactions)
	 */
	static function run($file, $times) {
			$tsCurr = array_pop($times);

			$xrefCounts = array();
			$litCounts = array();
			$intCounts = array();

			$i = 0;
			$pathways = StatPathway::getSnapshot($tsCurr);
			$total = count($pathways);
			foreach($pathways as $p) {
				if(($i % 100) == 0) logger("Processing $i out of $total");
				$i++;

				$wp = new Pathway($p->getPwId());
				if(!$wp->isReadable()) continue;

				$wp->setActiveRevision($p->getRevision());
				$data = new PathwayData($wp);

				$xc = count($data->getUniqueXrefs());
				$lc = count($data->getPublicationXRefs());
				$ic = count($data->getInteractions());
				array_push($xrefCounts, $xc);
				array_push($litCounts, $lc);
				array_push($intCounts, $ic);
			}

			$fout = fopen("$file.xrefs", 'w');
			fwrite($fout, "string\tnumber\n");
			fwrite($fout, "Pathway rank (by number of xrefs)\tNumber of xrefs\n");
			WikiPathwaysStatistics::writeFrequencies($fout, $xrefCounts);
			fclose($fout);

			$fout = fopen("$file.lit", 'w');
			fwrite($fout, "string\tnumber\n");
			fwrite($fout, "Pathway rank (by number of literature references)\tNumber of literature references\n");
			WikiPathwaysStatistics::writeFrequencies($fout, $litCounts);
			fclose($fout);

			$fout = fopen("$file.int", 'w');
			fwrite($fout, "string\tnumber\n");
			fwrite($fout, "Pathway rank (by number of connected lines)\tNumber of connected lines\n");
			WikiPathwaysStatistics::writeFrequencies($fout, $intCounts);
			fclose($fout);
	}
}
