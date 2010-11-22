var SearchPathways = {};

SearchPathways.resultId = "searchResults";
SearchPathways.loadId = "loading";
SearchPathways.errorId = "error";
SearchPathways.moreId = "more";

SearchPathways.currentSearchId = null;
SearchPathways.currentIndex = 0;
SearchPathways.currentResults = [];
SearchPathways.batchSize = 12;
SearchPathways.untilMore = 2;

SearchPathways.doSearch = function() {
	SearchPathways.clearResults();
	SearchPathways.resetIndex();
	SearchPathways.showProgress();
	
	var form = document.getElementById('searchForm');
	var div = document.getElementById(SearchPathways.resultId);
	var query = form.elements['query'].value;
	var species = form.elements['species'].value;
	var ids = form.elements['ids'].value;
	var codes = form.elements['codes'].value;
	var type = form.elements['type'].value;
	
	sajax_do_call(
		"SearchPathwaysAjax::doSearch",
		[query, species, ids, codes, type],
		SearchPathways.processResults
	);
}

SearchPathways.resetIndex = function() {
	SearchPathways.currentSearchId = new Date().getTime();
	SearchPathways.currentIndex = 0;
	SearchPathways.currentResults = [];
}

SearchPathways.processResults = function(xhr) {
	if(SearchPathways.checkResponse(xhr)) {
		var xml = SearchPathways.getRequestXML(xhr);
		var nodes = xml.getElementsByTagName("pathway");
		
		//Collect the results
		for(i=0;i<nodes.length;i++) {
			var n = nodes[i];
			var pw = n.firstChild.nodeValue;
			SearchPathways.currentResults.push(pw);
		}
		
		var div = document.getElementById(SearchPathways.resultId);
		if(nodes.length == 0) {
			div.innerHTML = "<b>No Results</b>";
			SearchPathways.hideProgress();
		} else {
			div.innerHTML = "<div class='resultCounter'><b>" + nodes.length + " pathways found</b></div>";
			//Now load the results in batches
			SearchPathways.loadBatch();
		}
	}
}

SearchPathways.loadBatch = function() {
	var index = SearchPathways.currentIndex;
	var results = SearchPathways.currentResults;
	var size = SearchPathways.batchSize;
	
	if(index >= results.length) {
		SearchPathways.hideProgress();
		return;
	}
	
	var end = Math.min(results.length, index + size);
	var batch = [];
	
	for(i=index;i<end;i++) {
		batch.push(results[i]);
	}
	
	SearchPathways.currentIndex = end;
	sajax_do_call(
		"SearchPathwaysAjax::getResults",
		[batch, SearchPathways.currentSearchId],
		SearchPathways.processBatch
	);
}

SearchPathways.more = function() {
	var div = document.getElementById(SearchPathways.moreId);
	div.innerHTML = "";
	
	SearchPathways.showProgress();
		
	SearchPathways.loadBatch();
}

SearchPathways.all = function() {
	var div = document.getElementById(SearchPathways.moreId);
	div.innerHTML = "";
	
	SearchPathways.untilMore = -1;
	
	SearchPathways.showProgress();
		
	SearchPathways.loadBatch();
}

SearchPathways.processBatch = function(xhr) {
	if(SearchPathways.checkResponse(xhr)) {
		var xml = SearchPathways.getRequestXML(xhr);
		var htmlNode = xml.getElementsByTagName("htmlcontent")[0];
		var sid = xml.getElementsByTagName("searchid")[0];
		sid = sid.firstChild.nodeValue;
		
		if(sid == SearchPathways.currentSearchId) {
			var div = document.getElementById(SearchPathways.resultId);
			div.innerHTML += htmlNode.firstChild.nodeValue;
		
			if(SearchPathways.untilMore > 0 && SearchPathways.currentIndex >= SearchPathways.batchSize * SearchPathways.untilMore) {
			SearchPathways.hideProgress();
			var div = document.getElementById(SearchPathways.moreId);
			div.innerHTML = "";
		
			if(SearchPathways.currentIndex < SearchPathways.currentResults.length) {
				var more = document.createElement("a");
				more.href = "javascript:SearchPathways.all();";
				more.innerHTML = "Show all results";
				div.appendChild(more);
				return;
			}
		}
	
			SearchPathways.loadBatch();
		}
	}
}

SearchPathways.clearResults = function() {
	var div = document.getElementById(SearchPathways.resultId);
	div.innerHTML = "";
}

SearchPathways.showProgress = function() {
	var div = document.getElementById(SearchPathways.loadId);
	div.style.display = "block";
}

SearchPathways.hideProgress = function() {
	var div = document.getElementById(SearchPathways.loadId);
	div.style.display = "none";
}

SearchPathways.checkResponse = function(xhr) {
	if (xhr.readyState==4){
		if (xhr.status==200) {
			return true;
		} else {
			SearchPathways.showError("Error: unable to process search.", xhr.responseText);
		}
	} else {
		SearchPathways.showError("Error: unable to process search.", xhr.responseText);
	}
}

SearchPathways.showError = function(e, details) {
	SearchPathways.hideProgress();
	var div = document.getElementById(SearchPathways.errorId);
	var html = "<B>" + e + "</B>";
	if(details) {
	 html += "<PRE>" + details + "</PRE>";
	}
	div.innerHTML = html;
}

SearchPathways.getRequestXML = function(xhr) {
	var text = xhr.responseText.replace(/^\s+|\s+$/g, '');
	return SearchPathways.parseXML(text);
}

SearchPathways.parseXML = function(xml) {
	var xmlDoc = null;
	if (window.DOMParser) {
		parser = new DOMParser();
		xmlDoc = parser.parseFromString(xml, "text/xml");
	} else { //Internet Explorer
		xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async = "false";
		xmlDoc.loadXML(xml);
	}
	return xmlDoc;
}
