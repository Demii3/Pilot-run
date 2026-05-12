<div class="content-header">
    <h1>Employee Dashboard</h1>
    <div class="card">
        <h2>Attendance Module</h2>
        <p>Hello <span class="userName">User</span>!, you are currently <span class="userTapStatus">#</span></p>
    </div>
</div>

<div class="content-body">

    <div id="locationSelector">
        <select id="locationSelect" class="form-select">
            <option value="" disabled selected>Select Location</option>
        </select>
    </div>

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

<!-- Bootstrap Modal -->
<div class="modal fade" id="helloModal" tabindex="-1" aria-labelledby="helloModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="helloModalLabel">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You have successfully tapped in.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="goodbyeModal" tabindex="-1" aria-labelledby="goodbyeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="goodbyeModalLabel">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You have successfully tapped out.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Location Error Modal -->
<div class="modal fade" id="locationErrorModal" tabindex="-1" aria-labelledby="locationErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="locationErrorModalLabel">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="locationErrorMessage">
                Please select a location first.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


