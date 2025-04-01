document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('signupForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    // Password requirements elements
    const length = document.getElementById('length');
    const uppercase = document.getElementById('uppercase');
    const lowercase = document.getElementById('lowercase');
    const number = document.getElementById('number');
    const special = document.getElementById('special');

    // Password validation function
    function validatePassword() {
        const value = password.value;
        
        // Check length
        if (value.length >= 8) {
            length.classList.add('valid');
        } else {
            length.classList.remove('valid');
        }
        
        // Check uppercase
        if (/[A-Z]/.test(value)) {
            uppercase.classList.add('valid');
        } else {
            uppercase.classList.remove('valid');
        }
        
        // Check lowercase
        if (/[a-z]/.test(value)) {
            lowercase.classList.add('valid');
        } else {
            lowercase.classList.remove('valid');
        }
        
        // Check number
        if (/\d/.test(value)) {
            number.classList.add('valid');
        } else {
            number.classList.remove('valid');
        }
        
        // Check special character
        if (/[!@#$%^&*]/.test(value)) {
            special.classList.add('valid');
        } else {
            special.classList.remove('valid');
        }
    }

    // Password match validation
    function validatePasswordMatch() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }

    // Event listeners
    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePasswordMatch);

    // Form submission
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });

    // Username validation
    const username = document.getElementById('username');
    username.addEventListener('input', function() {
        if (!/^[a-zA-Z0-9_]{3,50}$/.test(this.value)) {
            this.setCustomValidity('Username must be 3-50 characters long and can only contain letters, numbers, and underscores');
        } else {
            this.setCustomValidity('');
        }
    });

    // Email validation
    const email = document.getElementById('email');
    email.addEventListener('input', function() {
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
            this.setCustomValidity('Please enter a valid email address');
        } else {
            this.setCustomValidity('');
        }
    });
}); 