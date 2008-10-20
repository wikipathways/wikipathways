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
 * @subpackage SpecialPage
 * @subsubpackage SocialRewarding
 */



/**
 * Special page for displaying a history of ranking of authors. Only
 * available if caching is activated and caching method is set to "db".
 */
class SpecialSocialRewardingAuthorsHistory {

	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "SocialRewardingAuthorsHistory";
	}

	/**
	 * Is this query expensive (for some definition of expensive)?
	 *
	 * @access public
	 * @return boolean Is expensive
	 */
	function isExpensive() {
		return true;
	}


	/**
	 * Build rss / atom feeds?
	 *
	 * @access public
	 * @return boolean Is syndicated
	 */
	function isSyndicated() {
		return false;
	}


	/**
	 * Build header of page with form.
	 *
	 * @access private
	 * @return String HTML for page header
	 */
	function getPageHeader( ) {
		global $wgScript, $wgLang;
		global $SocialRewarding;

		$action = htmlspecialchars($wgScript);

		$dbr =& wfGetDB(DB_SLAVE);
		$rs = $dbr->query("SELECT timestamp FROM " . $dbr->tableName($SocialRewarding["DB"]["cache"]) . " ORDER BY timestamp DESC");

		$output = "
			<form action=" . $action . " method=get>
			Select date 
			<select name=sr_timestamp>
		";

		// Loop over all cached timestamps
		while ($row = $dbr->fetchRow($rs)) {
			if ($_GET["sr_timestamp"] == $row[0]) {
				$selected = " selected";
			} else {
				$selected = "";
			}
			$date = date("H:i, j F Y", $row[0]);
			$output .= "
				<option$selected value=$row[0]>" . $date . "</option>
			";
		}

		$output .= "
				</select>
				<br>
				<input type=hidden name=title value='" . $_GET["title"] . "'>
				<input type=checkbox name=sr_stars"; if ($_GET["sr_stars"]) $output.=" checked"; $output.="> Show stars
				&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=sr_sparklines"; if ($_GET["sr_sparklines"]) $output.=" checked"; $output.="> Show sparklines
				&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=sr_points"; if ($_GET["sr_points"]) $output.=" checked"; $output.="> Show scores
				&nbsp;&nbsp;&nbsp;
				<input type=submit value=Go>
			</form>
		";

		return $output;
	}


	/**
	 * Get results of page body.
	 *
	 * @access public
	 * @return String HTML output
	 */
	function getPage() {
		global $wgUser;
		global $SocialRewarding;

		$output = $this->getPageHeader() . "<br>";

		$skin = $wgUser->getSkin();

		// If no timestamp was set fetch Reward from cache (if enabled)
		if (!$_GET["sr_timestamp"]) {
			$mReward = new ManageReward();
			$reward = $mReward->getReward();
		// Else get cached object from database
		} else {
			$dbr =& wfGetDB(DB_SLAVE);
			$rs = $dbr->query("SELECT data FROM " . $dbr->tableName($SocialRewarding["DB"]["cache"]) . " WHERE timestamp = " . $_GET["sr_timestamp"]);
			$row = $dbr->fetchRow($rs);
			$sReward = str_replace("\'", "'", $row[0]);
			$reward = unserialize($sReward);
		}
		$authors = $reward->loadAllData();

		$output .= "<ol start=1 class=special>";

		foreach($authors as $key => $val) {
			$output .= "<li>";
			$output .= $skin->makeLink("User:$key", $key);
			if ($_GET["sr_stars"]) {
				$output .= " " . $reward->getDisplayStars($key);
			}
			if ($_GET["sr_sparklines"]) {
				$output .= " " . $reward->getDisplaySparklines($key);
			}
			if ($_GET["sr_points"]) {
				$output .= " <i>" . round($val, $SocialRewarding["reward"]["round"]) . "</i>";
			}
			$output .= "</li>";
		}

		$output .= "</ol>";

		return $output;
	}

}


/**
 * Create new instance of SpecialPage class and output HTML.
 *
 * @access public
 */
function wfSpecialSocialRewardingAuthorsHistory() {
	global $wgOut;
	$site = new SpecialSocialRewardingAuthorsHistory();
	$wgOut->addHTML($site->getPage());
}


?>