/*
Copyright (c) 2009, Yahoo! Inc.
All rights reserved.

Redistribution and use of this software in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the
      following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of Yahoo! Inc. nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission of Yahoo! Inc.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

var opentag_id = -1;
var div = document.getElementById('ontologyTags');
var otagroot = document.getElementById('ontologyContainer');
var treeRoot = document.getElementById('ontologyTrees');
var save_img = document.getElementById('save_img');
var save_link = document.getElementById('save_link');
var title = wgPageName;
var ontologies = YAHOO.lang.JSON.parse(ontologiesJSON);

for(i=0;i<ontologies.length;i++)
{
    div.innerHTML += "<div id='" + ontologies[i][0] + "'><b>" + ontologies[i][0] + "</b> : </div>";
}
fetchTags();

if(otagloggedIn == 1)
{
    createDOM = function() {

        for(i=0;i<ontologies.length;i++)
        {
            Treediv = document.createElement("span");
            Treediv.id = "ontologyTree" + (i + 1);
            Treediv.className = "ontologyTree";
            treeRoot.appendChild(Treediv);
        }
    } ();

    ontologytree = function() {
        function buildTree() {
            var tree = new Array();
            for (var no=0; no<3; no++) {
                tree[no] = new YAHOO.widget.TreeView("ontologyTree" + (no + 1));
                tree[no].setDynamicLoad(loadNodeData);
                var root = tree[no].getRoot();
                var aConcepts = [ontologies[no][0] + " - " + ontologies[no][1]] ;

                for (var i=0, j=aConcepts.length; i<j; i++) {
                    var tempNode = new YAHOO.widget.TextNode(aConcepts[i], root, false);
                    tempNode.c_id=tempNode.label.substring(tempNode.label.lastIndexOf(" - ")+3,tempNode.label.length);
                    tempNode.label = tempNode.label.substring(0,tempNode.label.lastIndexOf(" - "));
                }
                tree[no].subscribe("labelClick", function(node) {
                    displayTag(node.label,node.c_id,"true");
                    tree[0].destroy();
                    tree[1].destroy();
                    tree[2].destroy();
                    tree = null;
                    YAHOO.util.Event.onDOMReady(ontologytree.init, ontologytree,true);YAHOO.util.Event.onDOMReady(ontologytree.init, ontologytree,true);
                });
                tree[no].draw();
            }
        }

        return {
            init: function() {
                buildTree();
            }
        }
    } ();

function loadNodeData(node, fnLoadComplete)  {

    //Get the node's label and urlencode it; this is the word/s
    //on which we'll search for related words:
    // encodeURI(node.label);
    var ontology_id = getOntologyId(0,node.c_id);
    var sUrl = opath + "/otags.php?action=tree&tagId=" + encodeURI(node.c_id);
    var callback = {
        success: function(oResponse) {
            var oResults = YAHOO.lang.JSON.parse(oResponse.responseText);
            if((oResults.ResultSet.Result) && (oResults.ResultSet.Result.length)) {
                if(YAHOO.lang.isArray(oResults.ResultSet.Result)) {
                    for (var i=0, j=oResults.ResultSet.Result.length; i<j; i++) {

                        var tempNode = new YAHOO.widget.MenuNode(oResults.ResultSet.Result[i], node, false);
                        tempNode.c_id=tempNode.label.substring(tempNode.label.lastIndexOf(" - ")+3,tempNode.label.length);
                        if(tempNode.label.lastIndexOf("||")>0)
                        {
                            tempNode.isLeaf = true;
                            tempNode.c_id = tempNode.c_id.replace("||","");
                        }
                        tempNode.label = tempNode.label.substring(0,tempNode.label.lastIndexOf(" - "));

                    }
                }
            }
            oResponse.argument.fnLoadComplete();
        },

        failure: function(oResponse) {
            oResponse.argument.fnLoadComplete();
        },

        argument: {
            "node": node,
            "fnLoadComplete": fnLoadComplete
        },

        //timeout -- if more than 7 seconds go by, we'll abort
        //the transaction and assume there are no children:
        timeout: 13000
    };

    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
}

    ontologySearch = function () {
        var oDS = new YAHOO.util.XHRDataSource( opath + "/otags.php");
        // Set the responseType
        oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
        // Define the schema of the JSON results
        oDS.responseSchema = {
            resultsList : "ResultSet.Result",
            fields : ["label","id","ontology"]
        };
        oDS.maxCacheEntries = 15;
        // Instantiate the AutoComplete
        var oAC = new YAHOO.widget.AutoComplete("myInput", "myContainer", oDS);
        // Throttle requests sent
        oAC.queryDelay = 0.2;
        oAC.minQueryLength = 3;
        oAC.useShadow = true;
        oAC.prehighlightClassName = "yui-ac-prehighlight";

        // The webservice needs additional parameters
        oAC.generateRequest = function(sQuery) {
            return "?action=search&searchTerm=" + sQuery ;
        };

        oAC.resultTypeList = false;
        // Customize formatter to show thumbnail images
        oAC.formatResult = function(oResultData, sQuery, sResultMatch) {

            if(oResultData.label == "No results !")
                return  "<em>" + oResultData.label + "</em>";
            else
                return  "<em>" + oResultData.label + "</em><br />" + oResultData.ontology ;
        };

        var itemSelectHandler = function(sType, aArgs) {
            var oData = aArgs[2]; // object literal of data for the result
            if(oData.label == "No results !")
            {
            }
            else
            {
                displayTag(oData.label,oData.id,"true");
            }
        };

        oAC.itemSelectEvent.subscribe(itemSelectHandler);

        return {
            oDS: oDS,
            oAC: oAC
        };
    }();
}

function getOntologyName(tag_id)
{
    for(i=0;i<ontologies.length;i++)
    {
        if(tag_id.substring(0,2) == ontologies[i][1].substring(0,2))
        {
            ontology_name = ontologies[i][0];
            break;
        }
    }
    return(ontology_name);
}

function getOntologyId(type,tag_id)
{
    if(type == "version")
        for(i=0;i<ontologies.length;i++)
        {
            if(tag_id.substring(0,2) == ontologies[i][1].substring(0,2))
            {
                ontology_id = ontologies[i][3];
                break;
            }
        }
    else
        for(i=0;i<ontologies.length;i++)
        {
            if(tag_id.substring(0,2) == ontologies[i][1].substring(0,2))
            {
                ontology_id = ontologies[i][2];
                break;
            }
        }
    return ontology_id;
}


function removeTag(conceptId)
{
    disableSave();
    var rand = Math.random();

    var handleSuccess = function(o){
        enableSave();
        if(o.responseText != "SUCCESS"){
            alert("Sorry the tag cannot be deleted! Please try again!");
        }
        else
        {
            document.getElementById(conceptId).style.display = "none";
        }
    };

    var handleFailure = function(o){
        alert("Sorry the tag cannot be deleted! Please try again!");
    };

    var callback =
    {
        success:handleSuccess,
        failure:handleFailure,
        argument:['foo','bar']
    };

    var postData = "action=remove" + "&title=" + wgTitle +"&rand=" + rand + "&tagId=" + conceptId ;
    var request = YAHOO.util.Connect.asyncRequest('POST', opath + "/otags.php", callback, postData);
}
function addTag(concept, conceptId)
{
    
    var ontology = getOntologyName(conceptId);
    var rand = Math.random();
    disableSave();

    if(document.getElementById(ontology).innerHTML.indexOf(conceptId)>0)
    {
        document.getElementById('ontologyTagDisplay').innerHTML = "<div class='otag'><font color='red'>Error : The pathway is already tagged with this term !</font><br><a title='Close' href='javascript:closeTag();'><img src='" + opath + "/img/cancel.png' /></a><br></div>";
        return;
    }
    
    var handleSuccess = function(o){
        enableSave();
        if(o.responseText != "SUCCESS"){
            alert("Sorry the tag cannot be added! Please try again!");
        }
        else
        {
            document.getElementById(ontology_name).innerHTML += " <a class='ontologyTag' href='javascript:displayTag(\"" + concept + "\",\"" + conceptId + "\");' id=\"" + conceptId + "\">" + concept + "</a> ";
        }
    };

    var handleFailure = function(o){
        alert("Sorry the tag cannot be added! Please try again!");
    };

    var callback =
    {
        success:handleSuccess,
        failure:handleFailure,
        argument:['foo','bar']
    };

    var postData = "action=add" + "&title=" + wgTitle +"&rand=" + rand + "&tagId=" + conceptId + "&tag=" + concept;
    var request = YAHOO.util.Connect.asyncRequest('POST', opath + "/otags.php", callback, postData);
}

function fetchTags()
{
    var rand = Math.random();
    var handleSuccess = function(o){
        if(o.responseText != "SUCCESS"){

            var tagsJSON = YAHOO.lang.JSON.parse(o.responseText);
            var tags = tagsJSON.Resultset;

            for(i=0;i<tags.length;i++)
            {
                var ontologyName = tags[i].ontology;
                var concept = tags[i].term;
                var conceptId = tags[i].term_id;
                document.getElementById(ontologyName).innerHTML += " <a  class='ontologyTag' href='javascript:displayTag(\"" + concept + "\",\"" + conceptId + "\");' id=\"" + conceptId + "\">" + concept + "</a> ";
            }
        }
    };

    var handleFailure = function(o){
        alert("Sorry the tag cannot be fetched! Please try again!");
    };

    var callback =
    {
        success:handleSuccess,
        failure:handleFailure,
        argument:['foo','bar']
    };

    var postData = "action=fetch" + "&title=" + wgTitle +"&rand=" + rand  ;
    var request = YAHOO.util.Connect.asyncRequest('POST', opath + "/otags.php", callback, postData);
//    makeRequest("Deleted tag : " + tags[index][0] + " (" + ontology_name + ")");
}

function displayTag(concept, conceptId, newTag)
{
    if(opentag_id != conceptId)
    {
        ontology_version_id = getOntologyId("version",conceptId);
        var output = " ";
        var url = "http://bioportal.bioontology.org/visualize/" + ontology_version_id + "/" + conceptId;
        output="<div class='otag'><b>Term</b> : " + concept + "<br/><b>ID</b> : " + conceptId + "<br/>"
        + "<a href='" + url + "'  title='View more Info on BioPortal !' target='_blank'><img src='" + opath + "/img/info.png'></a>&nbsp;"

        if(otagloggedIn == 1)
            if(newTag == "true")
            {
                output += "<a title='Add' href='javascript:addTag(\"" + concept +  "\",\""+conceptId + "\");'><img src='" + opath + "/img/apply.png' /></a>&nbsp;";
                output += "<a title='Close' href='javascript:closeTag();'><img src='" + opath + "/img/cancel.png' /></a><br></div>";
            }
            else
            {
                output += "<a title='Close' href='javascript:closeTag();'><img src='" + opath + "/img/apply.png' /></a>&nbsp;";
                output += "<a title='Remove' href='javascript:removeTag(\"" + conceptId +  "\");'><img src='" + opath + "/img/cancel.png' /></a><br></div>";
            }
        opentag_id = conceptId;
    }
    else
    {
        var output = "<br>";
        opentag_id = -1;
    }
    document.getElementById('ontologyTagDisplay').innerHTML = output;
}

function closeTag()
{
    opentag_id = -1;
    document.getElementById('ontologyTagDisplay').innerHTML = "<br>";
    clearBox();
}


function clearBox()
{
    document.getElementById('myInput').value='';
}

function enableSave(opacity)
{
    if(opacity == null)
        opacity = 20;
    document.getElementById('otagprogress').style.display = "none";

}

function disableSave(opacity)
{
    closeTag();
    document.getElementById('otagprogress').style.display = "block";

}