function checkValidity() {
    if (this.required) {
        var optId = this.selectedIndex;
        var selOpt = this.options[optId];
        if (selOpt.value == '') {
            this.parentNode.classList.add('is-invalid');
        } else {
            this.parentNode.classList.remove('is-invalid');
        }
    }
}

document.addEventListener("DOMContentLoaded", function(event) { 
    var all_selects = document.getElementsByTagName('select');
    for (var i in all_selects) {
        if (!all_selects.hasOwnProperty(i)) {
            continue;
        }
        var xsel = all_selects[i];
        var xvalue = xsel.dataset.value;
        //console.log('Value of %o = %o', xsel, xvalue);
        // Walk all options, compare to desired value and set if matches
        for (var o in xsel.options) {
            if (xsel.options[o].value == xvalue) {
                xsel.selectedIndex = o;
                break;
            }
        }

        // Add eventlistener to change is-invalid state and run once
        xsel.addEventListener('change', checkValidity);
        var event = document.createEvent('HTMLEvents');
        event.initEvent('change', true, true);
        console.log('Event is: %o', event);
        xsel.dispatchEvent(event);
    }
});
