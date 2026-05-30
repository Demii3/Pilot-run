const searchEmployeeInput = document.getElementById('searchEmployeeInput');
const searchLocationInput = document.getElementById('newModalLocation');
const employeeSuggestionDropdown = document.getElementById('employeeSuggestionDropdown');
const locationSuggestionDropdown = document.getElementById('locationSuggestionDropdown');
const newModalId = document.getElementById('newModalId');
const newModalName = document.getElementById('newModalName');
const newModalDepartment = document.getElementById('newModalDepartment');
const newModalLocation = document.getElementById('newModalLocation');
const newModalCoordinates = document.getElementById('newModalLocationCoordinates');

if (employeeSuggestionDropdown) {
    employeeSuggestionDropdown.addEventListener('mousedown', function(e) {
        const option = e.target.closest('.employee-suggestion-item');
        if (!option) return;

        console.log('Selected employee:', {
            id: option.getAttribute('data-id'),
            name: option.getAttribute('data-name'),
            department: option.getAttribute('data-department')
        });

        // Populate the new modal fields with the selected employee's information
        newModalId.value = option.getAttribute('data-id');
        newModalName.value = option.getAttribute('data-name');
        newModalDepartment.value = option.getAttribute('data-department');
    });
}

if (locationSuggestionDropdown) {
    locationSuggestionDropdown.addEventListener('mousedown', function(e) {
        const option = e.target.closest('.location-suggestion-item');
        if (!option) return;

        console.log('Selected location:', {
            name: option.getAttribute('data-name'),
            coordinates: option.getAttribute('data-coordinates')
        });

        // Populate the new modal fields with the selected location's information
        newModalLocation.value = option.getAttribute('data-name');
        newModalCoordinates.value = option.getAttribute('data-coordinates');
    });
}

function empSearch() {
    const searchTerm = searchEmployeeInput.value;
    const payload = { searchTerm: searchTerm };
    fetch('./Modules/search_employee.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        // Populate the dropdown with employee suggestions
        locationSuggestionDropdown.innerHTML = '';
        data.forEach(employee => {
            let option = document.createElement('div');
            option.classList.add('col-md-12', 'employee-suggestion-item');
            option.setAttribute('data-id', employee.id);
            option.setAttribute('data-name', employee.name);
            option.setAttribute('data-department', employee.department);
            option.innerHTML = employee.name;
            employeeSuggestionDropdown.appendChild(option);
        });
        employeeSuggestionDropdown.classList.remove('d-none');
    });
}

searchEmployeeInput.addEventListener('focus', function() {
    empSearch();
});

searchEmployeeInput.addEventListener('focusout', function() {
    employeeSuggestionDropdown.innerHTML = '';
    employeeSuggestionDropdown.classList.add('d-none');
});

searchEmployeeInput.addEventListener('input', function() {
    const searchTerm = searchEmployeeInput.value;

    if (searchTerm.length < 2) {
        employeeSuggestionDropdown.classList.add('d-none');
        return;
    }

    empSearch();
});

function locSearch() {
    const searchTerm = searchLocationInput.value;
    const id = newModalId.value;
    const payload = { searchTerm: searchTerm, id: id };
    fetch('./Modules/search_location.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        // Populate the dropdown with location suggestions
        locationSuggestionDropdown.innerHTML = '';
        data.forEach(location => {
            let option = document.createElement('div');
            option.classList.add('col-md-12', 'location-suggestion-item');
            option.setAttribute('data-name', location.name);
            option.setAttribute('data-coordinates', location.coordinates);
            option.innerHTML = location.name;
            locationSuggestionDropdown.appendChild(option);
        });
        locationSuggestionDropdown.classList.remove('d-none');
    });
}

searchLocationInput.addEventListener('focus', function() {
    locSearch();
});

searchLocationInput.addEventListener('focusout', function() {
    locationSuggestionDropdown.innerHTML = '';
    locationSuggestionDropdown.classList.add('d-none');
});

searchLocationInput.addEventListener('input', function() {
    const searchTerm = searchLocationInput.value;

    if (searchTerm.length < 2) {
        locationSuggestionDropdown.classList.add('d-none');
        return;
    }

    locSearch();
});

