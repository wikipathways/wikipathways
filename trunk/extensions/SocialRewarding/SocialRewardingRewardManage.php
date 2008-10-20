<?php

# Copyright (C) 2007 Bernhard Hoisl <berni@hoisl.com>
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or 
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
# http://www.gnu.org/copyleft/gpl.html


/**
 * @package MediaWiki
 * @subpackage extensions
 * @subsubpackage SocialRewarding
 */



/**
 * Class for handling cache administration.
 */
class ManageReward {

	/* private */ var $SocialRewarding;
	/* private */ var $dbr;
	/* private */ var $table;
	/* private */ var $executionTime;


	/**
	 * Constructor
	 *
	 * @access public
	 */
	function ManageReward() {
		global $SocialRewarding;
		$this->SocialRewarding = $SocialRewarding;
		$this->dbr =& wfGetDB(DB_SLAVE);
		$this->table = $this->dbr->tableName($this->SocialRewarding["DB"]["cache"]);
	}


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "ManageReward";
	}


	/**
	 * Return either a new instance of Reward() or, if caching is
	 * enabled, try to load existing instance from cache.
	 *
	 * @access public
	 * @return Reward Reward instance
	 */
	function getReward() {
		$time_start = SocialRewardingMicrotime();

		if ($this->SocialRewarding["reward"]["cache"] == true) {
			$reward = $this->cacheMethod();
		} else {
			$reward = new Reward();
		}

		$time_end = SocialRewardingMicrotime();

		// Execution time of calculation in seconds (as float)
		$this->executionTime = $time_end - $time_start;

		return $reward;
	}


	/**
	 * Returns execution time of whole calculation in seconds.
	 *
	 * @access public
	 * @return float Execution time
	 */
	function getExecutionTime() {
		return $this->executionTime;
	}


	/**
	 * Calls defined function regarding to caching method set "db" or
	 * "file" (in config file).
	 *
	 * @access private
	 * @return Reward Reward instance
	 */
	function cacheMethod() {
		if ($this->SocialRewarding["reward"]["cacheMethod"] == "db") {
			$reward = $this->cacheRewardDB();
		} else {
			$reward = $this->cacheRewardFile();
		}
		return $reward;
	}



	/**
	 * If no caching file exists create one. Otherwise look at file
	 * modification date. If modification date is not exceeded
	 * regarding to caching timeout read file else create new one.
	 *
	 * @access private
	 * @return Reward Reward instance
	 */
	function cacheRewardFile() {
		if (!file_exists($this->SocialRewarding["reward"]["extensionPath"] . "/" . $this->SocialRewarding["reward"]["cacheFile"])) {
			$reward = $this->writeDataFile();
		} else {
			$time = filemtime($this->SocialRewarding["reward"]["extensionPath"] . "/" . $this->SocialRewarding["reward"]["cacheFile"]);
			$expires = SocialRewardingNow() - $this->SocialRewarding["reward"]["cacheTime"];
			if ($expires > $time) {
				$reward = $this->writeDataFile();
			} else {
				$reward = $this->readDataFile();
			}
		}
		return $reward;
	}


	/**
	 * Load new Reward() and initializes all data (loading data for
	 * "Recommender System" can be enabled separately). Serialize
	 * loaded data (generate a storable representation) and write it
	 * to specified caching file.
	 *
	 * @access private
	 * @return Reward Reward instance
	 */
	function writeDataFile() {
		$reward = new Reward();
		$reward->loadAllData();
		if ($this->SocialRewarding["reward"]["cacheRecommend"] == true) {
			$reward->loadAllDataRecommend();
		}
		$data = serialize($reward);
		$fp = @fopen($this->SocialRewarding["reward"]["extensionPath"] . "/" . $this->SocialRewarding["reward"]["cacheFile"], "w");
		fputs($fp, $data);
		fclose($fp);
		return $reward;
	}


	/**
	 * Read and unserialize data (creates a PHP value from a stored
	 * representation) from caching file.
	 *
	 * @access private
	 * @return Reward Reward instance
	 */
	function readDataFile() {
		// File() and implode() has same effect as file_get_contents()
		// $data = implode("", @file($this->SocialRewarding["reward"]["extensionPath"] . "/" . $this->SocialRewarding["reward"]["cacheFile"]));
		// Reads entire file into a string
		$data = @file_get_contents($this->SocialRewarding["reward"]["extensionPath"] . "/" . $this->SocialRewarding["reward"]["cacheFile"]);
		$reward = unserialize($data);
		return $reward;
	}




	/**
	 * If cached version in database is not out-of-time, unserialize
	 * it, else write new version in database.
	 *
	 * @access private
	 * @return Reward Reward instance
	 */
	function cacheRewardDB() {
		$sReward = $this->readDataDB();
		if ($sReward) {
			$reward = $sReward;
		} else {
			$reward = $this->writeDataDB();
		}
		return $reward;
	}


	/**
	 * Load new Reward() and initializes all data (loading data for
	 * "Recommender System" can be enabled separately). Serialize
	 * loaded data (generate a storable representation) and store it
	 * in database. ' characters must be escaped to execute query.
	 *
	 * @access private
	 * @return Reward Reward instance
	 */
	function writeDataDB() {
		$dbw =& wfGetDB(DB_MASTER);
		$reward = new Reward();
		$reward->loadAllData();
		if ($this->SocialRewarding["reward"]["cacheRecommend"] == true) {
			$reward->loadAllDataRecommend();
		}
		$data = serialize($reward);
		$data = str_replace("'", "\'", $data);
		$now = SocialRewardingConvertTimestamp(wfTimestampNow());
		$dbw->query("INSERT INTO " . $this->table . " (timestamp, data) VALUES ('$now', '$data')");
		return $reward;
	}


	/**
	 * Read and unserialize data (creates a PHP value from a stored
	 * representation) from database if cached version is not
	 * out-of-date.
	 *
	 * @access private
	 * @return Reward Reward instance
	 */
	function readDataDB() {
		$dbr =& wfGetDB(DB_SLAVE);
		$expires = SocialRewardingConvertTimestamp(wfTimestampNow()) - $this->SocialRewarding["reward"]["cacheTime"];
		$rs = $dbr->query("SELECT data FROM " . $this->table . " WHERE timestamp > $expires ORDER BY timestamp DESC");
		$num = $dbr->numRows($rs);
		if ($num > 0) {
			$row = $dbr->fetchRow($rs);
			$reward = str_replace("\'", "'", $row[0]);
			$reward = unserialize($reward);
		}
		return $reward;
	}


}

?>