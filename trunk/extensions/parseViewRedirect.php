<?php
 
    $wgHooks["ArticleViewRedirect"][] = 'wfBlockPathways';

    /**
     * Blocks the display of 'Redirected from...' for pathway pages. 
     * Prevents links to old pathway names which break stable id impl.
     */
    function wfBlockPathways ($article) {
	global $wgHooks;
	
	$ns = $article->getTitle()->getNamespace();
	if ($ns == NS_PATHWAY) {
		return false;
	} else {
		return true;
	}	    
    }

?>
