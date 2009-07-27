<?php
require_once("wpi/wpi.php");

class CreatePathwayPage extends SpecialPage
{
	private $this_url;
	private $create_priv_msg;

        function CreatePathwayPage() {
                SpecialPage::SpecialPage("CreatePathwayPage");
                self::loadMessages();
        }

        function execute( $par ) {
                global $wgRequest, $wgOut, $wpiScriptURL, $wgUser;
                $this->setHeaders();
		$this->this_url = SITE_URL . '/index.php';
		$this->create_priv_msg = wfMsg('create_private') ;
 
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
                $pwNameLen = strlen($pwName);
		$pwSpecies = $_GET['pwSpecies'];
		$override = $_GET['override'];
		$private = $_GET['private'];
		$uploading = $_POST['upload'];
		$private2 = $_POST['private2'];
		
		
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
			} elseif(!$pwName) {
				$wgOut->addWikiText("== Warning ==\n<font color='red'>No pathway name given!</font>\n'''Please specify a name for the pathway'''\n----\n");
                                $this->showForm($pwName, $pwSpecies, true, $private);
	                } elseif(!$pwSpecies) {
                                $wgOut->addWikiText("== Warning ==\n<font color='red'>No species given!</font>\n'''Please specify a species for the pathway'''\n----\n");
                                $this->showForm($pwName, $pwSpecies, true, $private);
			} elseif ($pwNameLen > 50) { 
 		                $wgOut->addWikiText("== Warning ==\n<font color='red'>Your pathway name is too long! ''($pwNameLen characters)''</font>\n"); 
				$wgOut->addWikiText("'''Please specify a name with less than 50 characters.'''\n----\n");
 		                $this->showForm($pwName, $pwSpecies, false, $private);
			} else {
				$this->startEditor($pwName, $pwSpecies, $private);
			}
		} elseif($uploading == '1') { //Upload button pressed   
                        $this->doUpload($uploading, $private2);
                } else {
			$this->showForm();
		}
	}

	function doUpload($uploading, $private2) {
		global $wgRequest, $wgOut, $wpiScriptURL, $wgUser;
		//Check for something... anything
		if (!empty($_FILES['gpml']['name'])) { 
			$size = $_FILES['gpml']['size'];
			//Check file size
			if ($size > 1000000) {
				$size = $size / 1000000;
				$wgOut->addWikiText("== Warning ==\n<font color='red'>File too large! ''($size MB)''</font>\n'''Please select a GPML file under 1MB.'''\n----\n");
                                $wgOut->addWikiText("----\n");
                                $this->showForm('','',false,'', $uploading, $private2);
			}
			$file = $_FILES['gpml']['name'];
			//Check for gpml extension
			if(!eregi(".gpml$", $file)){
				$wgOut->addWikiText("== Warning ==\n<font color='red'>Not a GPML file!</font>\n'''Please select a GPML file for upload.'''\n----\n");
                	        $wgOut->addWikiText("----\n");
                	        $this->showForm('','',false,'', $uploading, $private2);
			} else {
				//It looks good, let's create a new pathway!
				$gpmlTempFile = $_FILES['gpml']['tmp_name'];
				$GPML = fopen($gpmlTempFile, 'r');
				$gpmlData = fread($GPML, filesize($gpmlTempFile));
				fclose($GPML);
			        try {
			                $pathway = Pathway::createNewPathway($gpmlData);
                			$title = $pathway->getTitleObject();
                			if($private2) $pathway->makePrivate($wgUser);
	                                $wgOut->addWikiText("'''<font color='green'>Pathway successfully upload!</font>'''\n'''Check it out at $title.'''\n----\n");
        			} catch(Exception $e) {
                			wfDebug("GPML UPLOAD ERROR: $e");
        			}
                	        $this->showForm('','',false,'', $uploading, $private2);
			}
		} else {
			$wgOut->addWikiText("== Warning ==\n<font color='red'>No file detected!</font>\n'''Please try again.'''\n----\n");
			$this->showForm('','',false,'', $uploading, $private2);
		}
	}

	function startEditor($pwName, $pwSpecies, $private) {
		global $wgRequest, $wgOut, $wpiScriptURL;		
		$backlink = '<a href="javascript:history.back(-1)">Back</a>';
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

	function showForm($pwName = '', $pwSpecies = '', $override = '', $private = '', $uploading = 0, $private2 = '') {
		global $wgRequest, $wgOut, $wpiScriptURL;
                $this->addJavaScript();

		$html = tag('p', 'To create a new pathway on WikiPathways, specify the pathway name and species 
				and then click "create pathway" to start the pathway editor.<br>'
				);
		$html_form ="	<input type='hidden' name='create' value='1'>
				<input type='hidden' name='title' value='Special:CreatePathwayPage'>
				<table><td>Pathway name:
				<td><input type='text' name='pwName' value='$pwName'>
				<tr><td>Species:<td>
				<select name='pwSpecies'>";
		$species = Pathway::getAvailableSpecies();
		if(!$pwSpecies) {
			$pwSpecies = $species[0];
		}
		foreach($species as $sp) {
			$html_form .= "<option value='$sp'" . ($sp == $pwSpecies ? ' selected' : '') . ">$sp";
		}
		$html .= '</select>';
		if($override) {
			$html_form .= "<input type='hidden' name='override' value='1'>";
		}
		if($private) $private = 'CHECKED';
		$html_form .= "<tr><td colspan='2'><input type='checkbox' name='private' value='1' $private>" . wfMsg('create_private');
		$html_form .= "<tr><td><input type='submit' value='Create pathway'> </table>";
                $html.= tag('form', $html_form, array('action'=> SITE_URL . '/index.php', 'method'=>'get'));
		$wgOut->addHTML($html);

                //Toggle GPML upload option
                $elm = $this->getNewFormElements($uploading, $private2);
                $newdiv = $elm['div'];
                $newbutton = $elm['button'];
                $wgOut->addHTML("<BR> $newbutton");
                $wgOut->addHTML($newdiv);

	}
        function addJavaScript() {
                global $wgOut, $wgScriptPath;
                $js = <<<JS
<script type="text/javascript">
        function showhide(id, toggle, hidelabel, showlabel) {
                elm = document.getElementById(id);
                if(toggle.innerHTML == hidelabel) {
                        elm.style.display = "none";
                        toggle.innerHTML = showlabel;
                } else {
                        elm.style.display = "";
                        toggle.innerHTML = hidelabel;
                }
        }
</script>
JS;
                $wgOut->addScript($js);
        }

        function getNewFormElements($uploading = 0, $private2 = '') {
                global $wgUser;
		if($private2) $private2 = 'CHECKED';
		if (!$uploading) $display = "display:none";
                $div = <<<DIV
<div id="upload" style={$display}>
<table><td>
<FORM action="{$this->this_url}" method="post" enctype="multipart/form-data">
	<INPUT type="file" name="gpml" size="40">
	<tr><td> 
   	<INPUT type='checkbox' name='private2' value='1' {$private2}> {$this->create_priv_msg}<BR>
	<input type='hidden' name='upload' value='1'>
	<input type='hidden' name='title' value='Special:CreatePathwayPage'>
	<tr><td>
	<INPUT type='submit' value='Upload pathway'>
</FORM>
</table>
</div>
DIV;

    	$button = "<a href=\"javascript:showhide('upload', this, 'hide', 'show');\">Upload GPML</a>";
        return array('button' => $button, 'div' => $div);
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
