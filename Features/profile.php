<?php
// User Profile Edit Page
// Requires session to be logged in

/** @var mysqli $dbc */
session_start();

if (!isset($_SESSION['login']) || !isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit;
}

// Prevent caching to avoid showing logged-in content on back button
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

include __DIR__ . '/../Modules/dbcon.php';

$userId = $_SESSION['id'];

// Get current user info
/** @var string $name */
/** @var string $email */
/** @var string $username */
/** @var string|null $avatarPath */
$name = '';
$email = '';
$username = '';
$avatarPath = null;

$stmt = mysqli_prepare($dbc, "SELECT name, email, username, avatar_path FROM employees WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $name, $email, $username, $avatarPath);
if (!mysqli_stmt_fetch($stmt)) {
    header('Location: ../index.php');
    exit;
}
mysqli_stmt_close($stmt);

$currentAvatar = $avatarPath ? '../' . $avatarPath : '../Images/default-avatar.png';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../HR/Employee_management/employee_module.css">
    <link rel="icon" type="image/png" href="../Images/logo.jpg"/>
    <script src="../Modules/universal_logout_handler.js"></script>
    <style>
        .profile-card {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .avatar-container {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
        }
        .avatar-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid #3b82f6;
            object-fit: cover;
        }
        .avatar-upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            border: 2px solid white;
        }
        .avatar-upload-btn:hover {
            background: #2563eb;
        }
        #avatarInput {
            display: none;
        }
        .profile-header h2 {
            color: #1f2937;
            margin: 0;
            font-weight: 600;
        }
        .profile-header p {
            color: #6b7280;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .form-section {
            margin-bottom: 25px;
        }
        .form-section label {
            color: #374151;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        .form-section input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        .form-section input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn-save {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-save:hover {
            background: #2563eb;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .sidebar {
            min-height: calc(100vh - 70px);
        }
    </style>
</head>
<body>
    <nav class="custom-navbar">
        <div class="nav-left">
            <a class="logo-circle" href="../HR/index.php" aria-label="Go to Home">
                <img src="../Images/logo.jpg" alt="Logo">
            </a>
            <span class="company-name">Chengshi <br>Construction Corp</span>
        </div>
        <div class="nav-right">
            <button class="avatar" onclick="toggleMenu()">
                <img src="../Images/profilepic.jpg" alt="User">
            </button>
            <div id="profileMenu" class="dropdown-menu">
                <div class="profile-header">
                    <img src="../Images/profilepic.jpg" alt="User">
                    <span>User</span>
                </div>
                <a href="#" class="profile-item"> Settings & Privacy </a>
                <a href="#" class="profile-item"> Help & Support </a>
                <a href="../Modules/logout_process.php" class="profile-item" onclick="return handleLogout(event);"> Logout </a>
            </div>
        </div>
    </nav>

    <div class="employee-container">
        <div class="sidebar">
            <h2>My Account</h2>
            <button class="active" onclick="window.location.href='profile.php'">My Profile</button>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-container">
                    <img id="avatarImg" src="<?php echo htmlspecialchars($currentAvatar); ?>" alt="Avatar">
                    <div class="avatar-upload-btn" id="avatarUploadBtn" title="Click to upload new avatar">
                        📷
                    </div>
                    <input type="file" id="avatarInput" accept="image/*">
                </div>
                <h2><?php echo htmlspecialchars($name ?? ''); ?></h2>
                <p>@<?php echo htmlspecialchars($username ?? ''); ?></p>
            </div>

            <form id="profileForm">
                <div class="form-section">
                    <label for="fullName">Full Name</label>
                    <input 
                        type="text" 
                        id="fullName" 
                        name="name"
                        value="<?php echo htmlspecialchars($name ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-section">
                    <label for="emailAddr">Email Address</label>
                    <input 
                        type="email" 
                        id="emailAddr" 
                        name="email"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        required
                    >
                </div>

                <button type="submit" class="btn-save">Save Changes</button>
            </form>

            <div id="msg"></div>
        </div>
    </div>

    <div class="bg-container">
        <img src="../Images/bgimg.jpg" class="bg-image" alt="Background">
        <div class="overlay"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Set global attendance active status for logout handler
        window.isAttendanceActive = false;
        
        function toggleMenu() {
            document.getElementById('profileMenu').classList.toggle('active');
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('profileMenu');
            const avatar = document.querySelector('.avatar');
            if (!avatar.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('active');
            }
        });

        // Avatar upload
        document.getElementById('avatarUploadBtn').addEventListener('click', function() {
            document.getElementById('avatarInput').click();
        });

        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('avatar', file);

            $('#msg').removeClass().html('Uploading avatar...');

            $.ajax({
                url: 'api_avatar_upload.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(resp) {
                    try {
                        const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                        if (j.success) {
                            document.getElementById('avatarImg').src = j.avatar_url;
                            $('#msg').removeClass().addClass('alert alert-success').html(j.message || 'Avatar updated successfully');
                            setTimeout(() => $('#msg').fadeOut(), 3000);
                        } else {
                            $('#msg').removeClass().addClass('alert alert-danger').html(j.message || 'Failed to upload avatar');
                        }
                    } catch (e) {
                        $('#msg').removeClass().addClass('alert alert-danger').html('Unexpected response');
                    }
                },
                error: function() {
                    $('#msg').removeClass().addClass('alert alert-danger').html('Upload failed');
                }
            });
        });

        // Form submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = $('#fullName').val();
            const email = $('#emailAddr').val();

            $('#msg').html('Saving changes...').removeClass();

            $.post('api_profile_update.php', { 
                name: name, 
                email: email 
            }, function(resp) {
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    if (j.success) {
                        $('#msg').removeClass().addClass('alert alert-success').html(j.message || 'Profile updated successfully');
                        setTimeout(() => {
                            $('#msg').fadeOut();
                            location.reload();
                        }, 1500);
                    } else {
                        $('#msg').removeClass().addClass('alert alert-danger').html(j.message || 'Failed to update profile');
                    }
                } catch (e) {
                    $('#msg').removeClass().addClass('alert alert-danger').html('Unexpected response');
                }
            }).fail(function() {
                $('#msg').removeClass().addClass('alert alert-danger').html('Request failed');
            });
        });
    </script>
</body>
</html>
                    id="username" 
                    value="<?php echo htmlspecialchars($username); ?>"
                    disabled
                >
                <small class="text-muted">Username cannot be changed</small>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>

        <div id="msg" class="msg"></div>

        <hr>
        <div class="text-center">
            <a href="../index.php" class="btn btn-outline-secondary">Back to Home</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Set global attendance active status for logout handler
        window.isAttendanceActive = false;

        function toggleMenu() {
            document.getElementById('profileMenu').classList.toggle('active');
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('profileMenu');
            const avatar = document.querySelector('.avatar');
            if (!avatar.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('active');
            }
        });

        // Avatar upload
        $('#avatarUploadBtn').on('click', function(){
            $('#avatarInput').click();
        });

        $('#avatarInput').on('change', function(){
            const file = this.files[0];
            if (!file) return;

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showMsg('File too large (max 5MB)', 'danger');
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                showMsg('Please select an image file', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);

            $.ajax({
                url: 'api_avatar_upload.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(resp){
                    try {
                        const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                        if (j.success) {
                            $('#avatarImg').attr('src', j.avatar_url);
                            showMsg('Avatar updated', 'success');
                        } else {
                            showMsg(j.message || 'Failed to upload avatar', 'danger');
                        }
                    } catch(e) {
                        showMsg('Unexpected response', 'danger');
                    }
                },
                error: function(){
                    showMsg('Upload failed', 'danger');
                }
            });
        });

        // Profile update
        $('#profileForm').on('submit', function(e){
            e.preventDefault();
            const name = $('#fullName').val();
            const email = $('#emailAddr').val();

            showMsg('Saving...', 'info');

            $.post('api_profile_update.php', { name: name, email: email }, function(resp){
                try {
                    const j = typeof resp === 'object' ? resp : JSON.parse(resp);
                    if (j.success) {
                        showMsg(j.message || 'Profile updated', 'success');
                    } else {
                        showMsg(j.message || 'Failed to update', 'danger');
                    }
                } catch(e) {
                    showMsg('Unexpected response', 'danger');
                }
            }).fail(function(){
                showMsg('Request failed', 'danger');
            });
        });

        function showMsg(text, type){
            const typeClass = 'alert alert-' + type;
            $('#msg').removeClass().addClass(typeClass).text(text);
        }
    </script>
</body>
</html>
