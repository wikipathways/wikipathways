<?php
 /* NamespacePermissions - MediaWiki extension
  *
  * provides separate permissions for each action (read,edit,create,move)
  * on articles in custom namespaces for fine access management
  *
  * Author: Petr Andreev
  *
  * Sample usage:3
  *
  * $wgExtraNamespaces = array(100 => "Foo", 101 => "Foo_Talk");
  * // optional (example): allow registered users to view and edit articles in Foo
  * $wgGroupPermissions[ 'user' ][ 'ns100_read' ] = true;
  * $wgGroupPermissions[ 'user' ][ 'ns100_edit' ] = true;
  * // end of optional
  * require('extensions/NamespacePermissions.php');
  *
  * Permissions provided:
  *   # ns{$num}_read
  *   # ns{$num}_edit
  *   # ns{$num}_create
  *   # ns{$num}_move
  * where {$num} - namespace number (e.g. ns100_read, ns101_create)
  *
  * Groups provided:
  *   # ns{$title}RW - full access to the namespace {$title}
  *   # ns{$title}RO - read-only access to the namespace {$title}
  *   e.g. nsFoo_talkRW, nsFooRO
  */

 // permissions for autocreated groups should be set now,
 // before the User object for current user is instantiated
 namespacePermissionsCreateGroups();
 // other stuff should better be done via standard mechanism of running extensions
 $wgExtensionFunctions[] = "wfNamespacePermissions";

 // create groups for each custom namespace
 function namespacePermissionsCreateGroups() {
	 global $wgExtraNamespaces, $wgGroupPermissions;
	 foreach ( $wgExtraNamespaces as $num => $title ) {
		 $wgGroupPermissions[ "ns{$title}RW" ][ "ns{$num}_edit" ] = true;
		 $wgGroupPermissions[ "ns{$title}RW" ][ "ns{$num}_read" ] = true;
		 $wgGroupPermissions[ "ns{$title}RW" ][ "ns{$num}_create" ] = true;
		 $wgGroupPermissions[ "ns{$title}RW" ][ "ns{$num}_move" ] = true;
		 $wgGroupPermissions[ "ns{$title}RO" ][ "ns{$num}_read" ] = true;
	 }
 }

 function wfNamespacePermissions() {
	 global $wgHooks;

	 // use the userCan hook to check permissions
	 $wgHooks[ 'userCan' ][] = 'namespacePermissionsCheckNamespace';
 }

 function namespacePermissionsCheckNamespace( $title, $user, $action, $result ) {
	 if ( ( $ns = $title->getNamespace() ) >= 100 ) {
		 if ( ! $user->isAllowed("ns{$ns}_{$action}") ) {
			 $result = false;
			 return false;
		 }
	 }
	 return true;
 }

 /**
 * Add extension information to Special:Version
 */
 $wgExtensionCredits['other'][] = array(
		'name' => 'NamespacePermissions',
		'version' => '',
		'author' => 'Petr Andreev',
		'description' => 'flexible access management for custom namespaces',
		'url' => 'http://www.mediawiki.org/wiki/Extension:NamespacePermissions'
		);

