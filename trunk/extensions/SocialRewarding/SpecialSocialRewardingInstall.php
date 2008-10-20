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
 * @subpackage SpecialPage
 * @subsubpackage SocialRewarding
 */



/**
 * Special page for SocialRewarding package installation.
 */
class SpecialSocialRewardingInstall {

	/* private */ var $SocialRewarding;
	/* private */ var $dbr;
	/* private */ var $dbw;


	/**
	 * Constructor
	 *
	 * @access public
	 */
	function SpecialSocialRewardingInstall() {
		global $SocialRewarding;
		$this->SocialRewarding =& $SocialRewarding;
		$this->dbr =& wfGetDB(DB_SLAVE);
		$this->dbw =& wfGetDB(DB_MASTER);
	}


	/**
	 * Get name of class.
	 *
	 * @access public
	 * @return String Name of class
	 */
	function getName() {
		return "SocialRewardingInstall";
	}


	/**
	 * Is this query expensive (for some definition of expensive)?
	 *
	 * @access public
	 * @return boolean Is expensive
	 */
	function isExpensive() {
		return true;
	}


	/**
	 * Build rss / atom feeds?
	 *
	 * @access public
	 * @return boolean Is syndicated
	 */
	function isSyndicated() {
		return false;
	}


	/**
	 * Function to execute on GET request.
	 *
	 * @access private
	 * @return String HTML output
	 */
	function get() {
		global $wgDBserver;
		global $wgDBname;
		global $wgExtraNamespaces;

		$steps = 7;
		// Increase steps by one because of "Amount of References" initialization
		if (SocialRewardingGetPHPVersion() >= 5) {
			$steps++;
		}

		$output = "
			<style type=text/css>
				li { padding-bottom: 10px; }
			</style>

			<script language=javascript>
				function submitForm() {
					if (document.SocialRewardingForm.sr_viewed.checked == true && document.SocialRewardingForm.sr_viewed_method[0].checked == false && document.SocialRewardingForm.sr_viewed_method[1].checked == false) {
						alert(\"Please select a method for initialize page views!\");
						return false;
					}
				}
			</script>


			<form name=SocialRewardingForm action=" . $_SERVER["PHP_SELF"] . " method=post onSubmit=\"return submitForm()\">

			This is the installation page for the SocialRewarding extension for <a href=http://www.mediawiki.org target=_blank>
			MediaWiki</a>. Before installing the extension it is highly recommended that you	have a look at the README file of
			this package (<i>SocialRewardingREADME</i>). After reading you should proceed with the following steps (steps 1 - 4:
			installation, optional steps 5 - $steps: data initialization):

			<br><br>

			<ol>

				<li>
					If you can see this page, you probably have a running MediaWiki installation and obtained, unzipped, and
					integrated (edited <i>[MediaWikiPath]/LocalSettings.php</i>) the SocialRewarding package. If you have
					already done these things, you can proceed with step 4, otherwise you have to download the SocialRewarding
					package and unpack it to the extension directory of your MediaWiki installation
					(e.g., <i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/</i>).
				</li>

				<li>
					To activate the SocialRewarding extension in MediaWiki you have to edit your
					<i>[MediaWikiPath]/LocalSettings.php</i>. Insert the following at the end of the file:
					<br>
					<i>require_once(\"" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewarding.php\");</i>
				</li>

				<li>
					Start your favorite web-browser and navigate to the installation script (this one you already read). The URL
					should be something like
					<i>http://www.YourWebServer.org/MediaWiki/index.php/Special:SocialRewardingInstall</i>. Alternatively, you
					can also reach the installation page, if you browse the special pages of your MediaWiki and click on the
					link called \"Social Rewarding: Installation\".
				</li>

				<li>
					For the SocialRewarding extension to work you have to insert some tables in your database. This you can do by
					either insert the MySQL DDL statements in the file <i>SocialRewardingTables.sql</i> by yourself or enter the
					needed database related information in the following fields (if you do not enter anything in here except what
					is inserted automatically, insertion of database tables is skipped). If you have set database tables'
					prefixes in your MediaWiki installation, do not forget to add them in file <i>SocialRewardingTables.sql</i> if
					you want to insert the MySQL DDL statements on your own. If you use the form below, prefixes are added
					automatically (recommended).
					<br>

					<table>
						<tr>
							<td>
								Host:
							</td>
							<td>
								<input type=text name=sr_host value='$wgDBserver'>
							</td>
						</tr>
						<tr>
							<td>
								User:
							</td>
							<td>
								<input type=text name=sr_user> <i>User must have rights to create new tables.</i>
							</td>
						<tr>
							<td>
								Password:
							</td>
							<td>
								<input type=password name=sr_pw>
							</td>
						</tr>
						<tr>
							<td>
								Database name:
							</td>
							<td>
								<input type=text name=sr_db_name value='$wgDBname'>
							</td>
						</tr>
					</table>

				</li>

					<br>

					If you do not want existing data to be initialized, but the MySQL DDL statements should be inserted by this
					script, you can finish the installation by clicking on the button beneath.

					<br><br>

					<input type=submit name=sr_install_without value=\"Install (without data initialization)\">


				<li>
					Following steps 1 to 4 your SocialRewarding extension should work. You can edit
					<i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingConfig.php</i>
					to configure all parameters. If you have not read it, please take a look at the README file of this package
					(<i>SocialRewardingREADME</i>). As the SocialRewarding package can only work with data provided after its
					installation the next steps perform an initialization of already existing data as well as collecting and
					linking new data.
				</li>

				<li>
					<input type=checkbox name=sr_markup> Set auto-markups
					<br>
					If you have not had a look at the config file of the SocialRewarding package located at
					<i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingConfig.php</i>, it
					is now the time to do so. In the SocialRewarding config file you can set parameters for auto-markups which
					means that a markup is added automatically when a new article is created. Markups exist for displaying the
					results of all four social rewarding mechanisms in an article. Nevertheless, users can always modify or delete
					those markups by altering or removing the specific lines and saving the article in a new revision. Please set
					the specific variables in the config file depending on which auto-markups you want to add to existing articles.
					Only one new revision is created no matter how much auto-markups are enabled in the SocialRewarding config file.
		";

		// Optional select extra namespaces if there are any defined
		if ($wgExtraNamespaces) {
			$output .= "<br><br>";

			for ($i = 1; $i <= 3; $i++) {
				$output .= "
					<select name=sr_markup_ns$i>
					<option value=''>Additional pages:</option>
				";
				foreach ($wgExtraNamespaces as $key => $val) {
					if (fmod($key, 2) == 0) {
						$output .= "<option value='$key'>$val</option>";
					}
				}
				$output .= "
					</select>
					&nbsp;&nbsp;
				";
			}
			$output .= "
				<br>
				If you enable auto-markups to be set, they are inserted on main articles. If you want to add auto-markups on
				additional pages, you can do this by selecting the namespaces above. For setting auto-markups on more than three
				additional namespaces you can restart the installation and select other ones.
			";
		}



		$output .= "
				</li>

				<li>
					<input type=checkbox name=sr_viewed> Initialize page views
					<br>
					If you check the box above, page views of an article is split among all authors of an article to provide
					start data for the social rewarding mechanism \"Most Viewed Articles\".

					<br><br>

					<input type=radio name=sr_viewed_method value=equal> Equal shares &nbsp;&nbsp;
					<input type=radio name=sr_viewed_method value=repr> Representative shares
					<br>
					You can select if the amount of page views is split averaged (\"equal shares\") among all authors, which
					means that page views are linked to authors only by reason of their quantity of edits of an article. If
					you select \"representative shares\", there are two factors which defines the number of page views linked
					to an author: (1) the quantity of an author's edits of an article (like \"equal shares\") and (2) the size of
					these edits.

					<br><br>

					<input type=checkbox name=sr_viewed_system_pages> Initialize page views on system pages &nbsp;&nbsp;
					<input type=checkbox name=sr_viewed_user_pages> Initialize page views on user-pages
					<br>
					With these two options you can define if initialization of page views should also be calculated on pages
					created at installation time of MediaWiki and/or user-pages.
		";

		// Optional select extra namespaces if there are any defined
		if ($wgExtraNamespaces) {
			$output .= "<br><br>";

			for ($i = 1; $i <= 3; $i++) {
				$output .= "
					<select name=sr_viewed_ns$i>
					<option value=''>Additional pages:</option>
				";
				foreach ($wgExtraNamespaces as $key => $val) {
					if (fmod($key, 2) == 0) {
						$output .= "<option value='$key'>$val</option>";
					}
				}
				$output .= "
					</select>
					&nbsp;&nbsp;
				";
			}
			$output .= "
				<br>
				For initializing page views on extra pages please select namespaces above.
			";
		}

		$output.= "

				</li>
		";

		if (SocialRewardingGetPHPVersion() >= 5) {
			$output .= "
				<li>
					<input type=checkbox name=sr_references> Initialize amount of references
					<br>
					If you check the box above, the amount of references of an article is checked using the search engine
					<a href=http://www.google.com target=_blank>Google</a>. Attention: Using Google to check for amount of
					references can be very time-consuming depending on the number of articles (and revisions) in your database,
					the server's Internet connection, and methods selected in the config file. Therefore, the script is going to
					try to set a never-ending maximum execution time by using PHP's parameter <i>set_time_limit(0);</i>. If you
					are running PHP in safe mode, <i>set_time_limit(0);</i> has no effect and you have to set the
					<i>max_execution_time</i> parameter in your <i>php.ini</i> by yourself. Another important fact is that the
					SOAP interface used to query Google searches works only with PHP 5 and above (your PHP version is
					" . SocialRewardingGetPHPVersion() . "). You do not see this step when using a PHP version prior 5.

					<br><br>

					If you want to initialize the amount of references, you have to set parameters of the array
					<i>\$SocialRewarding[\"references\"]</i> in the config file located at
					<i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingConfig.php</i>
					depending on your configuration wanted.

					<br><br>

					<input type=checkbox name=sr_references_system_pages> Initialize amount of references on system pages &nbsp;&nbsp;
					<input type=checkbox name=sr_references_user_pages> Initialize amount of references on user-pages
					<br>
					With this option you can define if initialization of amount of references should also be calculated on user
					pages.
			";

			// Optional select extra namespaces if there are any defined
			if ($wgExtraNamespaces) {
				$output .= "<br><br>";

				for ($i = 1; $i <= 3; $i++) {
					$output .= "
						<select name=sr_references_ns$i>
						<option value=''>Additional pages:</option>
					";
					foreach ($wgExtraNamespaces as $key => $val) {
						if (fmod($key, 2) == 0) {
							$output .= "<option value='$key'>$val</option>";
						}
					}
					$output .= "
						</select>
						&nbsp;&nbsp;
					";
				}
				$output .= "
					<br>
					For initializing amount of references on extra pages please select namespaces above.
				";
			}

			$output .= "
				</li>
			";

		}

		$output .= "
					<br><br>

					If you have configured the SocialRewarding config file and filled out this form as wanted, you can finish
					the installation by clicking on the button beneath.

					<br><br>

					<input type=submit name=sr_install_with value=\"Install (with data initialization)\">

					<br><br>

					The installation may take only a few seconds to several minutes depending on your settings. If an error
					occurs, you can restart the installation at any time by browsing to this page again and by filling out the
					form exactly as you did before. The installation script recognizes the unfinished installation and
					continue the installation process at the point the error occurred. No data is lost and no double entries are
					made.

			</ol>

			</form>
		";

		return $output;

	}



	/**
	 * Function to execute on POST request (after submitting form).
	 *
	 * @access private
	 * @return String HTML output
	 */
	function post() {
		global $wgOut;
		global $wgUser;

		extract($this->dbr->tableNames("revision", "page", "text"));

		$error = false;
		$warning = false;

		$user = $wgUser;
		$install_start = SocialRewardingMicrotime();

		$output = "<ul>";

			if ($_POST["sr_install_without"]) {
				$output .= "<li><font color=#000000>You are installing the SocialRewarding extension without data initialization.</font></li>";
			} else {
				$output .= "<li><font color=#000000>You are installing the SocialRewarding extension with data initialization.</font></li>";
			}

			// Check if SocialRewarding package main file exists in extension directory
			if (!file_exists($this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewarding.php")) {
				$output .= "<li><font color=#FF0000>It seems that the files of the SocialRewarding extension are not completely copied to the <i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/</i> folder.</font></li>";
				$error = true;
			} else {
				$output .= "<li><font color=#009900>Found SocialRewarding main file.</font></li>";
			}

			if (!file_exists("LocalSettings.php") && $error == false) {
				$output .= "<li><font color=#FF0000>Cannot find file <i>LocalSettings.php</i>.</font></li>";
				$error = true;
			} else if ($error == false) {
				$local_settings = @file_get_contents("LocalSettings.php");
				$output .= "<li><font color=#009900>File <i>LocalSettings.php</i> found.</font></li>";

				// SocialRewarding extension must be loaded in "LocalSettings.php"
				if (!eregi($this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewarding.php", $local_settings)) {
					$output .= "<li><font color=#0000FF>It seems that you do not load the SocialRewarding package in <i>LocalSettings.php</i>. The SocialRewarding extension is disabled unless you add something like <i>require_once(\"" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewarding.php\");</i> at the end of your <i>LocalSettings.php</i>.</font></li>";
					$warning = true;
				} else {
					$output .= "<li><font color=#009900>SocialRewarding extension is loaded in <i>LocalSettings.php</i>.</font></li>";
				}
			}

			// Inserting MySQL tables
			if ($_POST["sr_host"] && $_POST["sr_user"] && $_POST["sr_db_name"] && $error == false) {
				$ddl = @file_get_contents($this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingTables.sql");
				if ($ddl == false) {
					$output .= "<li><font color=#FF0000>File <i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingTables.sql</i> not found.</font></li>";
					$error = true;
				} else {
					$output .= "<li><font color=#009900>Load file <i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingTables.sql</i> successfully.</font></li>";
					$connect = @mysql_connect($_POST["sr_host"], $_POST["sr_user"], $_POST["sr_pw"]);
					if ($connect == false) {
						$output .= "<li><font color=#FF0000>Cannot connect to MySQL host '" . $_POST["sr_host"] . "' (MySQL error message: " . mysql_error() . ").</font></li>";
						$error = true;
					} else {
						$output .= "<li><font color=#009900>Connected to MySQL host '" . $_POST["sr_host"] . "' successfully.</font></li>";
						$db = @mysql_select_db($_POST["sr_db_name"]);
						if ($db == false) {
							$output .= "<li><font color=#FF0000>Cannot select database '" . $_POST["sr_db_name"] . "' (MySQL error message: " . mysql_error() . ").</font></li>";
							$error = true;
						} else {
							$output .= "<li><font color=#009900>Selected database '" . $_POST["sr_db_name"] . "' successfully.</font></li>";
							$sqlQueries = explode("CREATE TABLE", $ddl);
							foreach($sqlQueries as $sql) {
								$check = false;
								foreach($this->SocialRewarding["DB"] as $db_name) {
									if (strpos($sql, $db_name) && strpos($sql, ";") && !strpos($sql, "Copyright (C)")) {
										$sql = str_replace($db_name, $this->dbr->tableName($db_name), $sql);
										$check = true;
										break;
									}
								}
								if ($check) {
									$sql = "CREATE TABLE$sql";
									$sql = strtok($sql, ";");
									$query = mysql_query($sql);
									if ($query == false) {
										$output .= "<li><font color=#FF0000>Cannot query MySQL statements (MySQL error message: " . mysql_error() . ").</font></li>";
										$error = true;
										//break;
									} else {
										$output .= "<li><font color=#009900>Queried MySQL DDL for table '$db_name' successfully.</font></li>";
									}
								}
							}
						}
					}
				}
			} else if ($error == false) {
				$output .= "<li><font color=#0000FF>Database: No tables where created. You have to do it on your own.</font></li>";
				$warning = true;
			}


			// Installation with data initialization
			if ($_POST["sr_install_with"] && $error == false) {

				if ($_POST["sr_markup"]) {
					$output .= "<li><font color=#000000>Set auto-markups is on.</font></li>";

					$sql = "
						SELECT
							page_title,
							page_namespace,
							rev_user_text
						FROM
							$page,
							$revision
						WHERE
							page_latest = rev_id
						AND
							(page_namespace = " . $this->SocialRewarding["reward"]["calcBasisArticlesNS"] . "
					";

					for ($i = 1; $i <= 3; $i++) {
						if ($_POST["sr_markup_ns$i"] != "") {
							$sql .= " OR page_namespace = " . $_POST["sr_markup_ns$i"];
						}
					}

					$sql .= ")";

					$rs = $this->dbr->query($sql);
					$i = 0;

					// Loop over all articles and insert auto-markups if not already inserted
					while ($row = $this->dbr->fetchRow($rs)) {
						$title = Title::newFromText($row[0], $row[1]);
						$r = Revision::newFromTitle($title);
						if (is_object($r)) {
							$revision_text = $r->getText();
							$newUser = User::newFromName($row[2]);
							if (is_object($newUser)) {
								$wgUser = $newUser;
							}
							if (!eregi("<!--  SocialRewarding Extension Automatic Code Insertion", $revision_text)) {
								$ok = SocialRewardingAutoMarkup(new Article($title));
								unset($wgOut->mRedirect);
								if ($ok == 1) {
									$i++;
								}
							}
						}
					}

					$wgUser = $user;

					$output .= "<li><font color=#0009900>Set auto-markups successfully (total pages: $i).</font></li>";
				} else {
					$output .= "<li><font color=#000000>Set auto-markups is off.</font></li>";
				}



				if ($_POST["sr_viewed"]) {
					$output .= "<li><font color=#000000>Initialize page views is on.</font></li>";

					$sql = "
						SELECT
							rev_id
						FROM
							" . $this->dbr->tableName($this->SocialRewarding["DB"]["viewedArticles"]) . "
					";

					$rs = $this->dbr->query($sql);
					while ($row = $this->dbr->fetchRow($rs)) {
						$counted_rev[$row[0]] = true;
					}


					if ($_POST["sr_viewed_method"] == "equal") {

						$sql = "
							SELECT
								rev_id,
								page_id,
								page_counter
							FROM
								$revision,
								$page
							WHERE
								$revision.rev_page = $page.page_id
							AND
								(page_namespace = " . $this->SocialRewarding["reward"]["calcBasisArticlesNS"] . "
						";

						if ($_POST["sr_viewed_system_pages"]) {
							$sql .= " OR page_namespace = " . $this->SocialRewarding["reward"]["calcBasisCorrectionNS"];
						}
						if ($_POST["sr_viewed_user_pages"]) {
							$sql .= " OR page_namespace = " . $this->SocialRewarding["reward"]["calcBasisUserPagesNS"];
						}

						for ($i = 1; $i <= 3; $i++) {
							if ($_POST["sr_viewed_ns$i"] != "") {
								$sql .= " OR page_namespace = " . $_POST["sr_viewed_ns$i"];
							}
						}

						$sql .= ")
							ORDER BY
								$revision.rev_id
						";


						$rs = $this->dbr->query($sql);
						while ($row = $this->dbr->fetchRow($rs)) {
							$rev[$row[0]] = $row[1];
							$views[$row[0]] = $row[2];
							$count[$row[1]]++;
						}

						$i = 0;
						foreach ($rev as $key => $val) {
							if (!$counted_rev[$key]) {
								$rev_count = 0;
								$i++;
								if ($count[$val] > 0) {
									$rev_count = round($views[$key] / $count[$val]);
								}
								$this->dbw->insert($this->SocialRewarding["DB"]["viewedArticles"], array(
									"rev_id" => $key,
									"rev_counter" => $rev_count
								));
	
							}
						}

						$output .= "<li><font color=#009900>Initialized page views successfully (method: \"equal shares\", total pages: $i).</font></li>";

					} else if ($_POST["sr_viewed_method"] == "repr") {

						$sql = "
							SELECT
								rev_id,
								" . $this->SocialRewarding["reward"]["sizeMethod"] . "($text.old_text),
								page_id,
								page_counter
							FROM
								$revision,
								$text,
								$page
							WHERE
								$revision.rev_id = $text.old_id
							AND
								$revision.rev_page = $page.page_id
							AND
								(page_namespace = " . $this->SocialRewarding["reward"]["calcBasisArticlesNS"] . "
						";

						if ($_POST["sr_viewed_system_pages"]) {
							$sql .= " OR page_namespace = " . $this->SocialRewarding["reward"]["calcBasisCorrectionNS"];
						}
						if ($_POST["sr_viewed_user_pages"]) {
							$sql .= " OR page_namespace = " . $this->SocialRewarding["reward"]["calcBasisUserPagesNS"];
						}

						for ($i = 1; $i <= 3; $i++) {
							if ($_POST["sr_viewed_ns$i"] != "") {
								$sql .= " OR page_namespace = " . $_POST["sr_viewed_ns$i"];
							}
						}

						$sql .= ")
							ORDER BY
								$revision.rev_id
						";

						$rs = $this->dbr->query($sql);
						while ($row = $this->dbr->fetchRow($rs)) {
							$count_article = count($article[$row[2]]);
							$article[$row[2]][$count_article] = $row[0];
							$size[$row[0]] = $row[1];
							$rev[$row[0]] = $row[2];
							$count[$row[0]] = $row[3];

							$rev_pos = array_search($row[0], $article[$row[2]]);
							$former_rev = $article[$row[2]][$rev_pos - 1];

							$size_rel[$row[0]] = abs($size[$row[0]] - $size[$former_rev]);
							$size_sum[$row[2]] += $size_rel[$row[0]];
						}

						$i = 0;
						foreach ($rev as $key => $val) {
							if (!$counted_rev[$key]) {
								$per_rev = 0;
								$i++;
								if ($size_sum[$val] > 0) {
									$per_rev = $size_rel[$key] / $size_sum[$val];
								}
								$rev_count = round($count[$key] * $per_rev);
								$this->dbw->insert($this->SocialRewarding["DB"]["viewedArticles"], array(
									"rev_id" => $key,
									"rev_counter" => $rev_count
								));
							}
						}
						$output .= "<li><font color=#009900>Initialized page views successfully (method: \"representative shares\", total pages: $i).</font></li>";
					} else {
						$output .= "<li><font color=#0000FF>Unknown page views initialization method (\"" . $_POST["sr_viewed_method"] . "\"). Page views not initialized.</font></li>";
						$warning = true;
					}


				} else {
					$output .= "<li><font color=#000000>Initialize page views is off.</font></li>";
				}



				if ($_POST["sr_references"]) {
					$output .= "<li><font color=#000000>Initialize amount of references is on.</font></li>";

					$sql = "
						SELECT
							rev_id
						FROM
							$revision,
							$page
						WHERE
							$revision.rev_page = $page.page_id
						AND
							(page_namespace = " . $this->SocialRewarding["reward"]["calcBasisArticlesNS"] . "
					";

					if ($_POST["sr_references_system_pages"]) {
						$sql .= " OR page_namespace = " . $this->SocialRewarding["reward"]["calcBasisCorrectionNS"];
					}
					if ($_POST["sr_references_user_pages"]) {
						$sql .= " OR page_namespace = " . $this->SocialRewarding["reward"]["calcBasisUserPagesNS"];
					}

					for ($i = 1; $i <= 3; $i++) {
						if ($_POST["sr_references_ns$i"] != "") {
							$sql .= " OR page_namespace = " . $_POST["sr_references_ns$i"];
						}
					}

					$sql .= ")";


					$rs = $this->dbr->query($sql);
					$i = 0;
					while ($row = $this->dbr->fetchRow($rs)) {
						// Execute hook funtion for Google search
						$ok = SocialRewardingReferences("", "", "", "", "", "", "", $row[0]);
						if ($ok == 1) {
							$i++;
						}
					}

					$output .= "<li><font color=#0009900>Initialized amount of references successfully (total pages: $i).</font></li>";

				} else {
					$output .= "<li><font color=#000000>Initialize amount of references is off.</font></li>";
				}
			}


			if (!$error & !$warning) {
				// No errors, no warnings -> delete installation file
				if (file_exists($this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingINSTALL")) {
					$deleted = @unlink($this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingINSTALL");
					if ($deleted == true) {
						$output .= "<li><font color=#009900>Deleted installation file.</font></li>";
					} else {
						$output .= "<li><font color=#0000FF>Cannot delete installation file. Please delete file <i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingINSTALL</i> on your own.</font></li>";
						$warning = true;
					}
				}
			} else if (!$error) {
				$output .= "<li><font color=#0000FF>Installation file was not deleted. If you do not want to restart the installation please delete file <i>[MediaWikiPath]/" . $this->SocialRewarding["reward"]["extensionPath"] . "/SocialRewardingINSTALL</i> on your own.</font></li>";
				$warning = true;
			}

		$install_end = SocialRewardingMicrotime();
		$install_time = round($install_end - $install_start, $this->SocialRewarding["reward"]["round"]);

		if (!$error) {
			$output .= "<li><font color=#000000>Installation completed in $install_time seconds.</font></li>";
		}

		$output .= "</ul>";
		$output .= "<br>";

		$skin = $wgUser->getSkin();

		if ($error) {
			$output .= "<font color=#FF0000><b>Installation aborted. There were errors during installation. Check error messages.<br>";
			$output .= "<br>Installation NOT completed successfully.</b></font>";
			$output .= "<br><br>You may want to " . $skin->makeLink("Special:SocialRewardingInstall", "restart") . " the installation</a>.";
		} else if ($warning) {
			$output .= "<font color=#0000FF><b>There were warnings during installation. Nevertheless the SocialRewarding extension could work. Check warning messages.<br>";
			$output .= "<br>Installation completed (with warnings).</b></font>";
			$output .= "<br><br>You may want to " . $skin->makeLink("Special:SocialRewardingInstall", "restart") . " the installation</a>.";
		}

		if (!$error && !$warning) {
			$output .= "<font color=#009900><b>Installation completed successfully.</b></font>";
		}

		return $output;
	}



	/**
	 * Get results of page body. A GET request displays
	 * another page body than a POST request.
	 *
	 * @access public
	 * @return String HTML output
	 */
	function getPage() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$output = $this->post();
		} else {
			$output = $this->get();
		}

		return $output;
	}

}


/**
 * Create new instance of SpecialPage class and output HTML.
 *
 * @access public
 */
function wfSpecialSocialRewardingInstall() {
	global $wgOut, $IP, $path;

	// Set a never ending execution time limit for this script
	set_time_limit(0);

	$site = new SpecialSocialRewardingInstall();
	$wgOut->addHTML($site->getPage());
}


?>