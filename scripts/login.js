document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('generalError');
    const isNestedPage = window.location.pathname.includes('/exercises/')
        || window.location.pathname.includes('/english/')
        || window.location.pathname.includes('/french/')
        || window.location.pathname.includes('/spanish/')
        || window.location.pathname.includes('/latvian/');
    const basePath = isNestedPage ? '../' : './';

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (errorMessage) {
                errorMessage.textContent = '';
                errorMessage.style.display = 'none';
            }
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            try {
                const response = await fetch(basePath + 'api/login_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                });
                
                console.log('Pieteikties response status:', response.status);
                console.log('Pieteikties response headers:', Object.fromEntries(response.headers.entries()));
                
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    throw new Error('Serveris atgrieza nederīgu JSON atbildi');
                }
                
                if (data.success) {
                    localStorage.setItem('userSessionToken', data.session.session_token);
                    localStorage.setItem('user', JSON.stringify(data.user));

                    setTimeout(() => {
                        window.location.href = basePath + 'index.html';
                    }, 1000);
                } else {
                    if (errorMessage) {
                        errorMessage.textContent = data.message || 'Neizdevās pieteikties. Mēģini vēlreiz.';
                        errorMessage.style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Pieteikšanās kļūda:', error);
                if (errorMessage) {
                    errorMessage.textContent = 'Pieteikšanās laikā radās kļūda. Mēģini vēlreiz.';
                    errorMessage.style.display = 'block';
                }
            }
        });
    }
});
