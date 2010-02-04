<?php
require_once('wpi/wpi.php');
require_once('Pager.php');
require_once('PageHistory.php');


$wgExtensionFunctions[] = "wfPathwayHistory";

function wfPathwayHistory() {
    global $wgParser;
    $wgParser->setHook( "pathwayHistory", "history" );
}

function history( $input, $argv, &$parser ) {
	$parser->disableCache();
	try {
		$pathway = Pathway::newFromTitle($parser->mTitle);
		return getHistory($pathway);
	} catch(Exception $e) {
		return "Error: $e";
	}
}

function getHistory($pathway) {
		global $wgUser, $wpiScriptURL;
		
		$gpmlTitle = $pathway->getTitleObject();
		$gpmlArticle = new Article($gpmlTitle);
		$hist = new PageHistory($gpmlArticle);

		$pager = new GpmlHistoryPager( $pathway, $hist );

		$s = $pager->getBody();
		return $s;
}

function historyRow($h, $style) {

	if($h) {
		$row = "<TR $style>";
		$row .= "<TD>$h[diff]";
		$row .= "<TD id=\"historyTable_$h[id]_tag\">$h[id]";
		$row .= "<TD>$h[rev]$h[view]";
		$row .= "<TD>$h[date]";
		$row .= "<TD>$h[user]";
		$row .= "<TD>$h[descr]";
		return $row;
	} else {
		return "";
	}
}

function historyLine($pathway, $row, $nr, $counter = '', $cur = false, $firstInList = false) {
	global $wpiScript, $wgLang, $wgUser, $wgTitle;
	
	$rev = new Revision( $row );
	
	$user = User::newFromId($rev->getUser());
	/* Show bots
	if($user->isBot()) {
		//Ignore bots
		return "";
	}
	*/
	
	$rev->setTitle( $pathway->getFileTitle(FILETYPE_GPML) );

	$revUrl = WPI_SCRIPT_URL . '?action=revert&pwTitle=' .
				$pathway->getTitleObject()->getPartialURL() .
				"&oldid={$rev->getId()}";
	
	$diff = diffButtons( $rev, $firstInList, $counter, $nr );

	$revert = "";
	if($wgUser->getID() != 0 && $wgTitle && $wgTitle->userCanEdit()) {
		$revert = $cur ? "" : "(<A href=$revUrl>revert</A>), ";
	}
	
	$dt = $wgLang->timeanddate( wfTimestamp(TS_MW, $rev->getTimestamp()), true );
	$oldid = $firstInList ? '' : "oldid=" . $rev->getId();
	$view = $wgUser->getSkin()->makeKnownLinkObj($pathway->getTitleObject(), 'view', $oldid );

	$date = $wgLang->timeanddate( $rev->getTimestamp(), true );
	$user = $wgUser->getSkin()->userLink( $rev->getUser(), $rev->getUserText() );
	$descr = htmlentities($rev->getComment());
	return array('diff'=>$diff, 'rev'=>$revert, 'view'=>$view, 'date'=>$date, 'user'=>$user, 'descr'=>$descr, 'id'=>$rev->getId());
}
        
/**
 * Generates dynamic display of radio buttons for selecting versions to compare
 */
function diffButtons( $rev, $firstInList, $counter, $linesonpage) {
                if( $linesonpage > 1) {
                        $radio = array(
                                'type'  => 'radio',
                                'value' => $rev->getId(),
# do we really need to flood this on every item?
#                               'title' => wfMsgHtml( 'selectolderversionfordiff' )
                        );

                        if( !$rev->userCan( Revision::DELETED_TEXT ) ) {
                                $radio['disabled'] = 'disabled';
                        }

                        /** @todo: move title texts to javascript */
                        if ( $firstInList ) {
			           $first = wfElement( 'input', array_merge(
                                        $radio,
                                        array(
                                                'style' => 'visibility:hidden',
                                                'name'  => 'old' ) ) );
                                $checkmark = array( 'checked' => 'checked' );
                        } else {
                                if( $counter == 2 ) {
                                        $checkmark = array( 'checked' => 'checked' );
                                } else {
                                        $checkmark = array();
                                }
                                $first = wfElement( 'input', array_merge(
                                        $radio,
                                        $checkmark,
                                        array( 'name'  => 'old' ) ) );
                                $checkmark = array();
                        }
                        $second = wfElement( 'input', array_merge(
                                $radio,
                                $checkmark,
                                array( 'name'  => 'new' ) ) );
                        return $first . $second;
                } else {
                        return '';
                }
}

class GpmlHistoryPager extends PageHistoryPager {
	private $pathway;
	private $nrShow = 4;

	function __construct( $pathway, $pageHistory ) {
		parent::__construct( $pageHistory );
		$this->pathway = $pathway;
	}

	function formatRow( $row ) {
		$latest = $this->mCounter == 1;
		$firstInList = $this->mCounter == 1;
		$style = ($this->mCounter <= $this->nrShow) ? '' : 'style="display:none"';
		
		$s = historyRow(historyLine($this->pathway, $row, $this->getNumRows(), $this->mCounter++, $latest, $firstInList), $style);
		
		$this->mLastRow = $row;
		return $s;
	}

	function getStartBody() {
		$this->mLastRow = false;
		$this->mCounter = 1;
		
		$nr = $this->getNumRows();
		
		if($nr < 1) {
			$table = '';
		} else {
			$table = '<form action="' . SITE_URL . '/index.php" method="get">';
			$table .= '<input type="hidden" name="title" value="Special:DiffAppletPage"/>';
			$table .= '<input type="hidden" name="pwTitle" value="' . $this->pathway->getTitleObject()->getFullText() . '"/>';
			$table .= '<input type="submit" value="Compare selected versions"/>';
			$table .= "<TABLE  id='historyTable' class='wikitable'><TR><TH>Compare<TH>Revision<TH>Action<TH>Time<TH>User<TH>Comment<TH id='historyHeaderTag' style='display:none'>";

		}

		if($nr >= $this->nrShow) {
			$expand = "<B>View all</B>";
			$collapse = "<B>View last " . ($this->nrShow - 1) . "</B>";
			$button = "<table><td width='51%'><div onClick='toggleRows(\"historyTable\", this, \"$expand\",
                                \"$collapse\", {$this->nrShow}, true)' style='cursor:pointer;color:#0000FF'>$expand<td width='20%'></table>";
			$table = $button . $table;
		}

		return $table;
	}

	function getEndBody() {
		$end = "</TABLE>";
		$end .= '<input type="submit" value="Compare selected versions"></form>';
		return $end;
	}
}

?>
