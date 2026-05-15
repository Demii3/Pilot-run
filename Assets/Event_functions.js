document.getElementById('userName').addEventListener('input', function(event) {
    if (event.target.value.length == 0) {
        event.target.classList.add('input-error');
    } else {
        event.target.classList.remove('input-error');
    }
});

document.getElementById('passWord').addEventListener('input', function(event) {
    if (event.target.value.length == 0) {
        event.target.classList.add('input-error');
    } else {
        event.target.classList.remove('input-error');
    }
});