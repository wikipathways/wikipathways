<?php

/**
 * QuestyCaptcha class
 *
 * @file
 * @author Benjamin Lees <emufarmers@gmail.com>
 * @ingroup Extensions
 */

class QuestyCaptcha extends SimpleCaptcha {

	/** Validate a captcha response */
	function keyMatch( $answer, $info ) {
		if ( is_array( $info['answer'] ) ) {
			return in_array( strtolower( $answer ), $info['answer'] );
		} else {
			return strtolower( $answer ) == strtolower( $info['answer'] );
		}
	}

	function addCaptchaAPI( &$resultArr ) {
		$captcha = $this->getCaptcha();
		$index = $this->storeCaptcha( $captcha );
		$resultArr['captcha']['type'] = 'question';
		$resultArr['captcha']['mime'] = 'text/plain';
		$resultArr['captcha']['id'] = $index;
		$resultArr['captcha']['question'] = $captcha['question'];
	}

	function getCaptcha() {
		global $wgCaptchaQuestions;
		if( !isset( $wgCaptchaQuestions ) || count( $wgCaptchaQuestions ) === 0 ) {
			$all = $this->getMessage( 'q&a' );
			$qna = split( "\n=== Q&A ===\n", $all, 2 );
			$count = 0;

			if( !isset( $qna[1] ) ) {
				die( $this->getMessage( 'no-q&a' ) );
			}
			foreach(split( "\n", $qna[1] ) as $l) {
				if( strtolower( substr($l, 0, 2) ) == "q:" ) {
					$wgCaptchaQuestions[$count]["question"] = trim( substr( $l, 2 ) );
				}

				if( strtolower( substr($l, 0, 2) ) == "a:" ) {
					$wgCaptchaQuestions[$count]["answer"] = trim( substr( $l, 2 ) );
				}
				if( isset( $wgCaptchaQuestions[$count]["answer"] ) &&
					isset( $wgCaptchaQuestions[$count]["question"] ) ) {
					global $wgParser;
					$wgCaptchaQuestions[$count]["question"] = $wgParser->recursiveTagParse
						( $wgCaptchaQuestions[$count]["question"] );
					$count++;
				}
			}
			if( $count < 1 ) {
				die( $this->getMessage( 'no-q&a-list' ) );
			}
		}
		return $wgCaptchaQuestions[mt_rand( 0, count( $wgCaptchaQuestions ) - 1 )]; // pick a question, any question
	}

	function getForm() {
		$captcha = $this->getCaptcha();
		if ( !$captcha ) {
			die( $this->getMessage( 'noquesty' ) );
		}
		$index = $this->storeCaptcha( $captcha );
		return "<p><label for=\"wpCaptchaWord\">{$captcha['question']}</label> " .
			'<input type="text" name="wpCaptchaWord" id="wpCaptchaWord" required="1">'.
			'<input type="hidden" name="wpCaptchaId" id="wpCaptchaId" value="'.$index.'">';
	}

	function getMessage( $action ) {
		$name = 'questycaptcha-' . $action;
		$text = wfMsg( $name );
		# Obtain a more tailored message, if possible, otherwise, fall back to
		# the default for edits
		return $text == "&lt;$name&gt;" ? wfMsg( 'questycaptcha-edit' ) : $text;
	}

	function showHelp() {
		global $wgOut;
		$wgOut->setPageTitle( wfMsg( 'captchahelp-title' ) );
		$wgOut->addWikiMsg( 'questycaptchahelp-text' );
	}
}
