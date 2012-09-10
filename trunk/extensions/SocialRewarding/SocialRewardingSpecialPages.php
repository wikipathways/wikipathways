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
 * Activate special pages if social rewarding methods are enabled.
 */

if ($SocialRewarding["reward"]["active"] == true) {

	// Display install special page only if installation was not completed successfully yet
	if (file_exists($SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingINSTALL")) {
		$wgExtensionFunctions[] = "wfSocialRewardingInstallSpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingInstall",
			"description" => "SocialRewarding installation",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: Installation".
		 *
		 * @access private
		 */
		function wfSocialRewardingInstallSpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingInstall"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardinginstall" => "Social Rewarding: Installation")
			);
		}
	}


	$wgExtensionFunctions[] = "wfSocialRewardingAuthorsRankingSpecialPage";
	$wgExtensionCredits['specialpage'][] = array(
		"name" => "SocialRewardingAuthorsRanking",
		"description" => "Displays ranking of Authors",
		"author" => "Bernhard Hoisl"
	);


	/**
	 * Function to add special page "Social Rewarding: Ranking of Authors".
	 *
	 * @access private
	 */
	function wfSocialRewardingAuthorsRankingSpecialPage() {
		SpecialPage::addPage(new SpecialPage("SocialRewardingAuthorsRanking"));
		global $wgMessageCache;
		$wgMessageCache->addMessages(
			array("socialrewardingauthorsranking" => "Social Rewarding: Ranking of Authors")
		);
	}


	$wgExtensionFunctions[] = "wfSocialRewardingListUsersSpecialPage";
	$wgExtensionCredits['specialpage'][] = array(
		"name" => "SocialRewardingListUsers",
		"description" => "Displays user list",
		"author" => "Bernhard Hoisl"
	);


	/**
	 * Function to add special page "Social Rewarding: User List".
	 *
	 * @access private
	 */
	function wfSocialRewardingListUsersSpecialPage() {
		SpecialPage::addPage(new SpecialPage("SocialRewardingListUsers"));
		global $wgMessageCache;
		$wgMessageCache->addMessages(
			array("socialrewardinglistusers" => "Social Rewarding: User List")
		);
	}


	// Only if caching is true and caching method is set to "db"
	if ($SocialRewarding["reward"]["cache"] == true && $SocialRewarding["reward"]["cacheMethod"] == "db") {
		
		$wgExtensionFunctions[] = "wfSocialRewardingAuthorsHistorySpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingAuthorsHistory",
			"description" => "Displays history of ranking of authors",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: History of Ranking of Authors".
		 *
		 * @access private
		 */
		function wfSocialRewardingAuthorsHistorySpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingAuthorsHistory"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardingauthorshistory" => "Social Rewarding: History of Ranking of Authors")
			);
		}


	}



	if ($SocialRewarding["references"]["active"] == true) {
		$wgExtensionFunctions[] = "wfSocialRewardingReferencesRevisionsSpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingReferencesRevisions",
			"description" => "Displays list of referenced revisions",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: Amount of
		 * References of Revisions (unweighted, directly from DB)".
		 *
		 * @access private
		 */
		function wfSocialRewardingReferencesRevisionsSpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingReferencesRevisions"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardingreferencesrevisions" => "Social Rewarding: Amount of References of Revisions (unweighted, directly from DB)")
			);
		}



		$wgExtensionFunctions[] = "wfSocialRewardingReferencesArticlesSpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingReferencesArticles",
			"description" => "Displays list of referenced articles",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: Amount of
		 * References of Articles (unweighted, directly from DB)".
		 *
		 * @access private
		 */
		function wfSocialRewardingReferencesArticlesSpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingReferencesArticles"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardingreferencesarticles" => "Social Rewarding: Amount of References of Articles (unweighted, directly from DB)")
			);
		}

	}






	if ($SocialRewarding["rating"]["active"] == true) {
		$wgExtensionFunctions[] = "wfSocialRewardingRatingRevisionsSpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingRatingRevisions",
			"description" => "Displays list of rated revisions",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: Rating of
		 * Revisions (unweighted, directly from DB)".
		 *
		 * @access private
		 */
		function wfSocialRewardingRatingRevisionsSpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingRatingRevisions"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardingratingrevisions" => "Social Rewarding: Rating of Revisions (unweighted, directly from DB)")
			);
		}



		$wgExtensionFunctions[] = "wfSocialRewardingRatingArticlesSpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingRatingArticles",
			"description" => "Displays list of rated articles",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: Rating of
		 * Articles (unweighted, directly from DB)".
		 *
		 * @access private
		 */
		function wfSocialRewardingRatingArticlesSpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingRatingArticles"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardingratingarticles" => "Social Rewarding: Rating of Articles (unweighted, directly from DB)")
			);
		}

	}







	if ($SocialRewarding["viewed"]["active"] == true) {
		$wgExtensionFunctions[] = "wfSocialRewardingMostViewedRevisionsSpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingMostViewedRevisions",
			"description" => "Displays list of most viewed revisions",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: Most Viewed
		 * Revisions (unweighted, directly from DB)".
		 *
		 * @access private
		 */
		function wfSocialRewardingMostViewedRevisionsSpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingMostViewedRevisions"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardingmostviewedrevisions" => "Social Rewarding: Most Viewed Revisions (unweighted, directly from DB)")
			);
		}



		$wgExtensionFunctions[] = "wfSocialRewardingMostViewedArticlesSpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingMostViewedArticles",
			"description" => "Displays list of most viewed articles",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: Most Viewed
		 * Articles (unweighted, directly from DB)".
		 *
		 * @access private
		 */
		function wfSocialRewardingMostViewedArticlesSpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingMostViewedArticles"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardingmostviewedarticles" => "Social Rewarding: Most Viewed Articles (unweighted, directly from DB)")
			);
		}

	}







	if ($SocialRewarding["recommend"]["active"] == true) {
		$wgExtensionFunctions[] = "wfSocialRewardingRecommenderSystemSpecialPage";
		$wgExtensionCredits['specialpage'][] = array(
			"name" => "SocialRewardingRecommenderSystem",
			"description" => "Displays list of recommended revisions/articles/authors and authors with same interest",
			"author" => "Bernhard Hoisl"
		);


		/**
		 * Function to add special page "Social Rewarding: Recommender
		 * System".
		 *
		 * @access private
		 */
		function wfSocialRewardingRecommenderSystemSpecialPage() {
			SpecialPage::addPage(new SpecialPage("SocialRewardingRecommenderSystem"));
			global $wgMessageCache;
			$wgMessageCache->addMessages(
				array("socialrewardingrecommendersystem" => "Social Rewarding: Recommender System")
			);
		}

	}


}

?>