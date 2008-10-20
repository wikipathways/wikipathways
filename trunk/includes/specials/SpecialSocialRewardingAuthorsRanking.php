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
 * Special page for displaying ranking of authors.
 */
class SpecialSocialRewardingAuthorsRanking {


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "SocialRewardingAuthorsRanking";
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
		global $wgScript;
		global $SocialRewarding;

		$action = htmlspecialchars($wgScript);

		$output = "
			<form action=" . $action . " method=get>
				<input type=hidden name=title value='" . $_GET["title"] . "'>
				<input type=checkbox name=sr_stars"; if ($_GET["sr_stars"]) $output.=" checked"; $output.="> Show stars
				&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=sr_sparklines"; if ($_GET["sr_sparklines"]) $output.=" checked"; $output.="> Show sparklines
				&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=sr_points"; if ($_GET["sr_points"]) $output.=" checked"; $output.="> Show scores
				&nbsp;&nbsp;&nbsp;
		";

		// If cache is disabled we can select which method should be calculated
		if ($SocialRewarding["reward"]["cache"] == false) {
			$output .= "
				<br>
				<input type=checkbox name=sr_references"; if ($_GET["sr_references"]) $output.=" checked"; $output.="> Amount of References
				&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=sr_rating"; if ($_GET["sr_rating"]) $output.=" checked"; $output.="> Rating of Articles
				&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=sr_viewed"; if ($_GET["sr_viewed"]) $output.=" checked"; $output.="> Most Viewed Articles
				&nbsp;&nbsp;&nbsp;
			";
		}
		$output .= "
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

		$mReward = new ManageReward();
		$reward = $mReward->getReward();

		// Unsetting following variables will result in not calculating points for this method
		if ($SocialRewarding["reward"]["cache"] == false) {
			if (!$_GET["sr_references"]) {
				unset($reward->rewardingMethod[0]);
			}
			if (!$_GET["sr_rating"]) {
				unset($reward->rewardingMethod[1]);
			}
			if (!$_GET["sr_viewed"]) {
				unset($reward->rewardingMethod[2]);
			}
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
function wfSpecialSocialRewardingAuthorsRanking() {
	global $wgOut;
	$site = new SpecialSocialRewardingAuthorsRanking();
	$wgOut->addHTML($site->getPage());
}


?>