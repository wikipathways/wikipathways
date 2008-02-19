<?php //{{MediaWikiExtension}}<source lang="php">
/*
 * EmbedVideo.php - Adds a parser function aembedding video from popular sources.
 * @author Jim R. Wilson
 * @version 0.1.2
 * @copyright Copyright (C) 2007 Jim R. Wilson
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 * -----------------------------------------------------------------------
 * Description:
 *     This is a MediaWiki extension which adds a parser function for embedding 
 *     video from popular sources (configurable).
 * Requirements:
 *     MediaWiki 1.6.x, 1.9.x, 1.10.x or higher
 *     PHP 4.x, 5.x or higher.
 * Installation:
 *     1. Drop this script (EmbedVideo.php) in $IP/extensions
 *         Note: $IP is your MediaWiki install dir.
 *     2. Enable the extension by adding this line to your LocalSettings.php:
 *         require_once('extensions/EmbedVideo.php');
 * Version Notes:
 *     version 0.1.2:
 *         Bug fix - now can be inserted in lists without breakage (from newlines)
 *         Code cleanup - would previously give 'Notice' messages in PHP strict.
 *     version 0.1.1:
 *         Code cleanup - no functional difference.
 *     version 0.1:
 *         Initial release.
 * -----------------------------------------------------------------------
 * Copyright (c) 2007 Jim R. Wilson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal 
 * in the Software without restriction, including without limitation the rights to 
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of 
 * the Software, and to permit persons to whom the Software is furnished to do 
 * so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all 
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES 
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR 
 * OTHER DEALINGS IN THE SOFTWARE. 
 * -----------------------------------------------------------------------
 */
 
# Confirm MW environment
if (defined('MEDIAWIKI')) {

# Credits
$wgExtensionCredits['parserhook'][] = array(
    'name'=>'EmbedVideo',
    'author'=>'Jim R. Wilson - wilson.jim.r&lt;at&gt;gmail.com',
    'url'=>'http://jimbojw.com/wiki/index.php?title=EmbedVideo',
    'description'=>'Adds a parser function aembedding video from popular sources.',
    'version'=>'0.1.2'
);

/**
 * Wrapper class for encapsulating EmbedVideo related parser methods
 */
class EmbedVideo {

    /**
     * Sets up parser functions.
     */
    function setup( ) {
    
        # Setup parser hook
    	global $wgParser, $wgVersion;
    	$hook = (version_compare($wgVersion, '1.7', '<')?'#ev':'ev');
    	$wgParser->setFunctionHook( $hook, array($this, 'parserFunction') );
    	
    	# Add system messages
    	global $wgMessageCache;
        $wgMessageCache->addMessage('embedvideo-missing-params', 'EmbedVideo is missing a required parameter.');
        $wgMessageCache->addMessage('embedvideo-bad-params', 'EmbedVideo received a bad parameter.');
        $wgMessageCache->addMessage('embedvideo-unparsable-param-string', 'EmbedVideo received the unparsable parameter string "<tt>$1</tt>".');
        $wgMessageCache->addMessage('embedvideo-unrecognized-service', 'EmbedVideo does not recognize the video service "<tt>$1</tt>".');
        $wgMessageCache->addMessage('embedvideo-bad-id', 'EmbedVideo received the bad id "$1" for the service "$2".');
        $wgMessageCache->addMessage('embedvideo-illegal-width', 'EmbedVideo received the illegal width parameter "$1".');
        $wgMessageCache->addMessage('embedvideo-embed-clause', 
            '<object width="$2" height="$3">'.
            '<param name="movie" value="$1"></param>'.
            '<param name="wmode" value="transparent"></param>'.
            '<embed src="$1" type="application/x-shockwave-flash" '.
            'wmode="transparent" width="$2" height="$3">'.
            '</embed></object>'
        );
    }
    
    /**
     * Adds magic words for parser functions.
     * @param Array $magicWords
     * @param $langCode
     * @return Boolean Always true
     */
    function parserFunctionMagic( &$magicWords, $langCode='en' ) {
        $magicWords['ev'] = array( 0, 'ev' );
        return true;
    }
    
    /**
     * Embeds video of the chosen service
     * @param Parser $parser Instance of running Parser.
     * @param String $service Which online service has the video.
     * @param String $id Identifier of the chosen service
     * @param String $width Width of video (optional)
     * @return String Encoded representation of input params (to be processed later)
     */
    function parserFunction( $parser, $service=null, $id=null, $width=null ) {
        if ($service===null || $id===null) return '<div class="errorbox">'.wfMsg('embedvideo-missing-params').'</div>';

        $params = array(
            'service' => trim($service),
            'id' => trim($id),
            'width' => ($width===null?null:trim($width)),
        );

        global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;
        if (!is_numeric($wgEmbedVideoMinWidth) || $wgEmbedVideoMinWidth<100) $wgEmbedVideoMinWidth = 100;
        if (!is_numeric($wgEmbedVideoMaxWidth) || $wgEmbedVideoMaxWidth>1024) $wgEmbedVideoMaxWidth = 1024;
        
        global $wgEmbedVideoServiceList;
        $service = $wgEmbedVideoServiceList[$params['service']];
        if (!$service) return '<div class="errorbox">'.wfMsg('embedvideo-unrecognized-service', @htmlspecialchars($params['service'])).'</div>';
        
        $id = htmlspecialchars($params['id']);
        $idpattern = ( isset($service['id_pattern']) ? $service['id_pattern'] : '%[^A-Za-z0-9_\\-]%' );
        if ($id==null || preg_match($idpattern,$id)) {
            return '<div class="errorbox">'.wfMsgForContent('embedvideo-bad-id', $id, @htmlspecialchars($params['service'])).'</div>';
        }
     
        # Build URL and output embedded flash object
        $ratio = 425 / 350;
        $width = 425;
        
        if ($params['width']!==null) {
            if (
                !is_numeric($params['width']) || 
                $params['width'] < $wgEmbedVideoMinWidth ||
                $params['width'] > $wgEmbedVideoMaxWidth
            ) return 
                '<div class="errorbox">'.
                wfMsgForContent('embedvideo-illegal-width', @htmlspecialchars($params['width'])).
                '</div>';
            $width = $params['width'];
        }
        $height = round($width / $ratio);
        $url = wfMsgReplaceArgs($service['url'], array($id, $width, $height));
        
        return $parser->insertStripItem(
            wfMsgForContent('embedvideo-embed-clause', $url, $width, $height),
            $parser->mStripState
        );
    }

}

/**
 * Wrapper function for language magic call (hack for 1.6 
 */

# Create global instance and wire it up!
$wgEmbedVideo = new EmbedVideo();
$wgExtensionFunctions[] = array($wgEmbedVideo, 'setup');
if (version_compare($wgVersion, '1.7', '<')) {
    # Hack solution to resolve 1.6 array parameter nullification for hook args
    function wfEmbedVideoLanguageGetMagic( &$magicWords ) {
        global $wgEmbedVideo;
        $wgEmbedVideo->parserFunctionMagic( $magicWords );
        return true;
    }
    $wgHooks['LanguageGetMagic'][] = 'wfEmbedVideoLanguageGetMagic';
} else {
    $wgHooks['LanguageGetMagic'][] = array($wgEmbedVideo, 'parserFunctionMagic');
}

# Build services list (may be augmented in LocalSettings.php)
$wgEmbedVideoServiceList = array(
    'dailymotion' => array(
        'url' => 'http://www.dailymotion.com/swf/$1'
    ),
    'funnyordie' => array(
        'url' => 
            'http://www.funnyordie.com/v1/flvideo/fodplayer.swf?file='.
            'http://funnyordie.vo.llnwd.net/o16/$1.flv&autoStart=false'
    ),
    'googlevideo' => array(
        'id_pattern'=>'%[^0-9\\-]%',
        'url' => 'http://video.google.com/googleplayer.swf?docId=$1'
    ),
    'sevenload' => array(
        'url' => 'http://page.sevenload.com/swf/en_GB/player.swf?id=$1'
    ),
    'revver' => array(
        'url' => 'http://flash.revver.com/player/1.0/player.swf?mediaId=$1'
    ),
    'youtube' => array(
        'url'=>'http://www.youtube.com/v/$1'
    )
);

} # End MW Environment wrapper
//</source>