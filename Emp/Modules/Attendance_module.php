<div class="content-header">
    <h1>Employee Dashboard</h1>
    <div class="card">
        <h2>Attendance Module</h2>
        <p>Hello <span class="userName">User</span>!, you are currently <span class="userTapStatus">#</span></p>
    </div>
</div>

<div class="content-body">
    <div id="Map-container" class="map-container"></div>

    <div class="welcome-card-container">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcomecard-left">
                <button id="tapIn" class="btn btn-success" type="button">
                    Tap In
                </button>
                <button id="refreshLocation" class="btn btn-warning" type="button">
                    Refresh Location
                </button>
                <button id="returnToHome" class="btn btn-primary" type="button">
                    Return to Home
                </button>
            </div>
        </div>
        
        <div class="welcome-card">
            <div class="welcomecard-right">
                <p id="month"></p>
                <p id="day"></p>
                <p id="year"></p>
                <h2 id="time"></h2>
            </div>
        </div>
    </div>
</div>


