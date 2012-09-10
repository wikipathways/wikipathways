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
 * This is the main file for loading the whole SocialRewarding package.
 */


/*
 * There are two configuration files "SocialRewardingConfigShort.php"
 * and "SocialRewardingConfigDetail.php". To setup the SocialRewarding
 * extension you can use either the short or the detailed version. Both
 * are identical except the in-depth explanation of variables in the
 * detailed version is abbreviated to a one line comment in the short
 * version. Do not forget to load the file you prefer beneath.
 */

// require_once("SocialRewardingConfigShort.php");
require_once("SocialRewardingConfigDetail.php");

require_once("SocialRewardingOtherFunctions.php");
require_once("SocialRewardingSpecialPages.php");
require_once("SocialRewardingMarkups.php");
require_once("SocialRewardingMarkupsFunctions.php");
require_once("SocialRewardingHooks.php");
require_once("SocialRewardingHooksFunctions.php");

/**
 * Only PHP >= 5 supports SOAP interface, using PHP prior version 5
 * will result in automatically disabling social rewarding mechanism
 * amount of references.
 */

if (SocialRewardingGetPHPVersion() >= 5) {
	require_once("SocialRewardingGoogleSearch.php");
}

require_once("SocialRewardingReward.php");
require_once("SocialRewardingRewardReferences.php");
require_once("SocialRewardingRewardRating.php");
require_once("SocialRewardingRewardViewed.php");
require_once("SocialRewardingRewardRecommend.php");
require_once("SocialRewardingRewardManage.php");



/*
 * Extending the $path variable to load special pages also from the
 * extensions directory. Otherwise all special pages have to be
 * copied to the includes directory.
 */

$path[] = "$IP/" . $SocialRewarding["reward"]["extensionPath"];
set_include_path( get_include_path() . PATH_SEPARATOR . implode( PATH_SEPARATOR, $path ) );


?>
