
function doToggle( elId, msg, expand, collapse ) {
    $("#"+elId+" .toggleMe").toggle();
    if( msg.innerHTML == expand ) {
        msg.innerHTML = collapse;
    } else {
        msg.innerHTML = expand;
    }
}

{
    // Closure city!
    /* Adapted from http://css-tricks.com/equal-height-blocks-in-rows/ */
    var currentTallest = 0,
    currentRowStart = 0,
    rowDivs = new Array(),
    topPosition = 0,
    leftMost = null;

    function alignRow( divs, tallest ) {
        for (var currentDiv = 0 ; currentDiv < divs.length ; currentDiv++) {
            divs[currentDiv].height(tallest);
        }
    }

    function itemOnRow( $el ) {
        topPosition = $el.position().top;

        if (leftMost === null) {
            // Grab the left position of the very first item
            leftMost = $el.position().left;
        }

        if (leftMost === $el.position().left ) {

            // Uncomment the following to highlight where this
            // function thinks a new row is starting.
            //$el.css("background", "red");

            // we just came to a new row.  Set all the heights
            // on the completed row
            alignRow(rowDivs, currentTallest);

            // set the variables for the new row
            rowDivs.length = 0; // empty the array
            currentRowStart = topPosition;
            currentTallest = $el.height() + 12;
            rowDivs.push($el);

        } else {
            // another div on the current row.  Add it to the
            // list and check if it's taller
            rowDivs.push($el);
            currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);

        }

        // do the last row
        alignRow(rowDivs, currentTallest);
    }
}

$(document).ready(
    function() {
        $('.browsePathways').each( function() {itemOnRow($(this)); });
    });

$(document).on('DOMNodeInserted', '.infinite-item',
               function(e) {
                   itemOnRow($(e.target));
               });