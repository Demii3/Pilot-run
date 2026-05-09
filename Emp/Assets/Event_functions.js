$(document).ready(function(){
    $.get('../Modules/check_session.php', function(data){
        if(data == '0'){
            window.location = '../';
        }
    });
});