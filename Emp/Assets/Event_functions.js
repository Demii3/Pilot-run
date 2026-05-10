$(document).ready(function(){
    $.get('../Modules/check_session.php', function(data){
        if(data == '0'){
            window.location = '../';
        }
    });

    // Logout handling is now done via direct links to logout_process.php
    };
});