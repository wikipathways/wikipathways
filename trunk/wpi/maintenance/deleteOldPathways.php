<?php

/**
 * Transition script for M34 -- Deletes pathway pages using wiki
 * deletion that are marked in the wiki as deleted.
 *
 * Usage: php deleteOldPathways.php
 *
 * @file
 * @ingroup Maintenance
 */

if( !isset($IP) ) $IP = dirname( dirname( dirname( __FILE__ ) ) );
require_once( "$IP/maintenance/commandLine.inc" );
require_once( "$IP/wpi/extensions/BrowsePathways/BrowsePathways_body.php");

$wgAutoloadClasses['PagerIterator']    = "$IP/wpi/extensions/Pager/PagerIterator.php";
$wgAutoloadClasses['CliPathwaysPager'] = "$IP/wpi/extensions/Pager/CliPathwaysPager.php";

# Setup complete, now start

function needsDeletion( $pathway ) {
	$title = Title::newFromText( $pathway, NS_PATHWAY );
	$page = Revision::loadFromTitle( wfGetDB(), $title );
	if( $page && substr( $page->getText(), 0, 10 ) === "{{deleted|" ) {
		return $title;
	}
}

function main()  {
	$pager = new PagerIterator( 'CliPathwaysPager' );

	foreach( $pager as $k => $v ) {
		echo "$k\n";
		if( $title = needsDeletion( $k ) ){
			echo "Deleting $k\n";

			$article = new Article( $title );
			$article->doDeleteArticle( "bulk delete of ".
				"marked-deleted Pathways" );
		}
	}
}

main();