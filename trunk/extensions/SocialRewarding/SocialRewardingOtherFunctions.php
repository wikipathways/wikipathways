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
 * Container for all useful functions which are not defined elsewhere.
 */



/**
 * Get user ID from logged in user or remote address if user is not
 * logged in.
 *
 * @access public
 * @return int User ID
 * or
 * @return String Remote address
 */
function SocialRewardingGetUserID() {
	global $wgUser;
	if ($wgUser->isLoggedIn()) {
		return $wgUser->getID();
	} else {
		return $_SERVER["REMOTE_ADDR"];
	}
}


/**
 * Convert timestamp to UNIX timestamp. There are two methods one for
 * PHP versions prior 5 and one for newer PHP verions.
 *
 * @access public
 * @param int $timestamp Timestamp (format like "YYYYMMDDHHMMSS")
 * @return int UNIX timestamp
 */
function SocialRewardingConvertTimestamp($timestamp) {
	if (SocialRewardingGetPHPVersion() >= 5) {
		$newDate = strtotime($timestamp);
	} else {
		$newDate = substr($timestamp,0,4) . "-" . substr($timestamp,4,2) . "-" . substr($timestamp,6,2) . " " . substr($timestamp,8,2) . ":" . substr($timestamp,10,2) . ":" . substr($timestamp,12,2);
		$newDate = strtotime($newDate);
	}
	return $newDate;
}


/**
 * Get current PHP version.
 *
 * @access public
 * @return String PHP version
 */
function SocialRewardingGetPHPVersion() {
	return phpversion();
}


/**
 * Get remote (IP) address from user host.
 *
 * @access public
 * @return String Remote address
 */
function SocialRewardingGetIP() {
	return $_SERVER["REMOTE_ADDR"];
}


/**
 * Get current URL.
 *
 * @access public
 * @return String URL
 */
function SocialRewardingGetURL() {
	return $_SERVER["REQUEST_URI"];
}


/**
 * Get current UNIX timestamp (time measured in the number of seconds
 * since the Unix Epoch from January 1 1970 00:00:00 GMT).
 *
 * @access public
 * @return int UNIX timestamp
 */
function SocialRewardingNow() {
	return time();
}


/**
 * Fetch current revision ID from article (if an article is displayed),
 * else return 0.
 *
 * @access public
 * @return int Revision ID
 */
function SocialRewardingGetRevID() {
	global $mediaWiki, $wgTitle;
	
	/* TK20081023
	 * $mediawiki is not defined if MW is not called from index.php, see:
	 * http://www.mediawiki.org/wiki/Manual:$mediaWiki
	 * By checking this first, we prevent crashes for the api.php, xml-rpc and soap webservice.
	 */
	if(!$mediaWiki) {
		return 0;
	}
	
	$article = $mediaWiki->articleFromTitle($wgTitle);
	if ($article) {
		return $article->getRevIdFetched();
	} else {
		return 0;
	}
}


/**
 * Split a string by a defined delimiter symbol and save pieces in
 * an array which is returned.
 *
 * @access public
 * @param String $string Text to be tokenized
 * @param String $delimiter Delimiter symbol
 * @return array Splitted text
 */
function SocialRewardingTokString($string, $delimiter) {
	$tok = strtok($string, $delimiter);
	while ($tok !== false) {
		$split[] = $tok;
		$tok = strtok($delimiter);
	}
	return $split;
}


/**
 * Return title text from database key.
 *
 * @access public
 * @param String $DBtitle Database key
 * @return String Title text
 */
function SocialRewardingDisplayTitle($DBtitle) {
        $pathway = Pathway::newFromTitle($DBtitle);
        $t = $pathway->getSpecies().":".$pathway->getName();
	return $t;

//	$t = Title::newFromDBkey($DBtitle);
//	return $t->getText();
}


/**
 * Get article ID from revision ID.
 *
 * @access public
 * @param int $rev_id Revision ID
 * @return int Article ID
 */
function SocialRewardingGetArticleFromRev($rev_id) {
	$revision = Revision::newFromId($rev_id);
	return $revision->getPage();
}


/**
 * Get author name from revision ID.
 *
 * @access public
 * @param int $rev_id Revision ID
 * @return String Author name
 */
function SocialRewardingGetAuthorFromRev($rev_id) {
	$revision = Revision::newFromId($rev_id);
	if (is_object($revision)) {
		// Fetch revision's author name without regard for view restrictions
		return $revision->getRawUserText();
	}
}


/**
 * Return current UNIX timestamp with microseconds. Function is
 * only necessary for PHP versions prior 5 to replicate PHP 5
 * behavior.
 *
 * @access public
 * @return float UNIX timestap with microseconds
 */
function SocialRewardingMicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

?>
