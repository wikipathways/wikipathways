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
 * Activate hooks if social rewarding methods are enabled.
 */

if ($SocialRewarding["reward"]["active"] == true) {

	// PHP must be >= 5 and installation must be finished
	if ($SocialRewarding["references"]["active"] == true && SocialRewardingGetPHPVersion() >= 5 && !file_exists($SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingINSTALL")) {
		// Everytime an article is edited
		$wgHooks["ArticleSaveComplete"][] = "SocialRewardingReferences";
	}

	if ($SocialRewarding["rating"]["active"] == true) {
		/**
		 * Need hook that does not produce endless loops, but is
		 * called every time an article is loaded.
		 */
		$wgHooks["SkinTemplateContentActions"][] = "SocialRewardingRating";
	}

	if ($SocialRewarding["viewed"]["active"] == true) {
		// Before page data is loaded
		$wgHooks["ArticlePageDataBefore"][] = "SocialRewardingMostViewed";
	}

	if ($SocialRewarding["recommend"]["active"] == true) {
		// After page data is loaded
		$wgHooks["ArticlePageDataAfter"][] = "SocialRewardingRecommend";
	}

	if ($SocialRewarding["reward"]["autoMarkup"] == true) {
		// After submitting a new article
		$wgHooks["ArticleInsertComplete"][] = "SocialRewardingAutoMarkup";
	}

}

?>