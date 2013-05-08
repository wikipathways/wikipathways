<?php

class LocalHooks {
	/* http://developers.pathvisio.org/ticket/1559 */
	static function stopDisplay( $output, $sk ) {
		if( 'MediaWiki:Questycaptcha-qna' === $output->getPageTitle() ) {
			global $wgUser, $wgTitle;
			if( !$wgTitle->userCan( "edit" ) ) {
				$output->clearHTML();
				$wgUser->mBlock = new Block( '127.0.0.1', 'WikiSysop', 'WikiSysop', 'none', 'indefinite' );
				$wgUser->mBlockedby = 0;
				$output->blockedPage();
				return false;
			}
		}
		return true;
	}

	/* http://www.pathvisio.org/ticket/1539 */
	static public function externalLink ( &$url, &$text, &$link, &$attribs = null ) {
		global $wgExternalLinkTarget;
		wfProfileIn( __METHOD__ );
		wfDebug(__METHOD__.": Looking at the link: $url\n");

		$linkTarget = "_blank";
		if( isset( $wgExternalLinkTarget ) && $wgExternalLinkTarget != "") {
			$linkTarget = $wgExternalLinkTarget;
		}

		/**AP20070417 -- moved from Linker.php by mah 20130327
		 * Added support for opening external links as new page
		 * Usage: [http://www.genmapp.org|_new Link]
		 */
		if ( substr( $url, -5 ) == "|_new" ) {
			$url = substr( $url, 0, strlen( $url ) - 5 );
			$linkTarget = "new";
		} elseif ( substr( $url, -7 ) == "%7c_new" ) {
			$url = substr( $url, 0, strlen( $url ) - 7 );
			$linkTarget = "new";
		}

		# Hook changed to include attribs in 1.15
		if( $attribs !== null ) {
			$attribs["target"] = $linkTarget;
			return true;		/* nothing else should be needed, so we can leave the rest */
		}

		/* ugh ... had to copy this bit from makeExternalLink */
		$l = new Linker;
		$style = $l->getExternalLinkAttributes( $url, $text, 'external ' );
		global $wgNoFollowLinks, $wgNoFollowNsExceptions;
		if( $wgNoFollowLinks && !(isset($ns) && in_array($ns, $wgNoFollowNsExceptions)) ) {
			$style .= ' rel="nofollow"';
		}

		$link = '<a href="'.$url.'" target="'.$linkTarget.'"'.$style.'>'.$text.'</a>';
		wfProfileOut( __METHOD__ );

		return false;
	}
}

$wgHooks['LinkerMakeExternalLink'][] = 'LocalHooks::externalLink';
$wgHooks['BeforePageDisplay'][] = 'LocalHooks::stopDisplay';

