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
 * Short config file of the SocialRewarding package. If you feel the need
 * to configure something, do it here.
 */



/**********************************************************
 * GLOBAL SOCIAL REWARDING CONFIGURATION
 **********************************************************/

$SocialRewarding["reward"]["active"]			= true;				// Activate SocialRewarding extension?
$SocialRewarding["reward"]["extensionPath"]		= "extensions/SocialRewarding";	// The path to the SocialRewarding extension directory.
$SocialRewarding["reward"]["cache"]			= true;				// Activate caching?
$SocialRewarding["reward"]["cacheRecommend"]		= true;				// Cache social rewarding mechanism "Recommender System", too?
$SocialRewarding["reward"]["cacheMethod"]		= "db";				// Caching method: "db" or "file".
$SocialRewarding["reward"]["cacheTime"]			= 600;				// Cache refresh in seconds, e.g. 1 hour = 3600, 1 day = 86400.
$SocialRewarding["reward"]["cacheFile"]			= "data";			// File name for caching (only necessary if caching is based on file system).
$SocialRewarding["reward"]["sizeMethod"]		= "LENGTH";			// Method for computing size: "LENGTH" or "CHAR_LENGTH".
$SocialRewarding["reward"]["round"]			= 2;				// All floats are rounded to x decimal places.
$SocialRewarding["reward"]["delimiter"]			= ",";				// Delimiter for string manipulation.
$SocialRewarding["reward"]["beginTimeInterval"]		= "";				// Timestamp from which to begin ("" = first timestamp in database).
$SocialRewarding["reward"]["endTimeInterval"]		= "";				// Ending timestamp ("" = last timestamp in database).
$SocialRewarding["reward"]["timeSizeWeight"]		= "50,50";			// Percentage of weighting of time and size calculation methods (in this order).
$SocialRewarding["reward"]["sizeCalcMethod"]		= "repr";			// Size calculation method: "mean" or "repr"esentative ("repr" is highly recommended).
$SocialRewarding["reward"]["articlePointsDecimalPlace"]	= true;				// Calculate article points with decimal places?
$SocialRewarding["reward"]["methodsWeight"]		= "33,33,33";			// Weighting for the three social rewarding mechanism in % (order: references, rating, viewed).
$SocialRewarding["reward"]["calcBasis"]			= "all";			// Calculation basis: "all", "articles", or "user_pages".
$SocialRewarding["reward"]["calcBasisCorrection"]	= true;				// Corrects calculation basis if basis is set to "all".
$SocialRewarding["reward"]["calcBasisCorrectionNS"]	= 8;				// Namespace for system pages (for correction).
$SocialRewarding["reward"]["calcBasisArticlesNS"]	= 0;				// Namespace for articles (main pages).
$SocialRewarding["reward"]["calcBasisUserPagesNS"]	= 2;				// Namespace for user-pages.
$SocialRewarding["reward"]["calcMethodStars"]		= "min_max";			// Stars calculation method: "min_max" or "mean".
$SocialRewarding["reward"]["calcUsersOnly"]		= false;			// Calculate only on registered users (without "MediaWiki default" user)?
$SocialRewarding["reward"]["starsScale"]		= "0,15,35,55,75,90";		// Scale for computing stars (in %).
$SocialRewarding["reward"]["starsDisplayHalf"]		= true;				// Display half stars? If true then "starsHalfBorders" and "starsHalf" must be set.
$SocialRewarding["reward"]["starsHalfBorders"]		= "25,75";			// Between which range (in %) should a half star be displayed?
$SocialRewarding["reward"]["starsRound"]		= "round";			// Rounding method for stars ("floor", "ceil", or "round"); only if "starsDisplayHalf" = false.
$SocialRewarding["reward"]["starsDir"]			= "SocialRewardingImg";		// Directory pointing to stars images.
$SocialRewarding["reward"]["starsFull"]			= "star_full.gif";		// File name of image for a full star.
$SocialRewarding["reward"]["starsEmpty"]		= "star_empty.gif";		// File name of image for an emtpy star.
$SocialRewarding["reward"]["starsHalf"]			= "star_half.gif";		// File name of image for a half star (only needed if "starsDisplayHalf" = true).
$SocialRewarding["reward"]["sparklinesPackageDir"]	= "sparkline-php-0.2";		// Name of directory to sparkline package.
$SocialRewarding["reward"]["sparklinesInterval"]	= 20;				// Displaying interval for sparklines.
$SocialRewarding["reward"]["sparklinesMinPercent"]	= 10;				// Minimum percentage value for sparklines to be displayed definitely.
$SocialRewarding["reward"]["sparklinesWidth"]		= 2;				// Width of sparklines in pixel.
$SocialRewarding["reward"]["sparklinesSpacing"]		= 0;				// Spacing of sparklines in pixel.
$SocialRewarding["reward"]["sparklinesHeight"]		= 12;				// Maximum height of sparklines in pixel. You should try to match height of stars.
$SocialRewarding["reward"]["sparklinesUnderAvgColor"]	= "C0C0C0";			// Color for under average sparklines.
$SocialRewarding["reward"]["sparklinesOverAvgColor"]	= "808080";			// Color for over average sparklines.
$SocialRewarding["reward"]["sparklinesBGColor"]		= "FFFFFF";			// Background color for sparklines.
$SocialRewarding["reward"]["autoMarkup"]		= true;				// Set automatic markups active? Every method has to be set active on its own.
$SocialRewarding["reward"]["autoMarkupExtra"]		= "";				// On which extra namespaces should auto-markups also be set?



/**********************************************************
 * AMOUNT OF REFERENCES CONFIGURATION
 **********************************************************/

$SocialRewarding["references"]["active"]		= true;				// Activate social rewarding mechanism "Amount of References" (need PHP >= 5)?
$SocialRewarding["references"]["siteSizeFactor"]	= true;				// Use site size factor for calculation?
$SocialRewarding["references"]["siteLinkFactor"]	= true;				// Use site link factor for calculation?
$SocialRewarding["references"]["siteSelfLinkFactor"]	= false;			// Use site self link factor for calculation?
$SocialRewarding["references"]["siteWeight"]		= "25,25,50";			// Weighting of "siteSizeFactor", "siteLinkFactor", and "siteSelfLinkFactor" (in %).
$SocialRewarding["references"]["articleScale"]		= "0,15,33,66,100,150";		// Scale for computing points of an article (in %).
$SocialRewarding["references"]["calcBasis"]		= "all";			// Calculation basis: "all", "articles", or "user_pages".
$SocialRewarding["references"]["calcBasisCorrection"]	= true;				// Corrects calculation basis if basis is set to "all".
$SocialRewarding["references"]["textMode"]		= "section";			// Limit search of links to a specified "section" or whole "article".
$SocialRewarding["references"]["textSection"]		= "References,Bibliography";	// List of delimiter words to recognize reference sections.
$SocialRewarding["references"]["textDelimiter"]		= "==";				// List of delimiter symbols to indicate headings.
$SocialRewarding["references"]["linkStart"]		= "http://";			// Start text indicating a link (recommended not to edit).
$SocialRewarding["references"]["linkDelimiter"]		= " <>[]'\"";			// Delimiter characters to find the end of a link.
$SocialRewarding["references"]["stripWWW"]		= true;				// Strips beginning www from links (better results for "siteSize").
$SocialRewarding["references"]["googleWholeDomain"]	= true;				// Search with Google on whole domain instead of subdirectories only.
$SocialRewarding["references"]["googleKey"]		= "";	// License key for using Google's SOAP Search API (Beta).
$SocialRewarding["references"]["googleFilter"]		= false;			// Filter Google results?
$SocialRewarding["references"]["googleRestrictCountry"]	= "";				// Restrict Google results to a country or topic (e.g. "countryAT")?
$SocialRewarding["references"]["googleRestrictLang"]	= "";				// Restrict google results to a language (e.g. "lang_de")?
$SocialRewarding["references"]["googleSafeSearch"]	= false;			// Activate Google's SafeSearch (filters adult content)?
$SocialRewarding["references"]["googleSearchAttempts"]	= 2;				// If an error occurs, how often should be tried to execute the query?
$SocialRewarding["references"]["markupLinkStdText"]	= "There is a total of $1 links pointing to the references ($2) in this article.";	// Standard markup message for "siteLinkFactor".
$SocialRewarding["references"]["markupSizeStdText"]	= "The total size of the sites used as references in this article is $3.";		// Standard markup message for "siteSizeFactor".
$SocialRewarding["references"]["markupSelfLinkStdText"]	= "$4 links are pointing to this site (until $5).";					// Standard markup message for "siteSelfLinkFactor".
$SocialRewarding["references"]["autoMarkup"]		= true;				// Set automatic "Amount of References" markup active?



/**********************************************************
 * RATING OF ARTICLES CONFIGURATION
 **********************************************************/

$SocialRewarding["rating"]["active"]			= true;				// Activate social rewarding mechanism "Rating of Articles"? 
$SocialRewarding["rating"]["scale"]			= "0,1,2,3,4,5";		// Rating scale (points a user can vote for an article).
$SocialRewarding["rating"]["comment"]			= true;				// Allow user comments (can on each page manually be turned off)?
$SocialRewarding["rating"]["commentStdSize"]		= 32;				// Standard size for comment text field.
$SocialRewarding["rating"]["commentStdMaxLength"]	= 255;				// Standard maximum character length for comments.
$SocialRewarding["rating"]["commentNewOnTop"]		= true;				// Should new comments be inserted on top of talk pages?
$SocialRewarding["rating"]["commentDisplayPoints"]	= true;				// Should voted points of a user be displayed on talk pages?
$SocialRewarding["rating"]["stdButtonCaption"]		= " Vote > ";			// Standard caption of rating button.
$SocialRewarding["rating"]["popup"]			= true;				// Should a JavaScript window be displayed after voting? 
$SocialRewarding["rating"]["stdPopupMsg"]		= "Thank you for your vote.";	// Standard message for JavaScript window. 
$SocialRewarding["rating"]["countAuthor"]		= true;				// Should a vote from an author of an article be counted?
$SocialRewarding["rating"]["onlyUsers"]			= false;			// Should only logged in users have the possibility to vote?
$SocialRewarding["rating"]["multipleVotes"]		= true;				// Should users has the possibility to vote several times?
$SocialRewarding["rating"]["voteTimeout"]		= 0;				// Timeout before next vote (in seconds), 0 = no timeout. Only if "multipleVotes" = true.
$SocialRewarding["rating"]["markupStdPointsText"]	= "This article was rated $1 time(s) with a score of $2 point(s) (until $3).";	// Standard markup text for "Rating of Articles".
$SocialRewarding["rating"]["autoMarkup"]		= true;				// Set automatic "Rating of Articles" (rating form) markup active?
$SocialRewarding["rating"]["autoMarkupPoints"]		= true;				// Set automatic "Rating of Articles" (display rating points) markup active?



/**********************************************************
 * MOST VIEWED ARTICLES CONFIGURATION
 **********************************************************/

$SocialRewarding["viewed"]["active"]			= true;				// Activate social rewarding mechanism "Most Viewed Articles"?
$SocialRewarding["viewed"]["stdMessage"]		= "This article has been accessed $1 time(s) (until $2).";	// Standard markup text for "Most Viewed Articles".
$SocialRewarding["viewed"]["countMethod"]		= "hits";			// Counting method: "hits" or "visits".
$SocialRewarding["viewed"]["countAuthor"]		= true;				// Count hits or visits from authors of an article?
$SocialRewarding["viewed"]["sessionTimeout"]		= 1800;				// Session timeout in seconds (important only with counting method "visits").
$SocialRewarding["viewed"]["addUserPages"]		= true;				// Do also compute hits or visits on user-pages?
$SocialRewarding["viewed"]["articleScale"]		= "0,25,50,100,200,300";	// Scale for calculating points of an article (in %).
$SocialRewarding["viewed"]["calcBasis"]			= "all";			// Calculation basis: "all", "articles", or "user_pages".
$SocialRewarding["viewed"]["calcBasisCorrection"]	= true;				// Corrects calculation basis if basis is set to "all".
$SocialRewarding["viewed"]["autoMarkup"]		= true;				// Set automatic "Most Viewed Articles" markup active?



/**********************************************************
 * RECOMMENDER SYSTEM CONFIGURATION
 **********************************************************/

$SocialRewarding["recommend"]["active"]			= true;				// Activate social rewarding mechanism "Recommender System"? 
$SocialRewarding["recommend"]["countMethod"]		= "hits";			// Counting method: "hits" or "visits".
$SocialRewarding["recommend"]["countAuthor"]		= true;				// Count hits or visits from authors of an article?
$SocialRewarding["recommend"]["sessionTimeout"]		= 1800;				// Session timeout in seconds (important only with counting method "visits").
$SocialRewarding["recommend"]["addUserPages"]		= true;				// Do also compute hits or visits on user-pages?
$SocialRewarding["recommend"]["reduceData"]		= 5;				// Calculate recommendations on how much top-articles of an author?
$SocialRewarding["recommend"]["countingMethod"]		= "repr";			// Counting method: "equal" or "repr"esentative.
$SocialRewarding["recommend"]["weighting"]		= true;				// Activate weighting method of articles?
$SocialRewarding["recommend"]["weightingMethod"]	= "repr";			// Weighting method: "equal" or "repr"esentative.
$SocialRewarding["recommend"]["reduceRecommendation"]	= 5;				// How much recommendations should be displayed?
$SocialRewarding["recommend"]["excludeVisitedArticles"]	= true;				// Exclude user's visited articles?
$SocialRewarding["recommend"]["excludeUsersArticles"]	= true;				// Exclude articles where user is author?
$SocialRewarding["recommend"]["markupStdMessage"]	= "Other interesting authors:";	// Standard markup text for "Recommender System".
$SocialRewarding["recommend"]["markupStdMethod"]	= "author";			// Standard "Recommender System" markup method ("revision", "article", "author", or "interestedAuthor").
$SocialRewarding["recommend"]["markupStdRank"]		= false;			// Display rank/position as standard?
$SocialRewarding["recommend"]["markupStdPoints"]	= false;			// Display points as standard?
$SocialRewarding["recommend"]["autoMarkup"]		= true;				// Set automatic "Recommender System" markup active?



/**********************************************************
 * DATABASE TABLES CONFIGURATION
 **********************************************************/

$SocialRewarding["DB"]["cache"]				= "sr__cache";			// Table to store cached objects in database.
$SocialRewarding["DB"]["references"]			= "sr__references";		// Table to store data for social rewarding mechanism "Amount of References".
$SocialRewarding["DB"]["ratedRevision"]			= "sr__ratedrevision";		// Table to store data for multiple votes and timeout of votes ("Rating of Articles").
$SocialRewarding["DB"]["rating"]			= "sr__rating";			// Table to store data for social rewarding mechanism "Rating of Articles".
$SocialRewarding["DB"]["viewedArticles"]		= "sr__viewedarticles";		// Table to store data for social rewarding mechanism "Most Viewed Articles".
$SocialRewarding["DB"]["visitRevision"]			= "sr__visitrevision";		// Table to store data for multiple visits and timeout of visits ("Most Viewed Articles").
$SocialRewarding["DB"]["recommend"]			= "sr__recommend";		// Table to store data for social rewarding mechanism "Recommender System".


?>