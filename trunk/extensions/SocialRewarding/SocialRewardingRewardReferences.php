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
 * "Amount of References".
 */
class RewardReferences extends Reward {

	/* private */ var $revisionSize;
	/* private */ var $revisionLink;
	/* private */ var $revisionCount;
	/* private */ var $revisionSelfLink;
	/* private */ var $avgSize;
	/* private */ var $avgLink;
	/* private */ var $avgSelfLink;
	/* private */ var $scale;
	/* private */ var $weight;


	/**
	 * Constructor, load also Reward constructor.
	 *
	 * @access public
	 */
	function RewardReferences() {
		parent::Reward();
		$this->loadData();
		$this->loadAvg();
		$this->loadScale();
		$this->loadWeight();
	}


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "RewardReferences";
	}


	/**
	 * Load needed data from database.
	 *
	 * @access private
	 */
	function loadData() {
		$key = "rev_id";
		$val[0][0] = "size";
		$var[0][1] =& $this->revisionSize;
		$val[1][0] = "link";
		$var[1][1] =& $this->revisionLink;
		$val[2][0] = "count";
		$var[2][1] =& $this->revisionCount;
		$val[3][0] = "self_link";
		$var[3][1] =& $this->revisionSelfLink;

		extract($this->dbr->tableNames("revision"));

		$sql = "
			SELECT
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["references"]) . ".rev_id AS $key,
				" . $val[0][0] .",
				" . $val[1][0] .",
				" . $val[2][0] .",
				" . $val[3][0] ."
			FROM
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["references"]) . "
			";

		// Time restrictions
		if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "" || $this->SocialRewarding["reward"]["endTimeInterval"]) {
			$sql .= "
					,
					$revision
				WHERE
					$revision.rev_id = " . $this->dbr->tableName($this->SocialRewarding["DB"]["references"]) . ".rev_id
			";
			if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "") {
				$sql .= " AND $revision.rev_timestamp >= '" . $this->SocialRewarding["reward"]["beginTimeInterval"] . "'";
			}
			if ($this->SocialRewarding["reward"]["endTimeInterval"] != "") {
				$sql .= " AND $revision.rev_timestamp <= '" . $this->SocialRewarding["reward"]["endTimeInterval"] . "'";
			}
		}

		$this->loadFromDBFour($sql, $key, $var[0][1], $val[0][0], $var[1][1], $val[1][0], $var[2][1], $val[2][0], $var[3][1], $val[3][0]);
	}


	/**
	 * Load data from database for average value.
	 *
	 * @access private
	 */
	function loadAvg() {
		extract($this->dbr->tableNames("revision", "page"));

		$sql = "SELECT
				(SUM(size) / SUM(count)) AS avg_size,
				(SUM(link) / SUM(count)) AS avg_link,
				AVG(Self_link) AS avg_self_link
			FROM
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["references"]) . ",
				$revision,
				$page
			WHERE
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["references"]) . ".rev_id = $revision.rev_id AND
				rev_page = page_id
			";

		switch($this->SocialRewarding["references"]["calcBasis"]) {
			case "articles":
				$sql .= " AND page_namespace = '" . $this->SocialRewarding["reward"]["calcBasisArticlesNS"] . "'";
				$row = $this->getDataFromDB($sql);
				$avgSize = $row[0];
				$avgLink = $row[1];
				$avgSelfLink = $row[2];
				break;
			case "user_pages":
				$sql .= " AND page_namespace = '" . $this->SocialRewarding["reward"]["calcBasisUserPagesNS"] . "'";
				$row = $this->getDataFromDB($sql);
				$avgSize = $row[0];
				$avgLink = $row[1];
				$avgSelfLink = $row[2];
				break;
			default:
				if (is_array($this->revisionSize) && is_array($this->revisionLink) && is_array($this->revisionCount) && array_sum($this->revisionCount) != 0 && is_array($this->revisionSelfLink) ) {
					$avgSize = array_sum($this->revisionSize) / array_sum($this->revisionCount);
					$avgLink = array_sum($this->revisionLink) / array_sum($this->revisionCount);
					$avgSelfLink = array_sum($this->revisionSelfLink) / count($this->revisionCount);
				}
				if ($this->SocialRewarding["references"]["calcBasisCorrection"] == true) {
					$sql .= " AND page_namespace = '" . $this->SocialRewarding["reward"]["calcBasisCorrectionNS"] . "'";
					$row = $this->getDataFromDB($sql);
					$avgSize -= $row[0];
					$avgLink -= $row[1];
					$avgSelfLink -= $row[2];
				}
		}

		$this->avgSize = $avgSize;
		$this->avgLink = $avgLink;
		$this->avgSelfLink = $avgSelfLink;
	}


	/**
	 * Load scale for calculating points.
	 *
	 * @access private
	 */
	function loadScale() {
		$this->scale = $this->getScale($this->SocialRewarding["references"]["articleScale"]);
	}


	/**
	 * Load weighting for "siteSizeFactor", "siteLinkFactor"
	 * and "siteSelfLinkFactor".
	 *
	 * @access private
	 */
	function loadWeight() {
		$this->weight = $this->getScale($this->SocialRewarding["references"]["siteWeight"]);
	}


	/**
	 * Get size of revision's references.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Size of revision's references
	 */
	function getSize($rev) {
		return $this->revisionSize[$rev];
	}


	/**
	 * Get amount of links to revision's references.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Number of links to revision's references
	 */
	function getLink($rev) {
		return $this->revisionLink[$rev];
	}


	/**
	 * Get amount of links to article.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Number of links to article
	 */
	function getSelfLink($rev) {
		return $this->revisionSelfLink[$rev];
	}


	/**
	 * Get amount of references.
	 *
	 * @access private
	 * @param int $rev Revision ID
	 * @return int Number of references
	 */
	function getCount($rev) {
		return $this->revisionCount[$rev];
	}


	/**
	 * Get average size of all references of all articles.
	 *
	 * @access private
	 * @return int Average size of all references
	 */
	function getAvgSize() {
		return $this->avgSize;
	}


	/**
	 * Get average amount of links to all references of all
	 * articles.
	 *
	 * @access private
	 * @return int Average amount of links to all references
	 */
	function getAvgLink() {
		return $this->avgLink;
	}


	/**
	 * Get average amount of links to all articles.
	 *
	 * @access private
	 * @return int Average amount of links to all articles
	 */
	function getAvgSelfLink() {
		return $this->avgSelfLink;
	}


	/**
	 * Get defined weighting factor: "siteSizeFactor" (0),
	 * "siteLinkFactor" (1) or "siteSelfLinkFactor" (2).
	 *
	 * @access private
	 * @param int $opt Weighting option
	 * @return float Weighting factor of method in percent
	 */
	function getWeight($opt) {
		return $this->weight[$opt];
	}


	/**
	 * Get weighted points of a revision.
	 *
	 * @access public
	 * @param int $rev Revision ID
	 * @return float Weighted points
	 */
	function getArticlePoints($rev) {

		if ($this->SocialRewarding["references"]["siteSizeFactor"] == true) {
			if ($this->getCount($rev) != 0 && $this->getAvgSize() != 0) {
				$sizePercent = $this->getSize($rev) / $this->getCount($rev) / $this->getAvgSize();
			}
			$sizePoints = $this->getPoints($sizePercent, $this->scale);
		}

		if ($this->SocialRewarding["references"]["siteLinkFactor"] == true) {
			if ($this->getCount($rev) != 0 && $this->getAvgLink() != 0) {
				$linkPercent = $this->getLink($rev) / $this->getCount($rev) / $this->getAvgLink();
			}
			$linkPoints = $this->getPoints($linkPercent, $this->scale);
		}

		if ($this->SocialRewarding["references"]["siteSelfLinkFactor"] == true) {
			if ($this->getAvgSelfLink() != 0) {
				$selfLinkPercent = $this->getSelfLink($rev) / $this->getAvgSelfLink();
			}
			$selfLinkPoints = $this->getPoints($selfLinkPercent, $this->scale);
		}

		$points = $sizePoints * $this->getWeight(0) + $linkPoints * $this->getWeight(1) + $selfLinkPoints * $this->getWeight(2);

		return $points;
	}

}


?>