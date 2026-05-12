<!-- Login Card -->
<div class="login-wrapper">
    <div class="login-card">

        <h1>LOGIN ACCOUNT</h1>

        <form id="formlogin">

            <label>Username</label>
            <input type="text" name="username" id="username">

            <label>Password</label>
            <input type="password" name="password" id="password">

            <div class="remember">
                <input type="checkbox" name="remember">
                <span>Remember me.</span>
            </div>

            <button type="button" id="loginBtn">LOGIN</button>

            <div class="forgot">
                <a href="Features/forgot_password.php">Forgot your password?</a>
            </div>

        </form>

    </div>
</div>

<script>
    $(document).ready(function(){
        // Restore remembered credentials if present
        if(localStorage.getItem('remember') === 'true'){
            $('#username').val(localStorage.getItem('remember_username') || '');
            $('#password').val(localStorage.getItem('remember_password') || '');
            $('input[name="remember"]').prop('checked', true);
        }

        $("#loginBtn").click(function(){
            $.post("Modules/login_process.php",$("form#formlogin").serialize(),function(d){
                if(d=='success'){
                    if($('input[name="remember"]').is(':checked')){
                        localStorage.setItem('remember','true');
                        localStorage.setItem('remember_username',$('#username').val());
                        localStorage.setItem('remember_password',$('#password').val());
                    } else {
                        localStorage.removeItem('remember');
                        localStorage.removeItem('remember_username');
                        localStorage.removeItem('remember_password');
                    }
                    document.location = "./";
                } else if(d=='Inactive'){
                    alert('Your account is inactive. Please contact the administrator.');
                } else {
                    alert(d);
                }
            });
        });
    });
</script>