$(function(){
  let editAssignmentId = null;

  function showMessage(text, type) {
    $('#msg').removeClass().addClass(type ? 'alert ' + type : '').text(text);
  }

  function resetForm() {
    editAssignmentId = null;
    $('#siteSelect').val('');
    $('#employeeSelect').val('');
    $('#assignBtn').text('Assign');
    $('#cancelEditBtn').addClass('d-none');
  }

  function refreshAssignments() {
    $.get('api_assignments_list.php', function(resp) {
      const data = typeof resp === 'object' ? resp : JSON.parse(resp);
      const $body = $('#assignmentTableBody');
      $body.empty();
      if (!data.success) {
        $('#tableEmpty').text(data.message || 'Unable to load assignments');
        return;
      }
      const assignments = data.data || [];
      if (assignments.length === 0) {
        $('#tableEmpty').text('No assignments found.');
        return;
      }
      $('#tableEmpty').text('');
      assignments.forEach(function(item, index) {
        const employeeText = item.employee_name + (item.employee_username ? ' (' + item.employee_username + ')' : '');
        const row = '<tr>' +
          '<td>' + (index + 1) + '</td>' +
          '<td>' + $('<div>').text(employeeText).html() + '</td>' +
          '<td>' + $('<div>').text(item.site_name).html() + '</td>' +
          '<td class="text-end">' +
          '<button type="button" class="btn btn-sm btn-outline-primary me-1 edit-assignment" data-id="' + item.tb_id + '" data-employee="' + item.employee_id + '" data-site="' + item.site_id + '">Edit</button>' +
          '<button type="button" class="btn btn-sm btn-outline-danger delete-assignment" data-id="' + item.tb_id + '">Delete</button>' +
          '</td>' +
          '</tr>';
        $body.append(row);
      });
    }).fail(function(){
      $('#tableEmpty').text('Failed to load assignments.');
    });
  }

  function saveAssignment() {
    const userId = $('#employeeSelect').val();
    const locId = $('#siteSelect').val();
    if (!userId || !locId) {
      showMessage('Select both site and employee.', 'alert-warning');
      return;
    }

    const url = editAssignmentId ? 'api_assignment_update.php' : 'api_assign_employee.php';
    const data = editAssignmentId ? { assignment_id: editAssignmentId, employee_id: userId, site_id: locId } : { user_id: userId, loc_id: locId };

    $.post(url, data, function(resp) {
      const result = typeof resp === 'object' ? resp : JSON.parse(resp);
      if (result.success) {
        showMessage(result.message || (editAssignmentId ? 'Updated successfully' : 'Assigned successfully'), 'alert-success');
        resetForm();
        refreshAssignments();
      } else {
        showMessage(result.message || 'Save failed', 'alert-danger');
      }
    }).fail(function(){
      showMessage('Request failed', 'alert-danger');
    });
  }

  $('#assignBtn').on('click', saveAssignment);

  $('#cancelEditBtn').on('click', function(){
    resetForm();
    showMessage('', '');
  });

  $(document).on('click', '.edit-assignment', function(){
    editAssignmentId = $(this).data('id');
    $('#employeeSelect').val($(this).data('employee'));
    $('#siteSelect').val($(this).data('site'));
    $('#assignBtn').text('Update');
    $('#cancelEditBtn').removeClass('d-none');
    showMessage('Editing assignment. Update both fields and click Update or Cancel.', 'alert-info');
  });

  $(document).on('click', '.delete-assignment', function(){
    const assignmentId = $(this).data('id');
    if (!confirm('Delete this assignment?')) return;
    $.post('api_assignment_delete.php', { assignment_id: assignmentId }, function(resp){
      const result = typeof resp === 'object' ? resp : JSON.parse(resp);
      if (result.success) {
        showMessage(result.message || 'Assignment deleted', 'alert-success');
        if (editAssignmentId === assignmentId) resetForm();
        refreshAssignments();
      } else {
        showMessage(result.message || 'Delete failed', 'alert-danger');
      }
    }).fail(function(){
      showMessage('Delete request failed', 'alert-danger');
    });
  });

  refreshAssignments();
});
