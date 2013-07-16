<?php

class PagePuller {
	public $wiki;
	public $page;
	public $user = "USERNAME";
	public $pass = "PASSWORD";
	protected $pageList;

	public function __construct( $wiki, $page ) {
		$this->wiki = $wiki;
		$this->page = self::trimBrackets( $page );
	}

	static private function trimBrackets( $page ) {
		return trim( $page, "[* ]" ); /* People like to use []
									   * around wiki pages */
	}

	public function pullPage( $pageName ) {
		/* Need a better method for this.  Later. */
		$url = $this->wiki . "?title=" . urlencode($pageName) . "&action=raw";
		return file_get_contents( $url );
	}

	public function getPageList() {
		if( $this->pageList ) {
			return $this->pageList;
		}

		$content = $this->pullPage( $this->page );
		if( $content !== false ) {
			$this->pageList = array_map( array( __CLASS__, "trimBrackets" ),
				explode( "\n", $content ) );
			$this->pageList[] = $this->page;
			return $this->pageList;
		}
		return false;
	}

	public function getPage( $page, $callback ) {
		$content = $this->pullPage( $page );

		if( $content !== false ) {
			# If it is an Image: or Media: page, fetch the file.
			$isImage = strstr( $page, "Image:" );
			$isMedia = strstr( $page, "Media:" );
			if( $isImage === $page || $isMedia === $page ) { /* Will only match if the name is Image:... */
				$msg = $this->pullFile( $page, $content );
			} else {
				# Put the content in the page
				if( $this->savePage( $page, $content ) ) {
					$msg = "success";
				} else {
					$msg = "error (see error log)";
				}
			}
		} else {
			$err = error_get_last();
			$msg = "Error fetching page: {$err['message']}";
		}
		call_user_func( $callback, "$page ... $msg" );
		return true;
	}

	public function setupContext( $method, $cookies = null) {
		$opts['http']['method'] = $method;

		if( $cookies ) {
			$opts['http']['headers'] = "Cookie: $cookies\r\n";
		}
		return stream_context_create( $opts );
	}

	public function loginForCookies( $apiUrl, $username, $password ) {
		/* The password and username should really go in the body of the request */
		$url = $apiUrl . "?format=json&action=login&lgname={$username}&lgpassword={$password}";

		$context = $this->setupContext( "POST" );
		$data = file_get_contents( $url, false, $context );
		if($data !== false ) {
			$resp = json_decode( $data );
			if( $resp->login->result === "Success" ) {
				$pref = $resp->login->cookieprefix;
				$uid  = $resp->login->lguserid;
				$sess = $resp->login->sessionid;
				return "{$pref}UserId=$uid; {$pref}UserName=$username; {$pref}_session=$sess";
			}
		}
		return null;
	}

	public function pullFile( $page, $contents ) {
		$repo = RepoGroup::singleton()->getLocalRepo();
		$title = Title::newFromText( $page );
		$img = new LocalFile( $title, $repo);

		wfDebug( "pull image {$img->getRel()} from {$this->wiki}" );

		/* need a better way to do this, but this is MW 1.13 */
		$cookies = null;
		if( $this->user ) {
			$rep = "img_auth.php/";
			$apiUrl = str_replace( "index.php", "api.php", $this->wiki );
		    $cookies = $this->loginForCookies( $apiUrl, $this->user, $this->pass );
		} else {
			$rep = "";
		}
		$root = str_replace( "index.php", $rep, $this->wiki );

		$this->setupContext( "GET", $cookies );
		$url = $root . $img->getRel();
		wfSuppressWarnings();
		$data = file_get_contents( $url );
		wfSuppressWarnings( true );
		if($data === false ) {
			$err = error_get_last();
			return "Error fetching file ($url): {$err['message']}";
		}

		$tmpfile = tempnam( wfTempDir(), "XXX" );
		if( $tmpfile === false ) {
			$err = error_get_last();
			return "Error creating tmp file: {$err['message']}";
		}

		if( file_put_contents( $tmpfile, $data ) === false ) {
			$err = error_get_last();
			return "Error saving tmp file: {$err['message']}";
		}

		$status = $img->upload( $tmpfile, "[[Special:PullPages|PullPages]] upload", $contents );
		if( !$status->isGood() ) {
			return "Errors during upload: " . $status->getWikiTextArray( $status->getErrorsArray );
		}
		return "success";
	}

	public function savePage( $page, $content ) {
		global $wgRequest, $wgUser;

		/* Skimmed from ApiEditPage -- MAH20130715 */
		$title = Title::newFromText( $page );
		$article = new Article($title);

		$reqArr['wpTextbox1'] = $content;
		if( $article->exists() ) {
			$reqArr['wpEdittime'] = $article->getTimestamp(); /* If we don't do this for existing articles, we get a conflict */
			$reqArr['wpSection'] = '0';
		} else {
			$reqArr['wpEdittime'] = wfTimestampNow();
			$reqArr['wpSection'] = 'new';
		}
		$reqArr['wpSummary'] = "[[Special:PullPages|PullPages]] import";
		$req = new FauxRequest($reqArr, true);
		$ep = new EditPage( $article );
		$ep->importFormData($req);

		# Do the actual save
		$oldRevId = $article->getRevIdFetched();
		$result = null;

		# Fake $wgRequest for some hooks inside EditPage
		# FIXME: This interface SUCKS
		$oldRequest = $wgRequest;
		$wgRequest = $req;
		$retval = $ep->internalAttemptSave($result);
		$wgRequest = $oldRequest;
		switch($retval)
		{
			case EditPage::AS_HOOK_ERROR:
			case EditPage::AS_HOOK_ERROR_EXPECTED:
				error_log(var_export(array($page, 'hookaborted'), true)); break;
			case EditPage::AS_IMAGE_REDIRECT_ANON:
				error_log(var_export(array($page, 'noimageredirect-anon'), true)); break;
			case EditPage::AS_IMAGE_REDIRECT_LOGGED:
				error_log(var_export(array($page, 'noimageredirect-logged'), true)); break;
			case EditPage::AS_SPAM_ERROR:
				error_log(var_export(array($page, 'spamdetected', $result['spam']), true)); break;
			case EditPage::AS_FILTERING:
				error_log(var_export(array($page, 'filtered'), true)); break;
			case EditPage::AS_BLOCKED_PAGE_FOR_USER:
				error_log(var_export(array($page, 'blockedtext'), true)); break;
			case EditPage::AS_MAX_ARTICLE_SIZE_EXCEEDED:
			case EditPage::AS_CONTENT_TOO_BIG:
				global $wgMaxArticleSize;
				error_log(var_export(array($page, 'contenttoobig', $wgMaxArticleSize), true)); break;
			case EditPage::AS_READ_ONLY_PAGE_ANON:
				error_log(var_export(array($page, 'noedit-anon'), true)); break;
			case EditPage::AS_READ_ONLY_PAGE_LOGGED:
				error_log(var_export(array($page, 'noedit'), true)); break;
			case EditPage::AS_READ_ONLY_PAGE:
				error_log(var_export(array($page, 'readonlytext'), true)); break;
			case EditPage::AS_RATE_LIMITED:
				error_log(var_export(array($page, 'actionthrottledtext'), true)); break;
			case EditPage::AS_ARTICLE_WAS_DELETED:
				error_log(var_export(array($page, 'wasdeleted'), true)); break;
			case EditPage::AS_NO_CREATE_PERMISSION:
				error_log(var_export(array($page, 'nocreate-loggedin'), true)); break;
			case EditPage::AS_BLANK_ARTICLE:
				error_log(var_export(array($page, 'blankpage'), true)); break;
			case EditPage::AS_CONFLICT_DETECTED:
				error_log(var_export(array($page, 'editconflict'), true)); break;
			#case EditPage::AS_SUMMARY_NEEDED: Can't happen since we set wpIgnoreBlankSummary
			#case EditPage::AS_TEXTBOX_EMPTY: Can't happen since we don't do sections
			case EditPage::AS_END:
				# This usually means some kind of race condition
				# or DB weirdness occurred. Throw an unknown error here.
				error_log(var_export(array($page, 'unknownerror', 'AS_END'), true)); break;
			case EditPage::AS_SUCCESS_NEW_ARTICLE:
			case EditPage::AS_SUCCESS_UPDATE:
				break;
			default:
				error_log(var_export(array($page, 'unknownerror', $retval), true)); break;
		}
		global $wgOut;
		if( $wgOut->mRedirect !== null ) { /* Otherwise we end up on the last page successfully imported */
			$wgOut->mRedirect = null;
			return true;
		}
		return false;
	}

}
