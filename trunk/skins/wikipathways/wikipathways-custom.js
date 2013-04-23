
function doToggle( elId, msg, expand, collapse ) {
    $("#"+elId+" .toggleMe").toggle();
    if( msg.innerHTML == expand ) {
        msg.innerHTML = collapse;
    } else {
        msg.innerHTML = expand;
    }
}