<?php
session_start();
if (!isset($_SESSION['otp_verified'])) {
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
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
}

.container {
  width: 360px;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  overflow: hidden;
  position: relative;
  background-color: #f6f7ec;
}

.top-header {
  position: relative;
  width: 100%;
  height: 130px;
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
  margin-top: 80px;
  text-align: center;
}

form { 
  padding: 30px 30px 25px 30px;
}

form label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1c6641;
  display: block;
  margin-bottom: 8px;
  margin-top: 15px;
}

.form-group {
  margin-bottom: 0;
}

.input-wrapper {
  position: relative;
}

input[type="password"] {
  width: 100%;
  padding: 12px 40px 12px 14px; 
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

.action-btn {
  width: 100%;
  background-color: #0a0a0a;
  color: #f6f7ec;
  font-size: 1rem;
  font-weight: 700;
  border: none;
  border-radius: 8px;
  padding: 12px;
  margin-top: 20px;
  cursor: pointer;
  box-shadow: 1px 2px 6px rgba(0,0,0,0.3);
  transition: background-color 0.2s;
}
.action-btn:hover { background-color: #222; }

#password-feedback { 
  font-size: 0.55rem;
  text-align: center;
  margin-top: 10px;
}
#password-strength-text,
#password-match-text {
  font-size: 0.8rem;            /* Slightly smaller than default */
  font-weight: 600;             /* Semi-bold */
  margin: 6px 0;                /* Space between texts */
}

.status-weak { color: #CC0000; }
.status-moderate { color: #FFA500; }
.status-strong { color: #006400; }
.match-status-nomatch { color: #CC0000; }
.match-status-match { color: #006400; }

.match-status-nomatch { color: #CC0000; } /* Red if passwords don't match */
.match-status-match { color: #006400; }   /* Green if passwords match */

#strength-progress-container {
  height: 8px;
  width: 100%;
  background-color: #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
  margin: 6px 0 10px 0;
}
#strength-progress-bar {
  height: 100%;
  width: 0%;
  background-color: #2196F3;
  transition: width 0.3s ease, background-color 0.3s ease;
  border-radius: 4px;
}

.bar-weak { background-color: #CC0000 !important; }       /* Red */
.bar-moderate { background-color: #FFA500 !important; }   /* Yellow/Orange */
.bar-strong { background-color: #006400 !important; }     /* Green */

</style>
</head>
<body>

<div class="container">
  <div class="top-header">
    <h2>Reset Your Password</h2>
  </div>

  <form id="resetForm" action="reset_password_action.php" method="POST" onsubmit="return validateResetForm();">
    <label>New Password:</label>
    <div class="input-wrapper">
      <input type="password" id="password" name="password" required>
      <img 
        src="https://cdn-icons-png.flaticon.com/512/159/159604.png" 
        alt="Toggle Password" 
        class="toggle-password" 
        id="togglePassword"
        onclick="togglePasswordVisibility('password', 'togglePassword')" 
      />
    </div>
    
    <label>Confirm Password:</label>
    <div class="input-wrapper">
      <input type="password" id="confirm_password" name="confirm_password" required>
      <img 
        src="https://cdn-icons-png.flaticon.com/512/159/159604.png" 
        alt="Toggle Password" 
        class="toggle-password" 
        id="toggleConfirmPassword"
        onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')" 
      />
    </div>

    <!-- <div id="strength-progress-container">
      <div id="strength-progress-bar"></div>
    </div> -->
    
    <div id="password-feedback">
      <div id="password-strength-text">Password Strength</div>

      <div id="strength-progress-container">
        <div id="strength-progress-bar"></div>
    </div>

      <div id="password-match-text">Password Matching</div>
    </div>

    <button type="submit" class="action-btn">Update Password</button>
  </form>
</div>

<script>
// Toggle password visibility
function togglePasswordVisibility(inputId, toggleId) {
  const passwordInput = document.getElementById(inputId);
  const toggleIcon = document.getElementById(toggleId);

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleIcon.src = 'https://cdn-icons-png.flaticon.com/512/2767/2767146.png';
    toggleIcon.alt = 'Hide Password';
  } else {
    passwordInput.type = 'password';
    toggleIcon.src = 'https://cdn-icons-png.flaticon.com/512/159/159604.png';
    toggleIcon.alt = 'Show Password';
  }
}

// Password strength function (same criteria as signup)
function getPasswordStrengthData(password) {
  const MIN_LENGTH = 6;
  let score = 0, strengthClass = '', strengthText = '';

  if(password.length < MIN_LENGTH) return {score:0,strengthClass:'weak',strengthText:'Too short <br> (Minimum of 6 characters)'};

  const hasLowercase = /[a-z]/.test(password);
  const hasUppercase = /[A-Z]/.test(password);
  const hasNumber = /\d/.test(password);
  const hasSpecial = /[!@#$%^&*()_+={}\[\]|\\:;"'<,>.?/~`-]/.test(password);

  score += Math.min(20, password.length*2);
  if(hasLowercase) score +=20;
  if(hasUppercase) score +=20;
  if(hasNumber) score +=20;
  if(hasSpecial) score +=20;

  if(score<40){strengthClass='weak';strengthText='Weak';}
  else if(score<80){strengthClass='moderate';strengthText='Moderate';}
  else{strengthClass='strong';strengthText='Strong';}

  score = Math.min(100,score);
  return {score,strengthClass,strengthText};
}

// Update strength and match text
function handleFeedback() {
  const password = document.getElementById('password').value;
  const confirm = document.getElementById('confirm_password').value;

  const strengthData = getPasswordStrengthData(password);
  
  // Update progress bar
  const progressBar = document.getElementById('strength-progress-bar');
  progressBar.style.width = strengthData.score + '%';
  progressBar.className = 'bar-' + strengthData.strengthClass;

  // Update text
  const strengthTextElement = document.getElementById('password-strength-text');
  strengthTextElement.innerHTML = 'Password ' + strengthData.strengthText;
  strengthTextElement.className = 'status-' + strengthData.strengthClass;

  // Password match feedback
  const matchTextElement = document.getElementById('password-match-text');
  if(password && confirm){
    if(password === confirm){
      matchTextElement.textContent='Passwords Match';
      matchTextElement.className='match-status-match';
    } else {
      matchTextElement.textContent='Passwords Do Not Match';
      matchTextElement.className='match-status-nomatch';
    }
  } else {
    matchTextElement.textContent='Password Matching';
    matchTextElement.className='';
  }
}

// Validate on form submission
function validateResetForm() {
  handleFeedback();
  const password = document.getElementById('password').value;
  const confirm = document.getElementById('confirm_password').value;
  const strength = getPasswordStrengthData(password);

  if(password !== confirm){
    alert('Passwords must match.');
    return false;
  }

  if(strength.strengthClass==='weak'){
    alert('Password is too weak. Use a mix of upper/lowercase letters, numbers, and special characters.');
    return false;
  }

  return true;
}

// Attach input listeners
document.getElementById('password').addEventListener('input', handleFeedback);
document.getElementById('confirm_password').addEventListener('input', handleFeedback);
</script>

</body>
</html>