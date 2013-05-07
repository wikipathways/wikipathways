<?php
class DeletePathway extends SpecialPage {
	function DeletePathway() {
		SpecialPage::SpecialPage("DeletePathway");
		self::loadMessages();
	}

	function execute($par) {
		global $wgOut, $wgUser, $wgLang;
		$this->setHeaders();

		$id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : null;

		if( $id === null ) {
			$wgOut->addHTML("Must supply an id!");
			return;
		}

		try {
			$pathway = new Pathway($id);
		} catch(Exception $e) {
			$wgOut->addHTML("Error: unable to find pathway $id");
			return;
		}

		$reason = isset( $_REQUEST['reason'] ) ? $_REQUEST['reason'] : null;

		if( $reason === null ) {
			$wgOut->addHTML("No reason given!");
			return;
		}

		$doit = isset( $_REQUEST['doit'] ) ? $_REQUEST['doit'] : null;

		if($doit) {
			$pathway->delete($reason);
			header("Location: {$pathway->getTitleObject()->getFullUrl()}");
			exit;
		} else {
			//Show a form
			$descr = wfMsgForContent( 'deletepathway_descr' );
			$descr = str_replace("[[PATHWAY]]" ,
				"<B><A href='{$pathway->getTitleObject()->getFullURL()}'>" .
				"{$pathway->getName()} ({$pathway->getSpecies()})</A></B>",
				$descr
			);
			$wgOut->addHTML("<P>" . $descr . "</P>");
			$url = SITE_URL . '/index.php';

			$form = <<<HTML
<FORM action="$url" method="get">
	<TABLE><TBODY><TR>
	<TD>Reason:
	<TD><INPUT name="reason" type="text">$reason</INPUT>
	<INPUT type="hidden" name="id" value="{$id}"/>
	<INPUT type="hidden" name="title" value="Special:DeletePathway"/>
	<TD><INPUT name="doit" type="submit" value="Delete"/>
	</TBODY></TABLE>
</FORM>
HTML;
			$wgOut->addHTML($form);
		}
	}

	static function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ( $messagesLoaded ) return true;
		$messagesLoaded = true;

		require( dirname( __FILE__ ) . '/DeletePathway.i18n.php' );
		foreach ( $allMessages as $lang => $langMessages ) {
			$wgMessageCache->addMessages( $langMessages, $lang );
		}
		return true;
	}
}
