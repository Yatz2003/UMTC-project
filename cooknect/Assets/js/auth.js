document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const strengthBar = document.querySelector('.password-strength-bar');
    const passwordRequirements = document.querySelectorAll('.password-requirements li');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check password requirements
            const hasMinLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Update requirement indicators
            passwordRequirements[0].classList.toggle('valid', hasMinLength);
            passwordRequirements[1].classList.toggle('valid', hasUpperCase);
            passwordRequirements[2].classList.toggle('valid', hasLowerCase);
            passwordRequirements[3].classList.toggle('valid', hasNumbers);
            passwordRequirements[4].classList.toggle('valid', hasSpecialChars);
            
            // Calculate strength
            if (password.length > 0) strength += 20;
            if (password.length >= 8) strength += 20;
            if (hasUpperCase) strength += 20;
            if (hasNumbers) strength += 20;
            if (hasSpecialChars) strength += 20;
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Change color based on strength
            if (strength < 40) {
                strengthBar.style.backgroundColor = '#f44336';
            } else if (strength < 80) {
                strengthBar.style.backgroundColor = '#ff9800';
            } else {
                strengthBar.style.backgroundColor = '#4CAF50';
            }
        });
    }
    
    // Confirm password match
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword !== password) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });
    });
});