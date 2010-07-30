<?php
   require_once('../wpi.php');
   
	function getThumb($pathway, $width = 200) {
		$pathway->updateCache(FILETYPE_IMG);
		$img = new Image($pathway->getFileTitle(FILETYPE_IMG));
		$img->loadFromFile();
		return $img->getThumbnail( $width, -1 );
	}
	
   function getRecentChanges() {
        $dbr =& wfGetDB( DB_SLAVE );
        $forceclause = $dbr->useIndexClause("rc_timestamp");
        $recentchanges = $dbr->tableName( 'recentchanges');

        $sql = "SELECT  
                                rc_namespace, 
                                rc_title,
                                rc_this_oldid
                        FROM $recentchanges $forceclause
                        WHERE 
                                rc_namespace = " . NS_PATHWAY . "
                        GROUP BY rc_title
                        ORDER BY rc_timestamp DESC
                ";

        //~ wfDebug ("SQL: $sql");

        $res = $dbr->query( $sql, "getRecentChanges" );

        $objects = array();
        while ($row = $dbr->fetchRow ($res))
        {
                try {
                                $ts = $row['rc_title'];
                        $p = Pathway::newFromTitle($ts);
                        $p->setActiveRevision($row['rc_this_oldid']);
                        if(!$p->getTitleObject()->isRedirect() && $p->isReadable()) {
                                $objects[] = $p;
                        }
                } catch(Exception $e) {
                        wfDebug("Unable to create pathway object for recent changes: $e");
                }

        }
        return array("pathways" => $objects);
}

$dom = new DOMDocument('1.0', 'utf-8');
$dom->formatOutput = true;

//Add the RSS element with version 2.0
$rss_element = $dom->createElement('rss');
$rss = $dom->appendChild($rss_element);

$rss_version = $dom->createAttribute('version'); 
$rss_version_text = $dom->createTextNode('2.0');
$rss_attribute = $rss->appendChild($rss_version);
$rss_attribute->appendChild($rss_version_text);

//Add Channel element
$channel_element = $dom->createElement('channel');
$channel = $rss->appendChild($channel_element);

//Add Wikipathways main info
$mainTitleElement = $dom->createElement('title', 'WikiPathways');
$mainLinkElement = $dom->createElement('link', SITE_URL);
$mainDescriptionElement = $dom->createElement('description', 'Wikipathways: Pathways for the people');
$mainImageElement = $dom->createElement('image');
$imageUrl = $dom->createElement('url', $wgLogo);
$imageCaption = $dom->createElement('title', 'WikiPathways');
$imageLink = $dom->createElement('link', SITE_URL);
$mainImageElement->appendChild($imageUrl);
$mainImageElement->appendChild($imageCaption);
$mainImageElement->appendChild($imageLink);

$channel->appendChild($mainTitleElement);
$channel->appendChild($mainLinkElement);
$channel->appendChild($mainDescriptionElement);
$channel->appendChild($mainImageElement);

//Add items

   $changedPathways = getRecentChanges();
   //var_dump($changedPathways); /*
   $GetTags = $_GET["tags"];
   if($GetTags) $GetTags = explode(",", $GetTags);
   else $GetTags = array();
   
   $printItem = false;
   foreach ($changedPathways["pathways"] as $p){
		if(!$p->isReadable()) continue; //Skip private pathways
		
      $mwtitle = $p->getTitleObject();

        $pageid = $mwtitle->getArticleID();
        $tags = CurationTag::getCurationTags($pageid);
        $pathwayTags = array();
        foreach ($tags as $tag) {
           $pathwayTags[]=substr($tag->getName(), 9);
        }
        $intersectedTagArray = array_intersect($GetTags, $pathwayTags);
        if (!$GetTags || (count($intersectedTagArray)>0)){
            $printItem = true;
        }
        else $printItem = false;
        
      if ($printItem){      
      $itemElement = $dom->createElement('item');
      $item = $channel->appendChild($itemElement);
      $title = $p->getName();
      $link = $p->getFullUrl();
      $gpmlDate = $p->getGpmlModificationTime();
      //print "<h1>$gpmlDate</h1>";
          $modificationDate = date("r", mktime(substr($gpmlDate, 8, 2), substr($gpmlDate, 10, 2), substr($gpmlDate, 12, 2), substr($gpmlDate, 4, 2), substr($gpmlDate, 6, 2), substr($gpmlDate, 0, 4)));
 
      $itemTitleElement = $dom->createElement('title', $title);
      $itemLinkElement = $dom->createElement('link', $link);
      $itemPubDate = $dom->createElement('pubDate', $modificationDate);
      $item->appendChild($itemTitleElement);
      $item->appendChild($itemLinkElement);
      $item->appendChild($itemPubDate);

      $mwtitle = $p->getTitleObject();
	
	$pageid = $mwtitle->getArticleID();
	$tags = CurationTag::getCurationTags($pageid);
        foreach ($tags as $tag) {
           $itemiTagElement = $dom->createElement('category', $tag->getName());
	   $item->appendChild($itemiTagElement);
        }    
      $latestRevId = $mwtitle->getLatestRevID();
      $latestRev = Revision::newFromId($latestRevId);
      $edit_description = $latestRev->getComment();
      $latest_user = $latestRev->getUser();
      
      $itemAuthorElement = $dom->createElement('author', User::newFromId($latest_user)->getName());
      
      $itemDescriptionElement = $dom->createElement('description');
      
      $description = $edit_description;
      //Add thumbnail to description
        if(!$p->isDeleted()) {
            $thumb = getThumb($p);
            $url = SITE_URL . $thumb->getUrl();
            $description = "<img src='${url}' align='left' border='0'> ${description}";
        }
        $descriptionCdata = $dom->createCDATASection($description);
        $itemDescriptionElement->appendChild($descriptionCdata);
        
      $item->appendChild($itemAuthorElement);
      $item->appendChild($itemDescriptionElement);
}}
echo $dom->saveXML();
?>
