<?php

registerTask('userCounts', 'UserCounts::run');

class UserCounts {
	static function run($file, $times) {
		$registered = array();
		$everActive = array();
		$intervalActive = array();

		$tsPrev = array_shift($times);
		foreach($times as $tsCurr) {
			$date = date('Y/m/d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);

			$users = StatUser::getSnapshot($tsCurr);

			$everCount = 0;
			$intervalCount = 0;

			$minEdits = 1;
			foreach($users as $u) {
				if(count($u->getPageEdits($tsCurr)) >= $minEdits)
					$everCount++;
				if(count($u->getPageEdits($tsCurr, $tsPrev)) >= $minEdits)
					$intervalCount++;
			}

			$everActive[$date] = $everCount;
			$intervalActive[$date] = $intervalCount;
			$registered[$date] = count($users) - $everCount;

			$tsPrev = $tsCurr;
		}

		$fout = fopen($file, 'w');
		fwrite($fout, "date\tnumber\tnumber\tnumber\n");
		fwrite($fout, "Time\tRegistered users\tEditing users\tEdited in month\n");

		foreach(array_keys($registered) as $date) {
			$row = array(
				$registered[$date], $everActive[$date], $intervalActive[$date]
			);
			fwrite($fout, $date . "\t" . implode("\t", $row) . "\n");
		}

		fclose($fout);
	}
}
