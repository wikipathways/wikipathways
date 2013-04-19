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
 * Detailed config file of the SocialRewarding package. If you feel the
 * need to configure something, do it here.
 */



/**********************************************************
 * GLOBAL SOCIAL REWARDING CONFIGURATION
 **********************************************************/

/**
 * Activate SocialRewarding extension?
 */

$SocialRewarding["reward"]["active"]			= true;


/**
 * The path to the SocialRewarding extension directory (where you
 * copied all files of the SocialRewarding extension to).
 */

$SocialRewarding["reward"]["extensionPath"]		= "extensions/SocialRewarding";


/**
 * Activate caching?
 */

$SocialRewarding["reward"]["cache"]			= false;


/**
 * Caching for social rewarding mechanism "Recommender Systems" can be
 * activated separately. $SocialRewarding["reward"]["cache"] must be
 * set true for this to take effect.
 */

$SocialRewarding["reward"]["cacheRecommend"]		= true;


/**
 * Caching method: "db" or "file". By selecting "db" a history of
 * calculated rankings of authors is kept. Attention: Data
 * to store in the database can exceed "max_allowed_packet"
 * size of MySQL. Then the limitation has to be extended.
 */

$SocialRewarding["reward"]["cacheMethod"]		= "db";


/**
 * Cache refresh in seconds, e.g. 1 hour = 3600, 1 day = 86400.
 */

$SocialRewarding["reward"]["cacheTime"]			= 600;


/**
 * Name of file for caching (only necessary if caching is based on file
 * system (that means $SocialRewarding["reward"]["cacheMethod"] = "file").
 */

$SocialRewarding["reward"]["cacheFile"]			= "SocialRewardingCache";


/**
 * Computing the size of a revision using methods "LENGTH" or
 * "CHAR_LENGTH" of MySQL. For an explanation consult the MySQL
 * documentation.
 */

$SocialRewarding["reward"]["sizeMethod"]		= "LENGTH";


/**
 * All floats are rounded to x decimal places.
 */

$SocialRewarding["reward"]["round"]			= 2;


/**
 * Delimiter for string manipulation. Every string which should be
 * tokenized has to be formatted using the character beneath as
 * delimiter.
 */

$SocialRewarding["reward"]["delimiter"]			= ",";


/**
 * Timestamp from which to begin calculating all social rewarding
 * mechanism ("" = first timestamp in database).
 */

$SocialRewarding["reward"]["beginTimeInterval"]		= "";


/**
 * Ending timestamp for calculation ("" = last timestamp in
 * database).
 */

$SocialRewarding["reward"]["endTimeInterval"]		= "";


/**
 * Percentage of weighting of time and size calculation methods
 * (in this order).
 */

$SocialRewarding["reward"]["timeSizeWeight"]		= "50,50";


/**
 * Calculation method for computing the size of a revision. "mean" is
 * faster and calculates the size of a revision by using an averaged
 * value. "repr" is a little slower but highly recommended because
 * the size changes from one revision to another is calculated using
 * exact values.
 */

$SocialRewarding["reward"]["sizeCalcMethod"]		= "repr";


/**
 * Calculate article points with decimal places? Only for calculation,
 * displayed values are rounded using
 * $SocialRewarding["reward"]["round"].
 */

$SocialRewarding["reward"]["articlePointsDecimalPlace"]	= true;


/**
 * Weighting for the three social rewarding mechanisms in percent.
 * Order: "Amount of References", "Rating of Articles" and "Most
 * Viewed Articles".
 */

$SocialRewarding["reward"]["methodsWeight"]		= "33,33,33";


/**
 * Basis for calculating social rewarding mechanisms: "all",
 * "articles", or "user_pages".
 */

$SocialRewarding["reward"]["calcBasis"]			= "all";


/**
 * Corrects calculation basis, that means excluding system pages
 * (=pages with namespace like
 * $SocialRewarding["reward"]["calcBasisCorrectionNS"]).
 */

$SocialRewarding["reward"]["calcBasisCorrection"]	= true;


/**
 * Namespace for system pages.
 */

$SocialRewarding["reward"]["calcBasisCorrectionNS"]	= 8;


/**
 * Namespace for articles (main pages).
 */

$SocialRewarding["reward"]["calcBasisArticlesNS"]	= 102;


/**
 * Namespace for user-pages.
 */

$SocialRewarding["reward"]["calcBasisUserPagesNS"]	= 2;


/**
 * Calculation method for authors' stars. "min_max": obtain a minimum
 * and a maximum value from all authors. By taking points from one
 * author with this range a percentage value can be generated. "mean":
 * getting an overall average value from all authors. By relating
 * points from one author to this average value percentage data can
 * be generated.
 */

$SocialRewarding["reward"]["calcMethodStars"]		= "min_max";


/**
 * Calculate social rewarding mechanisms only on registered users
 * (without "MediaWiki default" user)?
 */

$SocialRewarding["reward"]["calcUsersOnly"]		= true;


/**
 * Scale for calculating stars for authors in percent, e.g. from 0
 * to 15 percent 0 stars are displayed, from 15 to 35 percent 1 star
 * and so on. Scaling must be configured regarding which calculation
 * method is set in $SocialRewarding["reward"]["calcMethodStars"] and
 * the structure of the wiki community.
 */

$SocialRewarding["reward"]["starsScale"]		= "0,15,35,55,75,90";


/**
 * Should half stars be displayed? If set to true then
 * $SocialRewarding["reward"]["starsHalfBorders"] and
 * $SocialRewarding["reward"]["starsHalf"] must be set.
 */

$SocialRewarding["reward"]["starsDisplayHalf"]		= true;


/**
 * Between which range (in percent) should a half star be displayed?
 * For instance: "25,75" means that between a value of 2.25 and 2.75
 * two stars and a half is displayed.
 */

$SocialRewarding["reward"]["starsHalfBorders"]		= "25,75";


/**
 * Rounding method for stars, only if
 * $SocialRewarding["reward"]["starsDisplayHalf"] is set false. "floor":
 * rounding down to the previous integer; "ceil": rounding up to the
 * next integer; "round": rounding to the nearest integer (e.g. .5:
 * rounding up).
 */

$SocialRewarding["reward"]["starsRound"]		= "round";


/**
 * Directory pointing to stars images.
 */

$SocialRewarding["reward"]["starsDir"]			= "SocialRewardingImg";


/**
 * File name of image for a full star.
 */

$SocialRewarding["reward"]["starsFull"]			= "star_full.gif";


/**
 * File name of image for an empty star.
 */

$SocialRewarding["reward"]["starsEmpty"]		= "star_empty.gif";


/**
 * File name of image for a half star (only needed if
 * $SocialRewarding["reward"]["starsDisplayHalf"] is set true).
 */

$SocialRewarding["reward"]["starsHalf"]			= "star_half.gif";


/**
 * Name of directory to sparkline package.
 */

$SocialRewarding["reward"]["sparklinesPackageDir"]	= "sparkline-php-0.2";


/**
 * Displaying interval for sparklines.
 */

$SocialRewarding["reward"]["sparklinesInterval"]	= 20;


/**
 * Minimum percentage value for sparklines that are certainly be
 * displayed. Set this variable so that a thin line is displayed through
 * the whole sparkline even if no or to few data is given for an interval.
 */

$SocialRewarding["reward"]["sparklinesMinPercent"]	= 10;


/**
 * Width of sparklines in pixel.
 */

$SocialRewarding["reward"]["sparklinesWidth"]		= 2;


/**
 * Spacing of sparklines in pixel.
 */

$SocialRewarding["reward"]["sparklinesSpacing"]		= 0;


/**
 * Maximum height of sparklines in pixel. You should try to match
 * height of stars.
 */

$SocialRewarding["reward"]["sparklinesHeight"]		= 12;


/**
 * Color for under average sparklines.
 */

$SocialRewarding["reward"]["sparklinesUnderAvgColor"]	= "C0C0C0";


/**
 * Color for over average sparklines.
 */

$SocialRewarding["reward"]["sparklinesOverAvgColor"]	= "808080";


/**
 * Background color for sparklines.
 */

$SocialRewarding["reward"]["sparklinesBGColor"]		= "FFFFFF";


/**
 * Set automatic markups active? Auto-markups are markups which are
 * added automatically on the end of each new submitted article. There
 * exist auto-markups for every social rewarding mechanism which has to
 * be set active on its own. This is a global switch to turn all
 * auto-markups on/off with one move.
 */

$SocialRewarding["reward"]["autoMarkup"]		= false;


/**
 * On which extra namespaces should auto-markups also be set? For
 * example "100,102" means that on all articles with namespace "100" or
 * "102" auto-markups are actived. Only even namespaces are
 * accepted because odd ones are restricted to talk pages.
 */

$SocialRewarding["reward"]["autoMarkupExtra"]		= "";



/**********************************************************
 * AMOUNT OF REFERENCES CONFIGURATION
 **********************************************************/

/**
 * Activate social rewarding mechanism "Amount of References"? This
 * technique is only available using PHP >= 5 (because of a missing
 * SOAP interface in PHP versions prior 5). If you are using an older
 * PHP version $SocialRewarding["references"]["active"] is set
 * to false automatically.
 */

$SocialRewarding["references"]["active"]		= true;


/**
 * Compute the size of a reference and use this value in the calculation
 * process?
 */

$SocialRewarding["references"]["siteSizeFactor"]	= true;


/**
 * Count the number of links pointing to references in an article and use
 * this value in the calculation process?
 */

$SocialRewarding["references"]["siteLinkFactor"]	= true;


/**
 * Count the number of links pointing to an article and use this value in
 * the calculation process?
 */

$SocialRewarding["references"]["siteSelfLinkFactor"]	= true;


/**
 * Weighting of the three factors in percent. Order: "siteSizeFactor",
 * "siteLinkFactor" and "siteSelfLinkFactor". If you disable one factor
 * set weighting to zero, e.g. disabled "siteLinkFactor" (by setting
 * $SocialRewarding["references"]["siteLinkFactor"] = false): "50,0,50".
 */

$SocialRewarding["references"]["siteWeight"]		= "33,33,34";


/**
 * Scale for computing points of an article in percent, e.g. if an article
 * has over 150 percent of points relating to the average points calculated
 * for all articles it gets five points.
 */

$SocialRewarding["references"]["articleScale"]		= "0,15,33,66,100,150";


/**
 * Basis for calculating social rewarding mechanism "Amount of References":
 * "all", "articles", or "user_pages".
 */

$SocialRewarding["references"]["calcBasis"]		= "all";


/**
 * Corrects calculation basis, that means excluding system pages
 * (=pages with namespace like
 * $SocialRewarding["reward"]["calcBasisCorrectionNS"]).
 */

$SocialRewarding["references"]["calcBasisCorrection"]	= true;


/**
 * Should the search of references (links) be limited to a specified "section"
 * or the whole "article"?
 */

$SocialRewarding["references"]["textMode"]		= "article";


/**
 * If limitation of searching links is set to "section", a list of
 * delimiter words can be defined to recognize reference sections (the
 * section to search for links).
 */

$SocialRewarding["references"]["textSection"]		= "References,Bibliography";


/**
 * If limitation of searching links is set to "section" here delimiter
 * symbols can be defined to indicate headings, e.g. that sections starting
 * with "== References ==" are found.
 */

$SocialRewarding["references"]["textDelimiter"]		= "==";


/**
 * Start text for indicating a link (recommended to do not edit).
 */

$SocialRewarding["references"]["linkStart"]		= "http://";


/**
 * Delimiter characters to know the ending of a link, e.g. spaces, ">", "]"
 * and so on are indicating that a link ends here (also because these
 * symbols are not allowed in URLs).
 */

$SocialRewarding["references"]["linkDelimiter"]		= " <>[]'\"";


/**
 * If set true strips beginning "www" from links (better results
 * for $SocialRewarding["references"]["siteSizeFactor"]).
 */

$SocialRewarding["references"]["stripWWW"]		= true;


/**
 * Should Google search on whole domain instead of subdirectories only? For
 * instance, link "http://domain.org/MediaWiki/index.php" results in a
 * Google search in whole "http://domain.org".
 */

$SocialRewarding["references"]["googleWholeDomain"]	= true;


/**
 * License key for using Google's SOAP Search API (Beta). You have to
 * register on http://www.google.com/apis to obtain a key for your own.
 * "Your Google account and license key entitle you to 1.000 automated
 * queries per day" (according to Google's web-site), but it seems that this
 * limit is not checked by Google. The search interface was tested with a
 * lot more than a thousand queries per day (but that does not mean that
 * the limit is not going to be checked in the near future).
 */

//AP20081019 This key is for the test site! Please update when migrated

$SocialRewarding["references"]["googleKey"]		= "ABQIAAAAoYniuQ7BCMsISoA_eELPQhT2KLGOpQLYWIbO04u9V5hcfBi1eRSW-FT_1VgyHahQ8pDNPYU_Jf7lJw";


/**
 * "Activates or deactivates automatic results filtering, which hides very
 * similar results and results that all come from the same web host. Filtering
 * tends to improve the end user experience on Google, but for your
 * application you may prefer to turn it off. When enabled, filtering takes
 * the following actions: Near-Duplicate Content Filter = If multiple search
 * results contain identical titles and snippets, then only one of the
 * documents is returned. Host Crowding = If multiple results come from the
 * same web host, then only the first two are returned"
 * (http://www.google.com/apis/reference.html).
 */

$SocialRewarding["references"]["googleFilter"]		= false;


/**
 * "Restricts the search to a subset of the Google web index, such as a country
 * like 'Ukraine' or a topic like 'Linux'"
 * (http://www.google.com/apis/reference.html). For instance, restrict to
 * Austria use "countryAT".
 */

$SocialRewarding["references"]["googleRestrictCountry"]	= "";


/**
 * "Restricts the search to documents within one or more languages"
 * (http://www.google.com/apis/reference.html). For example, restrict language
 * to German use "lang_de".
 */

$SocialRewarding["references"]["googleRestrictLang"]	= "";


/**
 * "A Boolean value which enables filtering of adult content in the search
 * results. Many Google users prefer not to have adult sites included in their
 * search results. Google's SafeSearch feature screens for sites that contain
 * this type of information and eliminates them from search results. While no
 * filter is 100% accurate, Google's filter uses advanced proprietary technology
 * that checks keywords and phrases, URLs, and Open Directory categories"
 * (http://www.google.com/apis/reference.html).
 */

$SocialRewarding["references"]["googleSafeSearch"]	= false;


/**
 * If an error occurs in the Google query, how often should be tried to execute
 * the query?
 */

$SocialRewarding["references"]["googleSearchAttempts"]	= 2;


/**
 * Standard markup message for "siteLinkFactor". If a user does not set her/his
 * own markup message, this one is used. Markups can be inserted using
 * "<SocialRewardingReferences> Text </SocialRewardingReferences>".
 */

$SocialRewarding["references"]["markupLinkStdText"]	= "There is a total of $1 links pointing to the references ($2) in this article.";


/**
 * Standard markup message for "siteSizeFactor". If a user does not set her/his
 * own markup message, this one is used.
 */

$SocialRewarding["references"]["markupSizeStdText"]	= "The total size of the sites used as references in this article is $3.";


/**
 * Standard markup message for "siteSelfLinkFactor". If a user does not set
 * her/his own markup message, this one is used.
 */

$SocialRewarding["references"]["markupSelfLinkStdText"]	= "$4 links are pointing to this site (until $5).";


/**
 * Set automatic "Amount of References" markup active?
 */

$SocialRewarding["references"]["autoMarkup"]		= false;



/**********************************************************
 * RATING OF ARTICLES CONFIGURATION
 **********************************************************/

/**
 * Activate social rewarding mechanism "Rating of Articles"?
 */

$SocialRewarding["rating"]["active"]			= false;


/**
 * Rating scale (points a user can vote for an article).
 */

$SocialRewarding["rating"]["scale"]			= "0,1,2,3,4,5";


/**
 * Allow users to leave a comment (beside the rating). Comments are
 * displayed on the talk (discussion) page of the article. Can on each page
 * manually be turned off from an author. Markups can be inserted using
 * "<SocialRewardingRatingOfArticles></SocialRewardingRatingOfArticles>".
 * If you want to deactivate comments, set attribute comment=false, e.g.
 * "<SocialRewardingRatingOfArticles comment=false>".
 */

$SocialRewarding["rating"]["comment"]			= true;


/**
 * Standard size for comment text field. Can be modified on each page
 * from an author, e.g. set attribute "size=20".
 */

$SocialRewarding["rating"]["commentStdSize"]		= 32;


/**
 * Standard maximum character length for comments. Can be modified on
 * each page from an author, e.g. set attribute "maxlength=100".
 */

$SocialRewarding["rating"]["commentStdMaxLength"]	= 255;


/**
 * Should new comments be inserted on top of talk pages?
 */

$SocialRewarding["rating"]["commentNewOnTop"]		= false;


/**
 * Should voted points of a user be displayed on talk pages?
 */

$SocialRewarding["rating"]["commentDisplayPoints"]	= false;


/**
 * Standard caption of rating button. Can be modified on each page
 * from an author, e.g. set attribute "buttoncaption='Go'".
 */

$SocialRewarding["rating"]["stdButtonCaption"]		= " Vote > ";


/**
 * Should a JavaScript window be displayed after voting? Can be modified
 * on each page from an author, e.g. set attribute "popup=true".
 */

$SocialRewarding["rating"]["popup"]			= true;


/**
 * Standard message for JavaScript window. Can be modified
 * on each page from an author, e.g. set attribute
 * "popupmsg='Your vote was counted, thank you.'".
 */

$SocialRewarding["rating"]["stdPopupMsg"]		= "Thank you for your vote.";


/**
 * Should a vote from an author of an article be counted?
 */

$SocialRewarding["rating"]["countAuthor"]		= true;


/**
 * Should only logged in users have the possibility to vote?
 */

$SocialRewarding["rating"]["onlyUsers"]			= false;


/**
 * Should users have the possibility to vote several times?
 */

$SocialRewarding["rating"]["multipleVotes"]		= true;


/**
 * Timeout before next vote (in seconds), 0 = no timeout. Important only
 * if $SocialRewarding["rating"]["multipleVotes"] is set true.
 */

$SocialRewarding["rating"]["voteTimeout"]		= 0;


/**
 * Standard markup text for "Rating of Articles". If a user does not set
 * her/his own markup message, this one is used. Markups can be
 * inserted using
 * "<SocialRewardingRatingPoints> Text </SocialRewardingRatingPoints>".
 */

$SocialRewarding["rating"]["markupStdPointsText"]	= "This article was rated $1 time(s) with a score of $2 point(s) (until $3).";


/**
 * Set automatic "Rating of Articles" (rating form) markup active?
 */

$SocialRewarding["rating"]["autoMarkup"]		= false;


/**
 * Set automatic "Rating of Articles" (rating points) markup active?
 */

$SocialRewarding["rating"]["autoMarkupPoints"]		= false;



/**********************************************************
 * MOST VIEWED ARTICLES CONFIGURATION
 **********************************************************/

/**
 * Activate social rewarding mechanism "Most Viewed Articles"?
 */

$SocialRewarding["viewed"]["active"]			= true;


/**
 * Standard markup text for "Most Viewed Articles". If a user does not set
 * her/his own markup message, this one is used. Markups can be
 * inserted using
 * "<SocialRewardingMostViewedArticles show=true> Text
 * </SocialRewardingMostViewedArticles>".
 */

$SocialRewarding["viewed"]["stdMessage"]		= "This article has been accessed $1 time(s) (until $2).";


/**
 * Counting method: "hits" or "visits". "hits" means that every request
 * for a page is counted, "visits" that within a certain period of time
 * (defined in $SocialRewarding["viewed"]["sessionTimeout"]) only one
 * visit is counted regardless how often a user requests a page.
 */

$SocialRewarding["viewed"]["countMethod"]		= "hits";


/**
 * Count hits or visits from authors of an article?
 */

$SocialRewarding["viewed"]["countAuthor"]		= true;


/**
 * Session timeout in seconds before next visit is counted. Only important
 * if $SocialRewarding["viewed"]["countMethod"] = "visits".
 */

$SocialRewarding["viewed"]["sessionTimeout"]		= 1800;


/**
 * Do also compute hits or visits on user-pages?
 */

$SocialRewarding["viewed"]["addUserPages"]		= false;


/**
 * Scale for calculating points of an article in percent, e.g. if an
 * article has over 100 and under 200 percent visitors relating to the average
 * visiting rate calculated for all articles, it gets three points.
 */

$SocialRewarding["viewed"]["articleScale"]		= "0,25,50,100,200,300";


/**
 * Basis for calculating social rewarding mechanism "Most Viewed Articles":
 * "all", "articles", or "user_pages".
 */

$SocialRewarding["viewed"]["calcBasis"]			= "articles";


/**
 * Corrects calculation basis, that means excluding system pages
 * (=pages with namespace like
 * $SocialRewarding["reward"]["calcBasisCorrectionNS"]).
 */

$SocialRewarding["viewed"]["calcBasisCorrection"]	= true;


/**
 * Set automatic "Most Viewed Articles" markup active?
 */

$SocialRewarding["viewed"]["autoMarkup"]		= false;



/**********************************************************
 * RECOMMENDER SYSTEM CONFIGURATION
 **********************************************************/

/**
 * Activate social rewarding mechanism "Recommender System"?
 */

$SocialRewarding["recommend"]["active"]			= false;


/**
 * Counting method: "hits" or "visits".
 */

$SocialRewarding["recommend"]["countMethod"]		= "hits";


/**
 * Count hits or visits from authors of an article?
 */

$SocialRewarding["recommend"]["countAuthor"]		= true;


/**
 * Session timeout in seconds before next visit is counted. Only important
 * if $SocialRewarding["recommend"]["countMethod"] = "visits".
 */

$SocialRewarding["recommend"]["sessionTimeout"]		= 1800;


/**
 * Do also compute hits or visits on user-pages?
 */

$SocialRewarding["recommend"]["addUserPages"]		= false;


/**
 * Calculate recommendations on how much top-articles of an author?
 */

$SocialRewarding["recommend"]["reduceData"]		= 5;


/**
 * Counting every article as one ("equal") or sum up visits from an
 * article ("repr"esentative).
 */

$SocialRewarding["recommend"]["countingMethod"]		= "repr";


/**
 * Activate weighting of articles so to set articles which has been
 * more often visited by a user more important.
 */

$SocialRewarding["recommend"]["weighting"]		= true;


/**
 * "equal" means that the graduation of weighted articles is always
 * the same regardless of visits. "repr"esentative weights articles
 * on the basis of their visits.
 */

$SocialRewarding["recommend"]["weightingMethod"]	= "repr";


/**
 * How much recommendations should be displayed?
 */

$SocialRewarding["recommend"]["reduceRecommendation"]	= 5;


/**
 * Exclude user's visited articles?
 */

$SocialRewarding["recommend"]["excludeVisitedArticles"]	= false;


/**
 * Exclude articles where user is author?
 */

$SocialRewarding["recommend"]["excludeUsersArticles"]	= false;


/**
 * Standard markup text for "Recommender System". If a user does not set
 * her/his own markup message, this one is used. Markups can be
 * inserted using
 * "<SocialRewardingRecommend> Text </SocialRewardingRecommend>".
 */

$SocialRewarding["recommend"]["markupStdMessage"]	= "";


/**
 * Standard "Recommender System" markup method ("revision", "article",
 * "author", or "interestedAuthor"). Can be modified on each page
 * from an author, e.g. set attribute "method=article".
 */

$SocialRewarding["recommend"]["markupStdMethod"]	= "article";


/**
 * Display rank/position as standard? Can be modified on each page
 * from an author, e.g. set attribute "rank=true".
 */

$SocialRewarding["recommend"]["markupStdRank"]		= false;


/**
 * Display points as standard? Can be modified on each page from an
 * author, e.g. set attribute "points=true".
 */

$SocialRewarding["recommend"]["markupStdPoints"]	= false;


/**
 * Set automatic "Recommender System" markup active?
 */

$SocialRewarding["recommend"]["autoMarkup"]		= false;



/**********************************************************
 * DATABASE TABLES CONFIGURATION
 **********************************************************/

/**
 * If you want to rename database tables used in the SocialRewarding
 * extension, do it here. If you have set database tables' prefixes
 * in your MediaWiki, they are added automatically.
 */


/**
 * Table to store cached objects in database (only needed if
 * $SocialRewarding["reward"]["cache"] = true and
 * $SocialRewarding["reward"]["cacheMethod"] = "db").
 */

$SocialRewarding["DB"]["cache"]				= "sr__cache";


/**
 * Table to store data for social rewarding mechanism "Amount of
 * References".
 */

$SocialRewarding["DB"]["references"]			= "sr__references";


/**
 * Table to store data for multiple votes and timeout of votes ("Rating
 * of Articles").
 */

$SocialRewarding["DB"]["ratedRevision"]			= "sr__ratedrevision";


/**
 * Table to store data for social rewarding mechanism "Rating of
 * Articles".
 */

$SocialRewarding["DB"]["rating"]			= "sr__rating";


/**
 * Table to store data for social rewarding mechanism "Most Viewed
 * Articles".
 */

$SocialRewarding["DB"]["viewedArticles"]		= "sr__viewedarticles";


/**
 * Table to store data for multiple visits and timeout of visits ("Most
 * Viewed Articles").
 */

$SocialRewarding["DB"]["visitRevision"]			= "sr__visitrevision";


/**
 * Table to store data for social rewarding mechanism "Recommender
 * System".
 */

$SocialRewarding["DB"]["recommend"]			= "sr__recommend";


?>
