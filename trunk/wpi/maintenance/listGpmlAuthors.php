<HTML><BODY>
<?php
/*
Lists the value of the GPML author field for all pathways, and checks if it's
a registered user
*/

require_once("Maintenance.php");

echo "<TABLE border='1'>" .
	"<TH>Pathway<TH>GPML Author field<TH>WikiPathways username" .
	"<TH>User who created pathway";

foreach(Pathway::getAllPathways() as $pathway) {
	//Get the GPML author
	$pd = $pathway->getPathwayData();
	$gpml = $pd->getGpml();
	$author = $gpml["Author"];
	//Get the corresponding WP account
	if($author) {
		$user = userFromRealName($author);
	}
	$username = '';
	if($user) {
		$username = $user->getName();
	}
	//Get the user that created the first revision
	$rev = $pathway->getFirstRevision();
	$fstUser = User::newFromId($rev->getUser());
	$fstUserName = '';
	if($fstUser) {
		$fstUserName = $fstUser->getName();
	}
	//Filter out entries where the revision user matches the GPML user
	if($fstUserName == $username) continue;
	//Filter out entries that were not created by either
	//a bot or anonymous user
	if($fstUser && !($fstUser->isAnon() || $fstUser->isBot())) continue;

	echo "<TR><TH><A href='{$pathway->getFullUrl()}'>{$pathway->name()}</A><TD>$author<TD>$username<TD>$fstUserName";
}

echo "</TABLE>";

function userFromRealName($name) {
	$dbr = wfGetDB( DB_SLAVE );

	$query = "SELECT user_id FROM `user` WHERE user_real_name = '$name'";

	$res = $dbr->query($query);
	while($row = $dbr->fetchObject( $res )) {
		return User::newFromId($row->user_id);
	}
}
?>
</HTML></BODY>
