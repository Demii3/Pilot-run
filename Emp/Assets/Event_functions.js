$(document).ready(function(){
    $.get('../Modules/check_session.php', function(data){
        if(data == '0'){
            window.location = '../';
        }
    });

    const params = new URLSearchParams(window.location.search);
    const paramValue = params.get('logout');

    if(paramValue === 'logout') {
        $.post('./Modules/logout.php', function(response){
            if(response === 'success') {
                window.location = '../';
            } else {
                console.error('Logout failed');
            }
        }).fail(function() {
            console.error('Error during logout request');
        });
    };
});