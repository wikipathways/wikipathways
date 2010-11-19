<?php
require_once('OntologyFunctions.php');

switch($_REQUEST['action'])
{
    case 'remove' :
        echo OntologyFunctions::removeOntologyTag($_POST['tagId'],$_POST['title']);
        break;

    case 'add' :
        echo OntologyFunctions::addOntologyTag($_POST['tagId'],$_POST['tag'],$_POST['title']);
        break;

    case 'search' :
        echo OntologyFunctions::getBioPortalSearchResults($_GET['searchTerm']);
        break;
    
    case 'fetch' :
        echo OntologyFunctions::getOntologyTags($_POST['title']);
        break;

    case 'tree' :
        echo OntologyFunctions::getBioPortalTreeResults($_GET['tagId']);
        break;
}