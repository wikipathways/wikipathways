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
 * Special page for displaying list of recommended
 * revisions/articles/authors and authors with same interests.
 */
class SpecialSocialRewardingRecommenderSystem {


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "SocialRewardingRecommenderSystem";
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
				<table>
					<tr>
						<td>
							Timeframe:
							<select name=sr_recommendTime>
								<option value=''>none</option>
								<option value=hour"; if ($_GET["sr_recommendTime"] == "hour") { $output .= " selected"; } $output.=">last hour</option>
								<option value=day"; if ($_GET["sr_recommendTime"] == "day") { $output .= " selected"; } $output.=">last day</option>
								<option value=week"; if ($_GET["sr_recommendTime"] == "week") { $output .= " selected"; } $output.=">last week</option>
								<option value=month"; if ($_GET["sr_recommendTime"] == "month") { $output .= " selected"; } $output.=">last month</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<input type=radio name=sr_recommend value=1"; if ($_GET["sr_recommend"] == 1) $output .= " checked"; $output .= "> Recommended revisions
						</td>
						<td colspan=2>
							<input type=radio name=sr_recommend value=2"; if ($_GET["sr_recommend"] == 2 || !$_GET["sr_recommend"]) $output .= " checked"; $output .= "> Recommended articles
						</td>
					</tr>
					<tr>
						<td>
							<input type=radio name=sr_recommend value=3"; if ($_GET["sr_recommend"] == 3) $output .= " checked"; $output .= "> Recommended authors
						</td>
						<td>
							<input type=radio name=sr_recommend value=4"; if ($_GET["sr_recommend"] == 4) $output .= " checked"; $output .= "> Authors with same interests
						</td colspan=2>
					</tr>
					<tr>
						<td>
							<input type=checkbox name=sr_rank"; if ($_GET["sr_rank"]) $output.=" checked"; $output.= "> Display rank
						</td>
						<td>
							<input type=checkbox name=sr_points"; if ($_GET["sr_points"]) $output.=" checked"; $output.= "> Display points
						</td>
						<td width=40 align=right>
							<input type=submit value=Go>
						</td>
					</tr>
				</table>
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

		// Time restriction
		if ($_GET["sr_recommendTime"]) {
			if ($_GET["sr_recommendTime"] == "hour") {
				$recommendTime = 3600;
			} else if ($_GET["sr_recommendTime"] == "day") {
				$recommendTime = 86400;
			} else if ($_GET["sr_recommendTime"] == "week") {
				$recommendTime = 604800;
			} else if ($_GET["sr_recommendTime"] == "month") {
				$recommendTime = 18144000;
			}

			// No caching
			$recommend = new RewardRecommend($recommendTime);
		} else {
			$mReward = new ManageReward();
			$reward = $mReward->getReward();
			$recommend = $reward->getRecommend();
		}


		$output .= "<ol start=1 class=special>";

		if ($_GET["sr_recommend"] == 1) {
			$recommend->loadRecommendedRevisionsData();
			$recommendMethod = $recommend->recommendRevision;
		} else if (!$_GET["sr_recommend"] || $_GET["sr_recommend"] == 2) {
			$recommend->loadRecommendedArticlesData();
			$recommendMethod = $recommend->recommendArticle;
		} else if ($_GET["sr_recommend"] == 3) {
			$recommend->loadRecommendedAuthorsData();
			$recommendMethod = $recommend->recommendAuthor;
		} else {
			$recommend->loadInterestedAuthorsData();
			$recommendMethod = $recommend->interestedAuthor;
		}

		if (is_array($recommendMethod)) {
			foreach($recommendMethod as $key => $val) {
				$output .= "<li>";
				$output .= $skin->makeLink("User:$key", $key) . ": ";
				if (is_array($val)) {
					$i = 0;
					foreach($val as $key2 => $val2) {
						$i++;
						unset($outputAdd);

						if ($_GET["sr_recommend"] == 1) {
							$revision = Revision::newFromId($key2);
							$title = $revision->getTitle();
							$titleText = $title->getText();
							$outputAdd = $skin->makeLink("Pathway:$titleText", "[R$key2] $titleText", "oldid=$key2");
						} else if (!$_GET["sr_recommend"] || $_GET["sr_recommend"] == 2) {
							$title = Title::newFromID($key2);
							$titleText = $title->getText();
							$outputAdd = $skin->makeLink("Pathway:$titleText", $titleText);
						} else {
							$outputAdd = $skin->makeLink("User:$key2", $key2);
						}

						if (!$outputAdd) {
							$outputAdd = "---";
						}

						if ($_GET["sr_rank"]) {
							$output .= "$i. ";
						}
						$output .= $outputAdd;
						if ($_GET["sr_points"]) {
							$output .= " (" . round($val2, $SocialRewarding["reward"]["round"]) . ")";
						}

						if ($i != count($val)) {
							$output .= ", ";
						}
					}
				} else {
					$output .= "---";
				}
				$output .= "</li>";
			}
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
function wfSpecialSocialRewardingRecommenderSystem() {
	global $wgOut;
	$site = new SpecialSocialRewardingRecommenderSystem();
	$wgOut->addHTML($site->getPage());
}


?>
