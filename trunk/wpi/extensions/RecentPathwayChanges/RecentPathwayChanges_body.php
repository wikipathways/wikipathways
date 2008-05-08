<?php
require_once("QueryPage.php");
require_once("ChangesList.php");
require_once("wpi/wpi.php");

class RecentPathwayChanges extends SpecialPage
{		
        function RecentPathwayChanges() {
                SpecialPage::SpecialPage("RecentPathwayChanges");
                self::loadMessages();
        }

        function execute( $par ) {
                global $wgRequest, $wgOut;
                
                $this->setHeaders();

                list( $limit, $offset ) = wfCheckLimits();				
				//Recently changed pathway articles
				$ppp = new RecentQueryPage(NS_PATHWAY);

				$ppp->doQuery( $offset, $limit );
							
				return true;
        }

        function loadMessages() {
                static $messagesLoaded = false;
                global $wgMessageCache;
                if ( $messagesLoaded ) return true;
                $messagesLoaded = true;

                require( dirname( __FILE__ ) . '/RecentPathwayChanges.i18n.php' );
                foreach ( $allMessages as $lang => $langMessages ) {
                        $wgMessageCache->addMessages( $langMessages, $lang );
                }
                return true;
        }
		
}

class RecentQueryPage extends QueryPage {
	var $requestedSort = '';
	private $namespace;
	
	function __construct($namespace) {
		$this->namespace = $namespace;
	}
	
	function getName() {
		return "RecentPathwayChanges";
	}

	function isExpensive() {
		# page_counter is not indexed
		return true;
	}
	function isSyndicated() { return false; }

        /**
         * Show a drop down list to select a field for sorting.
         */
        function getPageHeader( ) {
 		global $wgRequest;
	        $requestedSort = $wgRequest->getVal('sort');
        
               $self = $this->getTitle();

                # Form tag
                $out = wfOpenElement( 'form', array( 'method' => 'post', 'action' => $self->getLocalUrl() ) );

                # Drop-down list
                $out .= wfElement( 'label', array( 'for' => 'sort' ), 'Sort by:' ) . ' ';
                $out .= wfOpenElement( 'select', array( 'name' => 'sort' ) );
                $fields = array('Date','Title','User');
                foreach( $fields as $field ) {
                        $attribs = array( 'value' => $field );
                        if( $field == $requestedSort )
                                $attribs['selected'] = 'selected';
                        $out .= wfElement( 'option', $attribs, $field );
                }
                $out .= wfCloseElement( 'select' ) . ' ';;# . wfElement( 'br' );

                # Submit button and form bottom
                $out .= wfElement( 'input', array( 'type' => 'submit', 'value' => wfMsg( 'allpagessubmit' ) ) );
                $out .= wfCloseElement( 'form' );

                return $out;
        }

	function getSQL() {
		global $wgUser, $wgOut;

		$dbr =& wfGetDB( DB_SLAVE );
		list( $recentchanges, $watchlist ) = $dbr->tableNamesN( 'recentchanges', 'watchlist' );
		
		//$days = 90;
       		//$cutoff_unixtime = time() - ( $days * 86400 );
        	//$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
        	//$cutoff = $dbr->timestamp( $cutoff_unixtime );

		$forceclause = $dbr->useIndexClause("rc_timestamp");

		$sql = "SELECT *,
				'RecentPathwayChanges' as type,
				rc_namespace as namespace,
			 	rc_title as title,
				UNIX_TIMESTAMP(rc_timestamp) as unix_time,
				rc_timestamp as value
			FROM $recentchanges $forceclause 
			WHERE rc_namespace = " . $this->namespace .
			" AND rc_bot = 0
			AND rc_minor = 0 "; 
		
		return $sql;
	}

        function getOrder() {
		global $wgRequest;
		$requestedSort = $wgRequest->getVal('sort');
		
		if ($requestedSort == 'Title'){
			return 'ORDER BY rc_title, rc_timestamp DESC';
		} elseif ($requestedSort == 'User'){
			return 'ORDER BY rc_user_text, rc_timestamp DESC';
		} else {
			return 'ORDER BY rc_timestamp DESC';
		}
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang;

		$userPage = Title::makeTitle( NS_USER, $result->rc_user_text );
		$name = $skin->makeLinkObj( $userPage, htmlspecialchars( $userPage->getText() ) );
		$date = date('d F Y', $result->unix_time);
		$comment = ($result->rc_comment ? $result->rc_comment : "no comment");
		$title = Title::makeTitle( NS_PATHWAY, $result->title ); 

		$this->message['hist'] = wfMsgExt( 'hist', array( 'escape'));
		$histLink = $skin->makeKnownLinkObj($title, $this->message['hist'],
				wfArrayToCGI( array(
                                'curid' => $result->rc_cur_id,
				'action' => 'history')));

		$this->message['diff'] = wfMsgExt('diff', array( 'escape'));
	 	if( $result->rc_type > 0 ) { //not an edit of an existing page
                        $diffLink = $this->message['diff'];
                } else {
                        $diffLink = "<a href='" . SITE_URL . 
                        	"/index.php?title=Special:DiffAppletPage&old={$result->rc_last_oldid}&new={$result->rc_this_oldid}" .
                        	"&pwTitle={$title->getFullText()}'>diff</a>";
		}

		$text = $wgContLang->convert($result->rc_comment);
		$plink = $skin->makeKnownLinkObj( $title, htmlspecialchars( $wgContLang->convert($title->getBaseText())) );

		/* Not link to history for now, later on link to our own pathway history
		$nl = wfMsgExt( 'nrevisions', array( 'parsemag', 'escape'),
			$wgLang->formatNum( $result->value ) );
		$nlink = $skin->makeKnownLinkObj( $nt, $nl, 'action=history' );
		*/

		return wfSpecialList("(".$diffLink.") . . ".$plink. ": <b>".$date."</b> by <b>".$name."</b>","<i>".$text."</i>");
	}
}
?>
