<?php

registerTask('userFrequencies', 'UserFrequencies::run');

class UserFrequencies {
	static function run($file, $times) {
		$last = array_pop($times);
		$exclude = WikiPathwaysStatistics::getExcludeByTag();
		$users = StatUser::getSnapshot($last);

		$editCounts = array();
		foreach($users as $u) {
			$mwu = User::newFromId($u->getId());
			if($mwu->isBot()) continue; //Skip bots

			$all = $u->getPageEdits();
			$edits = array_diff($all, $exclude);
			if(count($edits) > 0) $editCounts[$u->getName()] = count($edits);
		}

		$fout = fopen($file, 'w');
		fwrite($fout, "string\tstring\tnumber\n");
		fwrite($fout, "User\tUser rank\tNumber of edits\n");
		WikiPathwaysStatistics::writeFrequencies($fout, $editCounts, true);

		fclose($fout);
	}
}
