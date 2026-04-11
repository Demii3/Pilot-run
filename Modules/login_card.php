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
                <a href="#">Forgot your password?</a>
            </div>

        </form>

    </div>
</div>

<script>
    $(document).ready(function(){
        $("#loginBtn").click(function(){
            $.post("Modules/login_process.php",$("form#formlogin").serialize(),function(d){
                if(d=='success'){
                    alert(d);
                    document.location = "./";
                } else {
                    alert(d);
                }
            });
        });
    });
</script>