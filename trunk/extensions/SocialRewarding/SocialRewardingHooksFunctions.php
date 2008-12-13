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
 * Define all hook functions which are loaded in
 * "SocialRewardingHooks.php".
 */



/**
 * Handels database insertion of hits or visits of a user for
 * "Most Viewed Articles".
 *
 * @access public
 * @param Article $article Article object
 */
function SocialRewardingMostViewed($article) {
	global $title;
	global $wgUser;
	global $SocialRewarding;

	// If page displays an article $rev_id != 0 (filter out special pages etc.)
	$rev_id = SocialRewardingGetRevID();
	$userID = 0;

	if ($SocialRewarding["viewed"]["addUserPages"] == false) {
		// Get user ID from page title, if displaying user-page $userID > 0
		$userID = $wgUser->idFromName($title);
	}

	if ($rev_id != 0 && $userID == 0) {
		$dbr =& wfGetDB(DB_SLAVE);
		extract($dbr->tableNames("revision", "page"));
		$mostViewedTable = $dbr->tableName($SocialRewarding["DB"]["viewedArticles"]);
		$visitRevisionTable = $dbr->tableName($SocialRewarding["DB"]["visitRevision"]);

		$rs = $dbr->selectRow(
				$mostViewedTable,
				array("rev_counter"),
				array("rev_id" => $rev_id)
		);

		$revCounter = $rs->rev_counter;

		// Do not count when article is saved, edited etc. or the history is displayed
		if (!$_GET["action"] && !$_GET["diff"]) {
			$checkAuthor = true;

			// Checks if user is also author of the article
			if ($SocialRewarding["viewed"]["countAuthor"] == false) {
				$rs = $dbr->query("SELECT * FROM $revision, $page WHERE page_id = " . $article->getID() . " AND page_id = rev_page AND rev_user_text = '" . $wgUser->getName() . "'");
				$count_rows = $dbr->numRows($rs);
				if ($count_rows != 0) {
					$checkAuthor = false;
				}
			}

			if ($checkAuthor == true) {
				if ($SocialRewarding["viewed"]["countMethod"] == "visits") {
					$now = SocialRewardingConvertTimestamp(wfTimestampNow());

					// Delete all entries where session is timed out
					SocialRewardingDeleteFromTable($visitRevisionTable, "rev_touched <= " . ($now - $SocialRewarding["viewed"]["sessionTimeout"]));
					$rs = $dbr->selectRow(
							$visitRevisionTable,
							array("rev_id"),
							array(	"rev_id" => $rev_id,
								"user_id" => $wgUser->getName()
							)
					);

					// If there are no entries in the table user session timed out
					if (!$rs->rev_id) {
						SocialRewardingMostViewedDBWrite($mostViewedTable, $rev_id, $revCounter);
					}

					// Insert new session timestamp
					SocialRewardingVisitsRatingRecommendInsert($visitRevisionTable, $rev_id, $wgUser->getName(), $now);
				} else {
					// Count hits regardless of session timeouts
					SocialRewardingMostViewedDBWrite($mostViewedTable, $rev_id, $revCounter);
				}
			}
		}
	}
	return true;
}



/**
 * Update or insert new hit or visit in database by calling
 * specified function.
 *
 * @access private
 * @param String $table Database table
 * @param int $rev_id Revision ID
 * @param int &$count Number of hit or visit
 */
function SocialRewardingMostViewedDBWrite($table, $rev_id, &$count) {
	if (isset($count)) {
		$count++;
		SocialRewardingMostViewedUpdate($table, $rev_id, $count);
	} else {
		$count = 1;
		SocialRewardingMostViewedInsert($table, $rev_id, $count);
	}
}


/**
 * Update row in database table ("Most Viewed Articles").
 *
 * @access private
 * @param String $table Database table
 * @param int $rev_id Revision ID
 * @param int $count Number of hit or visit
 */
function SocialRewardingMostViewedUpdate($table, $rev_id, $count) {
	$dbw =& wfGetDB(DB_MASTER);
	$dbw->update(
		$table,
		array("rev_counter" => $count),
		array("rev_id" => $rev_id)
	);
}


/**
 * Insert new row in database table ("Most Viewed Articles").
 *
 * @access private
 * @param String $table Database table
 * @param int $rev_id Revision ID
 * @param int $count Number of hit or visit
 */
function SocialRewardingMostViewedInsert($table, $rev_id, $count) {
	$dbw =& wfGetDB(DB_MASTER);
	$dbw->insert($table, array(
		"rev_id" => $rev_id,
		"rev_counter" => $count
	));
}


/**
 * For session timestamp: insert new row in database table
 * ("Most Viewed Articles" and "Recommender System").
 *
 * @access private
 * @param String $table Database table
 * @param int $rev_id Revision ID
 * @param String $user User name
 * @param int $timestamp UNIX timestamp
 */
function SocialRewardingVisitsRatingRecommendInsert($table, $rev_id, $user, $timestamp) {
	$dbw =& wfGetDB(DB_MASTER);
	$dbw->insert($table, array(
		"rev_id" => $rev_id,
		"user_id" => $user,
		"rev_touched" => $timestamp
	));
}


/**
 * Delete a row from database table with defined conditions.
 *
 * @access private
 * @param String $table Database table
 * @param String $cond Conditions of WHERE clause
 */
function SocialRewardingDeleteFromTable($table, $cond) {
	$dbw =& wfGetDB(DB_MASTER);
	$dbw->query("DELETE FROM $table WHERE $cond");
}






/**
 * Handle insertion of data in database of social rewarding method
 * "Rating of Articles".
 *
 * @access public
 */
function SocialRewardingRating() {
	global $wgUser;
	global $SocialRewarding;

	// Only when article is rated and points are given
	if ($_GET["SocialRewardingRating"] == "true" && $_GET["points"] > -1) {
		$dbr =& wfGetDB(DB_SLAVE);

		$ratedRevisionTable = $dbr->tableName($SocialRewarding["DB"]["ratedRevision"]);
		$ratingTable = $dbr->tableName($SocialRewarding["DB"]["rating"]);

		$rev_id = SocialRewardingGetRevID();
		$user = $wgUser->getName();
		$now = SocialRewardingConvertTimestamp(wfTimestampNow());
		$points = $_GET["points"];

		if ($SocialRewarding["rating"]["multipleVotes"] == true) {
			// Delete all entries where session is timed out
			SocialRewardingDeleteFromTable($ratedRevisionTable, "rev_touched <= " . ($now - $SocialRewarding["rating"]["voteTimeout"]));
		}

		$rs = $dbr->selectRow(
				$ratedRevisionTable,
				array("rev_id"),
				array(	"rev_id" => $rev_id,
					"user_id" => $user
				)
		);

		// If there are no entries in the table user session timed out
		if (!$rs->rev_id) {
			// Handle insertion of rating in database
			SocialRewardingSaveRating($ratingTable, $rev_id, $points);
			if ($SocialRewarding["rating"]["comment"] == true && $_GET["comment"]) {
				// Handle comment
				SocialRewardingRatingComment($_GET["comment"], $user, $points);
			}

		}

		// Insert new session timestamp
		SocialRewardingVisitsRatingRecommendInsert($ratedRevisionTable, $rev_id, $user, $now);

	}
	return true;
}


/**
 * Handle insertion of rating in database.
 *
 * @access private
 * @param String $table Database table
 * @param int $rev_id Revision ID
 * @param int $points Rating points
 */
function SocialRewardingSaveRating($table, $rev_id, $points) {
	// Checks if only logged in users and/or none authors of articles can vote
	if (SocialRewardingRatingOnlyUsers() == true && SocialRewardingRatingCountAuthor() == true) {
		$dbr =& wfGetDB(DB_SLAVE);
		$dbw =& wfGetDB(DB_MASTER);

		$rs = $dbr->selectRow(
				$table,
				array("points", "count"),
				array("rev_id" => $rev_id)
		);

		if (!$rs->count) {
			$dbw->insert($table, array(
				"rev_id" => $rev_id,
				"points" => $points,
				"count" => 1
			));
		} else {
			$dbw->update(
				$table,
				array(	"points" => $rs->points + $points,
					"count" => $rs->count + 1
				),
				array("rev_id" => $rev_id)
			);
		}
	}
}


/**
 * Check if user is logged in when testing is activated in
 * config file.
 *
 * @access private
 * @return boolean User logged in
 */
function SocialRewardingRatingOnlyUsers() {
	global $wgUser;
	global $SocialRewarding;

	if ($SocialRewarding["rating"]["onlyUsers"] == true) {
		if ($wgUser->isLoggedIn()) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}


/**
 * Check if user is author of article when testing is activated
 * in config file.
 *
 * @access private
 * @return boolean User is author
 */
function SocialRewardingRatingCountAuthor() {
	global $wgTitle, $wgUser, $mediaWiki;
	global $SocialRewarding;

	if ($SocialRewarding["rating"]["countAuthor"] == false) {
		$dbr =& wfGetDB(DB_SLAVE);
		extract($dbr->tableNames("revision", "page"));
		$article = $mediaWiki->articleFromTitle($wgTitle);
		$rs = $dbr->query("SELECT * FROM $revision, $page WHERE page_id = " . $article->getID() . " AND page_id = rev_page AND rev_user_text='" . $wgUser->getName() . "'");

		if ($row = $dbr->fetchRow($rs)) {
			return false;
		} else {
			return true;
		}

	} else { 
		return true;
	}
}




/**
 * Insert comment on Talk (Discussion) page.
 *
 * @access private
 * @param String $comment Comment
 * @param String $user User name
 * @param int $points Rating points
 */
function SocialRewardingRatingComment($comment, $user, $points) {
	global $wgTitle, $wgParser, $mediaWiki;
	global $SocialRewarding;

	require_once("EditPage.php");

	$article = $mediaWiki->articleFromTitle($wgTitle);
	// PHP5 style
	// $title_text = $article->getTitle()->getText();
	$atitle = $article->getTitle();
	$title_text = $atitle->getText();

	// New Title from text, namespace = 1 (Talk pages)
	$title = Title::newFromText($title_text, 1);
	// Revision ID = 0 means current revision
	$article = new Article($title, 0);
	$editpage = new EditPage($article);

	if ($SocialRewarding["rating"]["commentDisplayPoints"] == true) {
		$add_points = " - $points Point(s)";
	}

	$new_text = $wgParser->doHeadings("== $user$add_points ==") . "\n\r";
	$new_text .= $comment;

	$aid = $article->getID();

	if ($aid == 0) {
		// Article::insertNewArticle( $text, $summary, $isminor, $watchthis, $suppressRC=false, $comment=false )
		$editpage->mArticle->insertNewArticle($new_text, "", false, false);
	} else {
		$revision = Revision::newFromTitle($title);
		$old_text = $revision->getText();

		if ($SocialRewarding["rating"]["commentNewOnTop"] == true) {
			$text  = "$new_text\n\r$old_text";
		} else {
			$text  = "$old_text\n\r$new_text";
		}

		// Article::updateArticle( $text, $summary, $minor, $watchthis, $forceBot = false, $sectionanchor = '' )
		$editpage->mArticle->updateArticle($text, "", false, false);
	}
}



/**
 * Insert comment manually (without predefined functions of
 * MediaWiki).
 * ATTENTION: This function is incomplete and not used! It
 * will not work as expected!
 *
 * @access private
 * @param String $comment Comment
 * @param String $user User name
 * @param int $points Rating points
 */
function SocialRewardingRatingCommentManual($comment, $user, $points) {
	global $wgTitle, $wgParser, $wgUser, $mediaWiki;
	global $SocialRewarding;

	$dbr =& wfGetDB(DB_SLAVE);
	$dbw =& wfGetDB(DB_MASTER);

	extract($dbr->tableNames("page", "revision", "text"));

	$article = $mediaWiki->articleFromTitle($wgTitle);
	// PHP5 style
	// $title = $article->getTitle()->getDBkey();
	$atitle = $article->getTitle();
	$title = $atitle->getDBkey();
	$timestamp = wfTimestampNow();

	$rs = $dbr->query("SELECT page_id, page_latest FROM $page WHERE page_title = '$title' AND page_namespace = 1");
	$count_rows = $dbr->numRows($rs);

	$rs2 = $dbr->query("SELECT MAX(rev_id) FROM $revision");
	$row2 = $dbr->fetchRow($rs2);
	$rev_id = $row2[0];
	$new_rev_id = $row2[0] + 1;

	if ($count_rows != 0) {
		$row = $dbr->fetchRow($rs);
		$page_id = $row[0];
		$old_page_latest = $row[1];
	} else {
		$rs = $dbr->query("SELECT MAX(page_id) FROM $page");
		$row = $dbr->fetchRow($rs);

		$new_page_id = $row[0] + 1;
		wfSeedRandom();
		$rand = wfRandom();

		$sql = "
				INSERT INTO
					$page (page_id, page_namespace, page_title, page_random, page_touched, page_latest)
				VALUES
					('$new_page_id', 1, '$title', '$rand', '$timestamp', '$new_rev_id')
		";

		$dbw->query($sql);
		$page_id = $new_page_id;
	}

	$user_id = $wgUser->getID();

	$sql = "
			INSERT INTO
				$revision (rev_id, rev_page, rev_text_id, rev_comment, rev_user, rev_user_text, rev_timestamp)
			VALUES
				('$new_rev_id', '$page_id', '$new_rev_id', '$user', '$user_id', '$user', '$timestamp')
	";

	$dbw->query($sql);

	$rs = $dbr->query("SELECT old_text FROM $text WHERE old_id = '$old_page_latest'");
	$row = $dbr->fetchRow($rs);
	$old_text = $row[0];

	$new_text  = "$old_text\n\r";
	// $new_text .= $wgParser->doHeadings("== $user ==") . "\n\r";
	$new_text .= "== $user ==\n\r";
	$new_text .= $comment;

	$flags = Revision::compressRevisionText($new_text);
	$page_len = strlen($new_text);

	$sql = "
			INSERT INTO
				$text (old_id, old_text, old_flags)
			VALUES
				('$new_rev_id', '$new_text', '$flags')
	";

	$dbw->query($sql);

	$dbw->query("UPDATE $page SET page_latest = '$new_rev_id', page_len = '$page_len' WHERE page_id = '$page_id'");

	// How to update pages and links??
	// INCOMPLETE
	// ...
}






/**
 * Handle insertion of social rewarding method "Amount
 * of References" in database. To pass revision ID all
 * other parameters have also to be passed because of
 * hook definition.
 *
 * @access public
 * @param Article $wArticle Article object
 * @param User $wUser User object who saved the article
 * @param String $wText New article text
 * @param String $wSummary Article summary (comment)
 * @param boolean $wIsMinor Minor flag
 * @param boolean $wIsWatch Watch flag
 * @param int $wSection Section number
 * @param int bitfield $flags Type of edit
 * @param int $rev_id Revision ID
 * @return boolean Inserted data
 */
function SocialRewardingReferences($wArticle, $wUser, $wText, $wSummary, $wIsMinor, $wIsWatch, $wSection, $flags, $rev) {
        global $wgTitle, $mediaWiki;
        global $SocialRewarding;

        $dbw =& wfGetDB(DB_MASTER);
        $dbr =& wfGetDB(DB_SLAVE);
        $table = $dbr->tableName($SocialRewarding["DB"]["references"]);

        if (is_object($rev)) {
		$revision = $rev;
		$rev_id = $revision->getId();
	} else {
		$rev_id = $rev;
	       	$revision = Revision::newFromID($rev_id);
	}
	
	if (is_object($revision)){

//		$ns = $revision->getTitle()->getNamespace();
//		if($ns != NS_PATHWAY) return 0; 

                $rs = $dbr->query("SELECT * FROM $table WHERE rev_id = $rev_id");
                if ($dbr->numRows($rs) == 0) {


                        $i = 0;

                        //AP20081020  custom way to split text and count links
                        $pathway = Pathway::newFromTitle($revision->getTitle());
			//$pathway->setActiveRevision($rev_id); //only needed during initialization; breaks when saving a pathway
                        $text = $pathway->getGpml();
                        $split_text = explode('PublicationXRef xmlns', $text);

                        foreach($split_text as $key => $val) {
                                $i++;
                        }

                        $countSize = 0;
                        $countLink = 0;
                        $countSelfLink = 0;

                        //AP20081020 new definitions of count and countSelfLink
                        $count = $i - 1;
                        $countSelfLink = $count;

                        // Only insert data in table if at least one factor is activated
                        if ($SocialRewarding["references"]["siteSizeFactor"] == true || $SocialRewarding["references"]["siteLinkFactor"] == true || $SocialRewarding["references"]["siteSelfLinkFactor"] == true) {
                                SocialRewardingDeleteFromTable($table, "rev_id = $rev_id");
                                $dbw->insert($table, array(
                                                "rev_id" => $rev_id,
                                                "size" => $countSize,
                                                "link" => $countLink,
                                                "count" => $count,
                                                "self_link" => $countSelfLink
                                ));
                        }

                        return 1;
                } else {
                        return 0;
                }
	}
}


/**
 * Handle insertion of social rewarding method "Recommender
 * system" in database. Very similar to
 * "SocialRewardingMostViewed()";
 *
 * @access public
 * @param Article $article Article object
 */
function SocialRewardingRecommend($article) {
	global $title;
	global $wgUser;
	global $SocialRewarding;

	// If page displays an article $rev_id != 0 (filter out special pages etc.)
	$rev_id = SocialRewardingGetRevID();
	$userID = 0;


	if ($SocialRewarding["recommend"]["addUserPages"] == false) {
		// Get user ID from page title, if displaying user-page $userID > 0
		$userID = $wgUser->idFromName($title);
	}

	if ($rev_id != 0 && $userID == 0) {
		$dbr =& wfGetDB(DB_SLAVE);
		extract($dbr->tableNames("page", "revision"));
		$recommendTable = $dbr->tableName($SocialRewarding["DB"]["recommend"]);

		// Do not count when article is saved, edited etc. or the history is displayed
		if (!$_GET["action"] && !$_GET["diff"]) {
			$checkAuthor = true;

			// Checks if user is also author of the article
			if ($SocialRewarding["recommend"]["countAuthor"] == false) {
				$rs = $dbr->query("SELECT * FROM $revision, $page WHERE page_id = " . $article->getID() . " AND page_id = rev_page AND rev_user_text = '" . $wgUser->getName() . "'");
				$count_rows = $dbr->numRows($rs);
				if ($count_rows != 0) {
					$checkAuthor = false;
				}
			}

			if ($checkAuthor == true) {
				$now = SocialRewardingConvertTimestamp(wfTimestampNow());
				if ($SocialRewarding["recommend"]["countMethod"] == "visits") {
					$rs = $dbr->query("SELECT * FROM $recommendTable WHERE rev_id = $rev_id AND user_id = '" . $wgUser->getName() . "' AND timestamp >= " . ($now - $SocialRewarding["recommend"]["sessionTimeout"]));
					$count_rows = $dbr->numRows($rs);

					// If there are no results from the query user session timed out
					if ($count_rows == 0) {
						SocialRewardingRecommendInsert($recommendTable, $rev_id, $wgUser->getName(), $now);
					}
				} else {
					SocialRewardingRecommendInsert($recommendTable, $rev_id, $wgUser->getName(), $now);
				}
			}
		}

	}
	return true;
}


/**
 * Insert new row in database table ("Recommender System").
 *
 * @access private
 * @param String $table Database table
 * @param int $rev_id Revision ID
 * @param String $user_id User name
 * @param int $timestamp UNIX timestamp
 */
function SocialRewardingRecommendInsert($table, $rev_id, $user_id, $timestamp) {
	$dbw =& wfGetDB(DB_MASTER);
	$dbw->insert($table, array(
		"rev_id" => $rev_id,
		"user_id" => $user_id,
		"timestamp" => $timestamp
	));
}





/**
 * Handles insertion of auto-markups based on parameters
 * set in the configuration file.
 *
 * @access public
 * @param Article $article Article object
 * @return boolean Added auto markups
 */
function SocialRewardingAutoMarkup($article) {
	global $wgLang;
	global $SocialRewarding;

	$title = $article->getTitle();
	$ns = $title->getNamespace();

	/**
	 * Checks if namespace is a namespace of an article or if extra
	 * namespaces are defined if namespace is in list of extra
	 * namespaces (specified in the config file) and namespace has
	 * an even value (because odd namespaces are reserved for Talk
	 * pages).
	 */
	if ($ns == $SocialRewarding["reward"]["calcBasisArticlesNS"] || (eregi($ns, $SocialRewarding["reward"]["autoMarkupExtra"]) && fmod($ns, 2) == 0)) {
		require_once("EditPage.php");
		$editpage = new EditPage($article);
		$revision = Revision::newFromTitle($article->getTitle());
		if (is_object($revision)) {
			$old_text = $revision->getText();

			$addOn = false;
			$new_text  = "<!--  SocialRewarding Extension Automatic Code Insertion (" . $wgLang->timeanddate(wfTimestampNow()) . ") -->\n";

			if ($SocialRewarding["rating"]["autoMarkup"] == true) {
				if ($SocialRewarding["rating"]["comment"] == true) {
					$comment = " comment=true";
				}

				if ($SocialRewarding["rating"]["popup"] == true) {
					$popup = " popup=true";
				}

				$new_text .= "<SocialRewardingRatingOfArticles$comment$popup></SocialRewardingRatingOfArticles>\n";

				if ($SocialRewarding["rating"]["autoMarkupPoints"] == true) {
					$new_text .= "<SocialRewardingRatingPoints></SocialRewardingRatingPoints>\n";
				}

				$addOn = true;
			}

			if ($SocialRewarding["recommend"]["autoMarkup"] == true) {
				$new_text .= "<SocialRewardingRecommend></SocialRewardingRecommend>\n";
				$addOn = true;
			}

			if ($SocialRewarding["references"]["autoMarkup"] == true) {
				$new_text .= "<SocialRewardingReferences></SocialRewardingReferences>\n";
				$addOn = true;
			}

			if ($SocialRewarding["viewed"]["autoMarkup"] == true) {
				$new_text .= "<SocialRewardingMostViewedArticles show=true></SocialRewardingMostViewedArticles>\n";
				$addOn = true;
			}

			$new_text .= "<!-- End Of Automatic Insertion -->";

			if ($addOn) {
				$text = "$old_text\n\n\n$new_text";

				// Article::updateArticle( $text, $summary, $minor, $watchthis, $forceBot = false, $sectionanchor = '' )
				$editpage->mArticle->updateArticle($text, "", false, false);

				return 1;
			} else {
				return 0;
			}

		} else {
			return 0;
		}
	} else {
		return 0;
	}
	return true;
}



?>
