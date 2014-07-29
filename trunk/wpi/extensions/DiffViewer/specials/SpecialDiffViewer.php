<?php
/**
 * HelloWorld SpecialPage for Example extension
 *
 * @file
 * @ingroup Extensions
 */

class SpecialDiffViewer extends SpecialPage {

	/**
	 * Initialize the special page.
	 */
	public function __construct() {
		// A special page should at least have a name.
		// We do this by calling the parent class (the SpecialPage class)
		// constructor method with the name as first and only parameter.
		parent::__construct( 'DiffViewer' );
	}

	/**
	 * Shows the page to the user.
	 * @param string $sub: The subpage string argument (if any).
	 *  [[Special:HelloWorld/subpage]].
	 */
	public function execute( $sub ) {
		global $wgRequest, $wgOut;

		$this->setupHeader();

		// Parse query parameters
		try {
			$revOld = $wgRequest->getVal( 'old' );
			$revNew = $wgRequest->getVal( 'new' );
			$pwTitle = $wgRequest->getVal( 'pwTitle' );
			$pathway = Pathway::newFromTitle($pwTitle);
		} catch(Exception $e) {
			$wgOut->addHTML(
				'<H2>Error</H2><P>The given title is not a pathway page!</P>'
			);
			return;
		}

		$pwName = $pathway->name() . ' (' . $pathway->species() . ')';
		$header = "
			<div class='diffview-nav'><a href='" . SITE_URL . "index.php/Pathway:{$pathway->getIdentifier()}'>←Go back to pathway page</a></div>
			<div class='pathvisiojs-diffviewer pathvisiojs-diffviewer-header'>
				<div class='pane pane-left'><div class='pane-inner'>{$pwName}, revision {$revOld}</div></div>
				<div class='pane pane-center'></div>
				<div class='pane pane-right'><div class='pane-inner'>{$pwName}, revision {$revNew}</div></div>
			</div>
			<div class='clearfix'></div>";

		$wgOut->addHTML($header);

		$pathway->setActiveRevision($revOld);
		$file1 = $pathway->getFileURL(FILETYPE_GPML);

		$pathway->setActiveRevision($revNew);
		$file2 = $pathway->getFileURL(FILETYPE_GPML);

		$wgOut->addHTML("<div id='pathvisiojs-container' data-pathway-old='$file1' data-pathway-new='$file2'></div>");
	}

	public function setupHeader() {
		global $wgOut, $wgScriptPath, $wgJsMimeType, $wgStyleVersion;
		$this->setHeaders();

		// $wgOut->setPageTitle( $this->msg( 'Pathway Difference Viewer' ) );
		$wgOut->setPageTitle( 'Pathway Difference Viewer' );

		// Add CSS
		$wgOut->addScript( "<link rel=\"stylesheet\" type=\"text/css\" href=\"$wgScriptPath/wpi/lib/pathvisiojs/css/pathvisiojs.bundle.css?$wgStyleVersion\" /> \n" );
		$wgOut->addScript( "<link rel=\"stylesheet\" type=\"text/css\" href=\"$wgScriptPath/wpi/extensions/DiffViewer/DiffViewer.css?$wgStyleVersion\" /> \n" );

		$scripts = array(
			"$wgScriptPath/wpi/js/querystring-parameters.js",
			// "$wgScriptPath/wpi/extensions/PathwayViewer/pathwayviewer.js",
			"$wgScriptPath/wpi/js/jquery/plugins/jquery.mousewheel.js",
			"$wgScriptPath/wpi/js/jquery/plugins/jquery.layout.min-1.3.0.js",
			// pvjs libs
			"//cdnjs.cloudflare.com/ajax/libs/async/0.7.0/async.js",
			"//cdnjs.cloudflare.com/ajax/libs/d3/3.4.6/d3.min.js",
			"//cdnjs.cloudflare.com/ajax/libs/lodash.js/2.4.1/lodash.min.js",
			"//cdnjs.cloudflare.com/ajax/libs/modernizr/2.7.1/modernizr.min.js",
			"//cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.2/typeahead.bundle.min.js",
			// pvjs
			"$wgScriptPath/wpi/lib/pathvisiojs/js/pathvisiojs.bundle.min.js",
			// extension
			"$wgScriptPath/wpi/extensions/DiffViewer/DiffViewer.js",
		);

		foreach ($scripts as $script) {
			$wgOut->addScript( "<script type=\"{$wgJsMimeType}\" src=\"$script?$wgStyleVersion\"></script>\n" );
		}
	}

}
