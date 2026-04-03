<!-- Welcome Card -->
<div class="welcome-card">
    <div class="welcomecard-left">
        <img src="../Images/profilepic.jpg" class="profile-img">
        <div>
            <p class="welcome-text">Welcome!</p>
            <h4 class="user-name"><?php echo $_SESSION['username']; ?></h4>
        </div>
    </div>

    <div class="welcomecard-right">
        <p id="month"></p>
        <p id="day"></p>
        <p id="year"></p>
        <h2 id="time"></h2>
    </div>
</div>
