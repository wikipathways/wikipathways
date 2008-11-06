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



require_once("QueryPage.php");


/**
 * Special page for displaying list of most viewed revisions.
 */
class SpecialSocialRewardingMostViewedRevisions extends PageQueryPage {


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "SocialRewardingMostViewedRevisions";
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
	 * SQL query string to be executed.
	 *
	 * @access private
	 * @return String SQL query
	 */
	function getSQL() {
		global $SocialRewarding;

		$dbr =& wfGetDB(DB_SLAVE);
		extract($dbr->tableNames("revision", "page"));

		$sql  = "SELECT
				'Social Rewarding: Most Viewed Revisions' AS type,
				page_namespace AS namespace,
				page_title AS title,
				rev_counter AS value,
				$revision.rev_id AS rev_id,
				rev_user_text AS user,
				rev_timestamp AS timestamp,
				page_id AS id
			FROM
				" . $dbr->tableName($SocialRewarding["DB"]["viewedArticles"]) . ",
				$revision,
				$page
			WHERE
				" . $dbr->tableName($SocialRewarding["DB"]["viewedArticles"]) . ".rev_id = $revision.rev_id AND
				rev_page = page_id AND page_is_redirect = 0 AND page_namespace = 102
			";

	/**	if ($_GET["sr_select"]) {
			if ($_GET["sr_select"] == "zero") {
				$_GET["sr_select"] = 0;
			}
			$sql.=" AND page_namespace LIKE '" . $_GET["sr_select"] . "' ";
		}
	*/
		return $sql;
	}


	/**
	 * SQL query string to order results.
	 *
	 * @access private
	 * @return String SQL query
	 */
	function getOrder() {
		if (!$_GET["sr_orderby"]) {
			$orderby = "value";
		} else {
			$orderby = $_GET["sr_orderby"];
		}

		if (!$_GET["sr_order"]) {
			$order = "desc";
		} else {
			$order = $_GET["sr_order"];
		}

		return " ORDER BY $orderby $order";
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
				<input type=hidden name=limit value='" . $_GET["limit"] . "'>
				<input type=hidden name=offset value='" . $_GET["offset"] . "'>
				Select
				<select name=sr_select>
					<option value=102"; if ($_GET["sr_select"]=="102" || !$_GET["sr_select"]) $output.=" selected"; $output.=">pathways</option>
				</select>
				order by
				<select name=sr_orderby>
					<option value=title"; if ($_GET["sr_orderby"]=="title") $output.=" selected"; $output.=">page title</option>
					<option value=rev_id"; if ($_GET["sr_orderby"]=="rev_id") $output.=" selected"; $output.=">revision</option>
					<option value=value"; if ($_GET["sr_orderby"]=="value" || !$_GET["sr_orderby"]) $output.=" selected"; $output.=">" . $SocialRewarding["viewed"]["countMethod"] . "</option>
					<option value=user"; if ($_GET["sr_orderby"]=="user") $output.=" selected"; $output.=">author</option>
				</select>
				&nbsp;&nbsp;
				<select name=sr_order>
					<option value=desc"; if ($_GET["sr_order"]=="desc") $output.=" selected"; $output.=">descending</option>
					<option value=asc"; if ($_GET["sr_order"]=="asc") $output.=" selected"; $output.=">ascending</option>
				</select>
				&nbsp;&nbsp;
				<input type=submit value=Go>
				<br>
				<input type=checkbox name=sr_author"; if ($_GET["sr_author"]) $output.=" checked"; $output.="> Show author
				&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=sr_date"; if ($_GET["sr_date"]) $output.=" checked"; $output.="> Show date
			</form>
			<br>
			";

		return $output;
	}


	/**
	 * Get results of page body.
	 *
	 * @access public
	 * @return String HTML output
	 */
	function formatResult($skin, $result) {
		global $wgUser, $wgLang;
		global $SocialRewarding;

                //exclude Tutorial pathways from list
                $taggedIds = CurationTag::getPagesForTag('Curation:Tutorial');
                if (in_array($result->id, $taggedIds)){
                        return null;
                }

		$userID = $wgUser->idFromName($result->title);

		if ($userID != 0) {
			$addUser = "User:";
		}
                else {
                        $addUser = "Pathway:";
                }

		if ($_GET["sr_author"]) {
			$author = "<i>Author: " . $skin->makeLink("User:" . $result->user, $result->user . "</i>");
		}
		if ($_GET["sr_date"]) {
			$date = "<i>" . $wgLang->timeanddate($result->timestamp, true) . "</i>";
		}
		if ($_GET["sr_author"] && $_GET["sr_date"]) {
			$comma = "<i>, </i>";
		}

		$titleText = SocialRewardingDisplayTitle($result->title);
		$output = $skin->makeLink($addUser.$result->title, "[R" . $result->rev_id . "] $titleText", "oldid=" . $result->rev_id, " (" . $result->value . " " . $SocialRewarding["viewed"]["countMethod"] . ") $author$comma$date");

		return $output;
	}

}


/**
 * Create new instance of SpecialPage class and output HTML.
 *
 * @access public
 */
function wfSpecialSocialRewardingMostViewedRevisions() {
	list($limit,$offset) = wfCheckLimits();
	$site = new SpecialSocialRewardingMostViewedRevisions();
	$site->doQuery($offset,$limit);
}

?>
