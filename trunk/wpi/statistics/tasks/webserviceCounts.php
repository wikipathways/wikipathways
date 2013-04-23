<?php

registerTask('webserviceCounts', 'WebserviceCounts::run');

class WebserviceCounts {
	static function run($file, $times) {
		$ownIps = array(
			'/137\.120\.14\.[0-9]{1,3}/',
			'/137\.120\.89\.38/',
			'/137\.120\.89\.24/',
			'/137\.120\.17\.25/',
			'/137\.120\.17\.35/',
			'/137\.120\.17\.33/',
			'/169\.230\.76\.87/'
		);

		$dates = array();
		$own = array();
		$ext = array();

		$tsPrev = array_shift($times);
		foreach($times as $tsCurr) {
			$date = date('Y/m/d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);

			$ipCounts = StatWebservice::getCountsByIp($tsPrev, $tsCurr);
			$ownCount = 0;
			$extCount = 0;

			foreach(array_keys($ipCounts) as $ip) {
				$isOwn = false;
				foreach($ownIps as $r) {
					if(preg_match($r, $ip)) {
						$isOwn = true;
						break;
					}
				}
				if($isOwn) $ownCount += $ipCounts[$ip];
				else $extCount += $ipCounts[$ip];
			}

			$own[$date] = $ownCount;
			$ext[$date] = $extCount;
			$dates[] = $date;
			$tsPrev = $tsCurr;
		}

		$fout = fopen($file, 'w');
		fwrite($fout, "date\tnumber\tnumber\n");
		fwrite($fout, "Time\tExternal\tInternal\n");

		foreach($dates as $date) {
			$row = array(
				$date, $ext[$date], $own[$date]
			);
			fwrite($fout, implode("\t", $row) . "\n");
		}

		fclose($fout);
	}
}
