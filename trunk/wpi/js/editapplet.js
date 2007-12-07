var label_maximize = '<img src="/skins/common/images/magnify-clip.png" id="maximize"/>';
var label_minimize = '<img src="/skins/common/images/magnify-clip.png" id="minimize"/>';

var masterApplet = false;
var masterActivated = false;

var appletButtons = [];

/*Array with objects that contain applet information
 - id: the id of the applet 
 - div: the div in which the applet resides
 - resizeable: the resizeable object (see resize.js)
*/
var applets = [];

//Register an applet button. This button
//will be disabled when an edit applet is activated
function registerAppletButton(id, base, keys, values) {
	appletButtons[id] = id;
	if(!masterApplet) {
		masterApplet = getAppletHTML('master', '0', '0', "org.pathvisio.gui.wikipathways.PathwayPageApplet", base, 'wikipathways.jar', keys, values);	
	}
}

function getAppletObject(id) {
	for(i=0;i<applets.length;i++) {
		var obj = applets[i];
		if(obj.id == id) {
			return obj;
		}
	}
}

function activateMasterApplet() {
	if(masterApplet && !masterActivated) {
		var div = document.createElement("div");
		div.id = "masterapplet";
		document.body.appendChild(div);
		div.innerHTML = masterApplet;
		masterActivated = true;
		window.status = "Activated master applet";
	}
}

//Uses appletobject.js
function doApplet(idImg, idApplet, basePath, main, width, height, keys, values, noresize) {	
	var image = document.getElementById(idImg);
	
	appletObject = new Object();
	applets[applets.length] = appletObject;
	appletObject.id = idApplet;	
	
	//Disable all edit buttons
	for(var idButton in appletButtons) {
		var button = document.getElementById(idButton);
		if(button) {
			button.style.display = "none";
		}
	}
	
	if(!width || width <= 0) {
		width = getParentWidth(image);
	}

	image.style.width = width + 'px';
	image.style.height = height;
	//Replace existing content with loading message
	image.innerHTML = '<div style="position:absolute;z-index:0">Loading...</div>';
	setClass(image, 'thumbinner');

	//First create a new div for the applet and add it to the idImg
	appletDiv = document.createElement('div');
	appletObject.div = appletDiv;
	appletDiv.id = idApplet;
	setClass(appletDiv, 'internal');
	appletDiv.style.width = '100%';
	appletDiv.style.height = '95%';
	appletDiv.style.clear = 'both';
	image.appendChild(appletDiv);
		
	//Create maximize div
	maximize = document.createElement('div');
	maximize.innerHTML = createMaximizeButton(idApplet);
	maximize.style.cssFloat = 'center';
	maximize.style.marginBottom = '10px';

	//Create resize hint
	resize = document.createElement('div');
	resize.innerHTML = '<img src="/skins/common/images/resize.png"/>';
	resize.style.position = 'absolute';
	resize.style.bottom = '0';
	resize.style.right = '0';
	image.appendChild(resize);
	image.appendChild(maximize);

	var appletHTML = getAppletHTML(idApplet, '100%', '100%', main, basePath, 'wikipathways.jar', keys, values);	
	appletObject.appletHTML = appletHTML;
	
	if(!noresize) {
		var resizeable = new Resizeable(idImg, {bottom: 10, right: 10, left: 0, top: 0});
		appletObject.resizeable = resizeable;
	}
	
	//ao.load( idApplet );
	setTimeout('addApplet("' + idApplet + '", "' + appletDiv.id + '")', 500);
}

function javaError() {
	window.alert('Java error! To edit the pathway, please install' +
			' or update Java at http://java.sun.com/javase/downloads');
}

function addApplet(idApplet, idDiv) {
	activateMasterApplet();
	var appletObject = getAppletObject(idApplet);
	var div = appletObject.div;
	div.innerHTML += appletObject.appletHTML;
}

function setClass(elm, cname) {
	elm.setAttribute('class', cname);
	elm.setAttribute('className', cname);
}

function getAppletHTML(id, width, height, main, base, archive, keys, values) {
    var params = '';
    if(keys != null && values != null) {
		for(i=0; i < keys.length; i++) {
			params += '<param name="' + keys[i] + '" value="' + values[i] + '"/>';	
		}
	}
	var html = '<APPLET code="' + main + '" codebase="' + base + '" width="100%" height="100%">' +
		params + '<BR><B>To edit the pathway, please download and install Java from: ' +
		'<a href="http://java.sun.com/javase/downloads">http://java.sun.com/javase/downloads</a></b></APPLET>';
	return html;
}

function getParentWidth(elm) {
	var p = findEnclosingTable(elm);
	var w = p.offsetWidth;	
		p.align="";
		return w;
}

function findEnclosingTable(elm) {
	//find getWidth of enclosing element / table
	var parent = elm.parentNode;
	var nn = parent.nodeName.toLowerCase();
	if(nn == 'td' || nn == 'tr' || nn == 'tbody') {
		while(true) {
			if(parent.parentNode == null || parent.nodeName.toLowerCase() == 'table') {
				break;
			} else {
				parent = parent.parentNode;
			}
		}
	}
	if(parent.nodeName.toLowerCase() == 'table') return parent;
	else return elm.parentNode; //Not in a table, just return the parent
}

function replaceElement(elmOld, elmNew) {
	var p = elmOld.parentNode;
	p.insertBefore(elmNew, elmOld);
	p.removeChild(elmOld);
}

var maxImg = '/skins/common/images/magnify-clip.png';

/* Maximize functions
 * TODO: create maximizable class with prototype
 */
function createMaximizeButton(id) {
	return("<a href=\"javascript:toggleMaximize(this, '" + id + "');\"><img src='" + maxImg + "'>Maximize</img></a>");
}

function toggleMaximize(button, id) {
	var obj = getAppletObject(id);
	if(obj) {
		var elm = obj.div.parentNode;
		var globalWrapper = document.getElementById('globalWrapper');
		if(obj.clone) {
			//Remove the clone
			document.body.removeChild(obj.clone);
			obj.clone = false;
			
			//Set the globalwrapper visible
			globalWrapper.style.display = "";

			//Reset the style parameters
			elm.style.position = "relative";
			elm.style.offsetLeft = obj.oldpos[0];
			elm.style.offsetTop = obj.oldpos[1];
			elm.style.width = obj.oldsize[0];
			elm.style.height = obj.oldsize[1];
		} else {		
			//Clone the div
			var clone = elm.cloneNode(true);	
			obj.clone = clone;			
			//Add the div to the root
			obj.oldParent = elm.parentNode;
			document.body.appendChild(clone);
			//Make the globalwrapper invisible
			globalWrapper.style.display = "none";
			
			//Modify the style parameters			
			obj.oldsize = Array(elm.style.width, elm.style.height);
			obj.oldpos = Array(elm.style.offsetLeft, elm.style.offsetTop);
			clone.style.position = "absolute";
			clone.style.width = "99%";
			clone.style.height = "99%";
		}
	}
}

function getViewportSize() {
	var viewportwidth;
	var viewportheight;

	// the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	if (typeof window.innerWidth != 'undefined')
	{
	     viewportwidth = window.innerWidth,
	     viewportheight = window.innerHeight
	}
	// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document
	else if (typeof document.documentElement != 'undefined'
	    && typeof document.documentElement.clientWidth !=
	    'undefined' && document.documentElement.clientWidth != 0)
	{
	      viewportwidth = document.documentElement.clientWidth,
	      viewportheight = document.documentElement.clientHeight
	}
	// older versions of IE
	else
	{
	      viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	      viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
	
	return Array(viewportwidth, viewportheight);
}
 
