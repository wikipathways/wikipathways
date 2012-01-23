<?php
$messages = array();
$messages['en'] = array(
	'tagemail_subject' => '{{SITENAME}} page $PAGETITLE has been changed by $PAGEEDITOR',
	'tagemail_body' => 'Dear $WATCHINGUSERNAME,


$PAGEEDITOR $ACTIONd curation tag "$TAGNAME" on page $PAGETITLE. See $PAGETITLE_URL for the current version.

Contact the editor:
mail: $PAGEEDITOR_EMAIL
wiki: $PAGEEDITOR_WIKI

There will be no other notifications in case of further changes unless you visit this page.
You could also reset the notification flags for all your watched pages on your watchlist.

             Your friendly {{SITENAME}} notification system

--
To change your watchlist settings, visit
{{fullurl:{{ns:special}}:Watchlist/edit}}

Feedback and further assistance:
{{fullurl:{{MediaWiki:Helppage}}}}'
);
?>
