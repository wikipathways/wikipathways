/**
 * JS code to hook into mediawiki divs already in place to 
 * generate main pathway image, along with log in, edit, 
 * and download controls. This code does everything right 
 * up to the point where pvjs is integrated, including the
 * creation of the pwImage_pvjs div that pvjs targets.
 */ 

// mater variable for height of pvjs viewer container divs
var viewer_height = '500px';
var viewer_width = '100%';
var viewer_min_width = '700px';
var viewer_max_width = '900px';

/**
 *  When page is ready:
 *   1. Grab pwImage div; clean up <a>; remove <img>
 *   2. Prepare new divs inside thumbinner
 *   3. Animate window, if supported 
 *   4. Add final div for pvjs
 */
$(window).ready(function() {

	var img = $('#pwImage');
	if (typeof img.get(0) != 'undefined'){ //i.e., skip for PathwayWidget cases
	  if (img.get(0).nodeName.toLowerCase()!= 'img') {
		img = $('#pwImage img');
	  }
	}
	if (img.parent().is('a')){
		var oldParent=img.parent();
		var newParent=oldParent.parent();
		oldParent.after(img);
		oldParent.remove();
	}
	var container = $('<div />')
		.attr('id', 'pwImage_container')
		.css({	width: viewer_width, 
			'min-width': viewer_min_width, 
			'max-width': viewer_max_width, 
			height: viewer_height, 
			margin:'0 0 0 0' 
		}); 
	var parent = img.parent();
	img.after(container);
	img.remove();

        //Make room for the login/edit/download buttons at the bottom
        parent.css({
                padding: '3px 6px 30px 3px' 
        });     

        if (ie) { //Animate gives problems in IE, just change style directly
                parent.css({
                	width: viewer_width,
                	'min-width': viewer_min_width, 
                        'max-width': viewer_max_width, 
                        height: viewer_height
                });
                afterAnimate(container);
        } else { //Animate for smooth transition
                parent.animate({
                        width: viewer_width,
			'min-width': viewer_min_width, 
                        'max-width': viewer_max_width, 
                        height: viewer_height
                }, 300, afterAnimate(container));
        }

}); 

/**
 * Adds the final div and the future home of the pvjs code.
 */
var afterAnimate = function(c) {
        var pvjs = $('<div/>')
                .attr('id','pwImage_pvjs')
                .css({	width: viewer_width,
			'min-width': viewer_min_width, 
                        'max-width': viewer_max_width, 
                        height: viewer_height
		});
        c.append(pvjs);
};

/** 
 * A short snippet for detecting versions of IE in JavaScript
 * without resorting to user-agent sniffing
 * 
 * If you're not in IE (or IE version is less than 5) then:
 *     ie === undefined
 * If you're in IE (>=5) then you can determine which version:
 *     ie === 7;  // IE7
 * Thus, to detect IE:
 *     if (ie) {}
 * And to detect the version:
 *     ie === 6  // IE6
 *     ie > 7  // IE8, IE9 ...
 *     ie < 9 // Anything less than IE9
 */

var ie = (function(){

    var undef,
        v = 3,
        div = document.createElement('div'),
        all = div.getElementsByTagName('i');

    while (
        div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',
        all[0]
    );

    return v > 4 ? v : undef;

}());
