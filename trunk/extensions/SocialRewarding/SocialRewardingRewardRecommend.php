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
 * Class containing all functions for social rewarding method "Recommender
 * System".
 */
class RewardRecommend extends Reward {

	/* private */ var $data;
	/* private */ var $recommend;
	/* public  */ var $recommendRevision;
	/* public  */ var $recommendArticle;
	/* public  */ var $recommendAuthor;
	/* public  */ var $interestedAuthor;


	/**
	 * Constructor - Load all data or restrict to a certain timeframe.
	 *
	 * @access public
	 * @param int $timeData Time restriction
	 */
	function RewardRecommend($timeData = false) {
		parent::Reward();
		if ($timeData == false) {
			$this->loadData();
		} else {
			$this->loadTimeData($timeData);
		}
		$this->prepareData();
	}


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "RewardRecommend";
	}


	/**
	 * Load needed data from database.
	 *
	 * @access private
	 */
	function loadData() {
		$key = "user_id";
		$val = "rev_id";

		extract($this->dbr->tableNames("revision"));

		$sql = "
			SELECT
				$key,
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["recommend"]) . ".$val
			FROM
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["recommend"]) . "
			";

		if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "" || $this->SocialRewarding["reward"]["endTimeInterval"]) {
			$sql .= "
					,
					$revision
				WHERE
					$revision.rev_id = " . $this->dbr->tableName($this->SocialRewarding["DB"]["recommend"]) . ".rev_id
			";
			if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "") {
				$sql .= " AND $revision.rev_timestamp >= '" . $this->SocialRewarding["reward"]["beginTimeInterval"] . "'";
			}
			if ($this->SocialRewarding["reward"]["endTimeInterval"] != "") {
				$sql .= " AND $revision.rev_timestamp <= '" . $this->SocialRewarding["reward"]["endTimeInterval"] . "'";
			}
		}


		$this->loadFromDB($sql, $this->data, $key, $val);
	}


	/**
	 * Retrieve data from database.
	 *
	 * @access private
	 * @param String $sql SQL query
	 * @param array &$var Variable to save results to
	 * @param int $key Unique key (like revision ID)
	 * @param String $val Database column name
	 */
	function loadFromDB($sql, &$var, $key, $val) {
		$rs = $this->dbr->query($sql);
		while($row = $this->dbr->fetchRow($rs)) {
			$count = $var[$row[$key]][$row[$val]] + 1;
			$var[$row[$key]][$row[$val]] = $count;
		}
	}


	/**
	 * Load data from database with time restriction.
	 *
	 * @access private
	 * @param int $time Time restriction
	 */
	function loadTimeData($time) {
		$key = "user_id";
		$val = "rev_id";
		$now = SocialRewardingConvertTimestamp(wfTimestampNow());

		$sql = "
			SELECT
				$key,
				$val
			FROM
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["recommend"]) . "
			WHERE
				timestamp > " . ($now - $time) . "
			";

		$this->loadFromDB($sql, $this->data, $key, $val);
	}


	/**
	 * Sort and reduce loaded data to value specified in the config
	 * file.
	 *
	 * @access private
	 */
	function prepareData() {
		if (is_array($this->data)) {
			foreach($this->data as $key => $val) {
				$this->sortArray($this->data[$key]);
				$this->reduceData($this->data[$key], $this->SocialRewarding["recommend"]["reduceData"]);
			}
		}
	}


	/**
	 * Reduce data to given value. Fastest method compatible with
	 * PHP 4 and 5 is activated.
	 *
	 * @access private
	 * @param array &$data List of values
	 * @param int $reduction Reduction level
	 */
	function reduceData(&$data, $reduction) {
		if (is_array($data)) {

			/* #1: PHP 4 & 5, but slow
			$i = 0;
			foreach($data as $key2 => $val2) {
				$i++;
				if ($i > $reduction) {
					unset($data[$key2]);
				}
			}
			*/

			/* #2: PHP 5 only, because PHP 4 will reset keys
			$data = array_slice($data, 0, $reduction, true);
			*/

			// #3: PHP 4 & 5
			$chunk = array_chunk($data, $reduction, true);
			$data = $chunk[0];

		}
	}


	/**
	 * Sort a given array in reverse order and maintain index
	 * association.
	 *
	 * @access private
	 * @param array &$data List of values
	 */
	function sortArray(&$data) {
		if (is_array($data)) {
			arsort($data);
		}
	}


	/**
	 * Exclude articles which user has visited. PHP 5 uses faster
	 * method which is not implemented in PHP 4.
	 *
	 * @access private
	 * @param array &$recommend Recommended list
	 * @param array &$data List of articles
	 */
	function excludeUsersVisitedArticles(&$recommend, $data) {
		if (is_array($recommend) && is_array($data)) {
			if (SocialRewardingGetPHPVersion() >= 5) {
				$recommend = array_diff_key($recommend, $data);
			} else {
				foreach ($data as $key => $val) {
					unset($recommend[$key]);
				}
			}
		}
	}


	/**
	 * Exclude articles from which the user is also author.
	 *
	 * @access private
	 * @param String $author User's name
	 * @param array &$recommend Recommended list
	 */
	function excludeUsersArticles($author, &$recommend) {
		if (is_array($recommend)) {
			foreach($recommend as $key => $val) {
				if (SocialRewardingGetAuthorFromRev($key) == $author) {
					unset($recommend[$key]);
				}
			}
		}
	}


	/**
	 * Load all recommended revisions for all authors.
	 *
	 * @access public
	 */
	function loadAllRecommendations() {
		if (!is_array($this->recommend) && is_array($this->data)) {
			foreach($this->data as $key => $val) {
					$this->recommend[$key] = $this->computeRecommendation($key);
			}
		}
	}


	/**
	 * Get recommended revisions for a given author's name. Check
	 * if recommendation was already calculated. If not calculate
	 * recommendation.
	 *
	 * @access public
	 * @param int $key Author's name
	 * @return array List of recommended revisions
	 */
	function getRecommendation($key) {
		if (is_array($this->recommend[$key])) {
			return $this->recommend[$key];
		} else {
			return $this->computeRecommendation($key);
		}
	}


	/**
	 * Compute recommended revisions for a given author's name.
	 *
	 * @access private
	 * @param int $key Author's name
	 * @return array List of recommended revisions
	 */
	function computeRecommendation($key) {
		// If there is data for this author
		if (is_array($this->data[$key])) {

			// For percentage calculation
			$sum = array_sum($this->data[$key]);

			// Loop over all authors
			foreach($this->data as $key2 => $val2) {

				// Author must not be the author for whom recommendations are computed
				if ($key != $key2) {
					$i = 1;

					// Loop over all revisions from the author
					foreach($this->data[$key] as $key3 => $val3) {
						$per = $val3 / $sum;

						// If revision exists in array of another author
						if (array_key_exists($key3, $this->data[$key2])) {

							// Loop over all revisions from the other author
							foreach($this->data[$key2] as $key4 => $val4) {

								// Which counting method
								$count = $this->getCount($val4);

								// Which weighting method
								$weight = $this->getWeight($i, $per);

								// Weighted counting of revision is saved
								$recommend[$key4] += $count * $weight;
							}
						}
						$i -= 1 / $this->SocialRewarding["recommend"]["reduceData"];
					}
				}
			}

			// Exclude author's visited articles
			if ($this->SocialRewarding["recommend"]["excludeVisitedArticles"] == true) {
				$this->excludeUsersVisitedArticles($recommend, $this->data[$key]);
			}

			// Exclude articles where user is author
			if ($this->SocialRewarding["recommend"]["excludeUsersArticles"] == true) {
				$this->excludeUsersArticles($key, $recommend);
			}

			// Sort array
			$this->sortArray($recommend);

			return $recommend;
		}
	}


	/**
	 * Should all revisions be counted equally or should 
	 * counting depending on visits of users?
	 *
	 * @access private
	 * @param int $val Hits or visits of user
	 * @return int Counting value
	 */
	function getCount($val) {
		if ($this->SocialRewarding["recommend"]["countingMethod"] == "equal") {
			$count = 1;
		} else {
			$count = $val;
		}
		return $count;
	}


	/**
	 * If weighting is activated then there are two methods:
	 * "equal" means that the graduation of weighted articles
	 * is always the same regardless of visits. "repr"esentative
	 * weights articles on the basis of their visits.
	 *
	 * @access private
	 * @param float $i Equal percentage evalue
	 * @param float $per Representative percentage value
	 * @return float Weighting factor
	 */
	function getWeight($i, $per) {
		if ($this->SocialRewarding["recommend"]["weighting"] == true && $this->SocialRewarding["recommend"]["weightingMethod"] == "equal") {
			$weight = $i;
		} else if ($this->SocialRewarding["recommend"]["weighting"] == true) {
			$weight = $per;
		} else {
			$weight = 1;
		}
		return $weight;
	}


	/**
	 * Get recommended revisions for a given author's name.
	 * The number of revisions displayed can be defined in
	 * the config file.
	 *
	 * @access public
	 * @param String $author Author's name
	 * @return array List of recommended revisions
	 */
	function getRecommendedRevisions($author) {
		if (is_array($this->recommendRevision[$author])) {
			return $this->recommendRevision[$author];
		}
		$recommend = $this->getRecommendation($author);
		$this->reduceData($recommend, $this->SocialRewarding["recommend"]["reduceRecommendation"]);
		return $recommend;
	}


	/**
	 * Get recommended articles for a given author's name.
	 * The number of articles displayed can be defined in
	 * the config file.
	 *
	 * @access public
	 * @param String $author Author's name
	 * @return array List of recommended articles
	 */
	function getRecommendedArticles($author) {
		if (is_array($this->recommendArticle[$author])) {
			return $this->recommendArticle[$author];
		}
		$recommend = $this->getRecommendation($author);
		if (is_array($recommend)) {
			foreach($recommend as $key => $val) {
				$recommendArticle[SocialRewardingGetArticleFromRev($key)] += $recommend[$key];
			}
			$this->sortArray($recommendArticle);
			$this->reduceData($recommendArticle, $this->SocialRewarding["recommend"]["reduceRecommendation"]);
			return $recommendArticle;
		}
	}


	/**
	 * Get recommended authors for a given author's name.
	 * The number of authors displayed can be defined in
	 * the config file.
	 *
	 * @access public
	 * @param String $author Author's name
	 * @return array List of recommended authors
	 */
	function getRecommendedAuthors($author) {
		if (is_array($this->recommendAuthor[$author])) {
			return $this->recommendAuthor[$author];
		}
		$recommend = $this->getRecommendation($author);
		if (is_array($recommend)) {
			foreach($recommend as $key => $val) {
				$recommendAuthor[SocialRewardingGetAuthorFromRev($key)] += $recommend[$key];
			}
			$this->sortArray($recommendAuthor);
			$this->reduceData($recommendAuthor, $this->SocialRewarding["recommend"]["reduceRecommendation"]);
			return $recommendAuthor;
		}
	}


	/**
	 * Get authors with same interests for a given author's name.
	 * The number of authors displayed can be defined in the
	 * config file.
	 *
	 * @access public
	 * @param String $author Author's name
	 * @return array List of authors with same interests
	 */
	function getSameInterestedAuthors($author) {
		if (is_array($this->interestedAuthor[$author])) {
			return $this->interestedAuthor[$author];
		}
		$this->loadAllRecommendations();
		if (is_array($this->recommend[$author])) {

			// For percentage calculation
			$sum = array_sum($this->recommend[$author]);
			$i = 1;

			// Loop over all revisions of author
			foreach($this->recommend[$author] as $key => $val) {
				$per = $val / $sum;

				// Loop over all authors
				foreach($this->recommend as $key2 => $val2) {

					// If revision exists in list of revisions of the other author
					if (is_array($val2) && array_key_exists($key, $val2) && $author != $key2) {

						// Loop over all revisions of the other author
						foreach($this->recommend[$key2] as $key3 => $val3) {

							// Which counting method
							$count = $this->getCount($val3);

							// Which weighting method
							$weight = $this->getWeight($i, $per);

							// Take other author's name as key and increase weighted counting
							$interestedAuthor[$key2] += $count * $weight;
						}
					}
				}
				$i -= 1 / $this->SocialRewarding["recommend"]["reduceData"];
			}
			$this->sortArray($interestedAuthor);
			$this->reduceData($interestedAuthor, $this->SocialRewarding["recommend"]["reduceRecommendation"]);
			return $interestedAuthor;
		}
	}



	/**
	 * Load all recommended revisions for all authors.
	 *
	 * @access public
	 */
	function loadRecommendedRevisionsData() {
		$this->loadAllRecommendations();
		if (is_array($this->recommend) && !is_array($this->recommendRevision)) {
			foreach($this->recommend as $key => $val) {
				$this->recommendRevision[$key] = $this->getRecommendedRevisions($key);
			}
		}
	}


	/**
	 * Load all recommended articles for all authors.
	 *
	 * @access public
	 */
	function loadRecommendedArticlesData() {
		$this->loadAllRecommendations();
		if (is_array($this->recommend) && !is_array($this->recommendArticle)) {
			foreach($this->recommend as $key => $val) {
				$this->recommendArticle[$key] = $this->getRecommendedArticles($key);
			}
		}
	}


	/**
	 * Load all recommended authors for all authors.
	 *
	 * @access public
	 */
	function loadRecommendedAuthorsData() {
		$this->loadAllRecommendations();
		if (is_array($this->recommend) && !is_array($this->recommendAuthor)) {
			foreach($this->recommend as $key => $val) {
				$this->recommendAuthor[$key] = $this->getRecommendedAuthors($key);
			}
		}
	}


	/**
	 * Load all authors with same interests for all authors.
	 *
	 * @access public
	 */
	function loadInterestedAuthorsData() {
		$this->loadAllRecommendations();
		if (is_array($this->recommend) && !is_array($this->interestedAuthor)) {
			foreach($this->recommend as $key => $val) {
				$this->interestedAuthor[$key] = $this->getSameInterestedAuthors($key);
			}
		}
	}


	/**
	 * Load all data (recommended revisions, articles and
	 * authors and authors with same interests) for all
	 * authors.
	 *
	 * @access public
	 */
	function loadAllData() {
		$this->loadAllRecommendations();
		if (is_array($this->recommend) && (!is_array($this->recommendRevision) || !is_array($this->recommendArticle) || !is_array($this->recommendAuthor) || !is_array($this->interestedAuthor))) {
			foreach($this->recommend as $key => $val) {
				$this->recommendRevision[$key] = $this->getRecommendedRevisions($key);
				$this->recommendArticle[$key] = $this->getRecommendedArticles($key);
				$this->recommendAuthor[$key] = $this->getRecommendedAuthors($key);
				$this->interestedAuthor[$key] = $this->getSameInterestedAuthors($key);
			}
		}
	}


}


?>