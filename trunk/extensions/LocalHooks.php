<?php

class LocalHooks {
	/* http://www.pathvisio.org/ticket/1539 */
	static public function externalLink ( &$url, &$text, &$link, &$attribs = null ) {
		global $wgExternalLinkTarget;
		wfProfileIn( __METHOD__ );
		if( $attribs !== null ) {
			if( $wgExternalLinkTarget !== false ) {
				$attribs["target"] = $wgExternalLinkTarget;
			} else {
				$attribs["target"] = "_blank";
			}
		} else {
			$link = '<a href="'.$url.'" target="_blank">'.$text.'</a>';
		}
		wfProfileOut( __METHOD__ );


		return true;
	}
}

$wgHooks['LinkerMakeExternalLink'][] = 'LocalHooks::externalLink';
