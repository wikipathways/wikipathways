<?php

set_include_path(get_include_path().PATH_SEPARATOR.realpath('../../includes').PATH_SEPARATOR.realpath('../../').PATH_SEPARATOR.realpath('../').PATH_SEPARATOR);
$dir = realpath(getcwd());
chdir("../../");
require_once ( 'WebStart.php');
require_once( 'Wiki.php' );
chdir($dir);

require_once("MetaTag.php");

/* First read a tag */
$title = Title::newFromText("Pathway:Homo sapiens:Sandbox");
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

?>
