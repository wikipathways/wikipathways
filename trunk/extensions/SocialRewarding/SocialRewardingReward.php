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
 * Main class for computing points for all three social rewarding methods
 * and displaying stars and sparklines.
 */
class Reward {

	/* private */ var $SocialRewarding;
	/* private */ var $dbr;
	/* private */ var $revisionTimestamp;
	/* private */ var $revisionSize;
	/* private */ var $revisionArticle;
	/* private */ var $revisionAuthor;
	/* public  */ var $rewardReferences;
	/* public  */ var $rewardRating;
	/* public  */ var $rewardViewed;
	/* private */ var $timeSizeWeight;
	/* private */ var $methodsWeight;
	/* private */ var $articlePoints;
	/* private */ var $timeChangeSum;
	/* private */ var $sizeChangeSum;
	/* private */ var $rewardingMethod;
	/* private */ var $rewardingMethodsCount;
	/* private */ var $firstTimestamp;
	/* private */ var $lastTimestamp;
	/* private */ var $articleRevision;
	/* private */ var $articleAuthor;
	/* public  */ var $authors;
	/* private */ var $starsScale;
	/* private */ var $sparklines;
	/* public  */ var $recommend;


	/**
	 * Constructor
	 *
	 * @access public
	 */
	function Reward() {
		global $SocialRewarding;
		$this->SocialRewarding = $SocialRewarding;
		$this->dbr =& wfGetDB(DB_SLAVE);
		// If constructor is loaded from class Reward (no parent class) load function initialize()
		if (!get_parent_class($this)) {
			$this->initialize();
		}
	}


	/**
	 * Load data from database, load all weights, load data from all
	 * three social rewarding methods, transform data and load all
	 * scales.
	 *
	 * @access private
	 */
	function initialize() {
		$this->loadData();
		$this->loadMethodsWeight();
		$this->loadTimeSizeWeight();
		$this->loadRewardingMethods();
		$this->articleAuthorRevision();
		$this->loadTimestamps();
		$this->loadStarsScale();
	}


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "Reward";
	}


	/**
	 * Get a new instance from class RewardReferences.
	 *
	 * @access private
	 * @return RewardReferences RewardReferences instance
	 */
	function newRewardReferences() {
		$this->rewardReferences = new RewardReferences();
		return $this->rewardReferences;
	}


	/**
	 * Get a new instance from class RewardRating.
	 *
	 * @access private
	 * @return RewardRating RewardRating instance
	 */
	function newRewardRating() {
		$this->rewardRating = new RewardRating();
		return $this->rewardRating;
	}


	/**
	 * Get a new instance from class RewardViewed.
	 *
	 * @access private
	 * @return RewardViewed RewardViewed instance
	 */
	function newRewardViewed() {
		$this->rewardViewed = new RewardViewed();
		return $this->rewardViewed;
	}


	/**
	 * Get minimum value of an array.
	 *
	 * @access public
	 * @param array $list List of values
	 * @return int Minimum value
	 */
	function getMin($list) {
		return min($list);
	}

	/**
	 * Get maximum value of an array.
	 *
	 * @access public
	 * @param array $list List of values
	 * @return int Maximum value
	 */
	function getMax($list) {
		return max($list);
	}


	/**
	 * Tokenize a string and divide each value by 100 ->
	 * values for scaling purpose.
	 *
	 * @access public
	 * @param String $scale Scale string
	 * @return array Scale values
	 */
	function getScale($scale) {
		$tokenized = SocialRewardingTokString($scale, $this->SocialRewarding["reward"]["delimiter"]);
		for ($i = 0; $i < count($tokenized); $i++) {
			$tokenized[$i] = $tokenized[$i] / 100;
		}
		return $tokenized;
	}


	/**
	 * Get points by passing percentage value and a scale array.
	 *
	 * @access public
	 * @param float $percent Percentage value
	 * @param array $scale Scale list
	 * @return int Points
	 * or
	 * @return float Points
	 */
	function getPoints($percent, $scale) {
		$points = 0;

		for ($i = count($scale) - 1; $i >= 0; $i--) {
			if ($percent - $scale[$i] >= 0) {
				$points = $i;
				break;
			}
		}

		// If decimal places should be included
		if ($scale[$points + 1] && $this->SocialRewarding["reward"]["articlePointsDecimalPlace"] == true) {
			$max = $scale[$points + 1];
			if ($max != 0) {
				$per = $percent / $max;
				$points += $per;
			}
		}

		return $points;
	}


	/**
	 * Get number of rows of result set of SQL query.
	 *
	 * @access public
	 * @param String $sql SQL query
	 * @return int Number of rows
	 */
	function getNumFromDB($sql) {
		$rs = $this->dbr->query($sql);
		return $this->dbr->numRows($rs);
	}


	/**
	 * Return result set of database query.
	 *
	 * @access public
	 * @param String $sql SQL query
	 * @return Resource Result set
	 */
	function getDataFromDB($sql) {
		$rs = $this->dbr->query($sql);
		return $this->dbr->fetchRow($rs);
	}


	/**
	 * Return timestamp of passed revision ID.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Timestamp of revision
	 */
	function getTimestamp($rev) {
		return $this->revisionTimestamp[$rev];
	}


	/**
	 * Return size of revision in bytes.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Size of revision
	 */
	function getSize($rev) {
		return $this->revisionSize[$rev];
	}


	/**
	 * Load needed data (timestamp and size of revision, revisions'
	 * article and authors) from database.
	 *
	 * @access private
	 */
	function loadData() {
		$key = "rev_id";
		$val[0][0] = "timestamp";
		$var[0][1] =& $this->revisionTimestamp;
		$val[1][0] = "size";
		$var[1][1] =& $this->revisionSize;
		$val[2][0] = "page";
		$var[2][1] =& $this->revisionArticle;
		$val[3][0] = "author";
		$var[3][1] =& $this->revisionAuthor;

		extract($this->dbr->tableNames("page", "revision", "text"));

		$sql = "SELECT
				$revision.rev_id AS $key,
				UNIX_TIMESTAMP(rev_timestamp) AS " . $val[0][0] . ",
				" . $this->SocialRewarding["reward"]["sizeMethod"] . "($text.old_text) AS " . $val[1][0] . ",
				rev_page AS " . $val[2][0] . ",
				rev_user_text AS " . $val[3][0] . "
			FROM
				$revision,
				$text,
				$page
			WHERE
				$revision.rev_id = $text.old_id
			AND
				$revision.rev_page = $page.page_id
		";

		// Limit time interval
		if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "") {
			$sql .= " AND $revision.rev_timestamp >= '" . $this->SocialRewarding["reward"]["beginTimeInterval"] . "'";
		}
		if ($this->SocialRewarding["reward"]["endTimeInterval"] != "") {
			$sql .= " AND $revision.rev_timestamp <= '" . $this->SocialRewarding["reward"]["endTimeInterval"] . "'";
		}

		// Restrict and correct pages
		switch($this->SocialRewarding["reward"]["calcBasis"]) {
			case "articles":
				$sql .= " AND page_namespace = '" . $this->SocialRewarding["reward"]["calcBasisArticlesNS"] . "'";
				break;
			case "user_pages":
				$sql .= " AND page_namespace = '" . $this->SocialRewarding["reward"]["calcBasisUserPagesNS"] . "'";
				break;
			default:
				$sql .= " AND page_namespace != '" . $this->SocialRewarding["reward"]["calcBasisCorrectionNS"] . "'";
		}

		// Only registered users or all
		if ($this->SocialRewarding["reward"]["calcUsersOnly"] == true) {
			$sql .= " AND rev_user != 0";
		}

		// Load data from databse
		$this->loadFromDBFour($sql, $key, $var[0][1], $val[0][0], $var[1][1], $val[1][0], $var[2][1], $val[2][0], $var[3][1], $val[3][0]);
	}


	/**
	 * Transform data so that revision IDs and author names
	 * can be obtained more easily from a given article ID.
	 *
	 * @access private
	 */
	function articleAuthorRevision() {
		$revisionArticle = $this->revisionArticle;
		ksort($revisionArticle);
		foreach ($revisionArticle as $key => $val) {
			$countArticle = count($article[$val]);
			$article[$val][$countArticle] = $key;

			$countAuthor = count($author[$this->revisionAuthor[$key]]);
			$author[$this->revisionAuthor[$key]][$countAuthor] = $key;
		}

		$this->articleRevision = $article;
		$this->articleAuthor = $author;
	}


	/**
	 * Load minimum and maximum timestamps from retrieved data.
	 *
	 * @access private
	 */
	function loadTimestamps() {
		$this->firstTimestamp = $this->getMin($this->revisionTimestamp);
		$this->lastTimestamp = $this->getMax($this->revisionTimestamp);
	}


	/**
	 * Load weighting for time and size calculation methods.
	 *
	 * @access private
	 */
	function loadTimeSizeWeight() {
		$this->timeSizeWeight = $this->getScale($this->SocialRewarding["reward"]["timeSizeWeight"]);
	}


	/**
	 * Load weighting for all three social rewarding methods.
	 *
	 * @access private
	 */
	function loadMethodsWeight() {
		$this->methodsWeight = $this->getScale($this->SocialRewarding["reward"]["methodsWeight"]);
	}


	/**
	 * Initialize instances of all three social rewarding methods
	 * (if activated).
	 *
	 * @access private
	 */
	function loadRewardingMethods() {
		$i = 0;

		if ($this->SocialRewarding["references"]["active"] == true && is_object($this->rewardReferences) == false) {
			$rewardingMethod[$i] = $this->newRewardReferences();
		}
		$i++;
		if ($this->SocialRewarding["rating"]["active"] == true && is_object($this->rewardRating) == false) {
			$rewardingMethod[$i] = $this->newRewardRating();
		}
		$i++;
		if ($this->SocialRewarding["viewed"]["active"] == true && is_object($this->rewardViewed) == false) {
			$rewardingMethod[$i] = $this->newRewardViewed();
		}

		$this->rewardingMethodsCount = $i;
		$this->rewardingMethod = $rewardingMethod;
	}


	/**
	 * Load scale for displaying stars.
	 *
	 * @access private
	 */
	function loadStarsScale() {
		$this->starsScale = $this->getScale($this->SocialRewarding["reward"]["starsScale"]);
	}


	/**
	 * Retrieve data from database (4 columns).
	 *
	 * @access public
	 * @param String $sql SQL query
	 * @param int $key Unique key (like revision ID)
	 * @param array &$var0 Variable to save results to
	 * @param String $val0 Database column name
	 * @param array &$var1 Variable to save results to
	 * @param String $val1 Database column name
	 * @param array &$var2 Variable to save results to
	 * @param String $val2 Database column name
	 * @param array &$var3 Variable to save results to
	 * @param String $val3 Database column name
	 */
	function loadFromDBFour($sql, $key, &$var0, $val0, &$var1, $val1, &$var2, $val2, &$var3, $val3) {
		$rs = $this->dbr->query($sql);
		while ($row = $this->dbr->fetchRow($rs)) {
			$var0[$row[$key]] = $row[$val0];
			$var1[$row[$key]] = $row[$val1];
			$var2[$row[$key]] = $row[$val2];
			$var3[$row[$key]] = $row[$val3];
		}
	}


	/**
	 * Retrieve data from database (1 column).
	 *
	 * @access public
	 * @param String $sql SQL query
	 * @param array &$var Variable to save results to
	 * @param int $key Unique key (like revision ID)
	 * @param String $val Database column name
	 */
	function loadFromDBOne($sql, &$var, $key, $val) {
		$rs = $this->dbr->query($sql);
		while ($row = $this->dbr->fetchRow($rs)) {
			$var[$row[$key]] = $row[$val];
		}
	}


	/**
	 * Get number of revisions.
	 *
	 * @access private
	 * @param int $article Article ID
	 * @return int Number of revisions
	 */
	function getCountRevisions($article) {
		return count($this->articleRevision[$article]);
	}


	/**
	 * Get article ID from revision ID.
	 *
	 * @access private
	 * @param int $rev_id Revision ID
	 * @return int Article ID
	 */
	function getArticleFromRev($rev_id) {
		return $this->revisionArticle[$rev_id];
	}


	/**
	 * Sum size of all revisions of a given article. Two methods:
	 * every size of all revisions is summed up or only the
	 * difference of one revision and its former one.
	 *
	 * @access private
	 * @param int $article Article ID
	 * @return int Summed up size
	 */
	function getArticleChangeSum($article) {
		if (!$this->sizeChangeSum[$article]) {
			if ($this->SocialRewarding["reward"]["sizeCalcMethod"] == "mean") {
				foreach ($this->articleRevision[$article] as $key => $val) {
					$this->sizeChangeSum[$article] += $this->revisionSize[$val];
				}
			} else {
				foreach ($this->articleRevision[$article] as $key => $val) {
					$this->sizeChangeSum[$article] += abs($this->revisionSize[$val] - $formerSize);
					$formerSize = $this->revisionSize[$val];
				}
			}
		}
		return $this->sizeChangeSum[$article];
	}


	/**
	 * Calculate percentage value of size change of a given
	 * revision ID in reference to all changes made to an article.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return float Percentage value
	 */
	function getArticleSizeChange($rev) {
		$article = $this->getArticleFromRev($rev);

		// Get amount of all changes made to an article
		$changeSum = $this->getArticleChangeSum($article);
		$revisionSize = $this->revisionSize[$rev];

		if ($this->SocialRewarding["reward"]["sizeCalcMethod"] == "mean") {
			if ($changeSum != 0) {
				$percent = $revisionSize / $changeSum;
			}
		} else {
			$formerSize = $this->revisionSize[$this->getFormerRevision($rev)];
			$changeRevision = abs($revisionSize - $formerSize);
			if ($changeSum != 0) {
				$percent = $changeRevision / $changeSum;
			}
		}

		return $percent;
	}


	/**
	 * Get former revision of a given revision ID.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Former revision ID
	 */
	function getFormerRevision($rev) {
		$article = $this->getArticleFromRev($rev);

		/**
		 * Three possible methods. The fastest one is activated.
		 */

		/* #1
		foreach ($this->articleRevision[$article] as $key => $val) {
			if ($rev == $val) {
				break;
			}
			$formerRev = $val;
		}
		*/

		/* #2
		$rev_pos = array_keys($this->articleRevision[$article], $rev);
		$formerRev = $this->articleRevision[$article][$rev_pos[0] - 1];
		*/

		// #3
		$rev_pos = array_search($rev, $this->articleRevision[$article]);
		$formerRev = $this->articleRevision[$article][$rev_pos - 1];

		return $formerRev;
	}


	/**
	 * Sum up timestamps of all revisions of an article in reference to
	 * first timestamp.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Summed up timestamps
	 */
	function getArticleTimeChangeSum($rev) {
		$article = $this->getArticleFromRev($rev);

		if (!$this->timeChangeSum[$article]) {
			foreach ($this->articleRevision[$article] as $key => $val) {
				$this->timeChangeSum[$article] += $this->revisionTimestamp[$val] - $this->firstTimestamp;
			}
		}

		return $this->timeChangeSum[$article];
	}


	/**
	 * Get percentage of timestamp of given revision ID in reference
	 * to all revisions' timestamps of an article.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return float Percentage value
	 */
	function getArticleTimeChange($rev) {
		$timeChangeSum = $this->getArticleTimeChangeSum($rev);
		$diff = $this->revisionTimestamp[$rev] - $this->firstTimestamp;
		if ($timeChangeSum != 0) {
			$percent = $diff / $timeChangeSum;
		}
		return $percent;
	}


	/**
	 * Weight given points of an article pertaining to time and
	 * size factor.
	 *
	 * @access private
	 * @param float $points Article points
	 * @param float $perTime Percentage time factor
	 * @param float $perSize Percentage size factor
	 * @return float Weighted points
	 */
	function weightArticlePoints($points, $perTime, $perSize) {
		$timePoints = $points * $this->timeSizeWeight[0] * $perTime;
		$sizePoints = $points * $this->timeSizeWeight[1] * $perSize;
		$sumPoints = $timePoints + $sizePoints;
		return $sumPoints;
	}


	/**
	 * Get article points of passed revision and social rewarding
	 * method.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @param object $rewardingMethod Rewarding method instance
	 * @return float Revision points
	 */
	function getMethodPoints($rev, $rewardingMethod) {
		return $rewardingMethod->getArticlePoints($rev);
	}


	/**
	 * First, get revision points for all activated social rewarding
	 * methods, then weight these points according to time and
	 * size factors.
	 *
	 * @access public
	 * @param int $rev Revision ID
	 * @return float Weighted revision points
	 */
	function getArticlePoints($rev) {
		if (!$this->articlePoints[$rev]) {
			for ($i = 0; $i <= $this->rewardingMethodsCount; $i++) {
				if ($this->rewardingMethod[$i]) {
					$methodPoints = $this->getMethodPoints($rev, $this->rewardingMethod[$i]);
					$weightedMethodsPoints += $methodPoints * $this->methodsWeight[$i];
				}
			}

			$perTime = $this->getArticleTimeChange($rev);
			$perSize = $this->getArticleSizeChange($rev);
			$this->articlePoints[$rev] = $this->weightArticlePoints($weightedMethodsPoints, $perTime, $perSize);
		}

		return $this->articlePoints[$rev];
	}


	/**
	 * Sum up all points for an author.
	 *
	 * @access public
	 * @param String $author Author's name
	 * @return float Author's points
	 */
	function getAuthorPoints($author) {
		foreach ($this->articleAuthor[$author] as $key => $val) {
			$points += $this->getArticlePoints($val);
		}
		return $points;
	}


	/**
	 * Load and sort all points for all authors.
	 *
	 * @access public
	 * @return array All authors' points
	 */
	function loadAllAuthorsPointsData() {
		if (!is_array($this->authors)) {
			foreach ($this->articleAuthor as $key => $val) {
				$authors[$key] = $this->getAuthorPoints($key);
			}
			arsort($authors);
			$this->authors = $authors;
		}
		return $this->authors;
	}


	/**
	 * Get number of stars for a given author. Two methods: "mean"
	 * and "min_max".
	 *
	 * @access private
	 * @param String $author Author's name
	 * @return array Number of stars
	 */
	function getAuthorStars($author) {
		$this->loadAllAuthorsPointsData();
		$points = $this->authors[$author];

		switch ($this->SocialRewarding["reward"]["calcMethodStars"]) {
			case "mean":
				$countAuthors = count($this->authors);
				if ($countAuthors != 0 && is_array($this->authors)) {
					$avg = array_sum($this->authors) / $countAuthors;
				}
				if ($avg != 0) {
					$percent = $points / $avg;
				}
				break;
			default:
				$diff = $this->getMax($this->authors) - $this->getMin($this->authors);
				$pointsNorm = $points - $this->getMin($this->authors);
				if ($diff != 0) {
					$percent = $pointsNorm / $diff;
				}
		}

		$scale = $this->getScale($this->SocialRewarding["reward"]["starsScale"]);
		$stars[0] = $this->getPoints($percent, $scale);
		$stars[1] = round($stars[0], $this->SocialRewarding["reward"]["round"]);
		$stars[2] = $this->SocialRewarding["reward"]["starsRound"]($stars[0]);

		return $stars;
	}


	/**
	 * Display stars for an author.
	 *
	 * @access public
	 * @param String $author Author's name
	 * @param boolean $points Display points
	 * @return String HTML for displaying stars
	 */
	function getDisplayStars($author, $points = false) {
		global $wgUser, $wgScriptPath;

		$skin = $wgUser->getSkin();
		$stars = $this->getAuthorStars($author);

		$starFull = $this->SocialRewarding["reward"]["extensionPath"] . "/" . $this->SocialRewarding["reward"]["starsDir"] . "/" . $this->SocialRewarding["reward"]["starsFull"];
		$starEmpty = $this->SocialRewarding["reward"]["extensionPath"] . "/" . $this->SocialRewarding["reward"]["starsDir"] . "/" . $this->SocialRewarding["reward"]["starsEmpty"];
		$starHalf = $this->SocialRewarding["reward"]["extensionPath"] . "/" . $this->SocialRewarding["reward"]["starsDir"] . "/" . $this->SocialRewarding["reward"]["starsHalf"];

		if ($this->SocialRewarding["reward"]["starsDisplayHalf"] == true) {
			$diff = $stars[0] - floor($stars[0]);
			$scale = $this->getScale($this->SocialRewarding["reward"]["starsHalfBorders"]);

			if ($diff < $scale[0]) {
				$star = floor($stars[0]);
				$starsImgAdd = $skin->makeExternalImage($wgScriptPath . "/" . $starEmpty,"$star star(s)");
			} else if ($diff <= $scale[1]) {
				$star = floor($stars[0]) . ".5";
				$starsImgAdd = $skin->makeExternalImage($wgScriptPath . "/" . $starHalf,"$star star(s)");
			} else {
				$star = ceil($stars[0]);
				$starsImgAdd = $skin->makeExternalImage($wgScriptPath . "/" . $starFull,"$star star(s)");
			}

			for ($i = 0; $i < floor($stars[0]); $i++) {
				$starsImg .= $skin->makeExternalImage($wgScriptPath . "/" . $starFull,"$star star(s)");
			}

			if (floor($stars[0]) != ceil($stars[0])) {
				$starsImg .= $starsImgAdd;
			}

			for ($i = count($this->starsScale) - 1; $i > ceil($stars[0]); $i--) {
				$starsImg .= $skin->makeExternalImage($wgScriptPath . "/" . $starEmpty,"$star star(s)");
			}

		} else {
			for ($i = 0; $i < $stars[2]; $i++) {
				$starsImg .= $skin->makeExternalImage($wgScriptPath . "/" . $starFull,"$stars[2] star(s)");
			}

			for ($i = count($this->starsScale) - 1; $i > $stars[2]; $i--) {
				$starsImg .= $skin->makeExternalImage($wgScriptPath . "/" . $starEmpty,"$stars[2] star(s)");
			}
		}

		if ($points == true) {
			$starsImg .= " ($stars[1])";
		}

		return $starsImg;
	}



	/**
	 * Get data for displaying sparklines for an author.
	 *
	 * @access private
	 * @param String $author Author's name
	 * @param boolean $format Format data as URL
	 * @return array Sparkline data
	 * or
	 * @return String Sparkline data as URL
	 */
	function getSparkLinesData($author, $format = false) {
		$period = $this->lastTimestamp - $this->firstTimestamp;
		$sparklinesInterval = $this->SocialRewarding["reward"]["sparklinesInterval"];

		if ($sparklinesInterval != 0) {
			$interval = $period / $sparklinesInterval;
		}

		$begin = $this->firstTimestamp;
		$end = $this->firstTimestamp + $interval;

		for ($i = 0; $i < $sparklinesInterval; $i++) {
			$data[$i] = 0;
			foreach ($this->articleAuthor[$author] as $key => $val) {
				$revTimestamp = $this->revisionTimestamp[$val];
				if ($revTimestamp >= $begin && $revTimestamp < $end) {
					$data[$i] += $this->getArticlePoints($val);
				}
			}
			$begin = $end;
			$end = $end + $interval;
		}

		if ($format == true) {
			$data = $this->formatSparklinesData($data);
		}

		return $data;
	}


	/**
	 * Format sparkline data as URL.
	 *
	 * @access private
	 * @param array $data Sparkline data
	 * @return String Sparkline data as URL
	 */
	function formatSparklinesData($data) {
		$minValue = $this->getSparklinesMin($data);
		$avg = $this->getSparklinesAvgDisplay($data);
		
		for ($i = 0; $i < $this->SocialRewarding["reward"]["sparklinesInterval"]; $i++) {
			$data_output =  $minValue + $data[$i];
			$output .= "&data$i=".round($data_output, $this->SocialRewarding["reward"]["round"]);
		}

		$output .= "&avg=$avg";

		return $output;
	}


	/**
	 * Get minimum value of given sparkline data.
	 *
	 * @access private
	 * @param array $data Sparkline data
	 * @return float Minimum value
	 */
	function getSparklinesMin($data) {
		$minPercent = $this->SocialRewarding["reward"]["sparklinesMinPercent"];
		$maxValue = $this->getMax($data);
		$minValue = $minPercent * $maxValue / 100;
		return $minValue;
	}


	/**
	 * Get average value from given sparkline data by dividing
	 * maximum value by two. Only for displaying purpose.
	 *
	 * @access private
	 * @param array $data Sparkline data
	 * @return float Average value
	 */
	function getSparklinesAvgDisplay($data) {
		return $this->getMax($data) / 2;
	}


	/**
	 * Get average value from given sparkline data.
	 *
	 * @access private
	 * @param array $data Sparkline data
	 * @return int Average value
	 */
	function getSparklinesAvg($data) {
		$sparklinesInterval = $this->SocialRewarding["reward"]["sparklinesInterval"];
		if ($sparklinesInterval !=0 && is_array($data)) {
			return array_sum($data) / $sparklinesInterval;
		}
	}


	/**
	 * Display sparklines for an author.
	 *
	 * @access public
	 * @param String $author Author's name
	 * @return String HTML for displaying sparklines
	 */
	function getDisplaySparklines($author) {
		global $wgUser, $wgScriptPath;

		$skin = $wgUser->getSkin();
		$this->loadAllSparklinesData();
		$data = $this->sparklines[$author];
		$minValue = $this->getSparklinesMin($data);
		$max = round($this->getMax($data), $this->SocialRewarding["reward"]["round"]);
		$min = round($this->getMin($data), $this->SocialRewarding["reward"]["round"]);
		$avg = round($this->getSparklinesAvg($data), $this->SocialRewarding["reward"]["round"]);
		$sparklinesData = $this->formatSparklinesData($data);
		$sparklines = $skin->makeExternalImage($wgScriptPath . "/" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingSparkLine.php?path=" . $this->SocialRewarding["reward"]["sparklinesPackageDir"] . $sparklinesData, "max: $max, min: $min, avg: $avg");

		return $sparklines;
	}


	/**
	 * Load all sparkline data for all authors.
	 *
	 * @access public
	 */
	function loadAllSparklinesData() {
		if (!is_array($this->sparklines)) {
			foreach ($this->articleAuthor as $key => $val) {
				$this->sparklines[$key] = $this->getSparklinesData($key);
			}
		}
	}


	/**
	 * Load all data (authors' points and sparkline data) for all
	 * authors.
	 *
	 * @access public
	 * @return array All authors' points
	 */
	function loadAllData() {
		if (!is_array($this->sparklines) || !is_array($this->authors)) {
			foreach ($this->articleAuthor as $key => $val) {
				$authors[$key] = $this->getAuthorPoints($key);
				$this->sparklines[$key] = $this->getSparklinesData($key);
			}
			arsort($authors);
			$this->authors = $authors;
		}
		return $this->authors;
	}


	/**
	 * Create new instance of RewardRecommend and load all data.
	 *
	 * @access public
	 */
	function loadAllDataRecommend() {
		$this->recommend = new RewardRecommend();
		$this->recommend->loadAllData();
	}


	/**
	 * If RewardRecommend instance exists return it, else create
	 * new instance and return this object.
	 *
	 * @access public
	 * @return RewardRecommend RewardRecommend instance
	 */
	function getRecommend() {
		if (is_object($this->recommend)) {
			return $this->recommend;
		} else {
			return new RewardRecommend();
		}
	}


}


?>