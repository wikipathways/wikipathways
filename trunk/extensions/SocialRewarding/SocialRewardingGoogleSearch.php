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
 * Class for query Google searches.
 */
class GoogleSearch {

	/* private */ var $soapClient;
	/* private */ var $params;
	/* private */ var $result;

	/**
	 * Constructor - set license key if passed, else take key
	 * specified in the configuration file. Initialize SOAP client
	 * and set parameters ("filter", "restrict", "safeSearch" and
	 * "lr") taken from the configuration file.
	 *
	 * @access public
	 * @param String $key License key
	 */
	function GoogleSearch($key = "") {
		global $SocialRewarding;

		if ($key == "") {
			$key = $SocialRewarding["references"]["googleKey"];
		}

	        $this->soapClient = new SoapClient("http://api.google.com/GoogleSearch.wsdl");
		$this->params = array(
					"key" => $key,
					"q" => "",
					"start" => 0,
					"maxResults" => 1,
					"filter" => $SocialRewarding["references"]["googleFilter"],
					"restrict" => $SocialRewarding["references"]["googleRestrictCountry"],
					"safeSearch" => $SocialRewarding["references"]["googleSafeSearch"],
					"lr" => $SocialRewarding["references"]["googleRestrictLang"],
					"ie" => "",
					"oe" => ""
		);
	}


	/**
	 * Set license key.
	 *
	 * @access public
	 * @param String $key License key
	 */
	function setKey($key) {
		$this->params["key"] = $key;
	}


	/**
	 * Set and format text to search for.
	 *
	 * @access public
	 * @param String $q Search text
	 */
	function setQuery($q) {
		$q = $this->formatQuery($q);
		$this->params["q"] = $q;
	}


	/**
	 * Get text to search for.
	 *
	 * @access public
	 * @return String Search text
	 */
	function getQuery() {
		return $this->params["q"];
	}


	/**
	 * Set filter.
	 *
	 * @access public
	 * @param boolean $filter Activate filter
	 */
	function setFilter($filter) {
		$this->params["filter"] = $filter;
	}


	/**
	 * Set country or topic restrictions.
	 *
	 * @access public
	 * @param String $restrict Country or topic restriction
	 */
	function setRestrict($restrict) {
		$this->params["restrict"] = $restrict;
	}


	/**
	 * Set safeSearch.
	 *
	 * @access public
	 * @param boolean $ss Activate safeSearch
	 */
	function setSafeSearch($ss) {
		$this->params["safeSearch"] = $ss;
	}


	/**
	 * Set language restrictions.
	 *
	 * @access public
	 * @param String $lr Language restriction
	 */
	function setLR($lr) {
		$this->params["lr"] = $lr;
	}


	/**
	 * Format search text so that there are no " or ' characters.
	 *
	 * @access private
	 * @param String $q Search text
	 * @return String Formatted search text
	 */
	function formatQuery($q) {
		$q = str_replace('"','',$q);
		$q = str_replace("'","",$q);
		return $q;
	}


	/**
	 * Query Google search.
	 *
	 * @access public
	 * @param String $q Search text
	 * @param String $opt Options ("size" or "link")
	 * @param int $attempt Number of search attempts
	 * @return array Search result
	 */
	function doSearch($q = "", $opt = "", $attempt = 1) {
		global $SocialRewarding;

		// If $q == "" expect that $params["q"] was already set
		if ($q != "") {
			$this->setQuery($q);
		}

		// "siteSizeFactor" or "siteLinkFactor"
		if ($opt == "size") {
			$q = "site:" . $this->getQuery();
		} else if ($opt == "link") {
			$q = "link:" . $this->getQuery();
		} else {
			$q = $this->getQuery();
		}

		try {
			$this->result = $this->soapClient->doGoogleSearch(
									$this->params["key"],
									$q,
									$this->params["start"],
									$this->params["maxResults"],
									$this->params["filter"],
									$this->params["restrict"],
									$this->params["safeSearch"],
									$this->params["lr"],
									$this->params["ie"],
									$this->params["oe"]
									);
		} catch (Exception $e) {

			// If an exception was caught try to submit query x times
			if ($attempt < $SocialRewarding["references"]["googleSearchAttempts"]) {
				$attempt++;
				$this->doSearch($q, $opt, $attempt);
			}
		}

		return $this->result;
	}


	/**
	 * Get estimated number of total results.
	 *
	 * @access public
	 * @return int Number of total results
	 */
	function getCount() {
		return $this->result->estimatedTotalResultsCount;
	}


}


?>