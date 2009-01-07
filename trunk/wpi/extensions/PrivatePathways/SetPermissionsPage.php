<?php
class SetPermissionsPage {
	private $permissions;
	private $manager;
	private $title;
	private $warnings = array();
	
	function __construct($article) {
		self::loadMessages();
		
		global $wgOut, $wgUser, $wgLang;
		
		$this->title = $article->getTitle();
		$this->manager = new PermissionManager($this->title->getArticleId());
		$this->permissions = $this->manager->getPermissions();
	}
	
	function execute() {
		global $wgUser;
		
		//Check if user is allowed to manage permissions
		if(!$this->title->userCan(PermissionManager::$ACTION_MANAGE)) {
			$h = wfMsg('pp_forbidden_title');
			$p = wfMsg('pp_forbidden_user');
			$wgOut->addHTML("<h1>$t</h1><p>$p</p>");
			return;
		}
		
		if($_REQUEST['apply']) {
			$newPerm = new PagePermissions($this->title->getArticleId());
			
			$vis = $_REQUEST['visibility'];
			if($vis == 'private') {
				$users = $_REQUEST['users'];
				$users = $users ? explode("\n", $users) : array();
				foreach($users as $un) {
					$u = User::newFromName(trim($un));
					if(!$u || $u->isAnon()) {
						$this->warn("Invalid user: " . $un);
						continue; //Skip invalid users
					}
					$id = $u->getId();
					$newPerm->addReadWrite($id);
					$newPerm->addManage($id);
				}
				//Prevent users from locking themselves out
				$newPerm->addReadWrite($wgUser->getId());
				$newPerm->addManage($wgUser->getId());
				$this->resetExpires($newPerm);
			} else {
				$newPerm = '';
			}
			$this->apply($newPerm);
		} else {
			$this->showForm();
		}
	}
	
	function warn($msg) {
		$this->warnings[] = $msg;
	}
	
	function resetExpires(&$perm) {
		global $ppExpiresAfter;
		if(!$ppExpiresAfter) {
			$ppExpiresAfter = 31; //Expire after 31 days by default
		}
		$expires = mktime(0, 0, 0, date("m") , date("d") + $ppExpiresAfter, date("Y"));
		$expires = wfTimestamp( TS_MW, $expires);
		$perm->setExpires($expires);
		return $perm;
	}
	
	function apply($perm) {
		$this->manager->setPermissions($perm);
		$this->permissions = $this->manager->getPermissions();
		$this->showForm();
	}
	
	function showForm() {
		global $wgOut, $wgUser, $wgLang;
		
		$descr = $wgOut->parse(wfMsg( 'pp_descr' ));
		$wgOut->addHTML("<P>$descr</P>");
		
		//Show warnings
		if(count($this->warnings)) {
			$warn = "<UL>";
			foreach($this->warnings as $msg) {
				$warn .= "<LI style='color:red'>$msg";
			}
			$warn .= "</UL>";
			$wgOut->addHTML($warn);
		}
		
		$descr_pub = $wgOut->parse(wfMsg( 'pp_descr_pub' ));
		$descr_pri = $wgOut->parse(wfMsg( 'pp_descr_pri' ));
		
		$pub_vis = '';
		$pri_vis = '';
		$pub_check = '';
		$pri_check = '';
		$users = '';
		if($this->permissions) {
			$pri_check = 'CHECKED';
			$pub_vis = 'style="display:none;"';
			$userNames = array();
			$users = $this->permissions->getManageUsers();
			foreach($users as $uid) {
				$userNames[] = User::newFromId($uid)->getName();
			}
			$users = implode("\n", $userNames);
			$expdate = $this->permissions->getExpires();
			$expdate = $wgLang->date($expdate, true);
			if($expdate) {
				$expires = wfMsg( 'pp_expires' );
				$expires = str_replace('$EXPIRE', $expdate, $expires);
				$expires = $wgOut->parse($expires);
				$postpone = wfMsg( 'pp_resetexpires' );
				$postpone = "<INPUT type='submit' name='apply' value='$postpone'>";
			}
		} else {
			$pub_check = 'CHECKED';
			$pri_vis = 'style="display:none;"';
			$users = $wgUser->getName();
		}
		
		$url = $this->title->getLocalUrl( 'action=' . PermissionManager::$ACTION_MANAGE );
		$wgOut->addHTML(
			"
			<FORM action='$url' method='post'>
			<INPUT type='hidden' name='page' value='{$this->title->getFullText()}'>
			<TABLE width='100%'><TBODY>
			<TR><TD><INPUT onclick='showPublic()' type='radio' name='visibility' value='public' $pub_check><B>Public</B>
			<DIV id='pub_div' $pub_vis class='box pub'>$descr_pub</DIV>
			<TR><TD><INPUT onclick='showPrivate()' type='radio' name='visibility' value='private' $pri_check><B>Private</B>
			<DIV id='pri_div' $pri_vis>
			<DIV class='box pri'>$descr_pri</DIV>
			<TABLE><TBODY>
				<TR><TD><TEXTAREA rows='5' name='users'>$users</TEXTAREA>
			</TABLE></TBODY>
			</DIV>
			<TR><TD><DIV class='box expires'>$expires</DIV>
			<TR><TD style='padding:1em'><INPUT type='submit' name='apply' value='Apply'>
			</TBODY></TABLE>
			</FORM>
			"
		);
		
		$wgOut->addScript(
			"<script type='text/javascript'>
				function showPublic() {
					var elm = document.getElementById('pub_div');
					elm.style.display = '';
					var elm = document.getElementById('pri_div');
					elm.style.display = 'none';
				}
				function showPrivate() {
					var elm = document.getElementById('pri_div');
					elm.style.display = '';
					var elm = document.getElementById('pub_div');
					elm.style.display = 'none';
				}
			</script>
			"
		);
		$wgOut->addHeadItem('style',
			"
			<style type='text/css' media='screen,projection'>
			.box {
				padding: 5px;
			}
			.pub {
				background-color: #C8FFC7
			}
			.pri {
				background-color: #FFA5A7
			}
			</style>
			"
		);
	}

	function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ( $messagesLoaded ) return true;
		$messagesLoaded = true;

		require( dirname( __FILE__ ) . '/SetPermissionsPage.i18n.php' );
		foreach ( $allMessages as $lang => $langMessages ) {
			$wgMessageCache->addMessages( $langMessages, $lang );
		}
		return true;
	}
}
?>
