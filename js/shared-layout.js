/**
 * Shared Layout Script
 * Loads header and footer components, manages theme, profile, and authentication
 */

// Determine the base path for assets (handles subdirectories)
function getBasePath() {
    const pathSegments = window.location.pathname.split('/');
    // If the current file is in a subdirectory, go up one level
    if (pathSegments[pathSegments.length - 1].includes('.html')) {
        // Check if we're in a subdirectory
        const currentPath = window.location.pathname;
        if (currentPath.includes('/exercises/') || currentPath.includes('/english/')) {
            return '../';
        }
    }
    return './';
}

const basePath = getBasePath();

document.addEventListener('DOMContentLoaded', async function() {
    // Check localStorage availability
    function isLocalStorageAvailable() {
        try {
            const test = 'test';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch(e) {
            return false;
        }
    }

    // Load header
    await loadHeader();
    
    // Load footer
    await loadFooter();
    
    // Initialize theme toggle
    initThemeToggle();
    
    // Initialize user profile
    initUserProfile();
});

/**
 * Load header component
 */
async function loadHeader() {
    try {
        const response = await fetch(basePath + 'shared-header.html');
        if (!response.ok) {
            console.error('Failed to load header:', response.status);
            return;
        }
        let html = await response.text();
        
        // Replace basePath placeholders with actual base path
        html = html.replace(/{basePath}/g, basePath);
        
        // Find or create header container
        let headerContainer = document.querySelector('header');
        if (!headerContainer) {
            headerContainer = document.createElement('div');
            document.body.insertBefore(headerContainer, document.body.firstChild);
        }
        
        headerContainer.outerHTML = html;
    } catch (error) {
        console.error('Error loading header:', error);
    }
}

/**
 * Load footer component
 */
async function loadFooter() {
    try {
        const response = await fetch(basePath + 'shared-footer.html');
        if (!response.ok) {
            console.error('Failed to load footer:', response.status);
            return;
        }
        let html = await response.text();
        
        // Replace basePath placeholders with actual base path
        html = html.replace(/{basePath}/g, basePath);
        
        // Append footer to body
        const footerContainer = document.createElement('div');
        footerContainer.innerHTML = html;
        document.body.appendChild(footerContainer.firstChild);
    } catch (error) {
        console.error('Error loading footer:', error);
    }
}

/**
 * Initialize theme toggle functionality
 */
function initThemeToggle() {
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    
    function isLocalStorageAvailable() {
        try {
            const test = 'test';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch(e) {
            return false;
        }
    }

    if (themeToggle) {
        // Check for saved theme preference
        let currentTheme = 'light';
        if (isLocalStorageAvailable()) {
            currentTheme = localStorage.getItem('theme') || 'light';
        }

        // Apply saved theme or system preference
        if (currentTheme === 'dark') {
            body.classList.add('dark');
            themeToggle.checked = true;
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            body.classList.add('dark');
            themeToggle.checked = true;
        }

        // Handle theme toggle changes
        themeToggle.addEventListener('change', function() {
            if (this.checked) {
                body.classList.add('dark');
                if (isLocalStorageAvailable()) {
                    localStorage.setItem('theme', 'dark');
                }
            } else {
                body.classList.remove('dark');
                if (isLocalStorageAvailable()) {
                    localStorage.setItem('theme', 'light');
                }
            }
        });
    }
}

/**
 * Initialize user profile navigation
 */
function initUserProfile() {
    function isLocalStorageAvailable() {
        try {
            const test = 'test';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch(e) {
            return false;
        }
    }

    let user = null;
    if (isLocalStorageAvailable()) {
        user = JSON.parse(localStorage.getItem('user'));
    }

    // Give elements time to be loaded
    setTimeout(() => {
        const userProfileNav = document.getElementById('userProfileNav');
        const authButtons = document.getElementById('authButtons');
        const headerProfileAvatar = document.getElementById('headerProfileAvatar');
        const headerUsername = document.getElementById('headerUsername');

        if (userProfileNav && authButtons) {
            if (user) {
                // Show profile navigation and hide auth buttons
                userProfileNav.style.display = 'flex';
                authButtons.style.display = 'none';

                // Update header profile information
                if (headerProfileAvatar && headerUsername) {
                    headerProfileAvatar.textContent = user.username.charAt(0).toUpperCase();
                    headerUsername.textContent = user.username;
                }
            } else {
                // Show auth buttons and hide profile navigation
                userProfileNav.style.display = 'none';
                authButtons.style.display = 'flex';
            }
        }
    }, 100);
}
