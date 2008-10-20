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
 * Activate markups if social rewarding methods are enabled.
 */

if ($SocialRewarding["reward"]["active"] == true) {

	if ($SocialRewarding["references"]["active"] == true) {
		$wgExtensionFunctions[] = "wfSocialRewardingReferencesMarkup";
		$wgExtensionCredits['other'][] = array(
			'name' => 'SocialRewardingReferencesMarkup',
			'description' => 'Markup for amount of references',
			'author' => 'Bernhard Hoisl'
		);


		/**
		 * Function to set markup "SocialRewardingReferences".
		 *
		 * @access private
		 */
		function wfSocialRewardingReferencesMarkup() {
			global $wgParser;
			$wgParser->setHook("SocialRewardingReferences", "SocialRewardingReferencesMarkup");
		}
	}


	if ($SocialRewarding["viewed"]["active"] == true) {
		$wgExtensionFunctions[] = "wfSocialRewardingMostViewedArticlesMarkup";
		$wgExtensionCredits['other'][] = array(
			'name' => 'SocialRewardingMostViewedArticlesMarkup',
			'description' => 'Markup for most viewed articles',
			'author' => 'Bernhard Hoisl'
		);


		/**
		 * Function to set markup "SocialRewardingMostViewedArticles".
		 *
		 * @access private
		 */
		function wfSocialRewardingMostViewedArticlesMarkup() {
			global $wgParser;
			$wgParser->setHook("SocialRewardingMostViewedArticles", "SocialRewardingMostViewedArticlesMarkup");
		}
	}


	if ($SocialRewarding["rating"]["active"] == true) {
		$wgExtensionFunctions[] = "wfSocialRewardingRatingOfArticlesMarkup";
		$wgExtensionCredits['other'][] = array(
			'name' => 'SocialRewardingRatingOfArticlesMarkup',
			'description' => 'Markup for rating of articles',
			'author' => 'Bernhard Hoisl'
		);


		/**
		 * Function to set markup "SocialRewardingRatingOfArticles".
		 *
		 * @access private
		 */
		function wfSocialRewardingRatingOfArticlesMarkup() {
			global $wgParser;
			$wgParser->setHook("SocialRewardingRatingOfArticles", "SocialRewardingRatingOfArticlesMarkup");
		}


		$wgExtensionFunctions[] = "wfSocialRewardingRatingPointsMarkup";
		$wgExtensionCredits['other'][] = array(
			'name' => 'SocialRewardingRatingPointsMarkup',
			'description' => 'Markup for displaying rating points',
			'author' => 'Bernhard Hoisl'
		);


		/**
		 * Function to set markup "SocialRewardingRatingPoints".
		 *
		 * @access private
		 */
		function wfSocialRewardingRatingPointsMarkup() {
			global $wgParser;
			$wgParser->setHook("SocialRewardingRatingPoints", "SocialRewardingRatingPointsMarkup");
		}
	}


	if ($SocialRewarding["recommend"]["active"] == true) {
		$wgExtensionFunctions[] = "wfSocialRewardingRecommendMarkup";
		$wgExtensionCredits['other'][] = array(
			'name' => 'SocialRewardingRecommendMarkup',
			'description' => 'Markup for recommender system',
			'author' => 'Bernhard Hoisl'
		);


		/**
		 * Function to set markup "SocialRewardingRecommend".
		 *
		 * @access private
		 */
		function wfSocialRewardingRecommendMarkup() {
			global $wgParser;
			$wgParser->setHook("SocialRewardingRecommend", "SocialRewardingRecommendMarkup");
		}
	}

}

?>