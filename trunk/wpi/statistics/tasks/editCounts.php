<?php

registerTask('editCounts', 'EditCounts::run');

class EditCounts {
	static function run($file, $times) {
		//Number of edits in month, number of total edits
		//Exclude bot edits
		//Exclude test/tutorial edits

		$exclude = WikiPathwaysStatistics::getExcludeByTag();

		$botEdits = array();
		$testEdits = array();
		$realEdits = array();
		$botEditsInt = array();
		$testEditsInt = array();
		$realEditsInt = array();

		$tsPrev = array_shift($times);
		foreach($times as $tsCurr) {
			$date = date('Y/m/d', wfTimestamp(TS_UNIX, $tsCurr));
			logger($date);

			$users = StatUser::getSnapshot($tsCurr);

			$botCount = $testCount = $realCount = 0;
			$botCountInt = $testCountInt = $realCountInt = 0;

			foreach($users as $u) {
				$mwu = User::newFromId($u->getId());
				$bot = $mwu->isBot();

				$edits = $u->getPageEdits($tsCurr);
				$editsInt = $u->getPageEdits($tsCurr, $tsPrev);

				if($bot) {
					$botCount += count($edits);
					$botCountInt += count($editsInt);
				} else {
					//Remove test edits
					$rc = array_diff($edits, $exclude);
					$rcInt = array_diff($editsInt, $exclude);

					$testCount += count($edits) - count($rc);
					$testCountInt += count($editsInt) - count($rcInt);
					$realCount += count($rc);
					$realCountInt += count($rcInt);
				}
			}

			$botEdits[$date] = $botCount;
			$botEditsInt[$date] = $botCountInt;
			$testEdits[$date] = $testCount;
			$testEditsInt[$date] = $testCountInt;
			$realEdits[$date] = $realCount;
			$realEditsInt[$date] = $realCountInt;

			$tsPrev = $tsCurr;
		}

		$fout = fopen($file, 'w');
		fwrite($fout, "date\tnumber\tnumber\tnumber\tnumber\tnumber\tnumber\n");
		fwrite($fout, "Time\tUser edits\tUser edits in month\t" .
			"Test/tutorial edits\tTest/tutorial edits in month\t" .
			"Bot edits\tBot edits in month\n");

		foreach(array_keys($realEdits) as $date) {
			$row = array(
				$realEdits[$date], $realEditsInt[$date],
				$testEdits[$date], $testEditsInt[$date],
				$botEdits[$date], $botEditsInt[$date]
			);
			fwrite($fout, $date . "\t" . implode("\t", $row) . "\n");
		}

		fclose($fout);
	}
}
