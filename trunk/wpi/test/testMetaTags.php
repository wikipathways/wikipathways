<?php

set_include_path(get_include_path().PATH_SEPARATOR.realpath('../../includes').PATH_SEPARATOR.realpath('../../').PATH_SEPARATOR.realpath('../').PATH_SEPARATOR);
$dir = realpath(getcwd());
chdir("../../");
require_once ( 'WebStart.php');
require_once( 'Wiki.php' );
chdir($dir);

require_once("MetaTag.php");

$starttime = wfTimestamp(TS_MW);

/* First read a nonexisting tag */
$title = Title::newFromText("Pathway:WP4");
echo "Tagging {$title->getFullText()}\n<BR>";
$tag = new MetaTag("MyTag:firsttag", $title->getArticleID());

$exists = $tag->exists() ? "does" : "doesn't";
echo "Tag {$tag->getName()} $exists exist<BR>\n";

echo "<pre>";
var_dump($tag);
echo "</pre>";

/* Then write */
$tag->setText("some tag text");
$tag->save();

echo "Saved tag<BR>\n";

echo "<pre>";
var_dump($tag);
echo "</pre>";

/* Read again */
$tag = new MetaTag("MyTag:firsttag", $title->getArticleID());

$exists = $tag->exists() ? "does" : "doesn't";
echo "Tag {$tag->getName()} $exists exist<BR>\n";

echo "<pre>";
var_dump($tag);
echo "</pre>";

/* Edit */
$tag->setText("Edited text");
$tag->save();

echo "Edited tag text <BR>\n";

/* Query by page */
$start = microtime(true);

echo "Query tags for page {$title->getFullText()}\n<BR>";
$tags = MetaTag::getTagsForPage($title->getArticleID());
$nrTags = count($tags);
echo "Page has $nrTags tags<BR>\n";

$time = microtime(true) - $start;
echo("Time: $time<BR>\n");

/* Query by tag */
$start = microtime(true);

$tag = "Curation:tutorial";
echo "Query pages for tag {$tag}\n<BR>";
$pages = MetaTag::getPagesForTag($tag);
$nrPages = count($pages);
echo "Tag has $nrPages pages<BR>\n";

$time = microtime(true) - $start;
echo("Time: $time<BR>\n");


/* Then delete */
echo "Removing tag<BR>\n";
$tag->remove();

/* Read again */
$tag = new MetaTag("MyTag:firsttag", $title->getArticleID());

$exists = $tag->exists() ? "does" : "doesn't";
echo "Tag {$tag->getName()} $exists exist<BR>\n";

echo "<pre>";
var_dump($tag);
echo "</pre>";

/* Get tag history */
echo "Recorded tag history:<BR>\n<TABLE>";

$history = $tag->getHistory($starttime);
foreach($history as $hr) {
	echo "<TR>";
	echo "<TD>" . $hr->getAction();
	echo "<TD>" . $hr->getUser();
	echo "<TD>" . $hr->getTime();
}
echo "</TABLE>";
