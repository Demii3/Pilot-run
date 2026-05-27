<div class="content-header">
    <h1>Employee Dashboard</h1>
    <div class="card">
        <h2>History Module</h2>
        <p>Hello <span class="userName">User</span>!, This is your attendance history</p>
    </div>
</div>

<div class="content-body">

    
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


