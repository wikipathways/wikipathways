<?php

/* Setup */

// Initialize an easy to use shortcut:
$dir = dirname( __FILE__ );
$dirbasename = basename( $dir );

$wgExtensionMessagesFiles['DiffViewer'] = dirname( __FILE__ ) . '/DiffViewer.i18n.php';
$wgAutoloadClasses['SpecialDiffViewer'] = $dir . '/specials/SpecialDiffViewer.php';


// Register special pages
// See also http://www.mediawiki.org/wiki/Manual:Special_pages
$wgSpecialPages['DiffViewer'] = 'SpecialDiffViewer';
$wgSpecialPageGroups['DiffViewer'] = 'other';
