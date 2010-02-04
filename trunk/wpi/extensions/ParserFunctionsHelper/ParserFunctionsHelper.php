<?php
/**
 * @author Jean-Lou Dupont
 * @package ParserFunctionsHelper
 * @version 1.0.0
 * @Id $Id$
*/
//<source lang=php>
if ( class_exists('StubManager') )
{
	$wgExtensionCredits['other'][] = array( 
		'name'    	=> 'ParserFunctionsHelper',
		'version' 	=> '1.0.0',
		'author'  	=> 'Jean-Lou Dupont',
		'description' => "Provides services to parser function extensions which require inserting content after the parsing phase.", 
		'url' 		=> 'http://mediawiki.org/wiki/Extension:ParserFunctionsHelper',	
	);
	StubManager::createStub2(	array(	'class' 		=> 'ParserFunctionsHelper', 
										'classfilename'	=> dirname(__FILE__).'/ParserFunctionsHelper.body.php',
										'hooks'			=> array( 'ParserAfterTidy', 
																'ParserFunctionsHelperSet' ),
									)
							);
}
else
	echo 'Extension:ParserFunctionsHelper requires Extension:StubManager';					
//</source>