
// Define the department options for each faculty
const departmentOptions = {
    "Applied Sciences": [
        "BSc. Computer Science",
        "BSc. Environmental Science",
        "BSc. Information Technology",
        "Diploma in Environment, Health and Safety",
        "Diploma in Librarianship Studies"
    ],
    "Business School": [
        "BSc. Accounting with Computing",
        "BSc. Banking and Finance",
        "BSc. Economics and Statistics",
        "BBA Marketing",
        "BBA Human Resource Management",
        "BBA Accounting",
        "BBA Management",
        "Diploma in Banking Technology & Accounting",
        "Diploma in Business Administration",
        "Diploma in Computerized Accounting"
    ],
    "Health Sciences": [
        "BSc. Dental therapy",
        "BSc. Medical Laboratory Technology",
        "BSc. Midwifery",
        "BSc. Nursing",
        "BSc. Physician Assistantship Studies",
        "Diploma In Medical Laboratory Technology"
    ],
    "School of Graduate Studies & Research": [
        "Msc. Midwifery"
    ]
};

// Get the faculty and department select elements
const facultySelect = document.getElementById("faculty");
const departmentSelect = document.getElementById("department");

// Event listener for faculty change
facultySelect.addEventListener("change", function() {
    const selectedFaculty = this.value;
    populateDepartmentOptions(selectedFaculty);
});

// Function to populate the department options based on the selected faculty
function populateDepartmentOptions(selectedFaculty) {
    // Clear the current department options
    departmentSelect.innerHTML = "<option value=''>Select Department</option>";

    // Get the department options for the selected faculty
    const departments = departmentOptions[selectedFaculty];

    // Add the department options to the select element
    if (departments) {
        departments.forEach(function (department) {
            const option = document.createElement("option");
            option.value = department;
            option.textContent = department;
            departmentSelect.appendChild(option);
        });
    }
}


// Event listener for form submission
document.getElementById("signup-form").addEventListener("submit", function(event) {
    event.preventDefault();

    // Get form data
    const formData = new FormData(this);

    // Send the form data to the PHP file for processing
    fetch("register.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Display the response message
        document.getElementById("success-message").textContent = data;
    })
    .catch(error => {
        console.error(error);
    });
});