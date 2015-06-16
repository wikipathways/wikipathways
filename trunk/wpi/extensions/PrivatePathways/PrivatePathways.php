<?php
require_once('SetPermissionsPage.php');

$wgHooks['userCan'][] = 'PermissionManager::checkRestrictions';
$wgHooks['SkinTemplateContentActions'][] = 'PermissionManager::addPermissionTab';
$wgHooks['UnknownAction'][] = 'PermissionManager::permissionTabAction';

/**
 * Manages loading and writing of permissions for a page
 */
class PermissionManager {
	/**
	 * Array containing namespaces for which the permissions
	 * can be managed
	 */
	public static $permission_namespaces = array(NS_PATHWAY);

	private $pageId;
	private $permissions;

	public function __construct($pageId) {
		$this->pageId = $pageId;
		$this->read();
		if($this->permissions && $this->permissions->isExpired()) {
			//Remove the permissions, since they are expired
			$this->clearPermissions(true);
		}
	}

	private function write($force = false) {
		$tag = new MetaTag(self::$TAG, $this->pageId);
		if($force) {
			$tag->setPermissions(array());
		}
		if($this->permissions) {
			$tag->setText(serialize($this->permissions));
			$tag->save();
		} else {
			$tag->remove();
		}
	}

	private function read() {
		$tag = new MetaTag(self::$TAG, $this->pageId);
		if($tag->exists()) {
			$this->permissions = unserialize($tag->getText());
		}
	}

	/**
	 * Get the permissions.
	 * @returns A PagePermissions object, or NULL if there are no permissions
	 * set (the page is public).
	 */
	public function getPermissions() {
		return $this->permissions;
	}

	/**
	 * Set the permissions. Old permissions will be overwritten.
	 * @param $permissions The PagePermissions object containing the
	 * permissions. Passing an empty value (NULL, false, '') will
	 * clear all permissions and make the page public
	 */
	public function setPermissions($permissions) {
		$this->permissions = $permissions;
		if($permissions && $permissions->isEmpty()) {
			$this->permissions = '';
		}
		$this->write();
	}

	/**
	 * Clear all current permissions and make the page public.
	 * @param $force Force write to database, even if the currently logged
	 * in user doesn't have permission to edit the page.
	 */
	public function clearPermissions($force = false) {
		$this->permissions = '';
		$this->write($force);
	}

	/**
	 * Hook function that checks for page restrictions for the given user.
	 */
	static function checkRestrictions($title, $user, $action, &$result) {
		//Only manage permissions on specified namespaces
		if($title->exists() && in_array($title->getNamespace(), self::$permission_namespaces)) {
			//Check if the user is in a group that's not affected by page permissions
			if(in_array("sysop", $user->getGroups())) { //TODO: the override group should be dynamic
				//Sysops can always manage
				if($action == self::$ACTION_MANAGE) {
					$result = true;
					return false;
				}
			} else { //If not, check page permissions
				$mgr = new PermissionManager($title->getArticleID());
				$perm = $mgr->getPermissions();

				if($perm) {
					$result = $perm->userCan($action, $user);
					return false;
				} else {
					//Manage condition needs special rules
					if($action == self::$ACTION_MANAGE) {
						//User can only make a page private when:
						//- the user is the only author
						//- the user can edit the page
						if(MwUtils::isOnlyAuthor($user->getId(), $title->getArticleID())) {
							$result = $title->userCan('edit');
						} else {
							$result = false;
						}
						return false;
					}
				}
			}
		}

		//Otherwise, use the default permissions
		$result = null;
		return true;
	}

	/**
	 * Reset the expiration date of the given permissions
	 * to $ppExpiresAfter days from now
	 */
	public static function resetExpires(&$perm) {
		global $ppExpiresAfter;
		if(!$ppExpiresAfter) {
			$ppExpiresAfter = 31; //Expire after 31 days by default
		}
		$expires = mktime(0, 0, 0, date("m") , date("d") + $ppExpiresAfter, date("Y"));
		$expires = wfTimestamp( TS_MW, $expires);
		$perm->setExpires($expires);
		return $perm;
	}

	/**
	 * Hook function that adds a tab to manage permissions, if
	 * the user is allowed to.
	 */
	static function addPermissionTab(&$content_actions) {
		global $wgTitle, $wgRequest;

		if($wgTitle->userCan(self::$ACTION_MANAGE)) {
			$action = $wgRequest->getText( 'action' );

			//Permissions already set, publish
			$content_actions[self::$ACTION_MANAGE] = array(
				'text' => 'permissions',
				'href' => $wgTitle->getLocalUrl( 'action=' . self::$ACTION_MANAGE ),
				'class' => $action == self::$ACTION_MANAGE ? 'selected' : false
			);
		}
		return true;
	}

	/**
	 * Excecuted when the user presses the 'permissions' tab
	 */
	static function permissionTabAction($action, $article) {
		if($action == self::$ACTION_MANAGE) {
			$pp = new SetPermissionsPage($article);
			$pp->execute();
			return false;
		}
		return true;
	}

	public static $TAG = "page_permissions"; #The name of the meta tag used to store the data
	public static $ACTION_MANAGE = "manage_permissions";
}

/**
 * Object that stores user permissions for a page
 */
class PagePermissions {
	private $pageId;
	private $permissions = array(); #Array where key is action, value is array of users
	private $expires;

	public function __construct($pageId) {
		$this->pageId = $pageId;
	}

	public function getPermissions() {
		return $this->permissions;
	}

	public function getPageId() {
		return $this->pageId;
	}

	/**
	 * Find out if the user can perform the given
	 * action based on the permissions in this object
	 */
	public function userCan($action, $user) {
		if($user instanceof User) {
			$user = $user->getId();
		}
		$p = $this->permissions[$action];
		if($p) {
			return (boolean)$p[$user];
		}
		return false;
	}

	/**
	 * Permit the user to perform the given action
	 */
	public function setUserPermission($user_id, $action) {
		$this->permissions[$action][$user_id] = $user_id;
	}

	/**
	 * Permit the user to read/write this page
	 */
	public function addReadWrite($user_id) {
		$this->setUserPermission($user_id, 'read');
		$this->setUserPermission($user_id, 'edit');
	}

	public function getManageUsers() {
		return $this->permissions[PermissionManager::$ACTION_MANAGE];
	}

	/**
	 * Permit the user to manage the permissions of this page
	 */
	public function addManage($user_id) {
		$this->setUserPermission($user_id, PermissionManager::$ACTION_MANAGE);
	}

	/**
	 * Remove all permissions for the given user
	 */
	public function clearPermissions($user_id) {
		foreach($this->permissions as &$a) {
			unset($a[$user_id]);
		}
	}

	/**
	 * Forbid the user to perform the given action
	 */
	public function removeUserPermission($user_id, $action) {
		unset($this->permissions[$action][$user_id]);
	}

	/**
	 * Set the expiration date of the permissions.
	 * The permissions will be cleared automatically
	 * after the given date.
	 */
	public function setExpires($timestamp) {
		$this->expires = $timestamp;
	}

	public function getExpires() {
		return $this->expires;
	}

	/**
	 * Check if the permissions are expired
	 */
	public function isExpired() {
		return $this->expires && ((float)$this->expires - (float)wfTimestamp(TS_MW)) <= 0;
	}

	/**
	 * Check if there are any permissions specified
	 * in this object.
	 * @return true if no permissions are specified
	 */
	public function isEmpty() {
		$empty = true;
		foreach($this->permissions as &$a) {
			if(count($a) > 0) {
				$empty = false;
				break;
			}
		}
		return $empty;
	}
}
?>
