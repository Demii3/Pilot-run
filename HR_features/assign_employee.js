$(function(){
  $('#assignBtn').on('click', function(){
    const userId = $('#employeeSelect').val();
    const locId = $('#siteSelect').val();
    $('#msg').removeClass().text('');
    if(!userId || !locId){
      $('#msg').addClass('alert alert-warning').text('Select both site and employee.');
      return;
    }
    $.post('api_assign_employee.php', { user_id: userId, loc_id: locId }, function(resp){
      try{
        const j = typeof resp === 'object' ? resp : JSON.parse(resp);
        if(j.success){
          $('#msg').addClass('alert alert-success').text(j.message || 'Assigned');
        } else {
          $('#msg').addClass('alert alert-danger').text(j.message || 'Failed');
        }
      }catch(e){
        $('#msg').addClass('alert alert-danger').text('Unexpected response');
      }
    }).fail(function(){
      $('#msg').addClass('alert alert-danger').text('Request failed');
    });
  });
});
