<?php
include 'config.php'; // your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Force role to PASSENGER
    $role = "PASSENGER";

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare query
    $stmt = $conn->prepare("INSERT INTO users (FirstName, LastName, Email, PasswordHash, Role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $firstname, $lastname, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        // redirect to login page
        header("Location: login.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Registration</title>
    <style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');

  * { box-sizing: border-box; }

  body {
    background-color: #d9eab1;
    font-family: 'Inter', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    -ms-overflow-style: none;
  }

   body::-webkit-scrollbar {
    display: none; /* Hides the scrollbar */
    width: 0; /* Ensures no width space is reserved for the scrollbar */
  }


  .container {
    width: 360px;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
    background-color: #f6f7ec;
    display: none;
  }

  .container.active { display: block; }

  .top-header {
    position: relative;
    width: 100%;
    height: 90px;
    background-color: #166943;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 75px;
  }

  .top-header h2 {
    color: #f6f7ec;
    font-size: 1.7rem;
    font-weight: 700;
    margin-top: 40px;
    text-align: center;
  }

  .back-arrow {
    position: absolute;
    top: 16px;
    left: 16px;
    cursor: pointer;
  }
  .back-arrow svg {
    width: 24px;
    height: 24px;
    fill: #f6f7ec;
  }

  form { padding: -0px 30px 30px 30px; }

  form label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1c6641;
    display: block;
    margin-bottom: 3px;
    margin-top: 16px;
  }

  .input-wrapper {
    position: relative;
  }

  input[type="text"], input[type="email"], input[type="password"] {
    width: 100%;
    padding: 10px 40px 10px 14px; /* space for eye icon */
    border-radius: 8px;
    border: none;
    box-shadow: inset 2px 2px 5px #cbe3a4, inset -2px -2px 5px #f6f7ec;
    font-size: 1rem;
    background-color: #f6f7ec;
    color: #333;
  }

  .toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    width: 22px;
    height: 22px;
    opacity: 0.7;
  }

  .checkbox-label {
    display: inline-flex;
    align-items: center;
    color: #1c6641;
    font-size: 0.875rem;
    user-select: none;
  }

  .forgot {
    color: #3c6463;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    text-decoration: none;
    margin-left: auto;
    user-select: none;
  }
  .forgot:hover { text-decoration: underline; }

  .action-btn {
    width: 100%;
    background-color: #0a0a0a;
    color: #f6f7ec;
    font-size: 1rem;
    font-weight: 700;
    border: none;
    border-radius: 8px;
    padding: 12px;
    margin: 24px 0 12px 0;
    cursor: pointer;
    box-shadow: 1px 2px 6px rgba(0,0,0,0.3);
  }
  .action-btn:hover { background-color: #222; }

  .social-text { font-size: 0.85rem; text-align: center; color: #556b39; margin-bottom: 16px; }
  .social-icons { display: flex; justify-content: center; gap: 20px; margin-bottom: 20px; }
  .social-icons img { width: 36px; height: 36px; cursor: pointer; }

  .bottom-text { font-size: 0.85rem; text-align: center; color: #446a2b; padding-bottom: 16px; }
  .bottom-text a { color: #4a6274; font-weight: 700; text-decoration: none; cursor:pointer; }
  .bottom-text a:hover { text-decoration: underline; }

label {
    color: #006400; /* Dark green from your image */
    font-weight: bold;
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
}

/* --- Input Wrapper (for positioning the eye icon) --- */
.input-wrapper {
    position: relative;
    margin-bottom: 10px; /* Space between input and feedback */
    max-width: 400px; /* Constrain width for example */
}

/* --- Input Field Styling --- */
input[type="password"],
input[type="email"],
input[type="text"] {
    width: 100%;
    padding: 10px 40px 10px 12px; /* Extra padding on right for icon */
    box-sizing: border-box;
    border-radius: 8px;
    border: 1px solid #38761D; /* Greenish border */
    background-color: #F8FFF0; /* Light greenish background */
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

input:focus {
    outline: none;
    border-color: #4CAF50; /* Lighter green on focus */
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
}

/* --- Toggle Password Icon Styling --- */
.toggle-password {
    position: absolute;
    right: 12px; /* Position from the right */
    top: 50%; /* Center vertically */
    transform: translateY(-50%); /* Adjust for true vertical centering */
    cursor: pointer;
    width: 20px; /* Icon size */
    height: 20px;
    opacity: 0.7; /* Slightly transparent */
    transition: opacity 0.2s ease;
}

.toggle-password:hover {
    opacity: 1; /* Fully opaque on hover */
}


/* --- Combined Password Feedback Area --- */
#combined-password-feedback {
    margin-top: 20px;
    max-width: 400px; /* Match input width */
    text-align: center;
    border-top: 1px solid #ddd; /* Separator line */
    padding-top: 15px;
}

/* Matching Text */
#password-match-text {
    font-size: 85%;
    font-weight: bold;
    color: #006400; /* Default neutral color */
    margin-bottom: 0px;
}

/* --- Strength Progress Bar --- */
#strength-progress-container {
    height: 8px; /* Height of the bar */
    width: 100%;
    background-color: #e0e0e0; /* Gray background for the empty part of the bar */
    border-radius: 4px; /* Rounded ends of the bar */
    overflow: hidden; /* Important to keep the inner bar rounded */
    margin: 10px 0;
}

#strength-progress-bar {
    height: 100%;
    width: 0%; /* Starts empty */
    background-color: #2196F3; /* Default blue color from your image */
    transition: width 0.3s ease, background-color 0.3s ease; /* Smooth animation */
    border-radius: 4px; /* Match container radius */
}

/* Strength Text */
#password-strength-text {
    font-size: 85%;
    font-weight: bold;
    color: #333; /* Default neutral color */
    margin-top: 0px;
}

/* --- Colors for Strength Feedback (Text and Bar) --- */

/* Weak */
.status-weak {
    color: #CC0000 !important; /* Red text */
}
.bar-weak {
    background-color: #CC0000 !important; /* Red bar fill */
}

/* Moderate */
.status-moderate {
    color: #FFA500 !important; /* Orange text */
}
.bar-moderate {
    background-color: #FFA500 !important; /* Orange bar fill */
}

/* Strong */
.status-strong {
    color: #006400 !important; /* Dark Green text */
}
.bar-strong {
    background-color: #006400 !important; /* Dark Green bar fill */
}

/* Matching Specific Colors */
.match-status-nomatch { color: #CC0000 !important; }
.match-status-match { color: #006400 !important; }
.match-status-warning { color: #FFA500 !important; }

</style>
</head>

<body>
  <div class="container sign-up active">
    <div class="top-header">
      <div class="back-arrow" id="back-to-login" title="Go Back">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
          <path d="M14 19l-7-7 7-7" stroke="#f6f7ec" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <h2>Passenger Sign Up</h2>
    </div>

    <?php if (!empty($message)): ?>
      <div class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

 <form method="POST" action="" onsubmit="return validateFormSubmission();">
 <label for="first-name">First Name</label>
 <input type="text" id="first-name" name="firstname" placeholder="John" minlength="2" required oninput="autoCapitalize(this)" />

 <label for="last-name">Last Name</label>
 <input type="text" id="last-name" name="lastname" placeholder="Smithson" minlength="2" required oninput="autoCapitalize(this)" />

  <label for="phone">Phone Number</label>
  <div class="phone-input-container" >
    
      <input type="text" id="phone" name="phone" class="phone-input" placeholder="09*********" pattern="^\d{11}$" title="Phone number must be exactly 11 digits" oninput="updateAsterisks(this)" />
  </div>
  
  <label for="email-signup">Email</label>
  <input type="email" id="email-signup" name="email" placeholder="User_Name!1@gmail.com" required oninput="validateStrictEmail(this)" />

<form onsubmit="return validateFormSubmission()">
  <label for="password-signup">Password</label>
  <div class="input-wrapper">
    <input 
      type="password" 
      id="password-signup" 
      name="password" 
      placeholder="Enter a password" 
      required 
      minlength="6" 
      oninput="handleCombinedFeedback()" 
    />
    <img 
      src="https://cdn-icons-png.flaticon.com/512/159/159604.png" 
      alt="Show Password" 
      class="toggle-password" 
      id="toggleSignupPassword"
      onclick="togglePasswordVisibility('password-signup', 'toggleSignupPassword')" 
      style="cursor: pointer;"
    />
  </div>

  <label for="confirm-password">Confirm Password</label>
  <div class="input-wrapper">
    <input 
      type="password" 
      id="confirm-password" 
      name="confirm_password" 
      placeholder="Confirm your password" 
      required 
      minlength="6" 
      oninput="handleCombinedFeedback()" 
    />
    <img 
      src="https://cdn-icons-png.flaticon.com/512/159/159604.png" 
      alt="Show Password" 
      class="toggle-password" 
      id="toggleConfirmPassword"
      onclick="togglePasswordVisibility('confirm-password', 'toggleConfirmPassword')" 
      style="cursor: pointer;"
    />
  </div>

  <div id="combined-password-feedback">
    <div id="password-match-text">Password Matching</div>
    <div id="strength-progress-container">
      <div id="strength-progress-bar"></div>
    </div>
    <div id="password-strength-text">Password Strength</div>
  </div>

  <button type="submit" class="action-btn">Register</button>

  <div class="bottom-text" style="margin-top: -10px;">
    Already have an account? <a href="login.php">Sign In</a>
  </div>
</form>


<script>
// 1. Auto-Capitalization and Length Check for Names
function autoCapitalize(inputField) {
  let value = inputField.value;

  // Step 1: Capitalize first letter, lowercase the rest
  if (value.length > 0) {
    const formattedValue = value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();

    // Only update if needed to avoid cursor jump
    if (inputField.value !== formattedValue) {
      inputField.value = formattedValue;
    }
  }

  // Step 2: Check length requirement (2 letters minimum)
  if (value.length < 2) {
    inputField.setCustomValidity('Name must be at least 2 characters.');
  } else {
    inputField.setCustomValidity('');
  }
}

// 2. Email Domain Check
function validateStrictEmail(inputField) {
  const emailValue = inputField.value.trim();

  // 1. Check for the required '@gmail.com' domain
  if (!emailValue.endsWith('@gmail.com')) {
    inputField.setCustomValidity('Email address must end with @gmail.com.');
    return;
  }

  // 2. Extract the local part (before @gmail.com)
  const localPart = emailValue.slice(0, -10);

  // 3. Basic local part validation: non-empty, no spaces, and valid characters
  const basicPattern = /^[a-zA-Z0-9._%+-]+$/;

  if (localPart.length === 0) {
    inputField.setCustomValidity('Email username cannot be empty.');
  } else if (!basicPattern.test(localPart)) {
    inputField.setCustomValidity('Email username contains invalid characters.');
  } else {
    inputField.setCustomValidity(''); // Valid
  }
}

// --- Helper function to get password strength data (returns score and classification) ---
function getPasswordStrengthData(password) {
    const MIN_LENGTH = 6;
    const MAX_LENGTH = 50; 
    let score = 0;
    let strengthClass = ''; 
    let strengthText = '';

    if (password.length === 0) {
        return { score: 0, strengthClass: '', strengthText: 'Enter a password' };
    }

    if (password.length < MIN_LENGTH) {
        return { score: 0, strengthClass: 'weak', strengthText: 'Too short' };
    }
    
    if (password.length > MAX_LENGTH) {
        return { score: 100, strengthClass: 'strong', strengthText: 'Strong (but too long!)' };
    }

    // Character type checks
    const hasLowercase = /[a-z]/.test(password);
    const hasUppercase = /[A-Z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecial = /[!@#$%^&*()_+={}\[\]|\\:;"'<,>.?/~`-]/.test(password); 
    
    // Scoring logic
    score += Math.min(20, password.length * 2); 
    if (hasLowercase) { score += 20; }
    if (hasUppercase) { score += 20; }
    if (hasNumber) { score += 20; }
    if (hasSpecial) { score += 20; }

    // Classification based on score
    if (score < 40) { 
        strengthClass = 'weak';
        strengthText = 'Weak';
    } else if (score < 80) { 
        strengthClass = 'moderate';
        strengthText = 'Moderate';
    } else { 
        strengthClass = 'strong';
        strengthText = 'Strong';
    }
    
    score = Math.min(100, score);

    return { score, strengthClass, strengthText };
}


// --- Main combined feedback handler (runs on input) ---
function handleCombinedFeedback() {
    const passwordInput = document.getElementById('password-signup');
    const confirmInput = document.getElementById('confirm-password');
    const password = passwordInput.value;
    const confirmPassword = confirmInput.value;

    // --- Update Password Strength ---
    const strengthData = getPasswordStrengthData(password);
    const progressBar = document.getElementById('strength-progress-bar');
    const strengthTextElement = document.getElementById('password-strength-text');

    progressBar.style.width = strengthData.score + '%';
    progressBar.className = 'bar-' + strengthData.strengthClass;
    strengthTextElement.textContent = 'Password ' + strengthData.strengthText;
    strengthTextElement.className = 'status-' + strengthData.strengthClass;


    // --- Update Password Matching Status ---
    const matchTextElement = document.getElementById('password-match-text');
    matchTextElement.className = ''; 

    if (password.length === 0 && confirmPassword.length === 0) {
        matchTextElement.textContent = 'Password Matching';
        matchTextElement.className = ''; 
    } else if (password.length > 0 && confirmPassword.length > 0) {
        if (password === confirmPassword) {
            matchTextElement.textContent = 'Password Matching';
            matchTextElement.className = 'match-status-match';
        } else {
            matchTextElement.textContent = 'Password Not Matching';
            matchTextElement.className = 'match-status-nomatch';
        }
    } else if (confirmPassword.length > 0) {
        matchTextElement.textContent = 'Password Matching: Fill main password';
        matchTextElement.className = 'match-status-warning';
    } else {
        matchTextElement.textContent = 'Password Matching: Confirm your password';
        matchTextElement.className = 'match-status-warning';
    }
}

// --- NEW VALIDATION FUNCTION (runs on form submit) ---
function validateFormSubmission() {
  const password = document.getElementById('password-signup').value;
  const confirmPassword = document.getElementById('confirm-password').value;
  const matchTextElement = document.getElementById('password-match-text');

  handleCombinedFeedback();

  // Clear live feedback before showing final error
  matchTextElement.textContent = '';

  // --- 1. Check for Password Match ---
  if (password !== confirmPassword) {
    matchTextElement.textContent = 'Submission Failed: Passwords must match.';
    matchTextElement.className = 'match-status-nomatch';
    return false;
  }

  // --- 2. Check for Minimum Length ---
  if (password.length < 6) {
    matchTextElement.textContent = 'Submission Failed: Password must be at least 6 characters.';
    matchTextElement.className = 'match-status-nomatch';
    return false;
  }

  // --- 3. Check for Weak Strength ---
  const strengthData = getPasswordStrengthData(password);
  if (strengthData.strengthClass === 'weak') {
    const strengthTextElement = document.getElementById('password-strength-text');
    strengthTextElement.textContent = 'Submission Failed: Password is too weak.';
    strengthTextElement.className = 'status-weak';
    return false;
  }

  return true;
}



// --- Toggle Password Visibility Function (for completeness) ---
function togglePasswordVisibility(inputId, toggleId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(toggleId);
    
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.src = 'https://cdn-icons-png.flaticon.com/512/2767/2767146.png'; // Slashed eye
      toggleIcon.alt = 'Hide Password';
    } else {
      passwordInput.type = 'password';
      toggleIcon.src = 'https://cdn-icons-png.flaticon.com/512/159/159604.png'; // Open eye
      toggleIcon.alt = 'Show Password';
    }
}

//phone number input asterisks update
  function updateAsterisks(inputField) {
  const maxLength = 11;
  
  // *** NEW LINE: Filter out all non-digit characters (\D) instantly ***
  inputField.value = inputField.value.replace(/\D/g, '');

  let currentLength = inputField.value.length;
  const remaining = maxLength - currentLength;
  const placeholderElement = document.getElementById('custom-placeholder');
  
  // 1. Limit the input length to 11 digits (if the filter above didn't catch it)
  if (currentLength > maxLength) {
    inputField.value = inputField.value.slice(0, maxLength);
    currentLength = maxLength; // Recalculate length after slicing
  }
  
  // 2. Generate the remaining asterisks
  if (remaining >= 0) {
    const asterisks = '*'.repeat(remaining);
    placeholderElement.textContent = asterisks;
  } else {
    // If input is complete, hide the placeholder
    placeholderElement.textContent = '';
  }
  
  // Call the counter update function if you still want the message
  // updatePhoneCounter(inputField); 
}

// NOTE: Ensure your existing updatePhoneCounter function is also defined.
   function togglePasswordVisibility(inputId, toggleId) {
    // Get the specific input element using the passed ID
    const passwordInput = document.getElementById(inputId);
    // Get the specific icon element using the passed ID
    const toggleIcon = document.getElementById(toggleId);
    
    // Check the current type of the input field
    if (passwordInput.type === 'password') {
      // 1. Show the password (change input type from 'password' to 'text')
      passwordInput.type = 'text';
      
      // 2. Change the icon to reflect the new state (Password is visible, so icon shows 'Hide')
      // Slashed eye URL used to indicate 'Hide Password'
      toggleIcon.src = 'https://cdn-icons-png.flaticon.com/512/2767/2767146.png'; 
      toggleIcon.alt = 'Hide Password';
      
    } else {
      // 1. Hide the password (change input type from 'text' back to 'password')
      passwordInput.type = 'password';
      
      // 2. Change the icon back (Password is hidden, so icon shows 'Show')
      // Open eye URL used to indicate 'Show Password'
      toggleIcon.src = 'https://cdn-icons-png.flaticon.com/512/159/159604.png';
      toggleIcon.alt = 'Show Password';
    }
}



</script>
  
</body>
</html>
