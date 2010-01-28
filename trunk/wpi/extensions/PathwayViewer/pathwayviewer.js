
//TODO: hyperlink cursor when over clickable object
//TODO: make viewer resizable (currently hard because resizing the flash object stretches the svg, maybe adjust viewport will help)

/**
 * Change this if the base path of the script (and resource files) is
 * different than the page root.
 */
if (typeof(PathwayViewer_basePath) == "undefined") 
    var PathwayViewer_basePath = '';

/**
 * This variable determines if xref info is displayed in a popup dialog ('popup')
 * or info panel next to the svg image ('panel', default).
 */
if (typeof(PathwayViewer_xrefinfo) == "undefined") 
    var PathwayViewer_xrefinfo = 'panel';

/**
 * Pathway viewer based on Svgweb.
 * Depends on:
 * - Svgweb (http://svgweb.googlecode.com/)
 * - JQuery (http://jquery.com/)
 *
 * Scroll and zoom based on code by Brad Neuberg:
 * http://codinginparadise.org/projects/svgweb-staging/tests/non-licensed/wikipedia/svgzoom/svgzoom.js
 */
var PathwayViewer = {};

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
	"getflash": "img/getflash.png"
}

/**
 * The amount of zoom ratio change per zoom step.
 */
PathwayViewer.zoomStep = 0.1;

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
 * Array of objects that contain the information
 * for each pathway. The objects should contain the following
 * fields:
 * - imageId, the id of the element that contains the png image
 * - svgUrl, the url where the svg content can be downloaded from
 */
PathwayViewer.pathwayInfo = [];

/**
 * Objects that store the current mouse drag state.
 */
PathwayViewer.draggers = {};

/**
 On page load:
 1. Load the svg objects in the background
 2. Add the buttons for starting the viewer when loading is finished
 */
PathwayViewer.onPageLoad = function(){
    PathwayViewer.loadSVG();
    PathwayViewer.loadGPML();
}
$(window).load(PathwayViewer.onPageLoad);

PathwayViewer.addLoadProgress = function(info, $img){
    var $load = jQuery('<img>').attr('src', PathwayViewer_basePath + PathwayViewer.icons.loading).attr('title', 'Loading viewer...');
    
    //Get the location of the top-right corner of the image
    var imgPos = $img.offset();
    
    var $div = jQuery('<div />').attr('id', info.imageId + PathwayViewer.idLoadProgress).css({
        'z-index': '1000',
        position: 'absolute',
        top: imgPos.top + 'px',
        left: (imgPos.left + $img.width() - 32) + 'px'
    });
    
    $div.append($load);
    $('body').append($div);
}

PathwayViewer.addFlashNotification = function(info){
    var $img = PathwayViewer.getImg(info.imageId);
	var $parent = $img.parent()
	if($parent.is('a')) {
		$parent = $parent.parent();
	}
	    
    var $flash = jQuery('<img>')
		.attr('src', PathwayViewer_basePath + PathwayViewer.icons.getflash)
		.attr('title', 'Install Flash player to zoom and view protein/metabolite info.');
    
    var $div = jQuery('<div id="flashlink"/>').css({
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

PathwayViewer.addStartButton = function(info){
    var $img = PathwayViewer.getImg(info.imageId);
    
    var $start = jQuery('<img>').attr('src', PathwayViewer_basePath + PathwayViewer.icons.start).attr('title', 'Click to activate pan and zoom.');
    
    //Get the location of the top-right corner of the image
    var imgPos = $img.offset();
    
    var $div = jQuery('<div />').attr('id', info.imageId + PathwayViewer.idStartButton).css({
        'z-index': '1000',
        position: 'absolute',
        top: (imgPos.top + 5) + 'px',
        left: (imgPos.left + $img.width() - 48) + 'px'
    });
    
    $div.append($start);
    $('body').append($div);
    
    var startFunction = function(e){
        PathwayViewer.startSVG(info);
    }
    
    $img.click(startFunction);
    $div.click(startFunction);
}

PathwayViewer.loadGPML = function(){
    for (var i in PathwayViewer.pathwayInfo) {
        var info = PathwayViewer.pathwayInfo[i];
        if (info.gpmlUrl) {
            var gpml = GpmlModel.load(info);
            GpmlModel.models[info.imageId] = gpml;
        }
    }
}

PathwayViewer.getImg = function(id){
    var $img = $('#' + id);
    if ($img.get(0).nodeName.toLowerCase() != 'img') {
        //Get the IMG descendants
        $img = $('#' + id + ' img');
    }
    return $img;
}

PathwayViewer.loadSVG = function(){
    for (var i in PathwayViewer.pathwayInfo) {
        var info = PathwayViewer.pathwayInfo[i];

		//Test if a suitable renderer has been found by svgweb
		if(!svgweb.config.use) {
			//If not, instead of loading the svg, add a notification
			//to help the user to install flash
			PathwayViewer.addFlashNotification(info);
			continue;
		}
	
        var $img = PathwayViewer.getImg(info.imageId);
        
        PathwayViewer.addLoadProgress(info, $img);
        
        var obj_id = info.imageId + PathwayViewer.idSvgObject;
        
        var obj = document.createElement('object', true);
        obj.id = obj_id;
        obj.setAttribute('type', 'image/svg+xml');
        obj.setAttribute('data', info.svgUrl);
        obj.setAttribute('width', $img.width());
        obj.setAttribute('height', $img.height());
        obj.addEventListener('load', function(){
            //Replace progress by start button
            $('#' + info.imageId + PathwayViewer.idLoadProgress).hide();
            PathwayViewer.addStartButton(info);
            
            //If the img tag is nested in an anchor tag,
            //remove it
            if ($img.parent().is('a')) {
                var $oldParent = $img.parent();
                var $newParent = $oldParent.parent();
                $oldParent.after($img);
                $oldParent.remove();
            }
        }, false);
        
        var imgPos = $img.offset();
        
        var $container = jQuery('<div />').attr('id', info.imageId + PathwayViewer.idContainer).css({
            'z-index': -1000,
            'position': 'absolute',
            'top': imgPos.top + 'px',
            'left': imgPos.left + 'px'
        });
        $('body').append($container);
        // $container.insertAfter($img);
        svgweb.appendChild(obj, $container.get(0));
        
        //Make sure the svg objects will be hidden when starting the edit applet
        $('#edit').click(function(e){
            $container.remove();
            $('#' + info.imageId + PathwayViewer.idStartButton).remove();
            $('#' + info.imageId + PathwayViewer.idControls).remove();
        });
    }
}

PathwayViewer.startSVG = function(info){
    console.log("Starting svg viewer for " + info.imageId);
    
    var svgObject = document.getElementById(info.imageId + PathwayViewer.idSvgObject);
    var svgRoot = svgObject.contentDocument.rootElement;
    var $svgObject = $(svgObject);
    
    var $img = PathwayViewer.getImg(info.imageId);
    //Insert image placeholder
    var $ph = jQuery('<div />').css({
        width: $img.width() + 'px',
        height: $img.height() + 'px'
    });
    $img.after($ph);
    
    //Hide the png image
    $img.hide();
    
    //Add event handlers
    var drag = PathwayViewer.newDragState($svgObject, svgRoot);
    PathwayViewer.draggers[info.imageId] = drag;
    
    $svgObject.mousedown(drag.mouseDown);
    $svgObject.mouseup(drag.mouseUp);
    //Add the mouseup and mouse move to the document,
    //so we can continue dragging outside the svg object
    $(document).mouseup(drag.mouseUp);
    $(document).mousemove(drag.mouseMove);
    
    var gpml = GpmlModel.models[info.imageId];
    if (gpml) {
        $svgObject.mousemove(function(e){
            if (!drag.dragging) {
                gpml.mouseMove($svgObject, svgRoot, e);
            }
        });
        $svgObject.mousedown(function(e){
            gpml.mouseDown($svgObject, svgRoot, e);
        });
    }
    $svgObject.mousewheel(function(e){
        PathwayViewer.mouseWheel(e, svgRoot, $svgObject);
    });
    
    //Add a resize handler to the window, to adjust
    //the absolute svg position
    $(window).resize(function(e){
        var offset = $ph.offset();
        $container.css({
            'top': offset.top + 'px',
            'left': offset.left + 'px'
        });
    });
    
    //Show the svg object
    var $container = $('#' + info.imageId + PathwayViewer.idContainer);
    $container.css({
        "z-index": 1000
    });
    
    //Remove the start button
    $('#' + info.imageId + PathwayViewer.idStartButton).hide();
    
    //Show the pan and zoom buttons
    PathwayViewer.addControls($container, svgRoot, info.imageId + PathwayViewer.idControls);
    
    PathwayViewer.zoomFit(svgRoot, $container.width(), $container.height());
}

PathwayViewer.addControls = function($container, svgRoot, id){
    var cOffset = $container.offset();
    var cWidth = $container.width();
    var cHeight = $container.height();
    
    var s = 5;
    var w = 20;
    var h = 20;
    
    var totalWidth = (w + s) * 3;
    
    var $controls = jQuery('<div />').attr('unselectable', 'on').attr('id', id);
    
    var create = function(src, fn, left, top){
        var btn = jQuery('<div />').addClass('').css({
            position: 'absolute',
            left: left + 'px',
            top: top + 'px',
            width: w + 'px',
            height: h + 'px',
            'background-image': 'url(' + src + ')'
        });
        btn.click(function(){
            fn(svgRoot);
        });
        return btn;
    };
    
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.left, PathwayViewer.panLeft, 0.5 * s + 0.5 * w, 2 * s + w));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.right, PathwayViewer.panRight, 1.5 * s + 1.5 * w, 2 * s + w));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.up, PathwayViewer.panUp, s + w, s));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.down, PathwayViewer.panDown, s + w, 3 * s + 2 * w));
    
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.zin, function(){
        PathwayViewer.zoomIn(svgRoot, $container);
    }, s + w, 5 * s + 3 * w));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.zfit, function(){
        PathwayViewer.zoomFit(svgRoot, cWidth, cHeight);
    }, s + w, 6 * s + 4 * w));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.zout, function(){
        PathwayViewer.zoomOut(svgRoot, $container);
    }, s + w, 7 * s + 5 * w));
    $container.append($controls);
    
    //Set correct position
    var left = cWidth - (3 * s + 2 * w);
    $controls.css({
        position: 'absolute',
        left: left,
        top: 0,
        'z-index': 1001
    });
}

PathwayViewer.zoomTo = function(svg, factor, x, y){
    //Zoom and ensure that x, y keeps pointing to the same svg coordinate after zooming
    var dx = x / svg.currentScale - x / factor;
    var dy = y / svg.currentScale - y / factor;
    svg.currentScale = factor;
    svg.currentTranslate.setX(svg.currentTranslate.getX() - dx);
    svg.currentTranslate.setY(svg.currentTranslate.getY() - dy);
}

PathwayViewer.zoomIn = function(svg, $container){
    PathwayViewer.zoomTo(svg, svg.currentScale * (1 + PathwayViewer.zoomStep), $container.width() / 2, $container.height() / 2);
}

PathwayViewer.zoomOut = function(svg, $container){
    PathwayViewer.zoomTo(svg, svg.currentScale / (1 + PathwayViewer.zoomStep), $container.width() / 2, $container.height() / 2);
}

PathwayViewer.zoomFit = function(svg, fw, fh){
    var w = svg.width.baseVal.value;
    var h = svg.height.baseVal.value;
    
    //Calculate the zoom factor to fit the complete svg
    var rw = fw / w;
    var rh = fh / h;
    var r = Math.min(rw, rh);
    svg.currentScale = r;
    svg.currentTranslate.setX(0);
    svg.currentTranslate.setY(0);
}

PathwayViewer.panUp = function(svg){
    svg.currentTranslate.setY(svg.currentTranslate.getY() - PathwayViewer.moveStep);
}

PathwayViewer.panLeft = function(svg){
    svg.currentTranslate.setX(svg.currentTranslate.getX() - PathwayViewer.moveStep);
}

PathwayViewer.panRight = function(svg){
    svg.currentTranslate.setX(svg.currentTranslate.getX() + PathwayViewer.moveStep);
}

PathwayViewer.panDown = function(svg){
    svg.currentTranslate.setY(svg.currentTranslate.getY() + PathwayViewer.moveStep);
}

PathwayViewer.mouseWheel = function(e, svg, $svgObject){
    e = e ? e : window.event;
    
    var wheelData = e.detail ? e.detail * -1 : e.wheelDelta;
    
    var offset = $svgObject.offset();
    var x = e.pageX - offset.left;
    var y = e.pageY - offset.top;
    
    if (wheelData > 0) {
        PathwayViewer.zoomTo(svg, svg.currentScale * (1 + PathwayViewer.zoomStep), x, y);
        //PathwayViewer.zoomIn(svg);
    }
    else {
        PathwayViewer.zoomTo(svg, svg.currentScale / (1 + PathwayViewer.zoomStep), x, y);
        //PathwayViewer.zoomOut(svg);
    }
    
    if (e.preventDefault) {
        e.preventDefault();
    }
    
    return false;
}

/**
 * This object stores the drag state (mouse up/down, drag position) for each
 * svg object.
 * @param {Object} svg The svg root node.
 */
PathwayViewer.newDragState = function($svgObject, svgRoot){
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
        var svgOffset = $svgObject.offset();
        if (svgOffset.left <= e.pageX &&
        (svgOffset.left + $svgObject.width()) >= e.pageY &&
        svgOffset.top <= e.pageY &&
        (svgOffset.top + $svgObject.height()) >= e.pageY) {
            drag.dragging = true;
            
            drag.pMouseDown = {
                x: e.pageX,
                y: e.pageY
            };
            drag.pTransDown = {
                x: svgRoot.currentTranslate.getX(),
                y: svgRoot.currentTranslate.getY()
            };
        }
    }
    
    drag.mouseMove = function(e){
        if (!drag.dragging) {
            return;
        }
        
        var dx = e.pageX - drag.pMouseDown.x;
        var dy = e.pageY - drag.pMouseDown.y;
        svgRoot.currentTranslate.setX(drag.pTransDown.x + dx / svgRoot.currentScale);
        svgRoot.currentTranslate.setY(drag.pTransDown.y + dy / svgRoot.currentScale);
    }
    
    drag.mouseUp = function(evt){
        drag.dragging = false;
    }
    return drag;
}

GpmlModel = {};

/**
 * Stores the parsed gpml for each svg image.
 */
GpmlModel.models = {};

GpmlModel.load = function(info){
    var gpml = {};
    gpml.gpmlSize = {
        width: 0,
        height: 0
    };
    
    /**
     * The pathway species.
     */
    gpml.species = '';
    
    /**
     * List of objects that trigger a hover event.
     * Fields:
     * - left, top, width, height: the coordinates (GPML)
     * - z, the z-order of the object
     * - id: the id of the object in the GPML DOM
     */
    gpml.hoverObjects = [];
    
    /**
     * The jquery parsed GPML.
     */
    gpml.$data = null;
    
    gpml.callback = function(data, textStatus){
        gpml.$data = $(data);
        if (typeof data == 'string') {
            //gpml.$data = $.xmlDOM(data, function(msg){
            //    console.log("unable to parse gpml: " + msg)
            //});
            var xml = PathwayViewer.parseGPML(data);
            gpml.$data = $(xml);
        }
        
        var graphics = gpml.$data.find('Pathway > Graphics');
        gpml.gpmlSize = {
            width: graphics.attr('BoardWidth'),
            height: graphics.attr('BoardHeight')
        };
        
        //Get the datanodes
        gpml.$data.find('DataNode > Graphics').each(function(){
            var jq = $(this);
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
            obj.type = obj.$data.attr('Type');
            gpml.hoverObjects.push(obj);
        });
        
        //Get the species
        gpml.species = gpml.$data.find('Pathway').attr('Organism');
    }
    
    gpml.getHoverObject = function($svgObject, svg, e){
        var hover = null;
        
        var svgSize = {
            width: svg.getAttribute('width'),
            height: svg.getAttribute('height')
        };
        
        //Get point relative to svg object
        var offset = $svgObject.offset();
        
        var p = {
            x: (e.pageX - offset.left) / svg.currentScale - svg.currentTranslate.getX(),
            y: (e.pageY - offset.top) / svg.currentScale - svg.currentTranslate.getY()
        }
        
        //Translate coordinates from gpml to svg
        r = 1 / 15;
        
        for (var i in gpml.hoverObjects) {
            obj = gpml.hoverObjects[i];
            
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
                break;
            }
        }
        
        return hover;
    }
    
    gpml.mouseMove = function($svgObject, svg, e){
        var hover = gpml.getHoverObject($svgObject, svg, e);
        //TODO: set cursor to svg
        if (hover) {
            svg.setAttribute('cursor', 'pointer');
            //document.body.style.cursor = 'pointer';
        }
        else {
            svg.setAttribute('cursor', 'default');
            //document.body.style.cursor = 'default';
        }
    }
    
    gpml.mouseDown = function($svgObject, svg, e){
        var hover = gpml.getHoverObject($svgObject, svg, e);
        if (hover) {
            //Get the xref properties
            var jqxref = hover.$data.find('Xref');
            var id = jqxref.attr('ID');
            var ds = jqxref.attr('Database');
            
            //Open the xref info
            var title = hover.textLabel + ' (' + hover.type + ')';
            
            if (PathwayViewer_xrefinfo == 'popup') {
                //Open an xref dialog
                var $dialog = XrefPanel.createDialog(id, ds, gpml.species, hover.textLabel);
                $dialog.dialog('option', 'autoResize', false);
                $dialog.dialog('option', 'title', title);
                $dialog.dialog('option', 'position', [e.pageX - $(window).scrollLeft(), e.pageY - $(window).scrollTop()]);
                $dialog.dialog('open');
            }
            else {
                //Close current dialog
                if (gpml.currentDialog) {
                    gpml.currentDialog.dialog('close');
                }
                var $dialog = XrefPanel.createDialog(id, ds, gpml.species, hover.textLabel);
                $dialog.dialog('option', 'autoResize', false);
                $dialog.dialog('option', 'title', title);
                $dialog.dialog('option', 'position', [$svgObject.offset().left + $svgObject.width() + 10 - -$(window).scrollLeft(), $svgObject.offset().top - $(window).scrollTop()]);
                $dialog.dialog('option', 'height', $svgObject.height());
                
                $dialog.dialog('open');
                
                gpml.currentDialog = $dialog;
            }
        }
    }
    
    $.ajax({
        url: info.gpmlUrl,
        success: gpml.callback,
        error: function(xr, msg, ex){
            console.log("Error loading gpml: " + msg);
            console.error(ex);
        },
        dataType: "xml"
    });
    return gpml;
}

debug = function(text){
    $("#debug").html(text);
}

/**
 * Cross-browser xml parsing.
 * From http://www.w3schools.com/Dom/dom_parser.asp
 */
PathwayViewer.parseGPML = function(xml){
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
