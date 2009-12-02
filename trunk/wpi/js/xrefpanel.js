
//TODO: info and linkout section headers?

if (typeof(XrefPanel_dataSourcesUrl) == "undefined") 
    var XrefPanel_dataSourcesUrl = '../../cache/datasources.txt';
if (typeof(XrefPanel_bridgeUrl) == "undefined") 
    var XrefPanel_bridgeUrl = ''; //Disable bridgedb webservice queries if url is not specified
if(typeof(XrefPanel_searchUrl) == "undefined") 
	var XrefPanel_searchUrl = false;
	
/**
 * A panel that displays information for an xref.
 * The xref information is provided by a bridgedb web service.
 */
var XrefPanel = {};

/**
 * Contains the url pattern for each
 * datasource. Will be loaded on document
 * load.
 */
XrefPanel.linkoutPatterns = {};

/**
 * Add hook functions to customize the external
 * references list for an xref (e.g. to add a
 * custom datasource link).
 *
 * The function should take an object as argument
 * where the properties are the datasources which values
 * are an array of identifiers.
 */
XrefPanel.xrefHooks = [];

/**
 * Add an xref hook for Gene Wiki (Wikipedia).
 */
XrefPanel.linkoutPatterns['Gene Wiki'] = 'http://plugins.gnf.org/cgi-bin/wp.cgi?id=$ID'
XrefPanel.xrefHooks.push(function(xrefs){
    if (xrefs['Entrez Gene']) 
        xrefs['Gene Wiki'] = xrefs['Entrez Gene'];
    return xrefs;
});

XrefPanel.loadImage = '<img src="' + PathwayViewer_basePath + '/img/loading_small.gif" />';

/**
 * Add hook functions to customize the info
 * section for an xref.
 *
 * The function should take the id, datasource, symbol and species.
 * as argument and return a jquery element that will
 * be appended to the info section.
 */
XrefPanel.infoHooks = [];

/**
 * Add an info hook for bridgedb properties.
 */
XrefPanel.createInfoCallback = function($div){
    return function(data, textStatus){
        $div.find('img').hide();
        $table = $('<table></table>');
        var lines = data.split("\n");
        for (var i in lines) {
            var cols = lines[i].split("\t");
            if (!cols[0] || !cols[1]) {
                continue;
            }
            $table.append('<tr><td><b>' + cols[0] + ':</b><td>' + cols[1]);
        }
        $div.append($table);
    }
}

XrefPanel.createErrorCallback = function($div, msg){
    return function(hr, errMsg, ex){
        $div.find('img').hide();
        $div.html('<font color="red">' + msg + '</font>');
    }
}

XrefPanel.infoHooks.push(function(id, datasource, symbol, species){
    if (XrefPanel_bridgeUrl) {
        var $div = $('<div id="bridgeInfo">' + XrefPanel.loadImage + '</div>');
        XrefPanel.queryProperties(id, datasource, species, XrefPanel.createInfoCallback($div), 
			XrefPanel.createErrorCallback($div, 'Unable to load info.'));
        return $div;
    } else {
		return false;
	}
});

/**
 * Add an info hook for search.wikipathways.org.
 */
XrefPanel.infoHooks.push(function(id, datasource, symbol, species){
    if (XrefPanel_searchUrl) {
        var url = XrefPanel_searchUrl.replace('$ID', id).replace('$DATASOURCE', datasource);
        return $('<p><a target="_blank" href="' + url + '">Other pathways containing ' + symbol + '...</a></p>')
    }
    return false;
});

/**
 * Add an xref hook for Gene Wiki (Wikipedia).
 */
XrefPanel.linkoutPatterns['Gene Wiki'] = 'http://plugins.gnf.org/cgi-bin/wp.cgi?id=$ID'
XrefPanel.xrefHooks.push(function(xrefs){
    if (xrefs['Entrez Gene']) 
        xrefs['Gene Wiki'] = xrefs['Entrez Gene'];
    return xrefs;
});

/**
 * Keeps dialog content cache, to prevent duplicate
 * requests to the bridgedb webservice.
 */
XrefPanel.dialogCache = {};

XrefPanel.onPageLoad = function(){
    //Load the datasources file
    XrefPanel.loadDataSources();
}

$(window).ready(XrefPanel.onPageLoad);

XrefPanel.getCachedDialog = function(id, datasource, species, symbol){
    return XrefPanel.cacheDialog[id + datasource + species + symbol];
}

XrefPanel.cacheDialog = function(id, datasource, species, symbol, $dialog){
    XrefPanel.cacheDialog[id + datasource + species + symbol] = $dialog;
}

XrefPanel.unCacheDialog = function(id, datasource, species, symbol){
    XrefPanel.cacheDialog[id + datasource + species + symbol] = null;
}

/**
 * Creates a jquery dialog that contains information on the given
 * xref.
 * @param {string} id The xref id
 * @param {string} datasource The xref data source
 * @param {string} species The xref species
 * @param {string} The entity symbol (e.g. 'TP53')
 */
XrefPanel.create = function(id, datasource, species, symbol){
    //Try to use cached version if exists.
    var $dialog = XrefPanel.getCachedDialog(id, datasource, species, symbol);
    if ($dialog) {
        return $dialog;
    }
    
    var maxXrefLines = 5; //Maximum number of xref links to show (scroll otherwise)
    var $contents = $('<div><div id="info" /><div id="xrefs">' + XrefPanel.loadImage + '</div></div>');
    
    $dialog = $contents.dialog({
        autoOpen: false
    });
    
    //Store in cache
    XrefPanel.cacheDialog(id, datasource, species, symbol, $dialog);
    
    //Add the info section
    var $infodiv = $contents.find('#info');
	$infodiv.append('<p><b>Annotated with: </b>' + XrefPanel.createXrefLink(id, datasource, true) + '</p>');
	
    //Run hooks that may add items to the info
    for (h in XrefPanel.infoHooks) {
        var $info = XrefPanel.infoHooks[h](id, datasource, symbol, species);
        if ($info) 
            $infodiv.append($info);
    }
    
    var cbXrefs = function(data, textStatus){
        var $div = $contents.find('#xrefs');
        $div.find('img').hide();
        var xrefs = {};
        var lines = data.split("\n");
        for (var i in lines) {
            var cols = lines[i].split("\t");
            if (typeof cols[1] == 'undefined' || cols[1] == 'null') {
                continue;
            }
            if (!xrefs[cols[1]]) {
                xrefs[cols[1]] = [];
            }
            xrefs[cols[1]].push(cols[0]);
        }
        
        //Run hooks that may modify the xrefs
        for (h in XrefPanel.xrefHooks) {
            xrefs = XrefPanel.xrefHooks[h](xrefs);
        }
        
        //Collect data sources and sort
        var dataSources = [];
        for (ds in xrefs) {
            dataSources.push(ds);
        }
        dataSources.sort();
        
        $accordion = $('<div />');
        for (var dsi in dataSources) {
            var ds = dataSources[dsi];
            var xrefHtml = '<table>';
            for (var i in xrefs[ds]) {
                xrefHtml += '<tr>' + XrefPanel.createXrefLink(xrefs[ds][i], ds, false);
            }
            $accordion.append('<h3><a href="#">' + ds + '</a></h3>');
            var $xdiv = $('<div class="xreflinklist"/>').html(xrefHtml + '</table>');
            if (xrefs[ds].length > maxXrefLines) {
                $xdiv.css({
                    height: maxXrefLines + 'em'
                });
            }
            var $wdiv = $('<div />').css('overflow', 'auto'); //Wrapper to prevent resizing of xref div
            $wdiv.append($xdiv);
            $accordion.append($wdiv);
        }
        $div.append($accordion);
        $accordion.accordion({
            autoHeight: false
        });
    }
    
    XrefPanel.queryXrefs(id, datasource, species, cbXrefs, XrefPanel.createErrorCallback($contents.find('#xrefs'), 'Unable to load linkouts.'));
    return $dialog;
    
}

XrefPanel.createXrefLink = function(id, datasource, withDataSourceLabel){
    var url = XrefPanel.linkoutPatterns[datasource];
	var label = withDataSourceLabel ? id + ' (' + datasource + ')' : id; 
    if (url) {
        url = url.replace('$ID', id);
        return '<a target="_blank" href="' + url + '">' + label + '</a>';
    }
    else {
        console.log("Unable to create link for " + id + ", " + datasource);
        return label;
    }
}

/**
 * Query all xrefs for the given datasource.
 */
XrefPanel.queryXrefs = function(id, datasource, species, success, error){
    if (!XrefPanel_bridgeUrl) 
        return;
    var url = XrefPanel_bridgeUrl + '/' + escape(species) + '/xrefs/' + escape(datasource) + '/' + id;
    $.ajax({
        url: url,
        processData: false,
        success: success,
        error: error
    });
}

/**
 * Query properties for xref
 */
XrefPanel.queryProperties = function(id, datasource, species, success, error){
    if (!XrefPanel_bridgeUrl) 
        return;
    var url = XrefPanel_bridgeUrl + '/' + escape(species) + '/attributes/' + escape(datasource) + '/' + id;
    $.ajax({
        url: url,
        processData: false,
        success: success,
        error: error
    });
}

XrefPanel.loadDataSources = function(){
    var callback = function(data, textStatus){
        //Parse the datasources file and fill the url object
        if (textStatus == 'success' || textStatus == 'notmodified') {
            var lines = data.split("\n");
            for (var l in lines) {
                var cols = lines[l].split("\t", -1);
                if (cols.length > 3 && cols[3]) {
                    XrefPanel.linkoutPatterns[cols[0]] = cols[3];
                }
            }
        }
    }
    $.get(XrefPanel_dataSourcesUrl, {}, callback);
}
