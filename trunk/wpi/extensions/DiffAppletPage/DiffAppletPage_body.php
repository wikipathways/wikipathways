<?php
require_once("wpi/wpi.php");

class DiffAppletPage extends SpecialPage
{		
        function DiffAppletPage() {
                SpecialPage::SpecialPage("DiffAppletPage");
                self::loadMessages();
        }

        function execute( $par ) {
                global $wgRequest, $wgOut;
                $this->setHeaders();
		
		try {
			$revOld = $_REQUEST['old'];
			$revNew = $_REQUEST['new'];
			$pwTitle = $_REQUEST['pwTitle'];
			$pathway = Pathway::newFromTitle($pwTitle);
		} catch(Exception $e) {
			$wgOut->addHTML(
			'<H2>Error</H2><P>The given title is not a pathway page!</P>'
			);
			return;
		}
		$pwName = $pathway->name() . ' (' . $pathway->species() . ')';
		$headerTable = <<<TABLE
<TABLE width="100%"><TBODY>
<TR align="center">
<TD>{$pwName}, revision {$revOld}
<TD>{$pwName}, revision {$revNew}
</TBODY></TABLE>
TABLE;
		$wgOut->addHTML($headerTable);
		$wgOut->addHTML(createDiffApplet($pathway, $revOld, $revNew));        }

        function loadMessages() {
                static $messagesLoaded = false;
                global $wgMessageCache;
                if ( $messagesLoaded ) return true;
                $messagesLoaded = true;

                require( dirname( __FILE__ ) . '/DiffAppletPage.i18n.php' );
                foreach ( $allMessages as $lang => $langMessages ) {
                        $wgMessageCache->addMessages( $langMessages, $lang );
                }
                return true;
        }
}

function createDiffApplet($pathway, $revOld, $revNew) {
	$pathway->setActiveRevision($revOld);
	$file1 = $pathway->getFileURL(FILETYPE_GPML);
	
	$pathway->setActiveRevision($revNew);
	$file2 = $pathway->getFileURL(FILETYPE_GPML);

	$base = EditApplet::getAppletBase();
	$applet = <<<APPLET
	<applet 
		width="100%" 
		height="500" 
		standby="Loading DiffView applet ..." 
		codebase="{$base}" 
		archive="{$base}/diffview.jar" 
		type="application/x-java-applet" 
		code="org.wikipathways.gpmldiff.AppletMain.class">
		<param name="old" value="$file1"/>
		<param name="new" value="$file2"/>
	</applet>
APPLET;
	return $applet;
}
?>
