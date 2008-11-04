<?php
require_once("QueryPage.php");
require_once("wpi/wpi.php");

class MostEditedPathwaysPage extends SpecialPage
{		
        function MostEditedPathwaysPage() {
                SpecialPage::SpecialPage("MostEditedPathwaysPage");
                self::loadMessages();
        }

        function execute( $par ) {
                global $wgRequest, $wgOut;
                
                $this->setHeaders();

                list( $limit, $offset ) = wfCheckLimits();				
				//Most edited pathway articles
				$ppp = new PathwayQueryPage(NS_PATHWAY);

				$ppp->doQuery( $offset, $limit );
							
				return true;
        }

        function loadMessages() {
                static $messagesLoaded = false;
                global $wgMessageCache;
                if ( $messagesLoaded ) return true;
                $messagesLoaded = true;

                require( dirname( __FILE__ ) . '/MostEditedPathwaysPage.i18n.php' );
                foreach ( $allMessages as $lang => $langMessages ) {
                        $wgMessageCache->addMessages( $langMessages, $lang );
                }
                return true;
        }
		
}

class PathwayQueryPage extends QueryPage {
	private $namespace;
	
	function __construct($namespace) {
		$this->namespace = $namespace;
	}
	
	function getName() {
		return "MostEditedPathwaysPage";
	}

	function isExpensive() {
		# page_counter is not indexed
		return true;
	}
	function isSyndicated() { return false; }

	function getSQL() {
		$dbr =& wfGetDB( DB_SLAVE );
		list( $revision, $page ) = $dbr->tableNamesN( 'revision', 'page' );
		return
			"
			SELECT
				'Mostrevisions' as type,
				page_namespace as namespace,
				page_title as title,
				COUNT(*) as value
			FROM $revision
			JOIN $page ON page_id = rev_page
			WHERE page_namespace = " . $this->namespace . "
			AND page_is_redirect = 0
			GROUP BY 1,2,3
			HAVING COUNT(*) > 1
			";
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang;
                $pathway = Pathway::newFromTitle($result->title);
                $title = Title::makeTitle( $result->namespace, $pathway->getSpecies().":".$pathway->getName() );
                $id = Title::makeTitle( $result->namespace, $result->title );
		$text = $wgContLang->convert("$result->value revisions");
		$plink = $skin->makeKnownLinkObj( $id, htmlspecialchars( $wgContLang->convert($title->getBaseText())) );

		/* Not link to history for now, later on link to our own pathway history
		$nl = wfMsgExt( 'nrevisions', array( 'parsemag', 'escape'),
			$wgLang->formatNum( $result->value ) );
		$nlink = $skin->makeKnownLinkObj( $nt, $nl, 'action=history' );
		*/

		return wfSpecialList($plink, $text);
	}
}
?>
