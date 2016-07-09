window.page_load_time = Math.floor(Date.now() / 1000);
window.expire_time    = window.page_load_time + window.seconds_left;

window.updateTimeLeft = ->
    now = Math.floor Date.now() / 1000
    time_left = window.expire_time - now
    obj = document.getElementById 'session_expires'
    minutes = Math.floor time_left / 60
    seconds = time_left % 60
    if minutes+seconds > 0
        obj.innerHTML = ' (' + ('0' + minutes).slice(-2) + ':' + ('0' + seconds).slice(-2) + ')'
    else
        obj?.innerHTML = ''
        window.clearInterval window.updateTimer

window.updateTimer = window.setInterval 'updateTimeLeft();', 1000
