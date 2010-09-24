<?php

class GraphMLConverter
{
    public $_graphML;
    private $_addedPathways;
    private $_relationsData;

    public function __construct($relationsData)
    {
        $this->_graphML = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                                                    <graphml xmlns="http://graphml.graphdrawing.org/xmlns"  
                                                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                                    xsi:schemaLocation="http://graphml.graphdrawing.org/xmlns/1.0/graphml.xsd">
                                                    </graphml>');
        $this->_addedPathways = array();
        $this->_relationsData = $relationsData;
        $this->addKeys();
        $this->addProperties();
        $this->addData();
    }

    private function addKeys()
    {
        $keys = array(
            array( 'name' => 'name', 'type' => 'string', 'for' => 'node' ),
            array( 'name' => 'pwId', 'type' => 'string', 'for' => 'node' ),
            array( 'name' => 'url', 'type' => 'string', 'for' => 'node' ),
            array( 'name' => 'score', 'type' => 'float', 'for' => 'edge' ),
        );

        foreach($keys as $key)
        {
            $keyElement = $this->_graphML->addChild("key");
            $keyElement->addAttribute("id", $key['name']);
            $keyElement->addAttribute("for",$key['for']);
            $keyElement->addAttribute("attr.name", $key['name']);
            $keyElement->addAttribute("attr.type", $key['type']);
        }
    }

    private function addNode($pwId)
    {
        if(!array_key_exists($pwId, $this->_addedPathways))
        {
            $nodeId = count($this->_addedPathways);
            $this->_addedPathways[$pwId]['id'] = $nodeId;

            $node = $this->_graphML->addChild("node");
            $node->addAttribute("id", $nodeId);

            $nodeData = $node->addChild("data", $pwId);
            $nodeData->addAttribute("key", "pwId");

            $nodeName = $node->addChild("data", htmlspecialchars(getPathwayName($pwId)));
            $nodeName->addAttribute("key", "name");

            return $nodeId;
        }
        else {
            return $this->_addedPathways[$pwId]['id'];
        }

    }

    private function addEdge($nodeId_1, $nodeId_2, $score)
    {
        $edge = $this->_graphML->addChild("edge");
        
        $edge->addAttribute("source", $nodeId_1);
        $edge->addAttribute("target", $nodeId_2);

        $edgeScore = $edge->addchild("data", (float)$score);
        $edgeScore->addAttribute("key", "score");
    }

    private function addData()
    {
        if(count($this->_relationsData) > 0)
        {
            foreach($this->_relationsData as $relation)
            {
                $pwId_1 = (string) $relation->pwId_1;
                $pwId_2 = (string) $relation->pwId_2;
                $score = (float)$relation->score;

                $nodeId_1 =  $this->addNode($pwId_1);
                $nodeId_2 =  $this->addNode($pwId_2);

                $this->addEdge($nodeId_1, $nodeId_2, $score);
            }
        }
    }

    private function addProperties()
    {
        $graphProperty = $this->_graphML->addChild('graph');
        $graphProperty->addAttribute("edgedefault", "undirected");
    }

    public function getGraphML()
    {
        return $this->_graphML->asXML();
    }

}


function getPathwayName($pwId)
{
    $pw = new Pathway($pwId);
    $pwName = $pw->getName();

    return $pwName;

}


?>