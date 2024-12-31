<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Point</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if the user is logged in by verifying session variables
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    ?>
      <nav class="navbar navbar-expand-lg bg-body-tertiary heronavbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../images/website/logo.png" alt="Job Point Logo" height="70">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/Job Point">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="jobs.php">Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="companies.php">Companies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Blog</a>
                    </li>
                </ul>
                <div class="d-flex button-container">
                    <a href="#" class="btn btn-custom btn-job-seeker" data-bs-toggle="modal" data-bs-target="#registrationModal" data-tab="job-seeker">
                        <i class="bi bi-person-circle"></i> JOB SEEKER
                    </a>
                    <a href="#" class="btn btn-custom btn-employer" data-bs-toggle="modal" data-bs-target="#registrationModal" data-tab="employer">
                        <i class="bi bi-briefcase-fill"></i> EMPLOYER
                    </a>
                    <a href="#" class="btn btn-custom btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="bi bi-box-arrow-in-right"></i> LOGIN
                    </a>
                </div>
            </div>
        </div>
    </nav>


    <script>
document.addEventListener('DOMContentLoaded', () => {
  const registrationModal = document.getElementById('registrationModal');
  const jobSeekerTab = document.getElementById('job-seeker-tab');
  const employerTab = document.getElementById('employer-tab');

  // Add event listeners to the buttons
  document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
    button.addEventListener('click', () => {
      const targetTab = button.getAttribute('data-tab');

      // Show the correct tab when the modal opens
      if (targetTab === 'job-seeker') {
        jobSeekerTab.click();
      } else if (targetTab === 'employer') {
        employerTab.click();
      }
    });
  });
});
</script>

<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registrationModalLabel">Registration Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="job-seeker-tab" data-bs-toggle="tab" data-bs-target="#job-seeker" type="button" role="tab" aria-controls="job-seeker" aria-selected="true">Job Seeker</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="employer-tab" data-bs-toggle="tab" data-bs-target="#employer" type="button" role="tab" aria-controls="employer" aria-selected="false">Employer</button>
          </li>
        </ul>
        <div class="tab-content pt-3" id="myTabContent">
          <div class="tab-pane fade show active" id="job-seeker" role="tabpanel" aria-labelledby="job-seeker-tab">
            <form method="post" action="../Process/register.php">
              <div class="mb-3">
                <label for="firstName" class="form-label">Name<span class="star">*</span></label>
                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter your first name" pattern="[A-Za-z]+" title="Only alphabets are allowed." required>
              </div>
              <div class="mb-3">
                <label for="middleName" class="form-label">Father's Name<span class="star">*</span></label>
                <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Enter your middle name" pattern="[A-Za-z]+" title="Only alphabets are allowed." required>
              </div>
              <div class="mb-3">
                <label for="lastName" class="form-label">Surname Name<span class="star">*</span></label>
                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter your last name" pattern="[A-Za-z]+" title="Only alphabets are allowed." required>
              </div>
              <div class="mb-3">
                <label for="gender" class="form-label">Gender<span class="star">*</span></label>
                <select class="form-select" id="gender" name="gender" required>
                  <option value="" disabled selected>Select gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="dateOfBirth" class="form-label">Date of Birth<span class="star">*</span></label>
                <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" required>
              </div>
              <script>
                const dobInput = document.getElementById('dateOfBirth');
                const dobError = document.getElementById('dobError');
                const today = new Date();
                const minDate = new Date(today.setFullYear(today.getFullYear() - 18)).toISOString().split('T')[0];
                dobInput.setAttribute('max', minDate);
                dobInput.addEventListener('change', () => {
                  if (dobInput.value > minDate) {
                    dobError.textContent = 'You must be at least 18 years old.';
                    dobInput.value = '';
                  } else {
                    dobError.textContent = '';
                  }
                });
              </script>
              <div class="mb-3">
                <label for="email" class="form-label">Email Address<span class="star">*</span></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
              </div>
              <div class="mb-3">
                <label for="Contact_No" class="form-label">Phone Number<span class="star">*</span></label>
                <input type="tel" class="form-control" id="Contact_No" name="Contact_No" placeholder="Enter your Phone Number" pattern="[0-9]{10}" title="Phone number must be 10 digits." required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password<span class="star">*</span></label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" minlength="8" title="Password must be at least 8 characters." required>
              </div>
              <div class="mb-3">
                <label for="serviceType" class="form-label">Service/Skill Type<span class="star">*</span></label>
                <select class="form-select" id="serviceType" name="serviceType" required>
                  <option value="" disabled selected>Select service type</option>
                  <option value="Law">Law</option>
                  <option value="Health">Health</option>
                  <option value="Medical">Medical</option>
                  <option value="Marketing">Marketing</option>
                  <option value="Real Estate">Real Estate</option>
                  <option value="Agriculture">Agriculture</option>
                  <option value="Consultants">Consultants</option>
                  <option value="Designing">Designing</option>
                  <option value="Services">Services</option>
                  <option value="Engineering">Engineering</option>
                  <option value="Call Center">Call Center</option>
                  <option value="E-Commerce">E-Commerce</option>
                  <option value="Transport">Transport</option>
                  <option value="Programming">Programming</option>
                  <option value="Banking/Finance">Banking/Finance</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="education" class="form-label">Highest Education Level<span class="star">*</span></label>
                <select class="form-select" id="education" name="education" required>
                  <option value="" disabled selected>Select education level</option>
                  <option value="Below 10th">Below 10th</option>
                  <option value="10th Pass">10th Pass</option>
                  <option value="12th Pass">12th Pass</option>
                  <option value="Diploma">Diploma</option>
                  <option value="Graduate">Graduate</option>
                  <option value="Postgraduate">Postgraduate</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="experience" class="form-label">Years of Experience<span class="star">*</span></label>
                <select class="form-select" id="experience" name="experience" required>
                  <option value="" disabled selected>Select years of experience</option>
                  <option value="0-1">0-1 years</option>
                  <option value="1-3">1-3 years</option>
                  <option value="3-5">3-5 years</option>
                  <option value="5+">5+ years</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself"></textarea>
              </div>
              <button type="submit" class="btn w-100" style="background-color: #0059b8; color: #ffffff; font-weight: bold;" name="register_jobseeker">Register</button>
            </form>
          </div>
          <div class="tab-pane fade" id="employer" role="tabpanel" aria-labelledby="employer-tab">
            <script>
              function toggleEmployerFields() {
                const employerType = document.querySelector('input[name="employer_type"]:checked').value;
                const individualFields = document.getElementById('individualFields');
                const organizationFields = document.getElementById('organizationFields');
                if (employerType === 'Individual') {
                  individualFields.style.display = 'block';
                  organizationFields.style.display = 'none';
                } else {
                  individualFields.style.display = 'none';
                  organizationFields.style.display = 'block';
                }
              }
            </script>
              <div class="mb-4">
                <label class="form-label">Employer Type<span class="star">*</span></label>
                
                <div class="mb-3 w-100">
                    <div class="d-flex justify-content-center align-items-center border rounded p-3 bg-light">
                        <div class="form-check mx-3 d-flex flex-column align-items-center">
                            <input type="radio" id="individual" name="employer_type" value="Individual" class="form-check-input" onchange="toggleEmployerFields()" required>
                            <label for="individual" class="form-check-label d-flex flex-column align-items-center">
                                <i class="bi bi-file-person-fill mb-1 text-primary" style="font-size: 1.5rem;"></i> 
                                Individual
                            </label>
                        </div>
                        <div class="form-check mx-3 d-flex flex-column align-items-center">
                            <input type="radio" id="organization" name="employer_type" value="Organization" class="form-check-input" onchange="toggleEmployerFields()" required>
                            <label for="organization" class="form-check-label d-flex flex-column align-items-center">
                                <i class="bi bi-building mb-1 text-primary" style="font-size: 1.5rem;"></i> 
                                Organization
                            </label>
                        </div>
                    </div>
                </div>


              </div>
              <div id="individualFields" style="display: none;">
                <form action="../Process/register_employer.php" method="post">
                    <div class="mb-3">
                    <label for="first_name" class="form-label">First Name<span class="star">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" pattern="[A-Za-z]+" title="Only alphabets are allowed." required>
                    </div>
                    <div class="mb-3">
                    <label for="middle_name" class="form-label">Middle Name<span class="star">*</span></label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" pattern="[A-Za-z]+" title="Only alphabets are allowed." required>
                    </div>
                    <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name<span class="star">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" pattern="[A-Za-z]+" title="Only alphabets are allowed." required>
                    </div>
                    <div class="mb-3">
                    <label for="building" class="form-label">Flat, House no.,Building, Company, Apartment<span class="star">*</span></label>
                    <input type="text" class="form-control" id="building" name="building" required>
                    </div>
                    <div class="mb-3">
                    <label for="street" class="form-label">Landmark ,Area, Street, Sector, Village<span class="star">*</span></label>
                    <input type="text" class="form-control" id="street" name="street" required>
                    </div>
                    <div class="mb-3">
                    <label for="city" class="form-label">City<span class="star">*</span></label>
                    <input type="text" class="form-control" id="city" name="city" required>
                    </div>
                    <div class="mb-3">
                    <label for="state" class="form-label">State<span class="star">*</span></label>
                    <input type="text" class="form-control" id="state" name="state" required>
                    </div>
                    <div class="mb-3">
                    <label for="country" class="form-label">Country<span class="star">*</span></label>
                    <input type="text" class="form-control" id="country" name="country" required>
                    </div>
                    <div class="mb-3">
                    <label for="pincode" class="form-label">Pincode<span class="star">*</span></label>
                    <input type="text" class="form-control" id="pincode" name="pincode" pattern="\d{6}" title="Enter a valid 6-digit pincode" required>
                    </div>
                    <div class="mb-3">
                    <label for="email" class="form-label">Email<span class="star">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                    <label for="password" class="form-label">Password<span class="star">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_no" class="form-label">Contact No<span class="star">*</span></label>
                        <input type="tel" class="form-control" id="contact_no" name="contact_no" pattern="\d{10}" title="Enter a valid 10-digit contact number" required>
                    </div>
                    <button type="submit" class="btn w-100" style="background-color: #0059b8; color: #ffffff; font-weight: bold;" name="register_individual">Register</button>
                </div>
              </form>
              <div id="organizationFields" style="display: none;">
              <form action="../Process/register_employer.php" method="post">
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name<span class="star">*</span></label>
                        <input type="text" class="form-control" id="company_name" name="company_name"  required>
                    </div>
                    <div class="mb-3">
                        <label for="registration_number" class="form-label">Registration Number<span class="star">*</span></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" minlength="21" maxlength="21" required>
                    </div>
                    <div class="mb-3">
                        <label for="building" class="form-label">Flat, House no.,Building, Company, Apartment<span class="star">*</span></label>
                        <input type="text" class="form-control" id="building" name="building" required>
                    </div>
                    <div class="mb-3">
                        <label for="street" class="form-label">Landmark ,Area, Street, Sector, Village<span class="star">*</span></label>
                        <input type="text" class="form-control" id="street" name="street" required>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City<span class="star">*</span></label>
                        <input type="text" class="form-control" id="city" name="city" pattern="[A-Za-z]+" required>
                    </div>
                    <div class="mb-3">
                        <label for="state" class="form-label">State<span class="star">*</span></label>
                        <input type="text" class="form-control" id="state" name="state" pattern="[A-Za-z]+" required>
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">Country<span class="star">*</span></label>
                        <input type="text" class="form-control" id="country" name="country" pattern="[A-Za-z]+" required>
                    </div>
                    <div class="mb-3">
                        <label for="pincode" class="form-label">Pincode<span class="star">*</span></label>
                        <input type="text" class="form-control" id="pincode" name="pincode" pattern="\d{6}" title="Enter a valid 6-digit pincode" required>
                    </div>
                    <div class="mb-3">
                        <label for="recruiter_name" class="form-label">Recruiter Name<span class="star">*</span></label>
                        <input type="text" class="form-control" id="recruiter_name" name="recruiter_name" pattern="[A-Za-z]+" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_contact_no" class="form-label">Company Contact No<span class="star">*</span></label>
                        <input type="tel" class="form-control" id="company_contact_no" name="company_contact_no" pattern="\d{10}" title="Enter a valid 10-digit contact number" required>
                    </div>
                    <div class="mb-3">
                        <label for="recruiter_contact_no" class="form-label">Recruiter Contact No<span class="star">*</span></label>
                        <input type="tel" class="form-control" id="recruiter_contact_no" name="recruiter_contact_no" pattern="\d{10}" title="Enter a valid 10-digit contact number" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_website" class="form-label">Company Website (Optional)</label>
                        <input type="url" class="form-control" id="company_website" name="company_website">
                    </div>
                    <div class="mb-3">
                        <label for="company_description" class="form-label">Company Description<span class="star">*</span></label>
                        <textarea class="form-control" id="company_description" name="company_description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email<span class="star">*</span></label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password<span class="star">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn w-100" style="background-color: #0059b8; color: #ffffff; font-weight: bold;" name="register_organization">Register</button>
                </form>
              </div>
             
              
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Custom JavaScript -->
<script>
  // Ensure only the active tab's content is visible
  document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    const tabContents = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
      button.addEventListener('click', function () {
        // Hide all tab contents
        tabContents.forEach(content => {
          content.classList.remove('show', 'active');
        });

        // Show the selected tab content
        const targetId = this.getAttribute('data-bs-target');
        const targetContent = document.querySelector(targetId);
        targetContent.classList.add('show', 'active');
      });
    });
  });
</script>


<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="loginForm" action="../Process/login.php" method="post">
                      <div class="mb-3">
                          <label for="loginIdentifier" class="form-label">Email or Mobile Number</label>
                          <input type="text" class="form-control" id="loginIdentifier" name="loginIdentifier" placeholder="Enter email or mobile number" required 
                              pattern="(^[0-9]{10}$|^([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$)" 
                              title="Please enter a valid email address or a 10-digit mobile number." 
                              oninput="validateInput(this)">
                      </div>

                      <div class="mb-3">
                          <label for="loginPassword" class="form-label">Password</label>
                          <input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="Enter password" required>
                      </div>
                      <div class="mb-3 text-end">
                          <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Forgot Password?</a>
                      </div>
                      <button type="submit" class="btn btn-primary w-100" name="login_user">Login</button>
                </form>
            </div>
        </div>
    </div>
  </div>


 <!-- Forgot Password Modal -->
 <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"> <!-- Bootstrap class for vertical centering -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm" action="../Process/forgot.php">
                        <div class="mb-3">
                            <label for="forgotEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="forgotEmail" placeholder="Enter your email address" required>
                        </div>
                        <div class="mb-3 text-end">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Back to Login</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php
  }else{
    if($_SESSION['user_type']==='Job Seeker'){
      ?>
      <nav class="navbar navbar-expand-lg bg-body-tertiary heronavbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../images/website/logo.png" alt="Job Point Logo" class="mainlogo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/Job Point">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Other Pages/jobs.php">Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Other Pages/companies.php">Companies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Blog</a>
                    </li>
                </ul>
                <div class="d-flex button-container">
                    <a href="../JobSeeker/account.php" class="btn btn-custom btn-employer">
                        <i class="bi bi-person-square"></i> ACCOUNT
                    </a>
                    <a href="../Process/logout.php" class="btn btn-custom btn-logout">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php
    }
    elseif($_SESSION['user_type']==='Employer Individual'){
    ?>

    <nav class="navbar navbar-expand-lg bg-body-tertiary heronavbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../images/website/logo.png" alt="Job Point Logo" class="mainlogo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/Job Point">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Other Pages/jobs.php">Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Other Pages/companies.php">Companies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Blog</a>
                    </li>
                </ul>
                <div class="d-flex button-container">
                    <a href="#" class="btn btn-custom btn-login" data-bs-toggle="modal" data-bs-target="#postJobModal" data-tab="postJobModal">
                        <i class="bi bi-pencil-square"></i> POST A JOB
                    </a>
                    <a href="../Employer/employer_individua_account.php" class="btn btn-custom btn-employer">
                        <i class="bi bi-person-square"></i> ACCOUNT
                    </a>
                    <a href="../Process/logout.php" class="btn btn-custom btn-logout">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Model for Employer In Organization -->
      <!-- Post Job Modal -->
      <div class="modal fade" id="postJobModal" tabindex="-1" aria-labelledby="postJobModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Large modal size -->
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="postJobModalLabel">Post a Job</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                      <form id="postJobForm" method="POST" action="../Process/post_job.php" enctype="multipart/form-data">
                      <!-- Featuring Image Section -->
                      <div class="mb-3">
                          <label for="featuringImage" class="form-label">Featuring Image (Optional)</label>
                          <div class="custom-file-upload" id="customFileUpload" onclick="document.getElementById('featuringImage').click();">
                              <input type="file" id="featuringImage" name="featuring_image" accept="image/*" style="display:none;" />
                              <span class="upload-icon">
                                  <i class="fas fa-upload"></i> <!-- Font Awesome upload icon -->
                              </span>
                              <span class="upload-text">Choose an image</span>
                          </div>
                          <img id="imagePreview" src="#" alt="Image Preview" style="display:none; margin-top:10px; max-width: 100%;" />
                      </div>

                      <script>
                      document.getElementById('featuringImage').addEventListener('change', function(event) {
                          const file = event.target.files[0];
                          const preview = document.getElementById('imagePreview');
                          
                          if (file) {
                              const reader = new FileReader();
                              reader.onload = function(e) {
                                  preview.src = e.target.result;
                                  preview.style.display = 'block'; // Show the image
                              }
                              reader.readAsDataURL(file);
                          } else {
                              preview.src = '#';
                              preview.style.display = 'none'; // Hide the image
                          }
                      });
                      </script>



                          <div class="mb-3">
                              <label for="jobTitle" class="form-label">Job Title<span class="text-danger">*</span></label>
                              <input type="text" class="form-control" id="jobTitle" name="job_title" placeholder="Enter job title" required>
                          </div>
                          <div class="mb-3">
                              <label for="jobType" class="form-label">Job Type<span class="text-danger">*</span></label>
                              <select class="form-select" id="jobType" name="job_type" required>
                                  <option value="">Select job type</option>
                                  <option value="Full-time">Full-time</option>
                                  <option value="Part-time">Part-time</option>
                                  <option value="Internship">Internship</option>
                                  <option value="Contract">Contract</option>
                              </select>
                          </div>
                          <div class="mb-3">
                              <label for="jobMode" class="form-label">Job Mode<span class="text-danger">*</span></label>
                              <select class="form-select" id="jobMode" name="job_mode" required>
                                  <option value="">Select job mode</option>
                                  <option value="Online">Online</option>
                                  <option value="Onsite">Onsite</option>
                                  <option value="Hybrid">Hybrid</option>
                              </select>
                          </div>
                          <div class="mb-3">
                              <label for="jobDescription" class="form-label">Job Description<span class="text-danger">*</span></label>
                              <textarea class="form-control" id="jobDescription" name="job_description" rows="4" placeholder="Enter job description" required></textarea>
                          </div>
                          <div class="mb-3">
                            <label for="education" class="form-label">Highest Education Level<span class="star">*</span></label>
                            <select class="form-select" id="education" name="education" required>
                              <option value="" disabled selected>Select education level</option>
                              <option value="Below 10th">Below 10th</option>
                              <option value="10th Pass">10th Pass</option>
                              <option value="12th Pass">12th Pass</option>
                              <option value="Diploma">Diploma</option>
                              <option value="Graduate">Graduate</option>
                              <option value="Postgraduate">Postgraduate</option>
                            </select>
                          </div>
                          <div class="mb-3">
                              <label for="skillsRequired" class="form-label">Type of Service/Skills Required<span class="text-danger">*</span></label>
                              <select class="form-select" id="serviceType" name="serviceType" required>
                              <option value="" disabled selected>Select service type</option>
                              <option value="Law">Law</option>
                              <option value="Health">Health</option>
                              <option value="Medical">Medical</option>
                              <option value="Marketing">Marketing</option>
                              <option value="Real Estate">Real Estate</option>
                              <option value="Agriculture">Agriculture</option>
                              <option value="Consultants">Consultants</option>
                              <option value="Designing">Designing</option>
                              <option value="Services">Services</option>
                              <option value="Engineering">Engineering</option>
                              <option value="Call Center">Call Center</option>
                              <option value="E-Commerce">E-Commerce</option>
                              <option value="Transport">Transport</option>
                              <option value="Programming">Programming</option>
                              <option value="Banking/Finance">Banking/Finance</option>
                              <option value="Other">Other</option>
                            </select>
                          </div>
                          <div class="mb-3">
                              <label for="applicationDeadline" class="form-label">Application Deadline<span class="text-danger">*</span></label>
                              <div class="d-flex">
                                  <input type="date" class="form-control w-50 me-2" id="applicationDeadline" name="application_deadline_date" required>
                                  <input type="time" class="form-control w-50" name="application_deadline_time" required>
                              </div>
                          </div>

                          <div class="mb-3">
                              <label for="vacancy" class="form-label">Number of Vacancies<span class="text-danger">*</span></label>
                              <input type="number" class="form-control" id="vacancy" name="vacancy" placeholder="Enter number of vacancies" required>
                          </div>
                          <div class="mb-3">
                              <label for="salary" class="form-label">Salary<span class="text-danger">*</span></label>
                              <input type="number" class="form-control" id="salary" name="salary" placeholder="Enter salary" required>
                          </div>
                          Job Location:
                          <div class="mb-3">
                          <label for="building" class="form-label">Flat, House no.,Building, Company, Apartment<span class="star">*</span></label>
                          <input type="text" class="form-control" id="building" name="building" required>
                          </div>
                          <div class="mb-3">
                          <label for="street" class="form-label">Landmark ,Area, Street, Sector, Village<span class="star">*</span></label>
                          <input type="text" class="form-control" id="street" name="street" required>
                          </div>
                          <div class="mb-3">
                          <label for="city" class="form-label">City<span class="star">*</span></label>
                          <input type="text" class="form-control" id="city" name="city" required>
                          </div>
                          <div class="mb-3">
                          <label for="state" class="form-label">State<span class="star">*</span></label>
                          <input type="text" class="form-control" id="state" name="state" required>
                          </div>
                          <div class="mb-3">
                          <label for="country" class="form-label">Country<span class="star">*</span></label>
                          <input type="text" class="form-control" id="country" name="country" required>
                          </div>
                          <div class="mb-3">
                          <label for="pincode" class="form-label">Pincode<span class="star">*</span></label>
                          <input type="text" class="form-control" id="pincode" name="pincode" pattern="\d{6}" title="Enter a valid 6-digit pincode" required>
                          </div>
                          
                          <button type="submit" class="btn btn-primary w-100" name="post_Job">Post Job</button>
                      </form>
                  </div>
              </div>
          </div>
      </div>

    <?php
    }
    elseif($_SESSION['user_type']==='Employer Organization'){
    ?>

    <nav class="navbar navbar-expand-lg bg-body-tertiary heronavbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../images/website/logo.png" alt="Job Point Logo" class="mainlogo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/Job Point">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Other Pages/jobs.php">Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Other Pages/companies.php">Companies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Blog</a>
                    </li>
                </ul>
                <div class="d-flex button-container">
                    <a href="#" class="btn btn-custom btn-login" data-bs-toggle="modal" data-bs-target="#postJobModal" data-tab="postJobModal">
                      <i class="bi bi-pencil-square"></i> POST A JOB
                    </a>
                    <a href="../Employer/employer_organization_account.php" class="btn btn-custom btn-employer" >
                        <i class="bi bi-person-square"></i> ACCOUNT
                    </a>
                    <a href="../Process/logout.php" class="btn btn-custom btn-logout">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Model for Employer In Organization -->
      <!-- Post Job Modal -->
      <div class="modal fade" id="postJobModal" tabindex="-1" aria-labelledby="postJobModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Large modal size -->
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="postJobModalLabel">Post a Job</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                      <form id="postJobForm" method="POST" action="../Process/post_job.php" enctype="multipart/form-data">
                      <!-- Featuring Image Section -->
                      <div class="mb-3">
                          <label for="featuringImage" class="form-label">Featuring Image (Optional)</label>
                          <div class="custom-file-upload" id="customFileUpload" onclick="document.getElementById('featuringImage').click();">
                              <input type="file" id="featuringImage" name="featuring_image" accept="image/*" style="display:none;" />
                              <span class="upload-icon">
                                  <i class="fas fa-upload"></i> <!-- Font Awesome upload icon -->
                              </span>
                              <span class="upload-text">Choose an image</span>
                          </div>
                          <img id="imagePreview" src="#" alt="Image Preview" style="display:none; margin-top:10px; max-width: 100%;" />
                      </div>

                      <script>
                      document.getElementById('featuringImage').addEventListener('change', function(event) {
                          const file = event.target.files[0];
                          const preview = document.getElementById('imagePreview');
                          
                          if (file) {
                              const reader = new FileReader();
                              reader.onload = function(e) {
                                  preview.src = e.target.result;
                                  preview.style.display = 'block'; // Show the image
                              }
                              reader.readAsDataURL(file);
                          } else {
                              preview.src = '#';
                              preview.style.display = 'none'; // Hide the image
                          }
                      });
                      </script>



                          <div class="mb-3">
                              <label for="jobTitle" class="form-label">Job Title<span class="text-danger">*</span></label>
                              <input type="text" class="form-control" id="jobTitle" name="job_title" placeholder="Enter job title" required>
                          </div>
                          <div class="mb-3">
                              <label for="jobType" class="form-label">Job Type<span class="text-danger">*</span></label>
                              <select class="form-select" id="jobType" name="job_type" required>
                                  <option value="">Select job type</option>
                                  <option value="Full-time">Full-time</option>
                                  <option value="Part-time">Part-time</option>
                                  <option value="Internship">Internship</option>
                                  <option value="Contract">Contract</option>
                              </select>
                          </div>
                          <div class="mb-3">
                              <label for="jobMode" class="form-label">Job Mode<span class="text-danger">*</span></label>
                              <select class="form-select" id="jobMode" name="job_mode" required>
                                  <option value="">Select job mode</option>
                                  <option value="Online">Online</option>
                                  <option value="Onsite">Onsite</option>
                                  <option value="Hybrid">Hybrid</option>
                              </select>
                          </div>
                          <div class="mb-3">
                              <label for="jobDescription" class="form-label">Job Description<span class="text-danger">*</span></label>
                              <textarea class="form-control" id="jobDescription" name="job_description" rows="4" placeholder="Enter job description" required></textarea>
                          </div>
                          <div class="mb-3">
                            <label for="education" class="form-label">Highest Education Level<span class="star">*</span></label>
                            <select class="form-select" id="education" name="education" required>
                              <option value="" disabled selected>Select education level</option>
                              <option value="Below 10th">Below 10th</option>
                              <option value="10th Pass">10th Pass</option>
                              <option value="12th Pass">12th Pass</option>
                              <option value="Diploma">Diploma</option>
                              <option value="Graduate">Graduate</option>
                              <option value="Postgraduate">Postgraduate</option>
                            </select>
                          </div>
                          <div class="mb-3">
                              <label for="skillsRequired" class="form-label">Type of Service/Skills Required<span class="text-danger">*</span></label>
                              <select class="form-select" id="serviceType" name="serviceType" required>
                              <option value="" disabled selected>Select service type</option>
                              <option value="Law">Law</option>
                              <option value="Health">Health</option>
                              <option value="Medical">Medical</option>
                              <option value="Marketing">Marketing</option>
                              <option value="Real Estate">Real Estate</option>
                              <option value="Agriculture">Agriculture</option>
                              <option value="Consultants">Consultants</option>
                              <option value="Designing">Designing</option>
                              <option value="Services">Services</option>
                              <option value="Engineering">Engineering</option>
                              <option value="Call Center">Call Center</option>
                              <option value="E-Commerce">E-Commerce</option>
                              <option value="Transport">Transport</option>
                              <option value="Programming">Programming</option>
                              <option value="Banking/Finance">Banking/Finance</option>
                              <option value="Other">Other</option>
                            </select>
                          </div>
                          <div class="mb-3">
                              <label for="applicationDeadline" class="form-label">Application Deadline<span class="text-danger">*</span></label>
                              <div class="d-flex">
                                  <input type="date" class="form-control w-50 me-2" id="applicationDeadline" name="application_deadline_date" required>
                                  <input type="time" class="form-control w-50" name="application_deadline_time" required>
                              </div>
                          </div>

                          <div class="mb-3">
                              <label for="vacancy" class="form-label">Number of Vacancies<span class="text-danger">*</span></label>
                              <input type="number" class="form-control" id="vacancy" name="vacancy" placeholder="Enter number of vacancies" required>
                          </div>
                          <div class="mb-3">
                              <label for="salary" class="form-label">Salary<span class="text-danger">*</span></label>
                              <input type="number" class="form-control" id="salary" name="salary" placeholder="Enter salary" required>
                          </div>
                          Job Location:
                          <div class="mb-3">
                          <label for="building" class="form-label">Flat, House no.,Building, Company, Apartment<span class="star">*</span></label>
                          <input type="text" class="form-control" id="building" name="building" required>
                          </div>
                          <div class="mb-3">
                          <label for="street" class="form-label">Landmark ,Area, Street, Sector, Village<span class="star">*</span></label>
                          <input type="text" class="form-control" id="street" name="street" required>
                          </div>
                          <div class="mb-3">
                          <label for="city" class="form-label">City<span class="star">*</span></label>
                          <input type="text" class="form-control" id="city" name="city" required>
                          </div>
                          <div class="mb-3">
                          <label for="state" class="form-label">State<span class="star">*</span></label>
                          <input type="text" class="form-control" id="state" name="state" required>
                          </div>
                          <div class="mb-3">
                          <label for="country" class="form-label">Country<span class="star">*</span></label>
                          <input type="text" class="form-control" id="country" name="country" required>
                          </div>
                          <div class="mb-3">
                          <label for="pincode" class="form-label">Pincode<span class="star">*</span></label>
                          <input type="text" class="form-control" id="pincode" name="pincode" pattern="\d{6}" title="Enter a valid 6-digit pincode" required>
                          </div>
                          
                          <button type="submit" class="btn btn-primary w-100" name="post_Job">Post Job</button>
                      </form>
                  </div>
              </div>
          </div>
      </div>


    <?php
    }
  ?>
    
    <?php
  // } else { ?>
   
  <?php
  }
} catch (Exception $e) {
  // Handle general errors
  error_log("Error: " . $e->getMessage());
  $_SESSION['status_title'] = "Error!";
  $_SESSION['status'] = "An unexpected error occurred.";
  $_SESSION['status_code'] = "error";
  header("Location: ../");
  exit();
}
?>


