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
}

function getAppletObject(id) {
	for(i=0;i<applets.length;i++) {
		var obj = applets[i];
		if(obj.id == id) {
			return obj;
		}
	}
}

//Uses appletobject.js
function doApplet(idImg, idApplet, basePath, main, width, height, keys, values, noresize, site_url, wgScriptPath) {	
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
	
	problems = document.createElement('a');
	problems.href = site_url + '/index.php?title=Help:Known_problems';
	problems.innerHTML = 'not working?';
	image.appendChild(problems);	

	var appletHTML = getAppletHTML(idApplet, '100%', '100%', main, basePath, 'wikipathways.jar', keys, values);	
	appletObject.appletHTML = appletHTML;
	
	if(!noresize) {
		var $resize = $(image).resizable();
		appletObject.resizeable = $resize;
		
		//Set the align attribute of the parent td element to left
		$(image).parent('td').attr("align", "left");
	}
	
	//ao.load( idApplet );
	setTimeout('addApplet("' + idApplet + '", "' + appletDiv.id + '")', 500);
	
	//Prevent back button / browser close to exit applet
	window.onbeforeunload = checkExit;
}

function checkExit() {
	var applets = document.applets;
	for(key in document.applets) {
		var applet = applets[key];
		try {
			if(!applet.mayExit()) {
				return "Any changes to the pathway will be lost!";
			}
		} catch(err) {
			//Ignore, probably not an applet
		}
	}
}

function javaError() {
	window.alert('Java error! To edit the pathway, please install' +
			' or update Java at http://java.sun.com/javase/downloads');
}

function addApplet(idApplet, idDiv) {
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
	var html = '<APPLET code="' + main + '" codebase="' + base + '" width="100%" height="100%" >' +
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

function getViewportSize() {
	var viewportwidth;
	var viewportheight;

	// the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	if (typeof window.innerWidth != 'undefined')
	{
	     viewportwidth = window.innerWidth;
	     viewportheight = window.innerHeight;
	}
	// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document
	else if (typeof document.documentElement != 'undefined'
	    && typeof document.documentElement.clientWidth !=
	    'undefined' && document.documentElement.clientWidth != 0)
	{
	      viewportwidth = document.documentElement.clientWidth;
	      viewportheight = document.documentElement.clientHeight;
	}
	// older versions of IE
	else
	{
	      viewportwidth = document.getElementsByTagName('body')[0].clientWidth;
	      viewportheight = document.getElementsByTagName('body')[0].clientHeight;
	}
	
	return Array(viewportwidth, viewportheight);
}
 
