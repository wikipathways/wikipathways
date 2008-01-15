<?php
require_once("QueryPage.php");
require_once("ChangesList.php");
require_once($wgScriptPath . "wpi/wpi.php");

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
                if ( $messagesLoaded ) return;
                $messagesLoaded = true;

                require( dirname( __FILE__ ) . '/RecentPathwayChanges.i18n.php' );
                foreach ( $allMessages as $lang => $langMessages ) {
                        $wgMessageCache->addMessages( $langMessages, $lang );
                }
        }
		
}

class RecentQueryPage extends QueryPage {
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

	function getSQL() {
		global $wgUser, $wgOut;

		$dbr =& wfGetDB( DB_SLAVE );
		list( $recentchanges, $watchlist ) = $dbr->tableNamesN( 'recentchanges', 'watchlist' );
		
		//$days = 90;
       		//$cutoff_unixtime = time() - ( $days * 86400 );
        	//$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
        	//$cutoff = $dbr->timestamp( $cutoff_unixtime );
		
		//$orderby_value = 'rc_timestamp as value';
                //$orderby_value = 'rc_user_text as value';
		$orderby_value = 'rc_title as value';

		$forceclause = $dbr->useIndexClause("rc_timestamp");

		$sql = "SELECT *,
				'RecentPathwayChanges' as type,
				rc_namespace as namespace,
			 	rc_title as title,
				UNIX_TIMESTAMP(rc_timestamp) as unix_time,
				$orderby_value
			FROM $recentchanges $foreclause 
			WHERE rc_namespace = " . $this->namespace .
			" AND rc_bot = 0
			AND rc_minor = 0 "; 
		
		return $sql;
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
                        $diffLink = $skin->makeKnownLinkObj( $title, $this->message['diff'],
                                wfArrayToCGI( array(
                                        'curid' => $result->rc_cur_id,
                                        'diff'  => $result->rc_this_oldid,
                                        'oldid' => $result->rc_last_oldid ),
                                        array( 'rcid' => $result->rc_id) ),
                                '', '', ' tabindex="'.$result->counter.'"');
		}

		$text = $wgContLang->convert($result->rc_comment);
		$plink = $skin->makeKnownLinkObj( $title, htmlspecialchars( $wgContLang->convert($title->getBaseText())) );

		/* Not link to history for now, later on link to our own pathway history
		$nl = wfMsgExt( 'nrevisions', array( 'parsemag', 'escape'),
			$wgLang->formatNum( $result->value ) );
		$nlink = $skin->makeKnownLinkObj( $nt, $nl, 'action=history' );
		*/

		return wfSpecialList("(".$diffLink.") (".$histLink.") . . ".$plink. ": <b>".$date."</b> by <b>".$name."</b>","<i>".$comment."</i>");
	}
}
?>
