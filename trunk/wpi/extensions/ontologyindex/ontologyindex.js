var server_url = wgServer + wgScript + "/Special:Ontology_Index?mode=";
var ontologies = eval('(' + ontologiesJSON + ')');
var last_select = "None";
var filter = "All";
var lastSelectedFilter = filter;
var species =  "Homo sapiens";
var last_select_species = species;
var top_level_terms = new Array(3);


addOnloadHook(
    function () {

    var containerDiv = document.getElementById("index_container");

    containerDiv.innerHTML = "<div class='btns'>" +
        "<font size='4'><a class='btn' href='" + server_url +"list'><span id='listMode'>List</span></a> <a class='btn' href='" + server_url +"image'><span id='imageMode'>Image</span></a> <a class='btn' href='" + server_url +"tree'><span id='treeMode'>Tree</span></a></font>" +
        "</div>";

    if(page_mode != 'tree')
    {
        containerDiv.innerHTML +=
            "<div id='index_mode'>" +
            "<b>Sort by :</b> " + "<a id='All' style='color: #FF0000;font-weight:bold;' onClick='setFilter(\"All\");'> Alphabetical</a> " + " | " + "<a id='Edited' onClick='setFilter(\"Edited\");'>Most Edited</a> | <a id='Popular' onClick='setFilter(\"Popular\");'>Most Viewed</a> | <a id='last_edited' onClick='setFilter(\"last_edited\");'>Last Edited</a>" +
            "</div>";

        if(YAHOO.util.Cookie.get('sort') != "" && YAHOO.util.Cookie.get('sort') != null)
        {
            var sort = YAHOO.util.Cookie.get('sort');
            setFilter(sort,"true");
        }
    }

    containerDiv.innerHTML +=
        "<div id='container_left'>" +
        "<div id='species_list'>Loading...</div>" +
        "<hr style='margin: 5px 0 5px 0;'>" +
        "<div id='ontology_list'>Loading...</div>" +
        "</div>" +
        "<div id='container_right'>" +
        "<div id='pathway_list'></div>" +
        "<div id='treeDiv'>Please select a top level Ontology term !</div>" +
        "</div>" ;

       document.getElementById(page_mode + "Mode").style.color = "#FF0000";
       document.getElementById(page_mode + "Mode").style.fontWeight = "bold";
       initOntologyList();
    }
);

function initOntologyList()
{
   document.getElementById("ontology_list").innerHTML = "<font size='4'><b>Ontologies :</b></font><br>" ;
   getSpecies();
}

function setOntology(root_id,id,get_pathways)
{

           if(root_id != " ")
               {
                    YAHOO.util.Cookie.set("ontology", root_id);
                    YAHOO.util.Cookie.set("ontologyId", id);
               }
           document.getElementById("pathway_list").innerHTML = "";
           if(last_select != null)
                {
                document.getElementById(last_select).style.color = "#002BB8";
                document.getElementById(last_select).style.fontWeight = "normal";
                }

           last_select = id;
           document.getElementById(id).style.color = "#FF0000";
           document.getElementById(id).style.fontWeight = "bold";
           if(get_pathways == "Yes")
                getPathwaysList();

}

function fetchOntologyList(no)
{
    var handleSuccess = function(o){

                    var oResults = YAHOO.lang.JSON.parse(o.responseText);
                    if((oResults.ResultSet.Result) && (oResults.ResultSet.Result.length)) {
                        if(YAHOO.lang.isArray(oResults.ResultSet.Result)) {
                                document.getElementById("ontology_list").innerHTML += "<b>" + ontologies[no][0] + "</b>";
                                for(var j=0; j < oResults.ResultSet.Result.length ; j++)
                                       {
                                        term = oResults.ResultSet.Result[j];
                                        id = term.substring(term.lastIndexOf(" - ")+3,term.length);
                                        id = id.replace("||","");
                                        term = term.substring(0,term.lastIndexOf(" - "));
                                        if(page_mode == 'tree')
                                            document.getElementById("ontology_list").innerHTML += "<ul><li><a  id=" + id + " onClick='createTree(\"" + term + " - " + id + "\",\"" + id + "\");'>" + term + '</a></li></ul>';
                                        else
                                            document.getElementById("ontology_list").innerHTML += "<ul><li><a  id=" + id + " onClick='setOntology(\"" + term + " - " + id + "\",\"" + id + "\" ,\"Yes\");'>" + term + '</a></li></ul>';
                                        }
                                document.getElementById("ontology_list").innerHTML += "<br>";
                                if(no < (ontologies.length-1))
                                    {
                                        no++;
                                        fetchOntologyList(no);
                                    }
                                else
                                    {
                                        if(page_mode == 'tree')
                                        {
                                            document.getElementById(last_select).style.color = "#FF0000";
                                            document.getElementById(last_select).style.fontWeight = "bold";
                                        }
                                        else
                                        {
                                            if(YAHOO.util.Cookie.get('ontologyId') != "" && YAHOO.util.Cookie.get('ontologyId') != null)
                                            {
                                                last_select = YAHOO.util.Cookie.get('ontologyId');
                                                setOntology(YAHOO.util.Cookie.get('ontology'),YAHOO.util.Cookie.get('ontologyId'),"No");
                                            }
                                            else
                                                setOntology(" ",last_select,"No");
                                            getPathwaysList();
                                        }
                                    }
                        }

	}

}
var handleFailure = function(o){
	if(o.responseText !== undefined){
		div.innerHTML = "<ul><li>Transaction id: " + o.tId + "</li>";
		div.innerHTML += "<li>HTTP status: " + o.status + "</li>";
		div.innerHTML += "<li>Status code message: " + o.statusText + "</li></ul>";
	}
}

var callback =
{
  success:handleSuccess,
  failure:handleFailure,
  argument: { foo:"foo", bar:"bar" }
};
    var sUrl = opath + "/ontologyindexscript.php?action=tree&mode=sidebar&ontology_id=" + ontologies[no][2] + "&concept_id=" + encodeURI(ontologies[no][1]) + "&species=" + species;
    var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);

}

if(page_mode == 'tree')
{
    function initTree() {
            var tree;
            tree = new YAHOO.widget.TreeView("treeDiv");

            tree.setDynamicLoad(loadNodeData);

            var root = tree.getRoot();
            if(YAHOO.util.Cookie.get("ontology") != "" && YAHOO.util.Cookie.get("ontology") != null)
                {
                    var ontologyRoot = YAHOO.util.Cookie.get("ontology").substr(0,YAHOO.util.Cookie.get("ontology").indexOf(" (")) + YAHOO.util.Cookie.get("ontology").substr(YAHOO.util.Cookie.get("ontology").indexOf(")")+1);

                    var tmpNode = new YAHOO.widget.TextNode(ontologyRoot , root, true);
                    last_select = YAHOO.util.Cookie.get("ontologyId");
                }
            else
                {
                    var tmpNode = new YAHOO.widget.TextNode("classic metabolic pathway - PW:0000002", root, true);
                    last_select = "PW:0000002";
                }
            tmpNode.c_id = tmpNode.label.substring(tmpNode.label.lastIndexOf(" - ")+3,tmpNode.label.length);
            tmpNode.label = tmpNode.label.substring(0,tmpNode.label.lastIndexOf(" - "));
            tree.subscribe("labelClick", function(node) {
               if(node.c_id.lastIndexOf("0000a")>0)
                   {
                        var pw_url = node.c_id.replace("0000a","");
                        winRef = window.open( wgServer + wgScript + "/Pathway:" + pw_url ,node.label);
                   }
           })
            tree.draw();

        return {
            init: function() {
                initTree();
            }
        }
    }
}

function getSpecies()
{
    var handleSuccess = function(o){
    var result = YAHOO.lang.JSON.parse(o.responseText);
    output = "<li>" + "<a id='All Species' onClick='setSpecies(\"All Species\");'>" + "All Species" + '</a>' + "</li>";
    for(var j=0; j<result.length; j++)
        {
            output += "<li>" + "<a id='" + result[j] + "' onClick='setSpecies(\"" + result[j] + "\");'>" + result[j] + '</a>' + "</li>";
        }
    document.getElementById("species_list").innerHTML = "<font size='4'><b>Species :</b></font><ul>" + output + "</ul>";
    if(YAHOO.util.Cookie.get("species") != "" && YAHOO.util.Cookie.get("species") != null)
        setSpecies(YAHOO.util.Cookie.get("species"));
    else
        setSpecies("Homo sapiens");
}

var handleFailure = function(o){

	}

var callback =
{
  success:handleSuccess,
  failure:handleFailure,
  argument: { foo:"foo", bar:"bar" }
};
    var sUrl = opath + "/ontologyindexscript.php?action=species";
    document.getElementById("species_list").innerHTML = "Loading...";
    var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);

}

function setSpecies(specie)
{
        species = specie;
        if( last_select_species != null)
            {
                document.getElementById(last_select_species).style.color = "#002BB8";
                document.getElementById(last_select_species).style.fontWeight = "normal";
            }
           document.getElementById(specie).style.color = "#FF0000";
           document.getElementById(specie).style.fontWeight = "bold";
           last_select_species = specie;
           YAHOO.util.Cookie.set("species", species);
           document.getElementById("ontology_list").innerHTML = "<font size='4'><b>Ontologies :</b></font><br>" ;
           document.getElementById("pathway_list").innerHTML = "";
           if(page_mode == 'tree')
               initTree();
           else
               document.getElementById("ontology_list").innerHTML += "<a id='None' onClick='setOntology(\"\",\"None\",\"Yes\");'>None</a><br />";
           fetchOntologyList(0);
}

function getPathwaysList()
{

    var handleSuccess = function(o){
    if(o.responseText != " ")
        document.getElementById("treeDiv").innerHTML = "<ul>" + o.responseText + "</ul>";
    else
        document.getElementById("treeDiv").innerHTML = "No pathways found !";
}

var handleFailure = function(o){
	if(o.responseText !== undefined){
		div.innerHTML = "<ul><li>Transaction id: " + o.tId + "</li>";
		div.innerHTML += "<li>HTTP status: " + o.status + "</li>";
		div.innerHTML += "<li>Status code message: " + o.statusText + "</li></ul>";
	}
}

var callback =
{
  success:handleSuccess,
  failure:handleFailure,
  argument: { foo:"foo", bar:"bar" }
};

    if(last_select != "None")
        var sUrl = opath + "/ontologyindexscript.php?filter=" + filter + "&action=" + page_mode + "&species="+ species + "&term=" + last_select;
    else
        var sUrl = opath + "/ontologyindexscript.php?filter=" + filter + "&action=" + page_mode + "&species="+ species + "&term=";
    document.getElementById("treeDiv").innerHTML = "Loading...";
    var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);

}

function createTree(root_id,id)
{
           var tree;
           tree = new YAHOO.widget.TreeView("treeDiv");
           tree.setDynamicLoad(loadNodeData);
           var root = tree.getRoot();
           var aConcepts = root_id ;
           document.getElementById("pathway_list").innerHTML = "";
           if(last_select != null)
                {
                document.getElementById(last_select).style.color = "#002BB8";
                document.getElementById(last_select).style.fontWeight = "normal";
                }

           last_select = id;
           last_select_id = root_id;
           YAHOO.util.Cookie.set("ontology", last_select_id);
           YAHOO.util.Cookie.set("ontologyId", last_select);
           document.getElementById(id).style.color = "#FF0000";
           document.getElementById(id).style.fontWeight = "bold";
           var tempNode = new YAHOO.widget.TextNode(aConcepts, root, tree);
           tempNode.c_id=tempNode.label.substring(tempNode.label.lastIndexOf(" - ")+3,tempNode.label.length);
           tempNode.label = tempNode.label.substring(0,tempNode.label.lastIndexOf(" - "));

           tree.draw();
           tree.subscribe("labelClick", function(node) {
               if(node.c_id.lastIndexOf("0000a")>0)
                   {
                        var pw_url = node.c_id.replace("0000a","");
                        winRef = window.open( wgServer + wgScript + "/Pathway:" + pw_url ,node.label);
                   }
            })

            return {
                init: function() {
                    buildTree(root_id);
                }
            }
}

 function loadNodeData(node, fnLoadComplete)
    {

            //Get the node's label and urlencode it; this is the word/s
            //on which we'll search for related words:
     		// encodeURI(node.label);

            var ontology_id = getOntologyId(node.c_id);
            var sUrl = opath + "/ontologyindexscript.php?tree_pw=yes&action=tree&mode=tree&ontology_id=" + ontology_id + "&concept_id=" + encodeURI(node.c_id) + "&species=" + species;
            var callback = {
                success: function(oResponse) {
                    var oResults = YAHOO.lang.JSON.parse(oResponse.responseText);
                    if((oResults.ResultSet.Result) && (oResults.ResultSet.Result.length)) {
                        if(YAHOO.lang.isArray(oResults.ResultSet.Result)) {
                            for (var i=0, j=oResults.ResultSet.Result.length; i<j; i++) {

                            var tempNode = new YAHOO.widget.MenuNode(oResults.ResultSet.Result[i], node, false);
                            tempNode.c_id=tempNode.label.substring(tempNode.label.lastIndexOf(" - ")+3,tempNode.label.length);

                            if(tempNode.c_id.lastIndexOf("0000a")>0 || tempNode.c_id.lastIndexOf("||")>0)
                                {
                                       tempNode.isLeaf = true;

                                }
                            tempNode.c_id = tempNode.c_id.replace("||","");
                            tempNode.label =    tempNode.label.substring(0,tempNode.label.lastIndexOf(" - "));
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
                timeout: 25000
            };

            YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
    }

function setFilter(filterName,init)
{
    document.getElementById(lastSelectedFilter).style.color = "#002BB8";
    document.getElementById(lastSelectedFilter).style.fontWeight = "normal";
    document.getElementById(filterName).style.color = "#FF0000";
    document.getElementById(filterName).style.fontWeight = "bold";
    YAHOO.util.Cookie.set("sort", filterName);
    lastSelectedFilter = filterName;
    filter = filterName;
    if(init == "" || init == null)
        getPathwaysList();
}

function getOntologyId(tag_id)
{
    for(i=0;i<ontologies.length;i++)
    {
        if(tag_id.substring(0,2) == ontologies[i][1].substring(0,2))
        {
            ontologyId = ontologies[i][2];
            break;
        }
    }
    return ontologyId;
}
