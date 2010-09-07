<?php
require_once('ontologyfunctions.php');

switch($_REQUEST['action'])
{
    case 'remove' :
        echo ontologyfunctions::removeOntologyTag($_POST['tagId'],$_POST['title']);
        break;

    case 'add' :
        echo ontologyfunctions::addOntologyTag($_POST['tagId'],$_POST['tag'],$_POST['title']);
        break;

    case 'search' :
        echo ontologyfunctions::getBioPortalSearchResults($_GET['searchTerm']);
        break;
    
    case 'fetch' :
        echo ontologyfunctions::getOntologyTags($_POST['title']);
        break;

    case 'tree' :
        echo ontologyfunctions::getBioPortalTreeResults($_GET['tagId']);
        break;
}