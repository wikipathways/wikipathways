<?php
require("PathwayWishList.php");

$wgExtensionFunctions[] = "wfWishList";

function wfWishList() {
    global $wgParser;
    $wgParser->setHook( "wishlist", "renderWishlist" );
}

function renderWishlist( $input, $argv, &$parser ) {
	$parser->disableCache();
	
	$wishlist = new PathwayWishList();
	
	$type = $argv['type'];
	$tableAttr = $argv['tableattr'];
	switch($type) {
		case 'newest':
			$limit = $argv['limit'];
			return createNewest($wishlist, $limit, $tableAttr);
		case 'topVoted':
		default:
			$limit = $argv['limit'];
			$threshold = $argv['threshold'];
			return createTopVoted($wishlist, $limit, $threshold, $tableAttr);
			
	}
}

function createNewest($wishlist, $limit = 5, $tableAttr = '') {
	global $wgUser, $wgLang;
	$limit = (int)$limit;
	$top = $wishlist->getWishlist('date');
	if(count($top) == 0) {
		return "<i>There are currently no wishlist items</i>";
	}
	/* List version
	$i = 0;
	foreach($top as $wish) {
		if($i >= $limit) break;
			
		$name = $wish->getTitle()->getText();
		$user = $wish->getRequestUser();
		$by = $wgUser->getSkin()->userLink( $user, $user->getName());
		$date = $wgLang->date($wish->getRequestDate());
		$out .= "<b>$name</b> ($date), ";
		$i++;
	}
	$out = substr($out, 0, -2);
	*/
	// table version
	$out = "<table $tableAttr><tbody>";
	$out .= "<th>Pathway<th>Requested by<th>Date";
	if(count($top) == 0) {
		return "<i>There are currently no wishlist items</i>";
	}
	$i = 0;
	foreach($top as $wish) {
		if($i >= $limit) break;
			
		$name = $wish->getTitle()->getText();
		$user = $wish->getRequestUser();
		$by = $wgUser->getSkin()->userLink( $user, $user->getName());
		$date = $wgLang->timeanddate($wish->getRequestDate());
		$out .= "<tr><td>$name<td>$by<td>$date";
		$i++;
	}
	$out .= "</tbody></table>";
	
	return $out;
}

function createTopVoted($wishlist, $limit = 5, $threshold = 1, $tableAttr = '') {
	global $wgUser;
	$threshold = (int)$threshold;
	$limit = (int)$limit;
	$top = $wishlist->getWishlist('votes');
	$out = "<table $tableAttr><tbody>";
	$out .= "<th>Pathway<th>Votes";
	if(count($top) == 0 || $top[0]->countVotes() < $threshold) {
		return "<i>There are currently no wishlist items with enough votes</i>";
	}
	$i = 0;
	foreach($top as $wish) {
		if($i >= $limit) break;
		$votes = $wish->countVotes();
		if($votes < $threshold) break;
		
		$name = $wish->getTitle()->getText();
		//$user = $wish->getRequestUser();
		//$by = $wgUser->getSkin()->userLink( $user, $user->getName());
		
		$out .= "<tr><td>$name<td align='center'>$votes";
		$i++;
	}
	$out .= "</tbody></table>";
	return $out;
}
?>
