checkValidity = ->
    if @required
        optId = @selectedIndex
        selOpt = @options[optId]
        if selOpt.value is ''
            @parentNode.classList.add 'is-invalid'
        else
            @parentNode.classList.remove 'is-invalid'

document.addEventListener 'DOMContentLoaded', (event) ->
    all_selects = document.getElementsByTagName 'select'
    for own i, xsel of all_selects
        xvalue = xsel.dataset.value
        #console.log 'Value of %o = %o', xsel, xvalue
        # Walk all options, compare to desired value and set if matches
        for o, ov of xsel.options
            if ov.value is xvalue
                xsel.selectedIndex = o
                break

        # Add eventlistener to change is-invalid state and run once
        xsel.addEventListener 'change', checkValidity
        event = document.createEvent 'HTMLEvents'
        event.initEvent 'change', true, true
        xsel.dispatchEvent event
