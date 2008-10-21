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
 * Data Definition Language (DDL) statements for creating MySQL
 * database tables.
 */



/** 
 * DDL for table "sr__cache"
 */

CREATE TABLE IF NOT EXISTS sr__cache (
  timestamp int(10) unsigned NOT NULL default '0',
  data mediumblob NOT NULL,
  PRIMARY KEY  (timestamp)
) TYPE=MyISAM;

# --------------------------------------------------------


/**
 * DDL for table "sr__references"
 */

CREATE TABLE IF NOT EXISTS sr__references (
  rev_id int(8) unsigned NOT NULL default '0',
  size bigint(20) unsigned NOT NULL default '0',
  link bigint(20) unsigned NOT NULL default '0',
  count bigint(20) unsigned NOT NULL default '0',
  self_link bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (rev_id)
) TYPE=MyISAM;

# --------------------------------------------------------


/** 
 * DDL for table "sr__ratedrevision"
 */

CREATE TABLE IF NOT EXISTS sr__ratedrevision (
  rev_id int(8) unsigned NOT NULL default '0',
  user_id varchar(255) binary NOT NULL default '',
  rev_touched varchar(14) binary NOT NULL default '',
  KEY rev_id (rev_id,user_id)
) TYPE=MyISAM;

# --------------------------------------------------------


/**
 * DDL for table "sr__rating"
 */

CREATE TABLE IF NOT EXISTS sr__rating (
  rev_id int(8) unsigned NOT NULL default '0',
  points bigint(20) unsigned NOT NULL default '0',
  count bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (rev_id)
) TYPE=MyISAM;

# --------------------------------------------------------


/**
 * DDL for table "sr__viewedarticles"
 */

CREATE TABLE IF NOT EXISTS sr__viewedarticles (
  rev_id int(8) unsigned NOT NULL default '0',
  rev_counter bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (rev_id)
) TYPE=MyISAM;

# --------------------------------------------------------


/**
 * DDL for table "sr__visitrevision"
 */

CREATE TABLE IF NOT EXISTS sr__visitrevision (
  rev_id int(8) unsigned NOT NULL default '0',
  user_id varchar(255) binary NOT NULL default '',
  rev_touched varchar(14) binary NOT NULL default '',
  KEY rev_id (rev_id,user_id)
) TYPE=MyISAM;


/**
 * DDL for table "sr__recommend"
 */

CREATE TABLE IF NOT EXISTS sr__recommend (
  rev_id int(8) unsigned NOT NULL default '0',
  user_id varchar(255) binary NOT NULL default '',
  timestamp int(10) unsigned NOT NULL default '0',
  KEY page_id (rev_id,user_id)
) TYPE=MyISAM;

# --------------------------------------------------------
