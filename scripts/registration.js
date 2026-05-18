document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        // Phone input formatting
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                // Remove all non-digit characters
                let value = e.target.value.replace(/\D/g, '');
                
                // Format the number based on length
                if (value.length > 0) {
                    // Add + at the start
                    value = '+' + value;
                    
                    // Add spaces and dashes for better readability
                    if (value.length > 3) {
                        value = value.slice(0, 3) + ' ' + value.slice(3);
                    }
                    if (value.length > 7) {
                        value = value.slice(0, 7) + '-' + value.slice(7);
                    }
                    if (value.length > 10) {
                        value = value.slice(0, 10) + '-' + value.slice(10);
                    }
                }
                
                e.target.value = value;
            });
        }

        // Birth date validation
        const birthDateInput = document.getElementById('birthDate');
        if (birthDateInput) {
            const today = new Date();
            const minAge = 13;
            const maxDate = new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate());
            birthDateInput.max = maxDate.toISOString().split('T')[0];
        }

        // Use a path relative to the current project copy so the request
        // does not accidentally go to another /api directory.
        const API_ENDPOINT = 'api/register_process.php';
        
        signupForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            // Get all form fields
            const formData = {
                username: document.getElementById('username').value.trim(),
                email: document.getElementById('email').value.trim(),
                firstName: document.getElementById('firstName').value.trim(),
                lastName: document.getElementById('lastName').value.trim(),
                country: document.getElementById('country').value,
                phone: document.getElementById('phone').value.trim(),
                birthDate: document.getElementById('birthDate').value,
                gender: document.getElementById('gender').value,
                password: document.getElementById('password').value.trim(),
                confirmPassword: document.getElementById('confirmPassword').value.trim()
            };
            
            // Clear previous errors
            document.querySelectorAll('.form-error').forEach(el => el.style.display = 'none');
            
            // Validate fields
            let hasError = false;

            if (formData.username.length < 3 || formData.username.length > 50) {
                showError('username', 'Lietotājvārdam jābūt no 3 līdz 50 rakstzīmēm');
                hasError = true;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
                showError('email', 'Lūdzu, ievadi derīgu e-pasta adresi');
                hasError = true;
            }

            if (formData.firstName.length < 2) {
                showError('firstName', 'Vārdam jābūt vismaz 2 rakstzīmju garam');
                hasError = true;
            }

            if (formData.lastName.length < 2) {
                showError('lastName', 'Uzvārdam jābūt vismaz 2 rakstzīmju garam');
                hasError = true;
            }

            if (!formData.country) {
                showError('country', 'Lūdzu, ievadi savu valsti');
                hasError = true;
            }

            // Phone validation - more flexible for international numbers
            if (formData.phone) {
                // Remove all non-digit characters for validation
                const phoneDigits = formData.phone.replace(/\D/g, '');
                if (phoneDigits.length < 8 || phoneDigits.length > 15) {
                    showError('phone', 'Lūdzu, ievadi derīgu tālruņa numuru (8-15 cipari)');
                    hasError = true;
                }
            }

            if (!formData.birthDate) {
                showError('birthDate', 'Lūdzu, izvēlies savu dzimšanas datumu');
                hasError = true;
            }

            if (!formData.gender) {
                showError('gender', 'Lūdzu, izvēlies dzimumu');
                hasError = true;
            }

            if (formData.password.length < 8) {
                showError('password', 'Parolei jābūt vismaz 8 rakstzīmju garai');
                hasError = true;
            }

            if (formData.password !== formData.confirmPassword) {
                showError('confirmPassword', 'Paroles nesakrīt');
                hasError = true;
            }

            if (hasError) {
                return;
            }

            // Disable submit button and show loading state
            const submitButton = signupForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Notiek reģistrācija...';
            
            try {
                const response = await fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const responseText = await response.text();
                    console.error('Non-JSON registration response:', responseText);
                    throw new Error('Server returned non-JSON response');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Reģistrācija veiksmīga. Lūdzu, pieslēdzies.');
                    
                    // Store user data if needed
                    if (data.user) {
                        localStorage.setItem('user', JSON.stringify(data.user));
                    }
                    
                    // Redirect to login page
                    window.location.href = 'login.html';
                } else {
                    // Show error message
                    showError(data.field || 'general', data.message);
                }
            } catch (error) {
                console.error('Registration error:', error);
                showError('general', 'Reģistrācijas laikā radās kļūda. Mēģini vēlreiz.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Reģistrēties';
            }
        });
    }
    
    function showError(field, message) {
        const errorElement = document.getElementById(field + 'Error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        } else {
            const generalError = document.getElementById('generalError');
            if (generalError) {
                generalError.textContent = message;
                generalError.style.display = 'block';
            }
        }
    }
});
