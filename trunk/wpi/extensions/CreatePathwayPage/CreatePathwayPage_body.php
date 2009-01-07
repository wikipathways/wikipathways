<?php
require_once("wpi/wpi.php");

class CreatePathwayPage extends SpecialPage
{
        function CreatePathwayPage() {
                SpecialPage::SpecialPage("CreatePathwayPage");
                self::loadMessages();
        }

        function execute( $par ) {
                global $wgRequest, $wgOut, $wpiScriptURL, $wgUser;
                $this->setHeaders();

		if(wfReadOnly()) {
			$wgOut->readOnlyPage( "" );
		}
		
		if(!$wgUser || !$wgUser->isLoggedIn()) {
			$wgOut->addWikiText(
			"== Not logged in ==\n
			You're not logged in. To create a new pathway, please [" . SITE_URL . 
			"/index.php?title=Special:Userlogin&returnto=Special:CreatePathwayPage log in] or 
			create an account first!");
			return;
		}
		
		$pwName = $_GET['pwName'];
		$pwSpecies = $_GET['pwSpecies'];
		$override = $_GET['override'];
		$private = $_GET['private'];
		
		if($_GET['create'] == '1') { //Submit button pressed
			//Check for pathways with the same name and species
			$exist = Pathway::getPathwaysByName($pwName, $pwSpecies);
			if(count($exist) > 0 && !$override) {
				//Print warning
				$pre = "A pathway";
				if(count($exist) > 1) {
					$pre = "Pathways";
				}
				$wgOut->addWikiText("== Warning ==\n<font color='red'>$pre with the name '$pwName' already exist on WikiPathways:</font>\n");
				foreach($exist as $p) {
					$wgOut->addWikiText(
						"* [[Pathway:" . $p->getIdentifier() . "|" . $p->getName() . " (" . $p->getSpecies() . ")]]"
					);
				}
				$wgOut->addWikiText("'''You may consider editing the existing pathway instead of creating a new one.'''\n");
				$wgOut->addWikiText("'''If you still want to create a new pathway, please use a unique name.'''\n");
				$wgOut->addWikiText("----\n");
				$this->showForm($pwName, $pwSpecies, true, $private);
			} else {
				$this->startEditor($pwName, $pwSpecies, $private);
			}
		} else {
			$this->showForm();
		}	}

	function startEditor($pwName, $pwSpecies, $private) {
		global $wgRequest, $wgOut, $wpiScriptURL;		
		$backlink = '<a href="javascript:history.back(-1)">Back</a>';
		if(!$pwName) {
			$wgOut->addHTML("<B>Please specify a name for the pathway<BR>$backlink</B>");
			return;
		}
		if(!$pwSpecies) {
			$wgOut->addHTML("<B>Please specify a species for the pathway<BR>$backlink</B>");
			return;
		}
		try {
			$wgOut->addHTML("<div id='applet'></div>");
			$pwTitle = "$pwName:$pwSpecies";
			$new = $private ? 'private' : 'true';
			$wgOut->addWikiText("{{#editApplet:direct|applet|$new|$pwTitle}}");
		} catch(Exception $e) {
			$wgOut->addHTML("<B>Error:</B><P>{$e->getMessage()}</P><BR>$backlink</BR>");
			return;
		}
	}

	function showForm($pwName = '', $pwSpecies = '', $override = '', $private = '') {
		global $wgRequest, $wgOut, $wpiScriptURL;
		$html = tag('p', 'To create a new pathway on WikiPathways, specify the pathway name and species 
				and then click "create pathway" to start the pathway editor.<br>'
				);
		$html .= "	<input type='hidden' name='create' value='1'>
				<input type='hidden' name='title' value='Special:CreatePathwayPage'>
				<td>Pathway name:
				<td><input type='text' name='pwName' value='$pwName'>
				<tr><td>Species:<td>
				<select name='pwSpecies'>";
		$species = Pathway::getAvailableSpecies();
		if(!$pwSpecies) {
			$pwSpecies = $species[0];
		}
		foreach($species as $sp) {
			$html .= "<option value='$sp'" . ($sp == $pwSpecies ? ' selected' : '') . ">$sp";
		}
		$html .= '</select>';
		if($override) {
			$html .= "<input type='hidden' name='override' value='1'>";
		}
		if($private) $private = 'CHECKED';
		$html .= "<tr><td colspan='2'><input type='checkbox' name='private' value='1' $private>" . wfMsg('create_private');
		$html = tag('table', $html);
		$html .= tag('input', "", array('type'=>'submit', 'value'=>'Create pathway'));
		$html = tag('form', $html, array('action'=> SITE_URL . '/index.php', 'method'=>'get'));
		$wgOut->addHTML($html);
	}

        function loadMessages() {
                static $messagesLoaded = false;
                global $wgMessageCache;
                if ( $messagesLoaded ) return true;
                $messagesLoaded = true;

                require( dirname( __FILE__ ) . '/CreatePathwayPage.i18n.php' );
                foreach ( $allMessages as $lang => $langMessages ) {
                        $wgMessageCache->addMessages( $langMessages, $lang );
                }
                return true;
        }
}
?>
