//TODO: highlight via url (with mapping?)
//TODO: hyperlink cursor when over clickable object (requires fix for http://code.google.com/p/svgweb/issues/detail?id=493)
//TODO: test immediate start on pathway page (prevent clicking before viewer is loaded)

/**
 * Change this if the base path of the script (and resource files) is
 * different than the page root.
 */
if (typeof(PathwayViewer_basePath) == "undefined") 
    var PathwayViewer_basePath = '';

/**
 * Array with information about pathways to which
 * the viewer should be applied. Add the argument
 * to the PathwayViewer contstructor.
 */
PathwayViewer_pathwayInfo = [];

/**
 After page is ready:
 1. Load the svg objects in the background
 2. Add the buttons for starting the viewer when loading is finished
 */
$(window).ready(function() {
	$.each(PathwayViewer_pathwayInfo, function(i, info) {
	   var viewer = new PathwayViewer(info);
	   PathwayViewer.viewers[info.imageId] = viewer;
	   viewer.loadGPML();
		if(info.start) {
        	viewer.startSVG(info);
      } else {
        	viewer.addStartButton(info);
      }
	});
});

/**
 * Pathway viewer based on Svgweb.
 * Depends on:
 * - Svgweb (http://svgweb.googlecode.com/)
 * - JQuery (http://jquery.com/)
 *
 * Scroll and zoom based on code by Brad Neuberg:
 * http://codinginparadise.org/projects/svgweb-staging/tests/non-licensed/wikipedia/svgzoom/svgzoom.js
 * 
 * Constructor takes a single object containing the necessary
 * the information for a pathway. The object should contain the following
 * fields:
 * - imageId, the id of the element that contains the png image
 * - svgUrl, the url where the svg content can be downloaded from
 */
function PathwayViewer(info) {
	this.info = info;
	this.highlights = [];
}

PathwayViewer.viewers = {};

/**
 * Urls to icons.
 */
PathwayViewer.icons = {
    "start": "img/start.png",
    "left": "img/nav_left.png",
    "right": "img/nav_right.png",
    "up": "img/nav_up.png",
    "down": "img/nav_down.png",
    "zin": "img/zoom_in.png",
    "zout": "img/zoom_out.png",
    "zfit": "img/zoom_fit.png",
    "loading": "img/loading.gif",
    "getflash": "img/getflash.png",
    "search": "img/search.png"
}

/**
 * The amount of zoom ratio change per zoom step.
 */
PathwayViewer.zoomStep = 0.2;

/**
 * The number of pixels to move per step (for panning controls).
 */
PathwayViewer.moveStep = 25;

/**
 * Postfix for id of the container element that contains the
 * svg object.
 */
PathwayViewer.idContainer = '_container';
/**
 * Postfix for id of element that contains the start button.
 */
PathwayViewer.idStartButton = '_startBtn';

/**
 * Postfix for id of element that contains the controls.
 */
PathwayViewer.idControls = '_controls';

/**
 * Postfix for id of svg object element.
 */
PathwayViewer.idSvgObject = '_svg';
/**
 * Postfix for id elements that contains the loading progress indicator.
 */
PathwayViewer.idLoadProgress = '_loading';

/**
 * Postfix for layout container
 */
PathwayViewer.idLayout = '_layout';

/**
 * Start the viewer (after start button is clicked).
 */
PathwayViewer.prototype.startSVG = function() {
    var that = this;
    console.log("Starting svg viewer for " + this.info.imageId);
    
    this.removeStartButton();
    
	//Test if a suitable renderer has been found
	if (!this.isFlashSupported()) {
		//If not, instead of loading the svg, add a notification
		//to help the user to install flash
		this.addFlashNotification();
		return;
	}
    
    //Replace the image by the container with the svgweb object and xref panel
    var $img = this.getImg(this.info.imageId);
    this.removeImgAnchor($img);
    
    var w = '100%'; if(this.info.width) w = this.info.width;
    var h = '500px'; if(this.info.height) h = this.info.height;
    
    var $container = $('<div />')
    	.attr('id', this.info.imageId + PathwayViewer.idContainer)
    	.css({
        width: w,
        height: h
		});
    
    var $parent = $img.parent();
    $img.after($container);
    $img.remove();
    
    //Create the layout pane
    var $layout = $('<div/>')
    	.attr('id', this.info.imageId + PathwayViewer.idLayout).css({
        width: '100%',
        height: '100%'
    });
    this.$viewer = $('<div/>').addClass('ui-layout-center').css({
        border: '1px solid #BBBBBB',
        'background-color': '#FFFFFF'
    });
    var $xrefpane = $('<div/>').addClass('ui-layout-east');
    $layout.append(this.$viewer);
    $layout.append($xrefpane);
    
    var afterAnimate = function() {
        //Apply the layout
        $container.append($layout);
        var east_width = 300;
        if(east_width > $container.width() * 0.5) {
        	east_width = $container.width() * 0.5; //Cover half of the viewer max
        }
        var layoutUtil = $layout.layout({
            applyDefaultStyles: true,
            center__applyDefaultStyles: false,
            east__size: east_width
        });
        layoutUtil.close('east');
        
        that.$viewer.css({
            overflow: 'hidden',
            'background-color': '#F9F9F9'
        });
        that.showLoadProgress($layout);
        
        //Add the SVG object to the center panel
        var obj_id = that.info.imageId + PathwayViewer.idSvgObject;
        
        var obj = document.createElement('object', true);
        obj.id = obj_id;
	     obj.setAttribute('type', 'image/svg+xml');
	     obj.setAttribute('data', that.info.svgUrl);
	     
        //Ideally we would use relative size here ('100%'), but this causes the
        //SVG to stretch on resizing the parent
        obj.setAttribute('width', screen.width + 'px');
        obj.setAttribute('height', screen.height + 'px');
        //obj.setAttribute('width', that.$viewer.width() + 'px');
        //obj.setAttribute('height', that.$viewer.height() + 'px');
        obj.addEventListener('SVGLoad', function() {
        		that.$svgObject = $('#' + that.info.imageId + PathwayViewer.idSvgObject);
        		that.svgRoot = that.$svgObject.get(0).contentDocument.rootElement;
            that.svgLoaded($xrefpane, layoutUtil);
            //Remove progress when loaded
            that.hideLoadProgress($layout);
        }, false);
        
        svgweb.appendChild(obj, that.$viewer.get(0));
    }
    
    //Change the size of the image parent
    if ($.browser.msie) { //Animate gives problems in IE, just change style directly 
        $parent.css({
            width: '100%',
            height: 'auto'
        });
        afterAnimate();
    }
    else { //Animate for smooth transition
        $parent.animate({
            width: '100%',
            height: 'auto'
        }, 300, afterAnimate);
    }
}

PathwayViewer.prototype.removeImgAnchor = function($img) {
	//If the img tag is nested in an anchor tag,
	//remove it
	if ($img.parent().is('a')) {
		var $oldParent = $img.parent();
		var $newParent = $oldParent.parent();
		$oldParent.after($img);
		$oldParent.remove();
	}
}

//Flash version detection copied from http://www.prodevtips.com/2008/11/20/detecting-flash-player-version-with-javascript/
PathwayViewer.prototype.isFlashSupported = function() {
	function getFlashVersion() {
		// ie
		try {
			try {
				// avoid fp6 minor version lookup issues
				// see: http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
				var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
				try { axo.AllowScriptAccess = 'always'; }
				catch(e) { return '6,0,0'; }
			} catch(e) {}
				return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];
				// other browsers
			} catch(e) {
				try {
					if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){
					return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
					}
				} catch(e) {}
		}
		return '0,0,0';
	}
	
	var version = getFlashVersion().split(',').shift();
	return version >= 10;
}

PathwayViewer.prototype.addStartButton = function(){
    //Test if a suitable renderer has been found
    if (!this.isFlashSupported()) {
        //If not, instead of loading the svg, add a notification
        //to help the user to install flash
        this.addFlashNotification();
        return;
    }
    
    var $img = this.getImg();
    
    this.removeImgAnchor($img);
    
    //Create a start image
    var $parent = $img.parent()
    var $start = jQuery('<img>').attr('src', PathwayViewer_basePath + PathwayViewer.icons.start).attr('title', 'Click to activate pan and zoom.');
    var $div = jQuery('<div/>').attr("id", this.info.imageId + PathwayViewer.idStartButton);
    
    //Add the start button
    $div.append($start);
    $img.before($div);
    
    $div.css({
        position: 'relative',
        height: '1px',
        width: '100%',
        overflow: 'visible',
        'text-align': 'right',
        'z-index': '1000'
    });
    
    var that = this;
    //Register the action
    var startFunction = function(e){
        that.startSVG();
    }
    
    $img.click(startFunction);
    $div.click(startFunction);
}

PathwayViewer.prototype.loadGPML = function() {
	var that = this;
	if(this.info.gpmlUrl) {
		this.gpml = new GpmlModel(this.info.gpmlUrl);
		this.gpml.load(function(gpml) {
			that.initSearchTerms();
		});
	}
}

PathwayViewer.prototype.showLoadProgress = function($container) {
    var $block = $container.find('progress_block');
    if ($block.length > 0) {
        $block.show();
    }
    else {
        var $img = $('<img>').attr('src', PathwayViewer_basePath + PathwayViewer.icons.loading).attr('title', 'Loading viewer...');
        var $load = $('<div>').css({
            display: 'block',
            position: 'relative',
            left: '50%',
            top: '50%',
            'text-align': 'left'
        });
        $load.append($img);
        
        $block = $('<div/>').addClass('progress_block').css({
            position: 'relative',
            width: '100%',
            height: '100%',
            'z-index': 100,
            cursor: 'wait',
            'background-color': '#FFFFFF',
            opacity: 1
        });
        $block.append($load);
        $container.append($block);
    }
}

PathwayViewer.prototype.hideLoadProgress = function($container){
    $container.find('.progress_block').hide();
}

PathwayViewer.prototype.addFlashNotification = function(){
    var $img = this.getImg();
    var $parent = $img.parent()
    if ($parent.is('a')) {
        $parent = $parent.parent();
    }
    
    var $flash = $('<img>').attr('src', PathwayViewer_basePath + PathwayViewer.icons.getflash).attr('title', 'Install Flash player to zoom and view protein/metabolite info.');
    
    var $div = $('<div id="flashlink"/>').css({
        position: 'relative',
        top: -$parent.height() + 20 + 'px',
        left: ($img.width() / 2) - 100 + 'px',
        'z-index': '1000'
    });
    
    var $link = $('<a></a>').attr('href', 'http://www.adobe.com/go/getflashplayer').attr('id', 'flashlink_a');
    $link.append($flash);
    $div.append($link);
    $parent.append($div);
}

PathwayViewer.prototype.removeStartButton = function() {
    $('#' + this.info.imageId + PathwayViewer.idStartButton).remove();
}

/**
 * Get the first img tag that's a child of the element
 * identified by id.
 */
PathwayViewer.prototype.getImg = function() {
    var $img = $('#' + this.info.imageId);
    if ($img.get(0).nodeName.toLowerCase() != 'img') {
        //Get the IMG descendants
        $img = $('#' + this.info.imageId + ' img');
    }
    return $img;
}

PathwayViewer.prototype.svgLoaded = function($xrefContainer, layout) {
	var that = this;
	
	this.svgWidth = this.svgRoot.width.baseVal.value;
	this.svgHeight = this.svgRoot.height.baseVal.value;
	
	this.$svgObject.disableSelection()
	
	//Add event handlers
	drag = this.newDragState(this.$svgObject, this.svgRoot);
	this.drag = drag;
	
	this.$viewer.mousedown(drag.mouseDown);
	this.$viewer.mouseup(drag.mouseUp);

	//Add the mouseup and mouse move to the document,
	//so we can continue dragging outside the svg object
	$(document).mouseup(this.drag.mouseUp);
	$(document).mousemove(this.drag.mouseMove);

	this.$viewer.mousemove(function(e) {
		if (!drag.dragging) {
			that.gpml.mouseMove(that.$svgObject.offset(), that.svgRoot, e);
		}
	});
	this.$viewer.mousedown(function(e) {
		that.gpml.mouseDown(
			layout, $xrefContainer, that.$svgObject.offset(), that.svgRoot, e);
	});
	this.$viewer.mousewheel(function(e) {
		that.mouseWheel(e);
	});

	//Show the pan and zoom buttons
	this.addControls();
	this.zoomFit();

	//Force SVG to be as wide as the object it is in (to avoid clipping)
	this.svgRoot.setAttribute('width', this.$svgObject.width() + 'px');
	this.svgRoot.setAttribute('height', this.$svgObject.height() + 'px');
}

PathwayViewer.prototype.addControls = function() {
	var that = this;
	var id = this.info.imageId + PathwayViewer.idControls;
	
	var s = 5;
	var w = 20;
	var h = 20;

	var totalWidth = (w + s) * 3;

	var $controls = jQuery('<div />').attr('id', id);
	$controls.disableSelection();

	var create = function(src, fn, left, top, title) {
		var btn = $('<div />').addClass('').css({
			position: 'relative',
			left: left + 'px',
			top: top + 'px',
			width: w + 'px',
			height: h + 'px',
			'background-image': 'url(' + src + ')'
		});
		btn.click(bind(that, fn));
		btn.bind('mousedown', function(e) {
			e.stopPropagation(); //Prevent dragging viewer when clicking control
		});
		btn.attr("title", title);
		return btn;
	};

	$controls.append(
		create(PathwayViewer_basePath + PathwayViewer.icons.up,
			"panUp", -s - 1.5 * w, s, "Pan up")
	);
	$controls.append(
		create(PathwayViewer_basePath + PathwayViewer.icons.left,
			"panLeft", -s - 2 * w, s, "Pan left")
	);
	$controls.append(
		create(PathwayViewer_basePath + PathwayViewer.icons.right, 
			"panRight", -s - w, -w + s, "Pan right")
	);
	$controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.down, 
		"panDown", -s - 1.5 * w, -w + s, "Pan down")
	);

	$controls.append(
		create(PathwayViewer_basePath + PathwayViewer.icons.zin, "zoomIn",
			 -s - 1.5 * w, -s, "Zoom in"));

	var svgW = this.svgRoot.width.baseVal.value;
	var svgH = this.svgRoot.height.baseVal.value;
	$controls.append(
		create(PathwayViewer_basePath + PathwayViewer.icons.zfit, 
			"zoomFit", -s - 1.5 * w, -s, "Zoom to fit"));
	$controls.append(
		create(PathwayViewer_basePath + PathwayViewer.icons.zout,
			"zoomOut", -s - 1.5 * w, -s, "Zoom out"));

	var $searchBox = this.createSearchBox(s, s);
	this.$viewer.append($searchBox);
	this.$viewer.append($controls);

	//Set correct position
	$controls.css({
		position: 'absolute',
		left: '100%',
		top: $searchBox.height() + s + 'px',
		'z-index': 1001
	});
}

PathwayViewer.prototype.createSearchBox = function(right, top) {
	var that = this;
	var $box = $('<div/>').addClass('ui-widget').addClass('ui-corner-all').css({
		position: 'absolute',
		right: right + 'px',
		top: top + 'px',
		'z-index': 1001,
		'background-color' : '#DDDDDD',
		border: '1px solid #AAAAAA'
	});
	
	var $input = $('<input id="PathwayViewerSearch"/>').css({ width: '100px' });
	
	var lastChange = { time: -1 };
	
	var onChange = function() {
		//Delay to prevent unwanted searches during typing
		var now = new Date().getTime()
		lastChange.time = now;
		var doit = function(now, lastChange) {
			if(lastChange.time == now) {
				that.search($input.attr('value'));	
			} else {
			}
		}
		window.setTimeout(function(){doit(now,lastChange)}, 500);
	}
	
	var onEnter = function() {
		lastChange.time = new Date().getTime(); //Don't execute onChange
		that.search($input.attr('value'));	
		//TODO: on hitting enter, focus on result and traverse
	}
	
	$input.autocomplete({ 
		source: [], select: onEnter,
		position: { my: "right top", at: "right bottom" }
	});
	$input.addClass('ui-corner-all');
	$input.bind('keyup', onChange);
	$input.bind('change', onEnter);
	$box.append($input);
	
	this.$searchInput = $input;
	this.initSearchTerms();
	
	var $icon = $('<img />').
		attr('src', PathwayViewer_basePath + PathwayViewer.icons.search).
		css({ 'vertical-align' : 'middle' });
	
	var origWidth = $input.width();
	
	$icon.click(function() {
		if($input.is(':visible')) {
			$input.animate({width: '1px'}, 300, function() { $input.hide(); });
		} else {
			$input.show();
			$input.animate({width: '100px'}, 300);
		}
	});
	
	$box.append($icon);
	
	$box.bind('mousedown', function(e) {
		e.stopPropagation(); //Prevent dragging viewer when clicking search box
	});
	return $box;
}

PathwayViewer.prototype.initSearchTerms = function() {
	if(this.$searchInput) {
		var terms = [];
		$.each(this.gpml.searchObjects, function(i, v) {
			if($.inArray(v.textLabel, terms) < 0) terms.push(v.textLabel);
		});
		this.$searchInput.autocomplete("option", "source", terms);
	}
}

PathwayViewer.prototype.search = function(query) {
	var that = this;
	this.clearHighlight();
	if(query && this.gpml && this.gpml.searchObjects) {
		var results = this.gpml.search(query);
		$.each(results, function(i,v) { that.highlight(v); });
	}
}

PathwayViewer.prototype.highlight = function(obj) {
	var left = obj.left * this.gpml.scale;
	var right = obj.right * this.gpml.scale;
	var top = obj.top * this.gpml.scale;
	var bottom = obj.bottom * this.gpml.scale;
	
	var rect = document.createElementNS(svgns, 'rect');
	rect.setAttribute('x', left);
	rect.setAttribute('y', top);
	rect.setAttribute('width', right - left);
	rect.setAttribute('height', bottom - top);
	rect.setAttribute('stroke', 'yellow');
	rect.setAttribute('stroke-width', '5');
	rect.setAttribute('fill-opacity', '0');
	rect.setAttribute('opacity', '0.5');
	this.svgRoot.appendChild(rect);
	
	this.highlights.push(rect);
}

PathwayViewer.prototype.clearHighlight = function() {
	var that = this;
	$.each(this.highlights, function(i, v) {
		that.svgRoot.removeChild(v);
	});
	this.highlights = [];
}

PathwayViewer.prototype.zoomTo = function(factor, x, y){
	var svg = this.svgRoot;
    //Zoom and ensure that x, y keeps pointing to the same svg coordinate after zooming
    var dx = x / svg.currentScale - x / factor;
    var dy = y / svg.currentScale - y / factor;
    svg.currentScale = factor;
    svg.currentTranslate.setX(svg.currentTranslate.getX() - dx);
    svg.currentTranslate.setY(svg.currentTranslate.getY() - dy);
}

PathwayViewer.prototype.zoomIn = function() {
	this.zoomTo(
		this.svgRoot.currentScale * (1 + PathwayViewer.zoomStep), 
		this.$viewer.width() / 2, this.$viewer.height() / 2
	);
}

PathwayViewer.prototype.zoomOut = function() {
	this.zoomTo(
		this.svgRoot.currentScale / (1 + PathwayViewer.zoomStep),
		this.$viewer.width() / 2, this.$viewer.height() / 2
	);
}

PathwayViewer.prototype.zoomFit = function() {
	var w = this.svgWidth;
	var h = this.svgHeight;
	var fw = this.$viewer.width();
	var fh = this.$viewer.height();
	
	//Calculate the zoom factor to fit the complete svg
	var rw = fw / w;
	var rh = fh / h;
	var r = Math.min(rw, rh);
	this.svgRoot.currentScale = r;

	//Center
	this.svgRoot.currentTranslate.setX(0.5 * fw / r - w / 2);
	this.svgRoot.currentTranslate.setY(0.5 * fh / r - h / 2);
}

PathwayViewer.prototype.panUp = function() {
    this.svgRoot.currentTranslate.setY(
    	this.svgRoot.currentTranslate.getY() + PathwayViewer.moveStep);
}

PathwayViewer.prototype.panLeft = function() {
    this.svgRoot.currentTranslate.setX(
    	this.svgRoot.currentTranslate.getX() + PathwayViewer.moveStep);
}

PathwayViewer.prototype.panRight = function() {
    this.svgRoot.currentTranslate.setX(
    	this.svgRoot.currentTranslate.getX() - PathwayViewer.moveStep);
}

PathwayViewer.prototype.panDown = function() {
    this.svgRoot.currentTranslate.setY(
    	this.svgRoot.currentTranslate.getY() - PathwayViewer.moveStep);
}

PathwayViewer.prototype.mouseWheel = function(e) {
	var svg = this.svgRoot;
	e = e ? e : window.event;

	var wheelData = e.detail ? e.detail * -1 : e.wheelDelta;

	var offset = this.$svgObject.offset();
	var x = e.pageX - offset.left;
	var y = e.pageY - offset.top;

	if(wheelData > 0) {
		this.zoomTo(svg.currentScale * (1 + PathwayViewer.zoomStep), x, y);
	} else {
		this.zoomTo(svg.currentScale / (1 + PathwayViewer.zoomStep), x, y);
	}

	if(e.preventDefault) {
		e.preventDefault();
	}

	return false;
}

/**
 * This object stores the drag state (mouse up/down, drag position) for each
 * svg object.
 */
PathwayViewer.prototype.newDragState = function() {
	var that = this;
    var drag = {
        dragging: false,
        pMouseDown: {
            x: 0,
            y: 0
        },
        pTransDown: {
            x: 0,
            y: 0
        }
    };
    
    drag.mouseDown = function(e){
        //Check if mouse is over svg element
        var svgOffset = that.$svgObject.offset();
        if (svgOffset.left <= e.pageX &&
        (svgOffset.left + that.$svgObject.width()) >= e.pageY &&
        svgOffset.top <= e.pageY &&
        (svgOffset.top + that.$svgObject.height()) >= e.pageY) {
            drag.dragging = true;
            
            drag.pMouseDown = {
                x: e.pageX,
                y: e.pageY
            };
            drag.pTransDown = {
                x: that.svgRoot.currentTranslate.getX(),
                y: that.svgRoot.currentTranslate.getY()
            };
        }
    }
    
    drag.mouseMove = function(e) {
        if (!drag.dragging) {
            return;
        }
        
        var dx = e.pageX - drag.pMouseDown.x;
        var dy = e.pageY - drag.pMouseDown.y;
        that.svgRoot.currentTranslate.setX(
        		drag.pTransDown.x + dx / that.svgRoot.currentScale);
        that.svgRoot.currentTranslate.setY(
        		drag.pTransDown.y + dy / that.svgRoot.currentScale);
    }
    
    drag.mouseUp = function(evt){
        drag.dragging = false;
    }
    return drag;
}

GpmlModel = function(gpmlUrl) {
	this.gpmlUrl = gpmlUrl;
	
	this.gpmlSize = {
		width: 0,
		height: 0
	};

	/**
	* The pathway species.
	*/
	this.species = '';

	/**
	* List of objects that trigger a hover event.
	* Fields:
	* - left, top, width, height: the coordinates (GPML)
	* - z, the z-order of the object
	* - id: the id of the object in the GPML DOM
	*/
	this.hoverObjects = [];

	/**
	* List of objects that can be searched.
	* Fields:
	* - left, top, width, height: the coordinates (GPML)
	* - text: The text to search
	*/
	this.searchObjects = [];
    
	/**
	* The jquery parsed GPML.
	*/
	this.$data = null;
};

GpmlModel.prototype.load = function(callback) {
	var that = this;
	this.callback = callback;
	
	$.ajax({
		url: this.gpmlUrl,
		success: function(data, textStatus) {
			that.loaded(data, textStatus, callback);
		},
		error: function(xr, msg, ex){
			console.log("Error loading gpml: " + msg);
			console.log(ex);
		},
		dataType: "xml"
	});
}

GpmlModel.prototype.loaded = function(data, textStatus, callback) {
	var that = this;
	
	this.$data = $(data);
	if (typeof data == 'string') {
		var xml = this.parseGPML(data);
		this.$data = $(xml);
	}

	var graphics = this.$data.find('Pathway > Graphics');
	this.gpmlSize = {
		width: graphics.attr('BoardWidth'),
		height: graphics.attr('BoardHeight')
	};

	var parseObject = function(jq) {
		var cx = parseFloat(jq.attr('CenterX'));
		var cy = parseFloat(jq.attr('CenterY'));
		var w = parseFloat(jq.attr('Width'));
		var h = parseFloat(jq.attr('Height'));
		var obj = {};
		obj.left = cx - w / 2;
		obj.top = cy - h / 2;
		obj.right = obj.left + w;
		obj.bottom = obj.top + h;
		obj.z = jq.attr('ZOrder');
		obj.$data = jq.parent();
		obj.textLabel = obj.$data.attr('TextLabel');
		if(obj.textLabel) 
			obj.textLabel = obj.textLabel.replace(/[\f\n\r\t\v]+/g, " ");
		obj.type = obj.$data.attr('Type');

		return obj;
	}
	//Get the datanodes
	this.$data.find('DataNode > Graphics').each(function(){
		var obj = parseObject($(this));
		that.hoverObjects.push(obj);
		that.searchObjects.push(obj);
	});

	//Get the labels
	this.$data.find('Label > Graphics').each(function() {
		var obj = parseObject($(this));
		that.searchObjects.push(obj);
	});

	//Get the species
	this.species = this.$data.find('Pathway').attr('Organism');

	//Determine the translation factor from GPML to svg coordinates
	var r = 1;
	var gpmlNs = this.$data.find('Pathway').attr('xmlns');
	var res = gpmlNs.match(/([0-9]{4})[a-z]{0,1}$/);
	if(res) {
		var ver = res[1];
		if(ver < 2010) r = 1 / 15;
	}
	this.scale = r;

	if(callback) callback(this);
}

GpmlModel.prototype.mouseMove = function(offset, svg, e){
	var hover = this.getHoverObject(offset, svg, e);
	if (hover) {
		svg.setAttribute('cursor', 'pointer');
	}
	else {
		svg.setAttribute('cursor', 'default');
	}
}
    
GpmlModel.prototype.mouseDown = function(layout, $xrefContainer, offset, svg, e){
	var hover = this.getHoverObject(offset, svg, e);
	if (hover) {
		//Get the xref properties
		var jqxref = hover.$data.find('Xref');
		var id = jqxref.attr('ID');
		var ds = jqxref.attr('Database');

		//Open the xref info
		var title = hover.textLabel + ' (' + hover.type + ')';

		var $panel = XrefPanel.create(id, ds, this.species, hover.textLabel);
		$xrefContainer.append($panel);
		$xrefContainer.children().hide();
		$panel.show();
		layout.open('east');
	}
}
    
GpmlModel.prototype.getHoverObject = function(offset, svg, e) {
	var hover = null;

	var svgSize = {
		width: svg.getAttribute('width'),
		height: svg.getAttribute('height')
	};

	var p = {
		x: (e.pageX - offset.left) / svg.currentScale - svg.currentTranslate.getX(),
		y: (e.pageY - offset.top) / svg.currentScale - svg.currentTranslate.getY()
	}

	var r = this.scale;

	for (var i in this.hoverObjects) {
		obj = this.hoverObjects[i];

		var robj = {
			left: obj.left * r,
			right: obj.right * r,
			bottom: obj.bottom * r,
			top: obj.top * r
		}

		//console.log(robj);
		var inx = p.x >= robj.left && p.x <= robj.right;
		var iny = p.y >= robj.top && p.y <= robj.bottom;
		if (inx && iny) {
			hover = obj;
			hover.svgleft = robj.left;
			hover.svgright = robj.right;
			hover.svgbottom = robj.bottom;
			hover.svgtop = robj.top;
			break;
		}
	}

	return hover;
}
    
GpmlModel.prototype.search = function(query) {
	query = query.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
	var re = new RegExp(query, 'i');
	var results = [];
	$.each(this.searchObjects, function(i, obj) {
		if(obj.textLabel) {
			if(obj.textLabel.match(re)) results.push(obj);
		}
	});
	return results;
}

/**
 * Cross-browser xml parsing.
 * From http://www.w3schools.com/Dom/dom_parser.asp
 */
GpmlModel.prototype.parseGPML = function(xml){
    var xmlDoc = null;
    if (window.DOMParser) {
        parser = new DOMParser();
        xmlDoc = parser.parseFromString(xml, "text/xml");
    }
    else // Internet Explorer
    {
        xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
        xmlDoc.async = "false";
        xmlDoc.loadXML(xml);
    }
    return xmlDoc;
}

debug = function(text){
    $("#debug").html(text);
}

function bind(toObject, methodName){
    return function(){toObject[methodName]()}
}
