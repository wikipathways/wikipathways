/* Fix to make DOMParser work under IE */
if (typeof DOMParser == "undefined") {
   DOMParser = function () {}

   DOMParser.prototype.parseFromString = function (str, contentType) {
      if (typeof ActiveXObject != "undefined") {
         var d = new ActiveXObject("MSXML.DomDocument");
         d.loadXML(str);
         return d;
      } else if (typeof XMLHttpRequest != "undefined") {
         var req = new XMLHttpRequest;
         req.open("GET", "data:" + (contentType || "application/xml") +
                         ";charset=utf-8," + encodeURIComponent(str), false);
         if (req.overrideMimeType) {
            req.overrideMimeType(contentType);
         }
         req.send(null);
         return req.responseXML;
      }
   }
}
/* End of fix */

var CurationTags = {};

/**
 * Stores a div for each tag, used to display
 * the tag information
 */
CurationTags.tagElements = new Array();

/**
 * Stores tag definitions for available tags
 */
CurationTags.tagDefinitions = {};
/**
 * Stores tag values for available tags
 */
CurationTags.tagData = {};

CurationTags.insertDiv = function(elementId, pageId) {
	//Set the innerHTML of the given elementId to display the curation tags panel
	CurationTags.pageId = pageId;
	
	CurationTags.rootDiv = document.getElementById(elementId);
	CurationTags.rootDiv.className = "tagroot";
	CurationTags.rootDiv.innerHTML = ""; //Clear all existing content of the div
	
	//Container for hide link
	CurationTags.hideDiv = document.createElement("div");
	CurationTags.hideDiv.id = "tagHide"
	CurationTags.hideLink = document.createElement("a");
	CurationTags.hideLink.href = "javascript:CurationTags.toggleHide();";
	CurationTags.hideLink.innerHTML = "hide";
	CurationTags.hideDiv.appendChild(CurationTags.hideLink);
	CurationTags.hideDiv.className = "taghide";
	CurationTags.rootDiv.appendChild(CurationTags.hideDiv);
	
	//Container for content
	CurationTags.contentDiv = document.createElement("div");
	CurationTags.contentDiv.id = "tagContent";
	CurationTags.contentDiv.className = "tagcontent";
	CurationTags.rootDiv.appendChild(CurationTags.contentDiv);
	
	//Container to display tags
	CurationTags.displayDiv = document.createElement("div");
	CurationTags.displayDiv.id = "tagDisplay";
	CurationTags.contentDiv.appendChild(CurationTags.displayDiv);
	
	//Container to edit tags
	CurationTags.editDiv = document.createElement("div");
	CurationTags.editDiv.id = "tagEdit";
	CurationTags.editDiv.className = "tagedit";
	CurationTags.editDiv.style.display = "none";
	CurationTags.contentDiv.appendChild(CurationTags.editDiv);
	
	//Container to display toolbar
	CurationTags.toolDiv = document.createElement("div");
	CurationTags.toolDiv.className = "tagtool";
	CurationTags.contentDiv.appendChild(CurationTags.toolDiv);
	CurationTags.createToolDivContent();
	
	//Overlay div to show errors
	CurationTags.errorDiv = document.createElement("div");
	CurationTags.errorDiv.className = "tagoverlay tagerror";
	CurationTags.contentDiv.appendChild(CurationTags.errorDiv);
	
	//Overlay div to show progress
	CurationTags.progressDiv = document.createElement("div");
	CurationTags.progressDiv.className = "tagoverlay tagprogress";
	CurationTags.contentDiv.appendChild(CurationTags.progressDiv);
	
	CurationTags.refreshNoTagsMsg();

	CurationTags.showProgress();
	CurationTags.loadAvailableTags();
	CurationTags.refresh();
}

CurationTags.toggleHide = function() {
	if(CurationTags.contentDiv.style.display == "none") {
		CurationTags.hideLink.innerHTML = "hide";
		CurationTags.contentDiv.style.display = "";
	} else {
		CurationTags.contentDiv.style.display = "none";
		CurationTags.hideLink.innerHTML = "show";
	}
}

CurationTags.loadAvailableTags = function() {
	CurationTags.showProgress();
	sajax_do_call(
		"CurationTagsAjax::getAvailableTags",
		[],
		CurationTags.loadAvailableTagsCallback
	);
}

CurationTags.loadAvailableTagsCallback = function(xhr) {
	if(CurationTags.checkResponse(xhr)) {
		var xml = CurationTags.getRequestXML(xhr);
		var nodes = xml.getElementsByTagName("Tag");
		
		var rtn = 0;
		var gtn = 0;
		
		for(i=0;i<nodes.length;i++) {
			var n = nodes[i];
			var revision = n.getAttribute("useRevision");
			var tagd = {};
			tagd.name = n.getAttribute("name");;
			tagd.displayName = n.getAttribute("displayName");
			revision == "true" ? tagd.revision = true : tagd.revision = false;
			CurationTags.tagDefinitions[tagd.name] = tagd;
		}
	} else {
		CurationTags.showError("Unable to load available tags");
	}
}

CurationTags.createToolDivContent = function() {
	var helpTag = "<TD><A title='Show help page' " +
		"href='" + CurationTags.helpLink + "' target='_blank'>" +
		"<IMG src='" + CurationTags.extensionPath + "/help.png'/></A>";
		
	var newTag = "";
	var newRevisionTag = "";
	
	if(CurationTags.mayEdit) {
		newTag = "<TD><A title='New tag' " +
		"href='javascript:CurationTags.newTag()'>" +
		"<IMG src='" + CurationTags.extensionPath + "/new.png'/></A>";
	}
	CurationTags.toolDiv.innerHTML = 
	"<TABLE><TR>" + newTag + helpTag +
	"</TABLE>";
}

CurationTags.newTag = function() {
	CurationTags.showEditDiv();
}

CurationTags.applyNewTag = function() {
	var nameBox = document.getElementById("tag_name");
	var textBox = document.getElementById("tag_text");
	
	if(!nameBox || !textBox) {
		CurationTags.showError("Couldn't find edit elements");
		return;
	}
	
	var tagName = nameBox.value;
	var tagText = textBox.value;
	var tagRev = "";
	
	var tagDef = CurationTags.tagDefinitions[tagName];
	if(tagDef && tagDef.revision) {
		tagRev = CurationTags.pageRevision;
	}
		
	if(!tagName) {
		CurationTags.showError("Tag name is empty");
		return;
	}
	
	CurationTags.saveTag(tagName, tagText, tagRev);
	
	CurationTags.showProgress();
}

CurationTags.cancelNewTag = function() {
	CurationTags.hideEditDiv();
}

CurationTags.removeTag = function(tagName) {
	CurationTags.showProgress();
	sajax_do_call(
		"CurationTagsAjax::removeTag",
		[tagName, CurationTags.pageId],
		CurationTags.removeTagCallback
	);
}

CurationTags.removeTagCallback = function(xhr) {
	if(CurationTags.checkResponse(xhr)) {
		var xml = CurationTags.getRequestXML(xhr);
		var tagName = xml.documentElement.firstChild.nodeValue;
		var elm = CurationTags.tagElements[tagName];
		if(elm) {
			CurationTags.displayDiv.removeChild(elm);
			CurationTags.tagElements[tagName] = false;
			CurationTags.tagData[tagName] = false;
		} else {
			CurationTags.showError("Element for tag '" + tagName + "' not found");
		}
	}
	CurationTags.hideProgress();
	CurationTags.refreshNoTagsMsg();
}

CurationTags.saveTag = function(tagName, tagText, tagRev) {
	sajax_do_call(
		"CurationTagsAjax::saveTag", 
		[tagName, CurationTags.pageId, tagText, tagRev], 
		CurationTags.saveTagCallback
	);
}

CurationTags.saveTagCallback = function(xhr) {
	CurationTags.hideProgress();
	if(CurationTags.checkResponse(xhr)) {
		var xml = CurationTags.getRequestXML(xhr);
		var tagName = xml.documentElement.firstChild.nodeValue;
		CurationTags.hideEditDiv();
		CurationTags.refresh(tagName);
	}
}

CurationTags.hideEditDiv = function() {
	CurationTags.editDiv.innerHTML = "";
	CurationTags.editDiv.style.display = "none";
}

CurationTags.showEditDiv = function(tagName) {
	//Remove old edit content
	CurationTags.hideEditDiv();
	
	var select = "<SELECT id='tag_name'>";
	select += "<OPTION value=''></OPTION>";
	
	var tagData = CurationTags.tagData[tagName];
	var tagText = "";
	if(tagData) {
		tagText = tagData.text;
	}
	
	var tagDefArray = CurationTags.tagDefinitions;
	var currTagDef = false;
	
	for(tn in tagDefArray) {
		var tagDef = tagDefArray[tn];
		var value = "value='" + tagDef.name + "'";
		var selected = "";
		if(String(tagDef.name) == String(tagName)) {
			selected = "selected='true'";
			currTagDef = tagDef;
		}
		var option = "<OPTION " + selected + value + ">" + 
			tagDef.displayName + "</OPTION>";
		select += option;
	}
	select += "</SELECT>";
	
	var useRev = currTagDef && currTagDef.revision;
		
	var nameBox = "<TR><TD>Select tag:<TD>" + select;
		
	var textBox = "<TR><TD>Tag text:" +
		"<TD><textarea id='tag_text' cols='40' rows='5'>" + tagText + "</textarea>";
	
	var buttons = "<TR align='right'><TD colspan='2'>" +
		"<A href='javascript:CurationTags.applyNewTag()' title='Apply'>" +
		"<IMG src='" + CurationTags.extensionPath + "/apply.png'/></A>" +
		"<A href='javascript:CurationTags.cancelNewTag()' title='Cancel'>" +
		"<IMG src='" + CurationTags.extensionPath + "/cancel.png'/></A>";
	var html = "<TABLE>" + nameBox + textBox + buttons +"</TABLE>";
	CurationTags.editDiv.innerHTML = html;
	CurationTags.editDiv.style.display = "";
	
	document.getElementById("tag_name").onchange = CurationTags.refreshEditValues; 
}

CurationTags.refreshEditValues = function() {
	var select = document.getElementById("tag_name");
	if(select) {
		var tagName = select.value;
		var textBox = document.getElementById("tag_text");
		if(textBox) {
			var tagd = CurationTags.tagData[tagName];
			if(tagd) {
				textBox.value = tagd.text;
			} else {
				textBox.value = "";
			}
		} else {
			textBox.value = "";
		}
	}
}

/**
 * Reloads the information for the given tag.
 * If tagName is null, all tags will be refreshed.
 */
CurationTags.refresh = function(tagName) {
	if(tagName) {
		CurationTags.loadTag(tagName);
	} else {
		CurationTags.displayDiv.innerHTML = "";
		CurationTags.loadTags();
	}
}

CurationTags.loadTags = function() {
	//Reset the tags display
	CurationTags.displayDiv.innerHTML = "";
	CurationTags.tagElements = new Array();
	
	//Get all tags and their info by calling the AJAX functions
	sajax_do_call(
		"CurationTagsAjax::getTagNames", 
		[CurationTags.pageId], 
		CurationTags.loadTagsCallback
	);
}

CurationTags.loadTagsCallback = function(xhr) {
	if(CurationTags.checkResponse(xhr)) {
		var xml = CurationTags.getRequestXML(xhr);
		var nodes = xml.documentElement.childNodes;
		
		for(i=0;i<nodes.length;i++) {
			CurationTags.loadTag(nodes[i].firstChild.nodeValue);
		}
		CurationTags.refreshNoTagsMsg();
	}
	CurationTags.hideProgress();
}

CurationTags.loadTag = function(tagName) {
	sajax_do_call(
		"CurationTagsAjax::getTagData",
		[tagName, CurationTags.pageId],
		CurationTags.loadTagCallback
	);
}

CurationTags.loadTagCallback = function(xhr) {
	if (CurationTags.checkResponse(xhr)){
		var xml = CurationTags.getRequestXML(xhr);
		var tagName = xml.documentElement.getAttribute("name");
		var tagRevision = xml.documentElement.getAttribute("revision");
		
		if(!tagRevision) {
			tagRevision = false;
		}

		var root = xml.documentElement;
		
		var html = root.getElementsByTagName("Html")[0].firstChild.nodeValue;
		var tagText = "";
		var textNode = root.getElementsByTagName("Text")[0];
		if(textNode.hasChildNodes()) {
			tagText = textNode.firstChild.nodeValue;
		}
		
		var elm = CurationTags.tagElements[tagName];
		var tagContent = document.getElementById("tagContent_" + tagName);
		
		if(!elm) { //New tag
			var elm = document.createElement("div");
			elm.id = "tagDiv_" + tagName;
			elm.className = "tagcontainer";
			
			//TODO: Only showing buttons on mouseover on tag works great under FF
			//but I can't get it to work under IE7
			//Fix is to make buttons semi-transparent by default, 
			//and solid on mouseover
			elm.onmouseover = function() { CurationTags.showTagButtons(tagName); }
			elm.onmouseout = function() { CurationTags.hideTagButtons(tagName); }
			
			//Store the element in the tagElements array
			CurationTags.tagElements[tagName] = elm;
			//Add to display panel
			CurationTags.displayDiv.appendChild(elm);
			
			if(CurationTags.mayEdit) {
				var btns = document.createElement("div");
				btns.id = "tagBtns_" + tagName;
				btns.className = "tagbuttons transparent";
				remove = "<A title='Remove' " +
					"href='javascript:CurationTags.removeTag(\"" + tagName + "\")'>" +
					"<IMG src='" + CurationTags.extensionPath + "/cancel.png'/></A>";
				edit = "<A title='Edit' " +
					"href='javascript:CurationTags.showEditDiv(\"" + tagName + "\")'>" +
					"<IMG src='" + CurationTags.extensionPath + "/edit.png'/></A>";
				btns.innerHTML = remove + edit;
				elm.appendChild(btns);
			}
			
			tagContent = document.createElement("div");
			tagContent.className = "tagcontents";
			tagContent.id = "tagContent_" + tagName;
			elm.appendChild(tagContent);
		}
		
		tagd = {};
		tagd.name = tagName;
		tagd.revision = tagRevision;
		tagd.text = tagText;
		CurationTags.tagData[tagName] = tagd;
		
		tagContent.innerHTML = html;
		
		CurationTags.refreshNoTagsMsg();
	}
}

CurationTags.showTagButtons = function(tagName) {
	var btn = document.getElementById("tagBtns_" + tagName);
	if(btn) {
		btn.className = "tagbuttons solid";
	}
}

CurationTags.hideTagButtons = function(tagName) {
	var btn = document.getElementById("tagBtns_" + tagName);
	if(btn) {
		btn.className = "tagbuttons transparent";
	}
}

/*
CurationTags.showTagButtons = function(tagName) {
	var btn = document.getElementById("tagBtns_" + tagName);
	if(btn) {
		btn.style.display = "block";
	}
}

CurationTags.hideTagButtons = function(tagName) {
	var btn = document.getElementById("tagBtns_" + tagName);
	if(btn) {
		btn.style.display = "none";
	}
}
*/

CurationTags.refreshNoTagsMsg = function() {
	if(!CurationTags.noTagsMsg) {
		CurationTags.noTagsMsg = document.createElement("div");
		CurationTags.noTagsMsg.id = "notags";
		CurationTags.noTagsMsg.innerHTML = "<P><I>No tags</I></P>";
		CurationTags.displayDiv.appendChild(CurationTags.noTagsMsg);
	} else {
		if(CurationTags.displayDiv.childNodes.length == 0) {
			CurationTags.displayDiv.appendChild(CurationTags.noTagsMsg);
		} else if(CurationTags.displayDiv.childNodes.length == 1) {
			CurationTags.noTagsMsg.style.display = "";
		} else {
			CurationTags.noTagsMsg.style.display = "none";
		}
	}
}

CurationTags.getRequestXML = function(xhr) {
	var text = CurationTags.trim(xhr.responseText);
	return new DOMParser().parseFromString(text,"text/xml");
}

CurationTags.trim = function(str) {
	return str.replace(/^\s+|\s+$/g, '');
}

CurationTags.dom2html = function(elm) {
	var tmp = document.createElement("div");
	tmp.appendChild(elm);
	return tmp.innerHTML;
}

CurationTags.checkResponse = function(xhr) {
	if (xhr.readyState==4){
		if (xhr.status==200) {
			return true;
		} else {
			CurationTags.showError("Error: " + xhr.statusText);
		}
	} else {
		CurationTags.showError("Error: " + xhr.statusText);
	}
}

/**
 * Overlays a progress monitor over the given element.
 */
CurationTags.showProgress = function() {
	CurationTags.progressDiv.style.display = "block";
}

/**
 * Removes the progress monitor from the given element
 */
CurationTags.hideProgress = function() {
	CurationTags.progressDiv.style.display = "none";
}

CurationTags.showError = function(msg) {
	CurationTags.errorDiv.style.display = "block";
	CurationTags.errorDiv.innerHTML = "<p class='tagerror'>" + msg + 
		" - <a href='javascript:CurationTags.hideError();'>close</a></p>";
}

CurationTags.hideError = function() {
	CurationTags.errorDiv.style.display = "none";
	CurationTags.errorDiv.innerHTML = "";
}
