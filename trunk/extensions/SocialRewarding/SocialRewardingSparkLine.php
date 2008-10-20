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
 * File for displaying sparklines. Calls needed methods of sparkline
 * package (which is located in directory "sparkline-php-0.2") to
 * generate an image which is returned.
 */


// Load SocialRewarding package
require_once("SocialRewarding.php");

// Load sparkline package
require_once($_GET["path"] . "/lib/Sparkline_Bar.php");

// New Sparkline_Bar() instance
$sparkline = new Sparkline_Bar();

// Set sparkline display variables
$sparkline->SetBarWidth($SocialRewarding["reward"]["sparklinesWidth"]);
$sparkline->SetBarSpacing($SocialRewarding["reward"]["sparklinesSpacing"]);
$sparkline->SetColorHtml($SocialRewarding["reward"]["sparklinesUnderAvgColor"], $SocialRewarding["reward"]["sparklinesUnderAvgColor"]);
$sparkline->SetColorHtml($SocialRewarding["reward"]["sparklinesOverAvgColor"], $SocialRewarding["reward"]["sparklinesOverAvgColor"]);
$sparkline->SetColorHtml($SocialRewarding["reward"]["sparklinesBGColor"], $SocialRewarding["reward"]["sparklinesBGColor"]);
$sparkline->SetColorBackground($SocialRewarding["reward"]["sparklinesBGColor"]);

// Data is passed through URL
foreach($_GET as $key => $val) {

	// Only variables containing "data" are important
	if (eregi("data", $key)) {

		// Get number of sparkline interval
		$i = explode("data", $key);

		// Under average values can have different colors then over average
		if ($val <= $_GET["avg"]) {
			$color=$SocialRewarding["reward"]["sparklinesUnderAvgColor"];
		} else {
			$color=$SocialRewarding["reward"]["sparklinesOverAvgColor"];
		}

		// Set sparkline data
		$sparkline->SetData($i[1], $val, $color);
	}
}

// Render sparkline image
$sparkline->Render($SocialRewarding["reward"]["sparklinesHeight"]);

// Output image
$sparkline->Output();

?>