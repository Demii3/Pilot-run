<div class="content-header">
    <h1>Employee Dashboard</h1>
    <div class="card">
        <h2>Attendance Module</h2>
        <p>Hello <span id="userName" class="userName">User</span>!, you may change your account details here</p>
    </div>
</div>

<div class="content-body">

    <div id="empInfocontainer" class="empIn-focontainer">
        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input id="name" type="text" class="form-control" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label">Department</label>
                <input id="department" type="text" class="form-control" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input id="email" type="text" class="form-control" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input id="Username" type="text" class="form-control" readonly>
            </div>

            <div id="passwordContainer" class="col-md-12 row g-3 d-none">
                <span><hr></span>
                <h4>Change Password</h4>
                <div class="col-md-6">
                    <label class="form-label">Current Password</label>
                    <input id="currentPassword" type="password" class="form-control">    
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Password</label>
                    <input id="newPassword" type="password" class="form-control">    
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input id="confirmPassword" type="password" class="form-control">
                    <label id="passwordError" class="form-label text-danger d-none">Passwords do not match.</label>
                </div>
            </div>

            <div class="col-md-6">
                <button id="editButton" class="btn btn-warning" onclick = "editEmpInfo()">Edit</button>
                <button id="changePasswordButton" class="btn btn-info d-none" onclick = "changePassword()">Change Password</button>
                <button id="cancelButton" class="btn btn-secondary d-none" onclick = "loadEmpInfoContent()">Cancel</button>
            </div>

            <div id='saveButtonContainer' class="col-md-6 d-none d-flex justify-content-end">
                <button id="saveButton" class="btn btn-primary" onclick = "saveInfo()">Save</button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="notification" tabindex="-1" aria-labelledby="notificationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="notificationLabel">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">Hello</div>
        </div>
    </div>
</div>



