<?php

if(php_sapi_name() != 'cli')
{
    echo "This script must be run from the command line\n";
    exit();
}

$currentDir = getcwd();
require_once('../../../search.php');
require_once('wpi.php');
chdir($currentDir);

$argsMsg = "Available options: \na)Initiate/Update => method=update b)Purge => method=purge\n\n";

if($argv[0] == 'labelMapper.php')
{
    if($argc == 2)
    {
        parse_str($argv[1], $args);
        switch($args['method'])
        {
            case 'update':
                $mapper = new LabelMapper();
                $mapper->init();
            break;
            case 'purge':
                $mapper = new LabelMapper();
                $mapper->purge();
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
 * Creates/Updates the associations between the labels and the pathways.
 *
 * Usage : a) Call the function init() using an instance of the Class b) Execute from command line by executing "php LabelMapper.php method=update"
 */

class LabelMapper
{
    private $_db;
    // 0 -> Not Initialzed 1 -> Initialzed
    private $_initialized = 0;
    private $_logCount = 0;
    public $_minLabelLength = 4;
    public $_labelMappingTable = 'labelmappings';
    public $_logFileName = "label-mapper.log";
    public $_errorFileName = "label-mapper-error.log";
    
    public function __construct()
    {
        $this->_db =& wfGetDB(DB_SLAVE);
    }

    public function init()
    {
        $labelCount = 0;

        if(file_exists($this->_logFileName))
        {
            $logs = file($this->_logFileName);
            $this->_logCount = count($logs) - 1;
            
            $lastUpdated = 0;
            if(count($logs > 1))
            {
                for($i = count($logs); $i > 0; $i--)
                {
                    $log = explode("\t", trim($logs[$i]));
                    $timeStamp = $log[5];
                    $logStatus = $log[4];
                    if($logStatus == 'finished')
                    {
                        $lastUpdated = $timeStamp;
                        break;
                    }
                }
                if($lastUpdated != 0)
                {
                    $this->_initialized = 1;
                }
            }
        }
        else
        {
            $this->createFiles();
        }

        $this->addLog("started");
        if($this->_initialized == 0)
        {
            $this->createTable();
            $pathways = $this->getPathways();
        }
        else
        {
            $pathways = $this->getPathways($lastUpdated);
        }

        if(count($pathways) > 0)
        {
            $labelCount = $this->updateCache($pathways);
        }
        else
        {
            $this->addLog("finished");
        }
    }

    private function updateCache($pathways)
    {
        $labelCount = 0;

        foreach($pathways as $pwId)
        {
            if($this->_initialized)
                $this->_db->delete( $this->_labelMappingTable, array( 'pwId' => $pwId ));
        }

        $mappingCount = 0;
        $labels = array();
        $labels = $this->getLabels($pathways);
        $labelCount = count($labels);

        if($labelCount > 0)
        {
            foreach($labels as $label)
            {
                if($this->_initialized)
                    $this->_db->delete( $this->_labelMappingTable, array('label' => $label));

                $mappings = $this->findPathwaysByLabel($label);
                $mappingCount += count($mappings);
                
                if(count($mappings) > 0)
                {
                    foreach($mappings as $mapping)
                    {
                        $this->_db->insert( $this->_labelMappingTable, array('label' => $label, 'pwId' => $mapping['pwId'], 'search_score' => $mapping['score'], 'species' =>  $mapping['species']));
                    }
                }
            }
        }
        $this->addLog("finished", $labelCount, count($pathways), $mappingCount);
        return $labelCount;
    }



    private function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `labelmappings` (
                  `id` int(8) unsigned NOT NULL auto_increment,
                  `label` varchar(100) NOT NULL,
                  `pwId` varchar(8) NOT NULL,
                  `search_score` double NOT NULL,
                  `species` varchar(50) NOT NULL,
                  PRIMARY KEY  (`id`)
                );";
        $res = $this->_db->query($sql);
    }

    public function findPathwaysByLabel($label, $species = '')
    {
        $query = str_replace(" ", " AND ", $label);
        try
        {
            $results = PathwayIndex::searchByText($query, $species);
        }
        catch(Exception $e)
        {
            $this->addError("Exception thrown for label: $label");
            return(array());
        }

        $mappings = array();
        if(count($results) > 0)
        {
            foreach($results as $result)
            {
                $mapping = array();
                $mapping['species'] = $result->getFieldValue("organism");
                $indexerId = $result->getFieldValue("indexerId");
                $mapping['pwId'] = substr($indexerId, strripos($indexerId, ":")+1);
                $mapping['score'] = (string)$result->getScore();
                $mappings[] = $mapping;
//                if($mapping['pwId'] == 'WP801')
//                {
//                    echo "yes $query\n";
//                    echo "no " .  $query) . "\n\n";
//                }
            }
        }
        return $mappings;
    }

    private function getLabels($pathways)
    {
        $labels = array();
        $stopLabels = array('pathway', 'protein', 'proteins', 'complex', 'nucleus', 'of proteins', 'cell', 'membrane', 'activation', 'interaction');

        if(!is_array($pathways))
            $pathways = array($pathways);

        foreach($pathways as $pwId)
        {
            $pathway = Pathway::newFromTitle($pwId);
            $pwGPML = $pathway->getGpml();
            $gpml = simplexml_load_string($pwGPML);

            foreach($gpml->Label as $label )
            {
                $attributes = $label->attributes();
                $label = strtolower(trim((string)$attributes->TextLabel));
                $label = str_replace(array("\n",":"), " ", $label);
                if(strlen($label) >= $this->_minLabelLength && !in_array($label, $stopLabels))
                    $labels[] = $label;
            }
        }

        $uniqueLabels = array_unique($labels);
        return $uniqueLabels;
    }

    public function getPathways($lastUpdated = 0, $species = '')
    {
        $pwList = array();

        if($lastUpdated == 0)
        {
            $results = (array)Pathway::getAllPathways($species);
            foreach($results as $pwId => $pwObject)
                $pwList[] = $pwId;
            return $pwList;
        }
        else
        {
            $timestamp = $lastUpdated;
            $dbr =& wfGetDB( DB_SLAVE );
            $forceclause = $dbr->useIndexClause("rc_timestamp");
            $recentchanges = $dbr->tableName( 'recentchanges');

            $sql = "SELECT
                                    rc_namespace,
                                    rc_title,
                                    MAX(rc_timestamp)
                            FROM $recentchanges $forceclause
                            WHERE
                                    rc_namespace = " . NS_PATHWAY . "
                                    AND
                                    rc_timestamp > '$timestamp'
                            GROUP BY rc_title
                            ORDER BY rc_timestamp DESC
                    ";
            $res = $dbr->query( $sql, "getRecentChanges" );

            while ($row = $dbr->fetchRow ($res))
            {
                    try {
                            $ts = $row['rc_title'];
                            $p = Pathway::newFromTitle($ts);
                            if(!$p->getTitleObject()->isRedirect() && $p->isReadable()) {
                                $pwList[] = $ts;
                            }
                    } catch(Exception $e) {
                            $this->addError("Exception thrown for pathway: $ts");
                    }
            }
        }

        return $pwList;
    }

    private function createFiles()
    {
        $this->_logCount = 0;
        $logHeaders = "id\tlabel-count\tpathway-count\tmappings\tstatus\ttimestamp\tcomments\n";
        $logHandle = fopen($this->_logFileName, 'w');
        if(!$logHandle)
        {
            echo "Please set proper permissions for log files!\n";
            exit();
        }
        fwrite($logHandle, $logHeaders);
        fclose($logHandle);
        $errorHandle = fopen($this->_errorFileName, 'w');
        fwrite($errorHandle, "error\ttimestamp");
        fclose($errorHandle);
    }

    public function purge()
    {
        unlink($this->_logFileName);
        unlink($this->_errorFileName);
        $dbr =& wfGetDB( DB_SLAVE );
        $sql = "TRUNCATE TABLE $this->_labelMappingTable";
        $res = $dbr->query($sql);
    }

    private function addLog($status, $labelCount = 0, $pathwayCount = 0, $mappings = 0, $comments = '')
    {
        $timeStamp = date("YmdiHs");
        $this->_logCount++;

        $log = "$this->_logCount\t$labelCount\t$pathwayCount\t$mappings\t$status\t$timeStamp\t$comments\t\n";
        $logHandle = fopen($this->_logFileName, 'a');
        if(!$logHandle)
        {
            echo "Please set proper permissions for log files!\n";
            exit();
        }
        fwrite($logHandle, $log);
        fclose($logHandle);
    }

    private function addError($error)
    {
        $timeStamp = date("YmdiHs");
        $errorHandle = fopen($this->_errorFileName, 'a');
        if(!$errorHandle)
        {
            echo "Please set proper permissions for log files!\n";
            exit();
        }
        $error = "$error\t$timeStamp";
        fwrite($errorHandle, $error);
        fclose($errorHandle);
    }

}