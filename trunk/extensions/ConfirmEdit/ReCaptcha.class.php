<?php

/* ported all the non-html5 stuff */
class Html {
	# List of void elements from HTML5, section 8.1.2 as of 2011-08-12
	private static $voidElements = array(
		'area',
		'base',
		'br',
		'col',
		'command',
		'embed',
		'hr',
		'img',
		'input',
		'keygen',
		'link',
		'meta',
		'param',
		'source',
		'track',
		'wbr',
	);

	# Boolean attributes, which may have the value omitted entirely.  Manually
	# collected from the HTML5 spec as of 2011-08-12.
	private static $boolAttribs = array(
		'async',
		'autofocus',
		'autoplay',
		'checked',
		'controls',
		'default',
		'defer',
		'disabled',
		'formnovalidate',
		'hidden',
		'ismap',
		'itemscope',
		'loop',
		'multiple',
		'muted',
		'novalidate',
		'open',
		'pubdate',
		'readonly',
		'required',
		'reversed',
		'scoped',
		'seamless',
		'selected',
		'truespeed',
		'typemustmatch',
		# HTML5 Microdata
		'itemscope',
	);

	/**
	 * Returns "</$element>", except if $wgWellFormedXml is off, in which case
	 * it returns the empty string when that's guaranteed to be safe.
	 *
	 * @since 1.17
	 * @param $element string Name of the element, e.g., 'a'
	 * @return string A closing tag, if required
	 */
	public static function closeElement( $element ) {
		$element = strtolower( $element );

		# Reference:
		# http://www.whatwg.org/specs/web-apps/current-work/multipage/syntax.html#optional-tags
		if ( in_array( $element, array(
			'html',
			'head',
			'body',
			'li',
			'dt',
			'dd',
			'tr',
			'td',
			'th',
		) ) ) {
			return '';
		}
		return "</$element>";
	}

	public static function expandAttributes( $attribs ) {
		$ret = '';
		$attribs = (array)$attribs;
		foreach ( $attribs as $key => $value ) {
			if ( $value === false || is_null( $value ) ) {
				continue;
			}

			# For boolean attributes, support array( 'foo' ) instead of
			# requiring array( 'foo' => 'meaningless' ).
			if ( is_int( $key )
			&& in_array( strtolower( $value ), self::$boolAttribs ) ) {
				$key = $value;
			}

			# Not technically required in HTML5, but required in XHTML 1.0,
			# and we'd like consistency and better compression anyway.
			$key = strtolower( $key );

			# Bug 23769: Blacklist all form validation attributes for now.  Current
			# (June 2010) WebKit has no UI, so the form just refuses to submit
			# without telling the user why, which is much worse than failing
			# server-side validation.  Opera is the only other implementation at
			# this time, and has ugly UI, so just kill the feature entirely until
			# we have at least one good implementation.
			if ( in_array( $key, array( 'max', 'min', 'pattern', 'required', 'step' ) ) ) {
				continue;
			}

			// http://www.w3.org/TR/html401/index/attributes.html ("space-separated")
			// http://www.w3.org/TR/html5/index.html#attributes-1 ("space-separated")
			$spaceSeparatedListAttributes = array(
				'class', // html4, html5
				'accesskey', // as of html5, multiple space-separated values allowed
				// html4-spec doesn't document rel= as space-separated
				// but has been used like that and is now documented as such
				// in the html5-spec.
				'rel',
			);

			# Specific features for attributes that allow a list of space-separated values
			if ( in_array( $key, $spaceSeparatedListAttributes ) ) {
				// Apply some normalization and remove duplicates

				// Convert into correct array. Array can contain space-seperated
				// values. Implode/explode to get those into the main array as well.
				if ( is_array( $value ) ) {
					// If input wasn't an array, we can skip this step

					$newValue = array();
					foreach ( $value as $k => $v ) {
						if ( is_string( $v ) ) {
							// String values should be normal `array( 'foo' )`
							// Just append them
							if ( !isset( $value[$v] ) ) {
								// As a special case don't set 'foo' if a
								// separate 'foo' => true/false exists in the array
								// keys should be authoritive
								$newValue[] = $v;
							}
						} elseif ( $v ) {
							// If the value is truthy but not a string this is likely
							// an array( 'foo' => true ), falsy values don't add strings
							$newValue[] = $k;
						}
					}
					$value = implode( ' ', $newValue );
				}
				$value = explode( ' ', $value );

				// Normalize spacing by fixing up cases where people used
				// more than 1 space and/or a trailing/leading space
				$value = array_diff( $value, array( '', ' ' ) );

				// Remove duplicates and create the string
				$value = implode( ' ', array_unique( $value ) );
			}

			# See the "Attributes" section in the HTML syntax part of HTML5,
			# 9.1.2.3 as of 2009-08-10.  Most attributes can have quotation
			# marks omitted, but not all.  (Although a literal " is not
			# permitted, we don't check for that, since it will be escaped
			# anyway.)
			#
			# See also research done on further characters that need to be
			# escaped: http://code.google.com/p/html5lib/issues/detail?id=93
			$badChars = "\\x00- '=<>`/\x{00a0}\x{1680}\x{180e}\x{180F}\x{2000}\x{2001}"
				. "\x{2002}\x{2003}\x{2004}\x{2005}\x{2006}\x{2007}\x{2008}\x{2009}"
				. "\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}";
			if ( $wgWellFormedXml || $value === ''
			|| preg_match( "![$badChars]!u", $value ) ) {
				$quote = '"';
			} else {
				$quote = '';
			}

			if ( in_array( $key, self::$boolAttribs ) ) {
				# In XHTML 1.0 Transitional, the value needs to be equal to the
				# key.  In HTML5, we can leave the value empty instead.  If we
				# don't need well-formed XML, we can omit the = entirely.
				if ( !$wgWellFormedXml ) {
					$ret .= " $key";
				} elseif ( $wgHtml5 ) {
					$ret .= " $key=\"\"";
				} else {
					$ret .= " $key=\"$key\"";
				}
			} else {
				# Apparently we need to entity-encode \n, \r, \t, although the
				# spec doesn't mention that.  Since we're doing strtr() anyway,
				# and we don't need <> escaped here, we may as well not call
				# htmlspecialchars().
				# @todo FIXME: Verify that we actually need to
				# escape \n\r\t here, and explain why, exactly.
				#
				# We could call Sanitizer::encodeAttribute() for this, but we
				# don't because we're stubborn and like our marginal savings on
				# byte size from not having to encode unnecessary quotes.
				$map = array(
					'&' => '&amp;',
					'"' => '&quot;',
					"\n" => '&#10;',
					"\r" => '&#13;',
					"\t" => '&#9;'
				);
				if ( $wgWellFormedXml ) {
					# This is allowed per spec: <http://www.w3.org/TR/xml/#NT-AttValue>
					# But reportedly it breaks some XML tools?
					# @todo FIXME: Is this really true?
					$map['<'] = '&lt;';
				}

				$ret .= " $key=$quote" . strtr( $value, $map ) . $quote;
			}
		}
		return $ret;
	}

	public static function openElement( $element, $attribs = array() ) {
		$attribs = (array)$attribs;
		# This is not required in HTML5, but let's do it anyway, for
		# consistency and better compression.
		$element = strtolower( $element );

		# In text/html, initial <html> and <head> tags can be omitted under
		# pretty much any sane circumstances, if they have no attributes.  See:
		# <http://www.whatwg.org/specs/web-apps/current-work/multipage/syntax.html#optional-tags>
		if ( !$attribs && in_array( $element, array( 'html', 'head' ) ) ) {
			return '';
		}

		# Remove HTML5-only attributes if we aren't doing HTML5, and disable
		# form validation regardless (see bug 23769 and the more detailed
		# comment in expandAttributes())
		if ( $element == 'input' ) {
			# Whitelist of types that don't cause validation.  All except
			# 'search' are valid in XHTML1.
			$validTypes = array(
				'hidden',
				'text',
				'password',
				'checkbox',
				'radio',
				'file',
				'submit',
				'image',
				'reset',
				'button',
				'search',
			);

			if ( isset( $attribs['type'] )
			&& !in_array( $attribs['type'], $validTypes ) ) {
				unset( $attribs['type'] );
			}

			if ( isset( $attribs['type'] ) && $attribs['type'] == 'search'
			&& !$wgHtml5 ) {
				unset( $attribs['type'] );
			}
		}

		if ( $element == 'textarea' && isset( $attribs['maxlength'] ) ) {
			unset( $attribs['maxlength'] );
		}

		return "<$element" . self::expandAttributes( $attribs ) . '>';
	}

	public static function rawElement( $element, $attribs = array(), $contents = '' ) {
		global $wgWellFormedXml;
		$start = self::openElement( $element, $attribs );
		if ( in_array( $element, self::$voidElements ) ) {
			if ( $wgWellFormedXml ) {
				# Silly XML.
				return substr( $start, 0, -1 ) . ' />';
			}
			return $start;
		} else {
			return "$start$contents" . self::closeElement( $element );
		}
	}
	public static function inlineScript( $contents ) {
		$attrs = array();

                $attrs['type'] = "text/javascript";

		return self::rawElement( 'script', $attrs, $contents );
	}
}


class ReCaptcha extends SimpleCaptcha {
	// reCAPTHCA error code returned from recaptcha_check_answer
	private $recaptcha_error = null;

	/**
	 * Displays the reCAPTCHA widget.
	 * If $this->recaptcha_error is set, it will display an error in the widget.
	 *
	 */
	function getForm() {
		global $wgReCaptchaPublicKey, $wgReCaptchaTheme;

		$useHttps = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' );
		$js = 'var RecaptchaOptions = ' . Xml::encodeJsVar( array( 'theme' => $wgReCaptchaTheme, 'tabindex' => 1  ) );

		return Html::inlineScript( $js ) . recaptcha_get_html( $wgReCaptchaPublicKey, $this->recaptcha_error, $useHttps );
	}

	/**
	 * Calls the library function recaptcha_check_answer to verify the users input.
	 * Sets $this->recaptcha_error if the user is incorrect.
	 * @return boolean
	 *
	 */
	function passCaptcha() {
		global $wgReCaptchaPrivateKey, $wgRequest;

		// API is hardwired to return wpCaptchaId and wpCaptchaWord, so use that if the standard two are empty
		$challenge = $wgRequest->getVal( 'recaptcha_challenge_field', $wgRequest->getVal( 'wpCaptchaId' ) );
		$response = $wgRequest->getVal( 'recaptcha_response_field', $wgRequest->getVal( 'wpCaptchaWord' ) );

		if ( $response === null ) {
			// new captcha session
			return false;
		}

		// Compat: WebRequest::getIP is only available since MW 1.19.
		$ip = method_exists( $wgRequest, 'getIP' ) ? $wgRequest->getIP() : wfGetIP();

		$recaptcha_response = recaptcha_check_answer(
			$wgReCaptchaPrivateKey,
			$ip,
			$challenge,
			$response
		);

		if ( !$recaptcha_response->is_valid ) {
			$this->recaptcha_error = $recaptcha_response->error;
			return false;
		}

		$recaptcha_error = null;

		return true;

	}

	function addCaptchaAPI( &$resultArr ) {
		global $wgReCaptchaPublicKey;

		$resultArr['captcha']['type'] = 'recaptcha';
		$resultArr['captcha']['mime'] = 'image/png';
		$resultArr['captcha']['key'] = $wgReCaptchaPublicKey;
		$resultArr['captcha']['error'] = $this->recaptcha_error;
	}

	/**
	 * Show a message asking the user to enter a captcha on edit
	 * The result will be treated as wiki text
	 *
	 * @param $action Action being performed
	 * @return string
	 */
	function getMessage( $action ) {
		$name = 'recaptcha-' . $action;
		$text = wfMsg( $name );

		# Obtain a more tailored message, if possible, otherwise, fall back to
		# the default for edits
		return wfEmptyMsg( $name, $text ) ? wfMsg( 'recaptcha-edit' ) : $text;
	}

	public function APIGetAllowedParams( &$module, &$params ) {
		return true;
	}

	public function APIGetParamDescription( &$module, &$desc ) {
		return true;
	}
}
