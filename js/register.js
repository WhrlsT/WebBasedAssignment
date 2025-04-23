document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded'); // Debug
    
    // Toggle password visibility for both password fields
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordField = this.previousElementSibling;
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        });
    });
    
    // Form validation
    const registerForm = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const registerBtn = document.querySelector('.register-btn');
    
    // Disable register button initially if it exists
    if (registerBtn) {
        registerBtn.disabled = true;
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            if (password.value !== confirmPassword.value) {
                event.preventDefault();
                alert('Passwords do not match!');
            }
        });
    }
    
    // OTP verification
    const sendVerificationBtn = document.getElementById('sendVerificationBtn');
    const verificationCode = document.getElementById('verificationCode');
    const verificationStatus = document.getElementById('verificationStatus');
    const emailInput = document.getElementById('email');
    
    console.log('Send button found:', !!sendVerificationBtn); // Debug
    
    // Format OTP input to only allow numbers
    if (verificationCode) {
        verificationCode.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Check if OTP is 6 digits
            if (this.value.length === 6) {
                verifyOTP(this.value);
            }
        });
    }
    
    // Send OTP button click handler
    if (sendVerificationBtn) {
        console.log('Adding click event to send button'); // Debug
        
        sendVerificationBtn.addEventListener('click', function() {
            console.log('Send button clicked'); // Debug
            
            const email = emailInput.value;
            
            if (!email) {
                verificationStatus.textContent = 'Please enter your email address first';
                verificationStatus.className = 'form-text text-danger';
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                verificationStatus.textContent = 'Please enter a valid email address';
                verificationStatus.className = 'form-text text-danger';
                return;
            }
            
            // Disable button and show loading state
            this.disabled = true;
            this.textContent = 'Sending...';
            verificationStatus.textContent = 'Sending OTP code...';
            verificationStatus.className = 'form-text text-info';
            
            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'process_register.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                console.log('Response received:', this.responseText); // Debug
                
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            verificationStatus.textContent = 'OTP code sent to your email';
                            verificationStatus.className = 'form-text text-success';
                            
                            // Start countdown for resend button
                            let countdown = 300; // 5 minutes in seconds
                            const sendBtn = document.getElementById('sendVerificationBtn');
                            sendBtn.disabled = true;
                            
                            const timer = setInterval(function() {
                                const minutes = Math.floor(countdown / 60);
                                const seconds = countdown % 60;
                                sendBtn.textContent = `Resend in ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                                countdown--;
                                
                                if (countdown < 0) {
                                    clearInterval(timer);
                                    sendBtn.textContent = 'Resend OTP';
                                    sendBtn.disabled = false;
                                }
                            }, 1000);
                        } else {
                            verificationStatus.textContent = response.message || 'Failed to send OTP code';
                            verificationStatus.className = 'form-text text-danger';
                            document.getElementById('sendVerificationBtn').disabled = false;
                            document.getElementById('sendVerificationBtn').textContent = 'Send OTP';
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e, this.responseText); // Debug
                        verificationStatus.textContent = 'Error processing response';
                        verificationStatus.className = 'form-text text-danger';
                        document.getElementById('sendVerificationBtn').disabled = false;
                        document.getElementById('sendVerificationBtn').textContent = 'Send OTP';
                    }
                } else {
                    verificationStatus.textContent = 'Server error, please try again';
                    verificationStatus.className = 'form-text text-danger';
                    document.getElementById('sendVerificationBtn').disabled = false;
                    document.getElementById('sendVerificationBtn').textContent = 'Send OTP';
                }
            };
            
            xhr.onerror = function() {
                console.error('XHR error'); // Debug
                verificationStatus.textContent = 'Connection error, please try again';
                verificationStatus.className = 'form-text text-danger';
                document.getElementById('sendVerificationBtn').disabled = false;
                document.getElementById('sendVerificationBtn').textContent = 'Send OTP';
            };
            
            const data = 'action=verify_email&email=' + encodeURIComponent(email);
            console.log('Sending data:', data); // Debug
            xhr.send(data);
        });
    } else {
        console.error('Send verification button not found!'); // Debug
    }
    
    // Function to verify OTP
    function verifyOTP(code) {
        if (!emailInput || !verificationStatus) {
            console.error('Required elements not found');
            return;
        }
        
        const email = emailInput.value;
        verificationStatus.textContent = 'Verifying OTP...';
        verificationStatus.className = 'form-text text-info';
        
        // Send AJAX request to verify code
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'process_register.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            console.log('Verify response:', this.responseText); // Debug
            
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    if (response.success) {
                        verificationStatus.textContent = 'OTP verified successfully';
                        verificationStatus.className = 'form-text text-success';
                        if (registerBtn) {
                            registerBtn.disabled = false;
                        }
                    } else {
                        verificationStatus.textContent = response.message || 'Invalid OTP code';
                        verificationStatus.className = 'form-text text-danger';
                        if (registerBtn) {
                            registerBtn.disabled = true;
                        }
                    }
                } catch (e) {
                    console.error('JSON parse error:', e, this.responseText); // Debug
                    verificationStatus.textContent = 'Error processing response';
                    verificationStatus.className = 'form-text text-danger';
                    if (registerBtn) {
                        registerBtn.disabled = true;
                    }
                }
            } else {
                verificationStatus.textContent = 'Server error, please try again';
                verificationStatus.className = 'form-text text-danger';
                if (registerBtn) {
                    registerBtn.disabled = true;
                }
            }
        };
        
        xhr.onerror = function() {
            console.error('XHR error during verification'); // Debug
            verificationStatus.textContent = 'Connection error, please try again';
            verificationStatus.className = 'form-text text-danger';
            if (registerBtn) {
                registerBtn.disabled = true;
            }
        };
        
        const data = 'action=check_code&email=' + encodeURIComponent(email) + '&code=' + encodeURIComponent(code);
        console.log('Sending verification data:', data); // Debug
        xhr.send(data);
    }
});