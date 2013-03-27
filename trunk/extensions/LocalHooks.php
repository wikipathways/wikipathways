<?php

class LocalHooks {
	/* http://www.pathvisio.org/ticket/1539 */
	static public function externalLink ( &$url, &$text, &$link, &$attribs = null ) {
		global $wgExternalLinkTarget;
		wfProfileIn( __METHOD__ );
		wfDebug(__METHOD__.": Looking at the link: $url\n");

		$linkTarget = "_blank";
		if( isset( $wgExternalLinkTarget ) && $wgExternalLinkTarget != "") {
			$linkTarget = $wgExternalLinkTarget;
		}
		# Hook changed to include attribs in 1.15
		if( $attribs !== null ) {
			$attribs["target"] = $linkTarget;
		} else {
			$link = '<a href="'.$url.'" target="'.$linkTarget.'">'.$text.'</a>';
		}
		wfProfileOut( __METHOD__ );

		return false;
	}
}

$wgHooks['LinkerMakeExternalLink'][] = 'LocalHooks::externalLink';
