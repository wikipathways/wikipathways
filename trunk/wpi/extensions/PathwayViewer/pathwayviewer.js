
//TODO: hyperlink cursor when over clickable object
//TODO: make viewer resizable (currently hard because resizing the flash object stretches the svg, maybe adjust viewport will help)

/**
 * Change this if the base path of the script (and resource files) is
 * different than the page root.
 */
if (typeof(PathwayViewer_basePath) == "undefined") 
    var PathwayViewer_basePath = '';

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
 * Postfix for layout container
 */
PathwayViewer.idLayout = '_layout';

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
    //PathwayViewer.loadSVG(); //Load svgweb after user starts viewer
    for (var i in PathwayViewer.pathwayInfo) {
        var info = PathwayViewer.pathwayInfo[i];
        if(info.start) {
        	PathwayViewer.startSVG(info);
        } else {
        	PathwayViewer.addStartButton(info);
        }
    }
    PathwayViewer.loadGPML();
}
$(window).load(PathwayViewer.onPageLoad);

PathwayViewer.showLoadProgress = function($container){
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

PathwayViewer.hideLoadProgress = function($container){
    $container.find('.progress_block').hide();
}

PathwayViewer.addFlashNotification = function(info){
    var $img = PathwayViewer.getImg(info.imageId);
    var $parent = $img.parent()
    if ($parent.is('a')) {
        $parent = $parent.parent();
    }
    
    var $flash = jQuery('<img>').attr('src', PathwayViewer_basePath + PathwayViewer.icons.getflash).attr('title', 'Install Flash player to zoom and view protein/metabolite info.');
    
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

PathwayViewer.removeStartButton = function(info){
    $('#' + info.imageId + PathwayViewer.idStartButton).remove();
}

PathwayViewer.addStartButton = function(info){
    //Test if a suitable renderer has been found by svgweb
    if (!svgweb.config.use) {
        //If not, instead of loading the svg, add a notification
        //to help the user to install flash
        PathwayViewer.addFlashNotification(info);
        return;
    }
    
    var $img = PathwayViewer.getImg(info.imageId);
    
    //If the img tag is nested in an anchor tag,
    //remove it
    if ($img.parent().is('a')) {
        var $oldParent = $img.parent();
        var $newParent = $oldParent.parent();
        $oldParent.after($img);
        $oldParent.remove();
    }
    
    //Create a start image
    var $parent = $img.parent()
    var $start = jQuery('<img>').attr('src', PathwayViewer_basePath + PathwayViewer.icons.start).attr('title', 'Click to activate pan and zoom.');
    var $div = jQuery('<div/>').attr("id", info.imageId + PathwayViewer.idStartButton);
    
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
    
    //Register the action
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

/**
 * Get the first img tag that's a child of the element
 * identified by id.
 */
PathwayViewer.getImg = function(id){
    var $img = $('#' + id);
    if ($img.get(0).nodeName.toLowerCase() != 'img') {
        //Get the IMG descendants
        $img = $('#' + id + ' img');
    }
    return $img;
}

/**
 * Start the viewer (after start button is clicked).
 */
PathwayViewer.startSVG = function(info){
    console.log("Starting svg viewer for " + info.imageId);
    
    PathwayViewer.removeStartButton(info);
    
    //Replace the image by the container with the svgweb object and xref panel
    var $img = PathwayViewer.getImg(info.imageId);
    
    var w = '100%'; if(info.width) w = info.width;
    var h = '500px'; if(info.height) h = info.height;
    
    var $container = $('<div />').attr('id', info.imageId + PathwayViewer.idContainer).css({
        width: w,
        height: h
    });
    
    var $parent = $img.parent();
    $img.after($container);
    $img.remove();
    
    //Create the layout pane
    var $layout = $('<div/>').attr('id', info.imageId + PathwayViewer.idLayout).css({
        width: '100%',
        height: '100%'
    });
    var $viewerpane = $('<div/>').addClass('ui-layout-center').css({
        border: '1px solid #BBBBBB',
        'background-color': '#FFFFFF'
    });
    var $xrefpane = $('<div/>').addClass('ui-layout-east');
    $layout.append($viewerpane);
    $layout.append($xrefpane);
    
    var afterAnimate = function(){
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
        
        $viewerpane.css({
            overflow: 'hidden',
            'background-color': '#F9F9F9'
        });
        PathwayViewer.showLoadProgress($layout);
        
        //Add the SVG object to the center panel
        var obj_id = info.imageId + PathwayViewer.idSvgObject;
        
        var obj = document.createElement('object', true);
        obj.id = obj_id;
        obj.setAttribute('type', 'image/svg+xml');
        obj.setAttribute('data', info.svgUrl);
        //Set to maximum size, so all content will be displayed after resizing parent
        //Ideally we would use relative size here ('100%'), but this causes the
        //SVG to stretch on resizing the parent
        obj.setAttribute('width', screen.width + 'px');
        obj.setAttribute('height', screen.height + 'px');
        obj.addEventListener('load', function(){
            var $svgObject = $('#' + info.imageId + PathwayViewer.idSvgObject);
            var svgRoot = $svgObject.get(0).contentDocument.rootElement;
            PathwayViewer.svgLoaded(info, $viewerpane, $xrefpane, $svgObject, svgRoot, layoutUtil);
            //Remove progress when loaded
            PathwayViewer.hideLoadProgress($layout);
        }, false);
        
        svgweb.appendChild(obj, $viewerpane.get(0));
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

PathwayViewer.svgLoaded = function(info, $container, $xrefContainer, $svgObject, svgRoot, layout){
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
            gpml.mouseDown(layout, $xrefContainer, $svgObject, svgRoot, e);
        });
    }
    $svgObject.mousewheel(function(e){
        PathwayViewer.mouseWheel(e, svgRoot, $svgObject);
    });
    
    //Show the pan and zoom buttons
    PathwayViewer.addControls($container, svgRoot, info.imageId + PathwayViewer.idControls);
    PathwayViewer.zoomFit(svgRoot, $container.width(), $container.height());
}

PathwayViewer.addControls = function($container, svgRoot, id){
    var s = 5;
    var w = 20;
    var h = 20;
    
    var totalWidth = (w + s) * 3;
    
    var $controls = jQuery('<div />').attr('unselectable', 'on').attr('id', id);
    
    var create = function(src, fn, left, top, title){
        var btn = jQuery('<div />').addClass('').css({
            position: 'relative',
            left: left + 'px',
            top: top + 'px',
            width: w + 'px',
            height: h + 'px',
            'background-image': 'url(' + src + ')'
        });
        btn.click(function(){
            fn(svgRoot);
        });
        btn.attr("title", title);
        return btn;
    };
    
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.up, PathwayViewer.panUp, -s - 1.5 * w, s, "Pan up"));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.left, PathwayViewer.panLeft, -s - 2 * w, s, "Pan left"));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.right, PathwayViewer.panRight, -s - w, -w + s, "Pan right"));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.down, PathwayViewer.panDown, -s - 1.5 * w, -w + s, "Pan down"));
    
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.zin, function(){
        PathwayViewer.zoomIn(svgRoot, $container);
    }, -s - 1.5 * w, s, "Zoom in"));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.zfit, function(){
        PathwayViewer.zoomFit(svgRoot, $container.width(), $container.height());
    }, -s - 1.5 * w, s, "Zoom to fit"));
    $controls.append(create(PathwayViewer_basePath + PathwayViewer.icons.zout, function(){
        PathwayViewer.zoomOut(svgRoot, $container);
    }, -s - 1.5 * w, s, "Zoom out"));
    $container.append($controls);
    
    //Set correct position
    $controls.css({
        position: 'absolute',
        left: '100%',
        top: '0',
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
    
    //Center
    svg.currentTranslate.setX(0.5 * fw / r - w / 2);
    svg.currentTranslate.setY(0.5 * fh / r - h / 2);
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
        
        //Determine the translation factor from GPML to svg coordinates
        var r = 1;
        var gpmlNs = gpml.$data.find('Pathway').attr('xmlns');
        var res = gpmlNs.match(/([0-9]{4})[a-z]{0,1}$/);
        if(res) {
            var ver = res[1];
            if(ver < 2010) r = 1 / 15;
        }
        gpml.scale = r;
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
        
        var r = gpml.scale;
                
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
    
    gpml.mouseDown = function(layout, $xrefContainer, $svgObject, svg, e){
        var hover = gpml.getHoverObject($svgObject, svg, e);
        if (hover) {
            //Get the xref properties
            var jqxref = hover.$data.find('Xref');
            var id = jqxref.attr('ID');
            var ds = jqxref.attr('Database');
            
            //Open the xref info
            var title = hover.textLabel + ' (' + hover.type + ')';
            
            var $panel = XrefPanel.create(id, ds, gpml.species, hover.textLabel);
            $xrefContainer.append($panel);
            $xrefContainer.children().hide();
            $panel.show();
            layout.open('east');
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
