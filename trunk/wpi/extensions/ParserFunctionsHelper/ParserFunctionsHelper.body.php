<?php
/**
 * @author Jean-Lou Dupont
 * @package ParserFunctionsHelper
 * @version 1.0.0
 * @Id $Id$
*/
//<source lang=php>
class ParserFunctionsHelper
{
	const thisType = 'other';
	const thisName = 'ParserFunctionsHelper';

	var $liste = array();
	
	/**
	 * Replacement entry point
	 */
	public function hParserAfterTidy( &$parser, &$text )
	{
		$this->findAnchorsAndReplace( $text );
		return true;
	}
	/**
	 * Performs the actual replacements in the text.
	 */
	protected function findAnchorsAndReplace( &$text )
	{
		if (empty( $this->liste ))
			return null;

		foreach( $this->liste as $key => &$key_liste )
			foreach( $key_liste as $index => &$e )
				$text = str_replace( '__'.$key.'__'.$index.'__', $e, $text );

		return true;
	}
	/**
	 * Hook called by client extensions to register a replacement
	 */
	public function hParserFunctionsHelperSet( $key, &$value, &$index, &$anchor )
	{
		// make sure to return the correct index
		// to the caller.
		if (isset( $this->liste[$key] ))
			$index = count( $this->liste[$key] );
		else
			$index = 0;
		
		// insert in our list
		$this->liste[$key][] = $value;
		
		// return an anchor
		$anchor = '__'.$key.'__'.$index.'__';
		
		// play nice.
		return true;
	}
} // end class

//</source>
