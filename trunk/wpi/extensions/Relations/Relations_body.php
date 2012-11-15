<?php
require_once("wpi/wpi.php");

class Relations extends SpecialPage
{		
	function __construct() {
		parent::__construct( 'Relations' );
		wfLoadExtensionMessages('Relations');
	}

	function execute( $par ) {
            
		global $wgRequest, $wgOut;
                $pathToApp = WPI_URL . "/extensions/Relations/app/index.html";

		$this->setHeaders();

                $wgOut->setPagetitle("Relations Visualization");

		# Get request data from, e.g.
		$param = $wgRequest->getText('param');

                $wgOut->addHTML("
                        <div>Relations Visualization is a simple Flex application which shows relationships between Pathways.
                        It is intended to be both a useful means of graphically exploring the large database, and also find relationships
                        between various pathways on the basis of multiple parameters. It gives an idea about how pathways relate and may help to
                        find a pathway of interest.
                        <br /><br />
                        It lets you visually research relationships and quickly find out which biological processes (pathways) are related to each other with respect to gene products / metabolites / continuity and to what extent.
                        They can also find out pathways of their field of interest by starting from one pathway and gradually exploring the pathways mapped to that!
                        <br /><br />
                        Right now, we can determine relationship on the basis of the Xrefs and the text labels contained in the pathways. This application is built using the Flex framework and the Flare Visualization library. The backend comprises of several
                        scripts (Php/Java) and uses MySQL/Derby for caching the scores and the data. Documentation for the same can be found <a href='http://socrates2.cgl.ucsf.edu/GenMAPP/wiki/Google_Summer_of_Code_2010/Chetan'>here</a>.

                        </div>
                        <h2>Launch</h2>
                        Click <a href='$pathToApp' target='_blank'>here</a> to launch the application.
                        <h2>Instructions</h2>
                         <ul>
                            <li>Launch the application using the link above.</li>
                            <li>Select a species from the drop down to fetch the data for the respective species.</li>
                            <li>Move around the screen by click-dragging the left mouse button.</li>
                            <li>Zoom in and out using the mouse wheel.</li>
                            <li>Set a minimum score, using the slider, for the relationships.</li>
                            <li>Switch layout by selecting a layout from the drop down list.</li>
                            <li>Open the pathway by double clicking the node.</li>
                         </ul>

                        ");

	}
}

?>
