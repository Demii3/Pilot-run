<?php
// User Profile Edit Page
// Requires session to be logged in

session_start();

if (!isset($_SESSION['login']) || !isset($_SESSION['id'])) {
    header('location: ../index.php');
    exit;
}

include __DIR__ . '/../Modules/dbcon.php';

$userId = $_SESSION['id'];

// Get current user info
$stmt = mysqli_prepare($dbc, "SELECT id, name, email, username, avatar_path FROM employees WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $id, $name, $email, $username, $avatarPath);
if (!mysqli_stmt_fetch($stmt)) {
    header('location: ../index.php');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 0 0 20px 20px;
        }
        .avatar-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }
        .avatar-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
        }
        .avatar-upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .avatar-upload-btn:hover {
            background: #f0f0f0;
        }
        #avatarInput {
            display: none;
        }
        .profile-form {
            max-width: 500px;
            margin: 30px auto;
        }
        .msg {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-header">
        <div class="avatar-container">
            <img id="avatarImg" src="<?php echo htmlspecialchars($currentAvatar); ?>" alt="Avatar">
            <div class="avatar-upload-btn" id="avatarUploadBtn">
                <span style="font-size: 20px;">📷</span>
            </div>
            <input type="file" id="avatarInput" accept="image/*">
        </div>
        <h2><?php echo htmlspecialchars($name); ?></h2>
        <p class="mb-0">@<?php echo htmlspecialchars($username); ?></p>
    </div>

    <div class="profile-form">
        <form id="profileForm">
            <div class="mb-3">
                <label for="fullName" class="form-label">Full Name</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="fullName" 
                    name="name"
                    value="<?php echo htmlspecialchars($name); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="emailAddr" class="form-label">Email</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="emailAddr" 
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input 
                    type="text" 
                    class="form-control" 
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
