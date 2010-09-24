<?php

if(php_sapi_name() != 'cli')
{
    echo "This script must be run from the command line\n";
    exit();
}

$currentDir = getcwd();
require_once('../../../wpi.php');
chdir($currentDir);

$argsMsg = "Available options: \na)Initiate/Update => method=update\n\n";

if($argv[0] == 'ontologyScorer.php')
{
    if($argc == 2)
    {
        parse_str($argv[1], $args);
        switch($args['method'])
        {
            case 'update':
                OntologyScorer::execute();
            break;
            default:
                echo $argsMsg;
        }

    }
    else
    {
        echo $argsMsg;
    }
}
/*
 * Calculates relations/scores between pairs of pathways based on the common Ontology Tags.
 *
 * Usage : a) Call the static function execute() b) Execute from command line by executing "php ontologyScorer.php method=update"
 */

class OntologyScorer
{
    private static $_ontologyMappingTable = "ontology";
    public static $_scoresFile = "ontology-relations-scores.txt";

    public static function execute()
    {
        self::init();
    }

    public static function init()
    {
        // Refresh the scores file
        if(is_file(self::$_scoresFile))
        {
            unlink(self::$_scoresFile);
        }

        $fh = fopen(self::$_scoresFile, 'w');
        if(!$fh)
        {
            echo "Please set proper permissions for Score file.\n";
            exit();
        }

        $fileHeaders = "PW1\tPW2\tNr Common ontology termss\n";
        fwrite($fh, $fileHeaders);
        fclose($fh);

        $relations = self::findRelations();

        if(count($relations) > 0)
        {
            foreach($relations as $relation)
            {
                self::logScore($relation->pwId_1, $relation->pwId_2, $relation->score);
            }
        }
    }

    private static function findRelations()
    {
        $dbw = wfGetDB( DB_MASTER );
        $relations = array();
        
        $sql = "select a.pw_id as pwId_1, b.pw_id as pwId_2, count(*) as score
                from " . self::$_ontologyMappingTable . " AS a
                inner join " . self::$_ontologyMappingTable . " AS b
                on
                a.term = b.term AND a.pw_id != b.pw_id
                group by a.pw_id, b.pw_id;";

        $res = $dbw->query($sql);
        while($row = $dbw->fetchObject($res))
        {
            $relations[] = $row;
        }
        return $relations;
    }

    private function logScore($pw1, $pw2, $score)
    {
        $fh = fopen(self::$_scoresFile, 'a');
        $log = "$pw1\t$pw2\t$score\n";
        fwrite($fh, $log);
        fclose($fh);
    }

}