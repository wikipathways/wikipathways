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
 * Define all markup functions which execution is specified in
 * "SocialRewardingMarkups.php".
 */



/**
 * Markup for "Amount of References".
 *
 * @access public
 * @param String $text Markup text
 * @param array $arg Markup attributes
 * @return String HTML output
 */
function SocialRewardingReferencesMarkup($text, $arg) {
	global $wgTitle, $wgLang, $mediaWiki;
	global $SocialRewarding;

	$dbr =& wfGetDB(DB_SLAVE);
	$article = $mediaWiki->articleFromTitle($wgTitle);
	$revision = Revision::newFromTitle($wgTitle);

	if ($revision) {
		// Only Amount of References from previous revision can be displayed
		$revision_previous = $revision->getPrevious();
		$rev_id = $revision_previous->getId();
	} else {
		$rev_id = 0;
	}

	if (!$text) {
		$text  = $SocialRewarding["references"]["markupLinkStdText"] . " ";
		$text .= $SocialRewarding["references"]["markupSizeStdText"] . " ";
		$text .= $SocialRewarding["references"]["markupSelfLinkStdText"];
	}

	$sql = "SELECT
			link,
			count,
			size,
			self_link
		FROM
			" . $dbr->tableName($SocialRewarding["DB"]["references"]) . "
		WHERE
			rev_id = $rev_id
	";

	$rs = $dbr->query($sql);
	$row = $dbr->fetchRow($rs);

	if ($row) {
		$output = str_replace("$1", $row[0], $text);
		$output = str_replace("$2", $row[1], $output);
		$output = str_replace("$3", $row[2], $output);
		$output = str_replace("$4", $row[3], $output);
		$output = str_replace("$5", $wgLang->timeanddate($article->getTimestamp()), $output);
		$output .= "\n";
	}

	return $output;
}




/**
 * Markup for "Most Viewed Articles".
 *
 * @access public
 * @param String $text Markup text
 * @param array $arg Markup attributes
 * @return String HTML output
 */
function SocialRewardingMostViewedArticlesMarkup($text, $arg) {
	global $wgTitle, $mediaWiki, $wgLang;
	global $SocialRewarding;

	if ($arg["show"] == "true") {
		if (!$text) {
			$text = $SocialRewarding["viewed"]["stdMessage"];
		}

		$dbr =& wfGetDB(DB_SLAVE);
		extract($dbr->tableNames("page", "revision"));
		$article = $mediaWiki->articleFromTitle($wgTitle);
		$page_id = $article->getID();

		$sql = "SELECT
				SUM(rev_counter)
			FROM
				" . $dbr->tableName($SocialRewarding["DB"]["viewedArticles"]) . ",
				$revision,
				$page
			WHERE
				" . $dbr->tableName($SocialRewarding["DB"]["viewedArticles"]) . ".rev_id = $revision.rev_id
			AND
				rev_page = page_id
			AND
				page_id = $page_id
			GROUP BY
				page_id
		";

		$rs = $dbr->query($sql);
		$row = $dbr->fetchRow($rs);

		if ($row) {
			$output = str_replace("$1", $row[0], $text);
			$output = str_replace("$2", $wgLang->timeanddate($article->getTimestamp()), $output);
			$output .= "\n";
		}
	}

	return $output;

}







/**
 * Markup for "Rating of Articles" - displaying rating form.
 *
 * @access public
 * @param String $text Markup text
 * @param array $arg Markup attributes
 * @return String HTML output
 */
function SocialRewardingRatingOfArticlesMarkup($text, $arg) {
	global $wgScript, $wgTitle;
	global $SocialRewarding;

	$action = htmlspecialchars($wgScript);
	$title_text = $wgTitle -> getDBKey();
	$tokenized = SocialRewardingTokString($SocialRewarding["rating"]["scale"], $SocialRewarding["reward"]["delimiter"]);

	for ($i = 0; $i < count($tokenized); $i++) {
		$radio .= "<input type=radio name=points value='$tokenized[$i]'> $tokenized[$i] &nbsp;";
	}

	if ($SocialRewarding["rating"]["comment"] == true && $arg["comment"] == "true") {
		if ($arg["size"]) {
			$size = $arg["size"];
		} else {
			$size = $SocialRewarding["rating"]["commentStdSize"];
		}

		if ($arg["maxlength"]) {
			$max_length = $arg["maxlength"];
		} else {
			$max_length = $SocialRewarding["rating"]["commentStdMaxLength"];
		}

		$comment = "
			</tr>
			<tr>
				<td>
					Comment:
				</td>
				<td>
					<input type=text name=comment size='$size' maxlength='$max_length'>
				</td>
		";
	}

	if ($arg["buttoncaption"]) {
		$buttoncaption = $arg["buttoncaption"];
	} else {
		$buttoncaption = $SocialRewarding["rating"]["stdButtonCaption"];
	}

	$output = "
		<form action = $action method=get>
			<input type=hidden name=title value='Pathway:$title_text'>
			<input type=hidden name=SocialRewardingRating value=true>
			<table height=20 valign=center>
				<tr>
					<td>
						Rating:
					</td>
					<td>
						$radio
					</td>
						$comment
					<td>
						<input type=submit value='$buttoncaption'>
					</td>
				</tr>
			</table>
		</form>
	";


	if ($SocialRewarding["rating"]["popup"] == true && $arg["popup"] == "true") {
		if ($arg["popupmsg"]) {
			$msg = $arg["popupmsg"];
		} else {
			$msg = $SocialRewarding["rating"]["stdPopupMsg"];
		}
		
		$output .= "
			<script language=javascript>
				var uri = window.location.href;
				if (uri.indexOf('SocialRewardingRating=true')!=-1) {
					alert('$msg');
				}
			</script>
		";
	}

	return $output;
}




/**
 * Markup for "Rating of Articles" - displaying rating points.
 *
 * @access public
 * @param String $text Markup text
 * @param array $arg Markup attributes
 * @return String HTML output
 */
function SocialRewardingRatingPointsMarkup($text, $arg) {
	global $wgLang, $wgTitle, $mediaWiki;
	global $SocialRewarding;

	$dbr =& wfGetDB(DB_SLAVE);
	extract($dbr->tableNames("page", "revision"));
	$article = $mediaWiki->articleFromTitle($wgTitle);
	$page_id = $article->getID();

	if (!$text) {
		$text = $SocialRewarding["rating"]["markupStdPointsText"];
	}

	$sql = "SELECT
			SUM(count),
			SUM(points) / SUM(count) AS points
		FROM
			" . $dbr->tableName($SocialRewarding["DB"]["rating"]) . ",
			$revision,
			$page
		WHERE
			" . $dbr->tableName($SocialRewarding["DB"]["rating"]) . ".rev_id = $revision.rev_id
		AND
			rev_page = page_id
		AND
			page_id = $page_id
		GROUP BY
			page_id
	";

	$rs = $dbr->query($sql);
	$row = $dbr->fetchRow($rs);

	if ($row) {
		$output = str_replace("$1", $row[0], $text);
		$output = str_replace("$2", $row[1], $output);
		$output = str_replace("$3", $wgLang->timeanddate($article->getTimestamp()), $output);
		$output .= "\n";
	}

	return $output;
}



/**
 * Markup for "Recommender System".
 *
 * @access public
 * @param String $text Markup text
 * @param array $arg Markup attributes
 * @return String HTML output
 */
function SocialRewardingRecommendMarkup($text, $arg) {
	global $wgUser;
	global $SocialRewarding;

	$user = $wgUser->getName();
	$skin = $wgUser->getSkin();

	$mReward = new ManageReward();
	$reward = $mReward->getReward();
	$recommend = $reward->getRecommend();

	/**
	 * If loadAllData() was already called from cache manager
	 * ManageReward(), initialization will not happen twice so
	 * it is no problem to call the function again.
	 */
	$recommend->loadAllData();

	if ($arg["method"]) {
		$method = $arg["method"];
	} else {
		$method = $SocialRewarding["recommend"]["markupStdMethod"];
	}

	if ($method == "revision") {
		$recommendMethod = $recommend->recommendRevision[$user];
	} else if ($method == "article") {
		$recommendMethod = $recommend->recommendArticle[$user];
	} else if ($method == "interestedAuthor") {
		$recommendMethod = $recommend->interestedAuthor[$user];
	} else {
		$recommendMethod = $recommend->recommendAuthor[$user];
		$method = "author";
	}
	

	if (!$text) {
		$output = $SocialRewarding["recommend"]["markupStdMessage"] . "<br>";
	} else {
		$output = $text . "<br>";
	}

	$i = 0;
	if (is_array($recommendMethod)) {
		foreach($recommendMethod as $key => $val) {
			$i++;
			if ($method == "author" || $method == "interestedAuthor") {
				$outputAdd = $skin->makeLink("User:$key", $key);
			} else if ($method == "revision") {
				$revision = Revision::newFromId($key);
				if (is_object($revision)) {
					$title = $revision->getTitle();
					$titleText = $title->getText();
					$outputAdd = $skin->makeLink("Pathway:$titleText", $titleText, "oldid=$key");
				}
			} else if ($method == "article") {
				$title = Title::newFromID($key);
				$titleText = $title->getText();
				$outputAdd = $skin->makeLink("Pathway:$titleText", $titleText);
			}

			if ($arg["rank"] == "true" && $SocialRewarding["recommend"]["markupStdRank"] == true) {
				$output .= "$i. ";
			}
			$output .= "  $outputAdd";
			if ($arg["points"] == "true" && $SocialRewarding["recommend"]["markupStdPoints"] == true) {
				$output .= " (" . round($val, $SocialRewarding["reward"]["round"]) . ")";
			}
			$output .= "<br>";
		}
		$output .= "\n";
	}

	if ($i != 0) {
		return $output;
	}

}


?>
