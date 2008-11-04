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
 * Special page for displaying list of most viewed articles.
 */
class SpecialSocialRewardingMostViewedArticles extends PageQueryPage {


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "SocialRewardingMostViewedArticles";
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
				'Social Rewarding: Most Viewed Articles' AS type,
				page_namespace AS namespace,
				page_title AS title,
				SUM(rev_counter) AS value,
				page_len AS length,
				page_id
			FROM
				" . $dbr->tableName($SocialRewarding["DB"]["viewedArticles"]) . ",
				$revision,
				$page
			WHERE
				" . $dbr->tableName($SocialRewarding["DB"]["viewedArticles"]) . ".rev_id = $revision.rev_id AND
				rev_page = page_id AND page_is_redirect = 0
		";

		if ($_GET["sr_select"]) {
			if ($_GET["sr_select"] == "zero") {
				$_GET["sr_select"] = 0;
			}
			$sql.=" AND page_namespace LIKE '" . $_GET["sr_select"] . "'";
		}

		$sql .= " GROUP BY page_id ";

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
					<option value=%"; if ($_GET["sr_select"]=="all" || !$_GET["sr_select"]) $output.=" selected"; $output.=">all</option>
					<option value=zero"; if ($_GET["sr_select"]=="zero") $output.=" selected"; $output.=">articles</option>
					<option value=2"; if ($_GET["sr_select"]=="2") $output.=" selected"; $output.=">user-pages</option>
				</select>
				order by
				<select name=sr_orderby>
					<option value=title"; if ($_GET["sr_orderby"]=="title") $output.=" selected"; $output.=">page title</option>
					<option value=page_len"; if ($_GET["sr_orderby"]=="page_len") $output.=" selected"; $output.=">page length</option>
					<option value=value"; if ($_GET["sr_orderby"]=="value" || !$_GET["sr_orderby"]) $output.=" selected"; $output.=">" . $SocialRewarding["viewed"]["countMethod"] . "</option>
				</select>
				&nbsp;&nbsp;
				<select name=sr_order>
					<option value=desc"; if ($_GET["sr_order"]=="desc") $output.=" selected"; $output.=">descending</option>
					<option value=asc"; if ($_GET["sr_order"]=="asc") $output.=" selected"; $output.=">ascending</option>
				</select>
				&nbsp;&nbsp;
				<input type=submit value=Go>
				<br>
				<input type=checkbox name=sr_author"; if ($_GET["sr_author"]) $output.=" checked"; $output.="> Show author(s)
				&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=sr_length"; if ($_GET["sr_length"]) $output.=" checked"; $output.="> Show length
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
		global $SocialRewarding;


		if ($result->namespace == $SocialRewarding["reward"]["calcBasisUserPagesNS"]) {
			$addUser = "User:";
		}
		else {
			$addUser = "Pathway:";
		}

		if ($_GET["sr_length"]) {
			$length = "<i>" . $result->length . " bytes</i>";
		}
		if ($_GET["sr_author"]) {
			$dbr =& wfGetDB(DB_SLAVE);
			extract($dbr->tableNames("revision"));
			$rs = $dbr->query("SELECT rev_user_text as author FROM $revision WHERE rev_page = " . $result->page_id . " GROUP BY author");
			$num = $dbr->numRows($rs);

			$author = "<i>Author(s): ";
			for ($i = 0; $i < $num; $i++) {
				$row = $dbr->fetchRow($rs);
				$author .= $skin->makeLink("User:" . $row["author"], $row["author"], "", ", ");
			}
			$author = substr($author,0,strlen($author)-2) . "</i>";

		}
		if ($_GET["sr_author"] && $_GET["sr_length"]) {
			$comma = "<i>, </i>";
		}

		$titleText = SocialRewardingDisplayTitle($result->title);
		$output = $skin->makeLink($addUser.$result->title, "$addUser$titleText", "", " (" . $result->value . " " . $SocialRewarding["viewed"]["countMethod"] . ") $length$comma$author");

		return $output;
	}

}


/**
 * Create new instance of SpecialPage class and output HTML.
 *
 * @access public
 */
function wfSpecialSocialRewardingMostViewedArticles() {
	list($limit,$offset) = wfCheckLimits();
	$site = new SpecialSocialRewardingMostViewedArticles();
	$site->doQuery($offset,$limit);
}

?>
