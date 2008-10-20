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
 * Class for computing points for a revision for social rewarding method
 * "Most Viewed Articles".
 */
class RewardViewed extends Reward {

	/* private */ var $revisionViewed;
	/* private */ var $sum;
	/* private */ var $scale;

	/**
	 * Constructor, load also Reward constructor.
	 *
	 * @access public
	 */
	function RewardViewed() {
		parent::Reward();
		$this->loadData();
		$this->loadSum();
		$this->loadScale();
	}


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "RewardViewed";
	}


	/**
	 * Load needed data from database.
	 *
	 * @access private
	 */
	function loadData() {
		$key = "rev_id";
		$val = "visits";

		extract($this->dbr->tableNames("revision"));

		$sql = "
			SELECT
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["viewedArticles"]) . ".rev_id AS $key,
				rev_counter AS $val
			FROM
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["viewedArticles"]) . "
			";

		// Time restrictions
		if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "" || $this->SocialRewarding["reward"]["endTimeInterval"]) {
			$sql .= "
					,
					$revision
				WHERE
					$revision.rev_id = " . $this->dbr->tableName($this->SocialRewarding["DB"]["viewedArticles"]) . ".rev_id
			";
			if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "") {
				$sql .= " AND $revision.rev_timestamp >= '" . $this->SocialRewarding["reward"]["beginTimeInterval"] . "'";
			}
			if ($this->SocialRewarding["reward"]["endTimeInterval"] != "") {
				$sql .= " AND $revision.rev_timestamp <= '" . $this->SocialRewarding["reward"]["endTimeInterval"] . "'";
			}
		}

		$this->loadFromDBOne($sql, $this->revisionViewed, $key, $val);
	}


	/**
	 * Load data from database for summarize all views
	 * of all revisions.
	 *
	 * @access private
	 */
	function loadSum() {
		extract($this->dbr->tableNames("revision", "page"));

		$sum = 0;
		$sql = "SELECT
				SUM(rev_counter)
			FROM
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["viewedArticles"]) . ",
				$revision,
				$page
			WHERE
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["viewedArticles"]) . ".rev_id = $revision.rev_id AND
				rev_page = page_id
			";

		switch($this->SocialRewarding["viewed"]["calcBasis"]) {
			case "articles":
				$sql .= " AND page_namespace = '" . $this->SocialRewarding["reward"]["calcBasisArticlesNS"] . "'";
				$row = $this->getDataFromDB($sql);
				$sum = $row[0];
				break;
			case "user_pages":
				$sql .= " AND page_namespace = '" . $this->SocialRewarding["reward"]["calcBasisUserPagesNS"] . "'";
				$row = $this->getDataFromDB($sql);
				$sum = $row[0];
				break;
			default:
				if (is_array($this->revisionViewed)) {
					$sum = array_sum($this->revisionViewed);
				}
				if ($this->SocialRewarding["viewed"]["calcBasisCorrection"] == true) {
					$sql .= " AND page_namespace = '" . $this->SocialRewarding["reward"]["calcBasisCorrectionNS"] . "'";
					$row = $this->getDataFromDB($sql);
					$sum -= $row[0];
				}
		}

		$this->sum = $sum;
	}


	/**
	 * Load scale for calculating points.
	 *
	 * @access private
	 */
	function loadScale() {
		$this->scale = $this->getScale($this->SocialRewarding["viewed"]["articleScale"]);
	}


	/**
	 * Get number of revisions.
	 *
	 * @access private
	 * @return int Number of revisions
	 */
	function getCount() {
		return count($this->revisionViewed);
	}


	/**
	 * Get average amount of views of all revisions.
	 *
	 * @access private
	 * @return float Average views of all revisions
	 */
	function getMean() {
		$count = $this->getCount();
		if ($count != 0) {
			return $this->sum / $count;
		}
	}


	/**
	 * Get sum of all views of all revisions.
	 *
	 * @access private
	 * @return int Sum of all views
	 */
	function getSum() {
		return $this->sum;
	}


	/**
	 * Get amount of views of a revision.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Number of views
	 */
	function getViews($rev) {
		return $this->revisionViewed[$rev];
	}


	/**
	 * Get weighted points of a revision.
	 *
	 * @access public
	 * @param int $rev Revision ID
	 * @return float Weighted points
	 */
	function getArticlePoints($rev) {
		$mean = $this->getMean();
		if ($mean != 0) {
			$percent = $this->getViews($rev) / $mean;
		}
		$points = $this->getPoints($percent, $this->scale);
		return $points;
	}

}


?>