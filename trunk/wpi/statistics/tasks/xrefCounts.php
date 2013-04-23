<?php

//Do not register, many requests to the bridgedb web service makes this
//script too slow.
//registerTask('xrefCounts', 'XrefCounts::run');

class XrefCounts {
	static function run($file, $times) {
		$datasources = array(
			"HMDB" => "Worm", //Use worm database for metabolites (is small, so faster)
			"Ensembl Human" => "Homo sapiens",
		);

		$unmappable = array(
			"HMDB" => array(
				"Entrez Gene", "Ensembl Human", "MGI",
				"SwissProt", "Ensembl", "RefSeq", "Other",
				"UniGene", "HUGO", "", "SGD", "RGD"
			),
			"Ensembl Human" => array(
				"ChEBI", "HMDB", "PubChem", "Other",
				"CAS", ""
			),
		);

		$datasourceCounts = array();

		$mappingCache = array();

		foreach($times as $tsCurr) {
			$date = date('Y/m/d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);

			$xrefsPerSpecies = array();

			$pathways = StatPathway::getSnapshot($tsCurr);
			foreach($pathways as $p) {
				$species = $p->getSpecies();
				$gpml = new SimpleXMLElement($p->getGpml());
				foreach($gpml->DataNode as $dn) {
					$id = (string)$dn->Xref['ID'];
					$system = (string)$dn->Xref['Database'];
					if(!$id || !$system) continue;
					$xrefsPerSpecies[$species][] = "$id||$system";
				}
			}
			foreach(array_keys($xrefsPerSpecies) as $s) {
				$xrefsPerSpecies[$s] = array_unique($xrefsPerSpecies[$s]);
			}

			$counts = array();
			foreach(array_keys($datasources) as $ds) {
				logger("Mapping $ds");

				if(!array_key_exists($ds, $mappingCache)) {
					$mappingCache[$ds] = array();
				}
				$myCache = $mappingCache[$ds];

				$mapped = array();
				$db = $datasources[$ds];

				$tomap = array();
				if(in_array($db, array_keys($xrefsPerSpecies))) {
					$tomap = $xrefsPerSpecies[$db];
				} else {
					foreach($xrefsPerSpecies as $x) $tomap += $x;
					$tomap = array_unique($tomap);
				}

				$i = 0;
				foreach($tomap as $x) {
					$idsys = explode('||', $x);
					$id = $idsys[0];
					$system = $idsys[1];
					if(in_array($system, $unmappable[$ds])) {
						continue;
					}
					if($system == $ds) {
						$mapped[] = $x;
						continue;
					}
					if(array_key_exists($x, $myCache)) {
						$mapped += $myCache[$x];
					} else {
						$xx = self::mapID($id, $system, $db, $ds);
						$myCache[$x] = $xx;
						$mapped += $xx;
					}
					$i += 1;
					if(($i % 10) == 0) logger("mapped $i out of " . count($tomap));
				}

				logger("Mapped: " . count($mapped));
				$counts[$ds] = count($mapped);
			}
			$datasourceCounts[$date] = $counts;
			logger(memory_get_usage() / 1000);
		}

		$fout = fopen($file, 'w');
		fwrite($fout, "date\t" .
			implode("\t", array_fill(0, count($datasources), "number")) . "\n");
		fwrite($fout, "Time\t" .
			implode("\t", array_keys($datasources)) . "\n");

		foreach(array_keys($datasourceCounts) as $date) {
			$values = $datasourceCounts[$date];
			fwrite($fout, $date . "\t" . implode("\t", $values) . "\n");
		}

		fclose($fout);
	}

	static function mapID($id, $system, $db, $ds) {
		global $wpiBridgeUrl;
		if(!$wpiBridgeUrl) $wpiBridgeUrl = 'http://webservice.bridgedb.org/';

		$mapped = array();

		if($db == "metabolites") $db = "Homo sapiens";
		$db_url = urlencode($db);
		$ds_url = urlencode($ds);
		$xd_url = urlencode($system);
		$xi_url = urlencode($id);
		$url =
			"$wpiBridgeUrl$db_url/xrefs/$xd_url/$xi_url?dataSource=$ds_url";
		logger("opening $url");
		$handle = fopen($url, "r");

		if ($handle) {
			while(!feof($handle)) {
				$line = fgets($handle);
				$cols = explode("\t", $line);
				if(count($cols) == 2) {
					$mapped[] = $cols[0] . ':' . $cols[1];
				}
			}
			fclose($handle);
		} else {
			logger("Error getting data from " . $url);
		}
		return $mapped;
	}
}
