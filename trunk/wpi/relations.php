<?php

$currentDir = getcwd();
require_once('wpi.php');
chdir($currentDir);

if(php_sapi_name() == 'cli')
{
    $argsMsg = "Available options :

Note: Parameters are Case sensitive
a)Update => method=update
Params: [type]=[path to score file], Seperate multiple entries by space
Example: php relations.php method=update xref=xref.txt label=label.txt

b)Get Relations => method=relation
Params: [param-name]=[param-value], Separate multiple entries by &
Available Params: pwId_1, pwId_2, minScore, type(xref/label/ontology), species(replace the space with a -)
Example: php relations.php method=relation pwId_1=WP500 pwId_2=WP520 minscore=5 type=xref species=Homo-sapiens\n
";
    if($argv[0] == 'relations.php')
    {
        if($argc >= 2)
        {
            parse_str($argv[1], $args);
            switch($args['method'])
            {
                case 'update':

                    $relationType = array();

                    for($i = 2; $i < count($argv); $i++)
                    {
                        parse_str($argv[$i], $params);

                        if(count($params) == 0)
                        {
                            echo "Error: Please enter proper parameters\n\n";
                            echo $argsMsg;
                        }
                        else
                        {
                            foreach($params as $type => $file)
                            {
                                if($type == '' || $file == '')
                                {
                                    echo "Error in parameters";
                                    exit();
                                }
                                $relationType[] = array('type' => $type, 'file' => $file);
                            }                            
                        }
                    }
                    Relations::updateDb($relationType);
                break;

                case 'relation':
                    
                    $type = "";
                    $pwId_1 = "";
                    $pwId_2 = "";
                    $minscore = "";
                    $species = "";

                    for($i = 2; $i < count($argv); $i++)
                    {
                        parse_str($argv[$i]);
                    }

                    if($species != "")
                        $species = str_replace("-", " ", $species);

//                    echo $type . " " . $pwId_1 . " " . $pwId_2 . " " . $minscore;
                    
                    $results = Relations::fetchRelations($type, $pwId_1, $pwId_2, $minscore, $species);

                    echo "Pathway Id 1\tPathway Id 2\tScore\tRelation type\tSpecies\n";
                    if(count($results) > 0)
                    {
                        foreach($results as $relation)
                        {
                            echo "{$relation->pwId_1}\t{$relation->pwId_2}\t{$relation->score}\t{$relation->type}\t{$relation->species}\n";
                        }
                    }
                break;

                default:
                    echo "Error in parameters\n\n";
                    echo $argsMsg;
                    exit();
            }

        }
        else
        {
            echo $argsMsg;
            exit();
        }
    }
}
    
class Relations
{
    public static $_relationsTable = "relations";
    private static $_pathwaySpecies = array();

    public static function lastUpdated()
    {
        $result['xref'] =  date("F d, Y H:i:s", filemtime(self::$_xrefScoreFile));
        $result['label'] =  date("F d, Y H:i:s", filemtime(self::$_labelScoreFile));
        return $result;
    }

    public static function checkFile($file)
    {
        if(!is_file($file))
            return false;
        else
            return true;
    }

    private static function refreshTable($relationType)
    {
        $dbw =& wfGetDB( DB_MASTER );
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::$_relationsTable . "` (
                    `pwId_1` VARCHAR( 10 ) NOT NULL ,
                    `pwId_2` VARCHAR( 10 ) NOT NULL ,
                    `type` VARCHAR( 10 ) NOT NULL ,
                    `score` FLOAT UNSIGNED NOT NULL,
                    `species` VARCHAR( 50 ) NOT NULL
                );";
        $create = $dbw->query($sql);
        $sql = "Delete FROM "  . self::$_relationsTable . " WHERE type ='" . $relationType . "'" ;
        $truncate = $dbw->query($sql);
    }

    public static function updateDb($relationType)
    {
        $dbw =& wfGetDB( DB_MASTER );

        foreach($relationType as $method)
        {
            if(!self::checkFile($method['file']))
            {
                echo "Error: File not found! {$method['file']}\n";
                exit();
            }

            //Remove only those type of relations which are being updated.
            self::refreshTable($method['type']);

            $file = file($method['file']);
            for($i = 1; $i < count($file); $i++)
            {
                $relation = explode("\t",trim($file[$i]));

                if($relation[2] > 0)
                {
                     $species = self::getSpecies($relation[0]);
                     $score = self::normalizeScore($relation[2], $relation[3], $relation[4]);

                     $dbw->insert( self::$_relationsTable , array(
                                                    'pwId_1' => $relation[0],
                                                    'pwId_2' => $relation[1],
                                                    'type' => $method['type'],
                                                    'score' => $score,
                                                    'species' => $species
                                            ));
                }

            }

            unset($file);
        }
    }

    public static function fetchRelations($type = '', $pwId_1 = '', $pwId_2 = '', $minScore = 0, $species = '')
    {
        $dbr =& wfGetDB(DB_SLAVE);
        $query = "SELECT * FROM " . self::$_relationsTable ;
        $minScore = (float)$minScore;
        $conditions = array();
        
        if($type != '')
            $conditions[] = "type = '$type'";

        if($pwId_1 != '' && $pwId_2 != '')
        {
            $conditions[] = "((pwId_1 = '$pwId_1' AND pwId_2 = '$pwId_2') OR (pwId_1 = '$pwId_2' AND pwId_2 = '$pwId_1'))";
        }
        elseif($pwId_1 != '' && $pwId_2 == '' || $pwId_1 == '' && $pwId_2 != '')
        {
            $pwId = ($pwId_1 == "")?$pwId_2:$pwId_1;
            $conditions[] = "(pwId_1 = '$pwId' OR pwId_2 = '$pwId')";
        }

        if($minScore > 0)
            $conditions[] = "score > $minScore";
        
        if($species != '')
            $conditions[] = "species = '$species'";

        if(count($conditions) > 0)
        {
            $cons = implode (" AND ", $conditions);
            $query .= " Where $cons";
        }

        $res = $dbr->query($query);
        while($row = $dbr->fetchObject($res))
        {
            $result[] = $row;
        }
        $dbr->freeResult( $res );

        return $result;
    }

    private static function getSpecies($pathwayId)
    {
        if(array_key_exists($pathwayId, self::$_pathwaySpecies))
        {
            return self::$_pathwaySpecies[$pathwayId];
        }
        else
        {
            $pathway = Pathway::newFromTitle($pathwayId);
            $species = $pathway->getSpecies();
            self::$_pathwaySpecies[$pathwayId] =  $species;
            return $species;
        }
    }

    private static function normalizeScore($score, $pwId_1Count, $pwId_2Count)
    {
        return $normalizedScore = $score / min($pwId_1Count, $pwId_2Count);
    }
}

?>