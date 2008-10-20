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
 * "Rating of Articles".
 */
class RewardRating extends Reward {

	/* private */ var $revisionRating;
	/* private */ var $scale;
	/* private */ var $min;
	/* private */ var $max;


	/**
	 * Constructor, load also Reward constructor.
	 *
	 * @access public
	 */
	function RewardRating() {
		parent::Reward();
		$this->loadData();
		$this->loadScale();
		$this->loadMinMax();
	}


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "RewardRating";
	}


	/**
	 * Load needed data from database.
	 *
	 * @access private
	 */
	function loadData() {
		$key = "rev_id";
		$val = "points";

		extract($this->dbr->tableNames("revision"));

		$sql = "
			SELECT
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["rating"]) . ".rev_id AS $key,
				(points / count) AS $val
			FROM
				" . $this->dbr->tableName($this->SocialRewarding["DB"]["rating"]) . "
			";

		// Time restriction
		if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "" || $this->SocialRewarding["reward"]["endTimeInterval"]) {
			$sql .= "
					,
					$revision
				WHERE
					$revision.rev_id = " . $this->dbr->tableName($this->SocialRewarding["DB"]["rating"]) . ".rev_id
			";
			if ($this->SocialRewarding["reward"]["beginTimeInterval"] != "") {
				$sql .= " AND $revision.rev_timestamp >= '" . $this->SocialRewarding["reward"]["beginTimeInterval"] . "'";
			}
			if ($this->SocialRewarding["reward"]["endTimeInterval"] != "") {
				$sql .= " AND $revision.rev_timestamp <= '" . $this->SocialRewarding["reward"]["endTimeInterval"] . "'";
			}
		}

		$this->loadFromDBOne($sql, $this->revisionRating, $key, $val);
	}


	/**
	 * Load scale for calculating points.
	 *
	 * @access private
	 */
	function loadScale() {
		$this->scale = SocialRewardingTokString($this->SocialRewarding["rating"]["scale"], $this->SocialRewarding["reward"]["delimiter"]);
	}


	/**
	 * Load minimum and maximum value of scale.
	 *
	 * @access private
	 */
	function loadMinMax() {
		$this->min = $this->getMin($this->scale);
		$this->max = $this->getMax($this->scale);
	}


	/**
	 * Get rating points of passed revision.
	 *
	 * @access private
	 * @param int $rev_id Revision ID
	 * @return float Rating points
	 */
	function getRating($rev_id) {
		return $this->revisionRating[$rev_id];
	}


	/**
	 * Get weighted rating points of a revision.
	 *
	 * @access public
	 * @param int $rev_id Revision ID
	 * @return float Weighted rating points
	 */
	function getArticlePoints($rev_id) {
		$points = $this->getRating($rev_id);

		// Min-max normalization and weighting
		if ($this->max - $this->min >= 0) {
			$weighted = ($points - $this->min) / ($this->max - $this->min) * (count($this->scale) - 1);
		}

		return $weighted;
	}


}


?>