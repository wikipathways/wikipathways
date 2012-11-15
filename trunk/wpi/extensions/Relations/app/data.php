<?php

require_once("Graphml.php");
require_once("../../../relations.php");


if($_GET['action'] == "")
{
    echo("Please enter the required parameters.");
}
else
{
    switch($_GET['action'])
    {
        case "relations":
            $relations = Relations::fetchRelations($_GET['type'], "", "", $_GET['minscore'], $_GET['species']);
            header("Content-Type: text/xml; charset=UTF-8");
            $graphMLConv = new GraphMLConverter($relations);
            echo $graphMLConv->getGraphML();

            break;

        case "species":
            $species = Pathway::getAvailableSpecies();
            echo json_encode($species);
            break;

        case "info":
            $pw = new Pathway($_GET['pwId']);
            $pwInfo = array(
                            'pwId' => $_GET['pwId'],
                            'name' => $pw->getName(),
                            'pwImageUrl' => getPathwayThumbnail($pw),
                            'pwUrl' => $pw->getFullURL()
                        );
            echo json_encode($pwInfo);
            break;

        case "save":
            $dir = "save";
            $fileName = time() . mt_rand(1, 10000) . ".jpg";
            try
            {
                if(move_uploaded_file( $_FILES['Filedata']['tmp_name'], "$dir/$fileName"))
                {
                    echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/" . $dir . "/" . $fileName;
                }
                else
                {
                    die("error");
                }
            }
            catch(Exception $e)
            {
                die("error");
            }
            break;
    }
}

function getPathwayThumbnail( $pathway, $width = 700, $height = 450 ) {

        $pathway->updateCache(FILETYPE_IMG);
        $img = new Image($pathway->getFileTitle(FILETYPE_IMG));

        $img->loadFromFile();

        $thumbUrl = '';
        $error = '';
        
        if ( $img->exists() ) {
                $imageWidth  = $img->getWidth();
                $imageHeight = $img->getHeight();
        }
        if ( $width == 0) {
                $width = $imageWidth;
        }
        if ( $height == 0) {
                $height = $imageHeight;
        }

        $thumb = $img->getThumbnail( $width, $height );
        if ( $thumb )
        {
            return $thumbUrl = "http://" . $_SERVER['HTTP_HOST'] . $thumb->getUrl();
        } 
        else
        {
            return $pathway->getFileURL($type);
        }
}


?>