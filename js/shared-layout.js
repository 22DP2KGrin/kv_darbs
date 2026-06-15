/**
 * Shared Layout Script
 * Loads header and footer components, manages theme, profile, and authentication
 */

// Determine the base path for assets (handles subdirectories)
function getBasePath() {
    const path = window.location.pathname;
    const nestedFolders = ['/exercises/', '/english/', '/french/', '/spanish/', '/latvian/'];
    return nestedFolders.some(folder => path.includes(folder)) ? '../' : './';
}

const basePath = getBasePath();

function isHomePage() {
    const path = window.location.pathname.replace(/\/+$/, '');
    return path === '' || path.endsWith('/index.html') || path.endsWith('/kv_darbs');
}

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

document.addEventListener('DOMContentLoaded', async function() {
    document.body.classList.add('has-shared-layout');
    injectSharedHeaderStyles();

    // Load header
    await loadHeader();
    
    // Load footer
    await loadFooter();
    
    // Initialize theme toggle
    initThemeToggle();
    
    // Initialize user profile
    initUserProfile();

    // Load AI chat widget on pages that use the shared layout
    loadAiWidget();
});

function injectSharedHeaderStyles() {
    if (document.getElementById('sharedHeaderStyles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'sharedHeaderStyles';
    style.textContent = `
        body.has-shared-layout {
            display: block !important;
            justify-content: flex-start !important;
            align-items: stretch !important;
            padding: 0 !important;
        }

        body.has-shared-layout > .exercise-page,
        body.has-shared-layout > .container {
            margin-left: auto;
            margin-right: auto;
        }

        body.has-shared-layout > .exercise-page {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        body.dark.has-shared-layout {
            --header-bg: #0f172a;
            --footer-bg: #0f172a;
            --footer-text: #cbd5e1;
            --foreground: #e5e7eb;
            --body-color: #e5e7eb;
            --dark-color: #e5e7eb;
            --secondary-color: #cbd5e1;
            --light-color: #111827;
            color: #e5e7eb;
            background: #111827 !important;
        }

        body.dark.has-shared-layout > .exercise-page,
        body.dark.has-shared-layout > .container,
        body.dark.has-shared-layout .question,
        body.dark.has-shared-layout .question-container,
        body.dark.has-shared-layout #result,
        body.dark.has-shared-layout .result-container,
        body.dark.has-shared-layout .incorrect-answer-item {
            background-color: rgba(15, 23, 42, 0.94) !important;
            color: #e5e7eb !important;
            border-color: rgba(148, 163, 184, 0.28) !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.28) !important;
        }

        body.dark.has-shared-layout .option {
            background-color: rgba(30, 41, 59, 0.92) !important;
            color: #e5e7eb !important;
            border-color: rgba(148, 163, 184, 0.32) !important;
        }

        body.dark.has-shared-layout .option:hover,
        body.dark.has-shared-layout .option.selected,
        body.dark.has-shared-layout .option:has(input[type="radio"]:checked) {
            background-color: rgba(79, 70, 229, 0.24) !important;
            border-color: var(--primary-color, #6366f1) !important;
        }

        body.dark.has-shared-layout .option label,
        body.dark.has-shared-layout .question p,
        body.dark.has-shared-layout .question,
        body.dark.has-shared-layout .question-counter,
        body.dark.has-shared-layout .exercise-info,
        body.dark.has-shared-layout .result-feedback,
        body.dark.has-shared-layout #score,
        body.dark.has-shared-layout #explanation,
        body.dark.has-shared-layout .incorrect-answer-question,
        body.dark.has-shared-layout .incorrect-answer-details {
            color: #e5e7eb !important;
        }

        body.dark.has-shared-layout .progress,
        body.dark.has-shared-layout .progress-container {
            background-color: rgba(30, 41, 59, 0.9) !important;
        }

        body.dark.has-shared-layout .prev-btn,
        body.dark.has-shared-layout .secondary-button {
            background-color: #1e293b !important;
            color: #e5e7eb !important;
            border-color: rgba(148, 163, 184, 0.35) !important;
        }

        body.dark.has-shared-layout .prev-btn:hover:not(:disabled),
        body.dark.has-shared-layout .secondary-button:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        body.dark.has-shared-layout .prev-btn:disabled {
            background-color: #111827 !important;
            color: #64748b !important;
        }

        body.dark.has-shared-layout .incorrect-answers {
            background-color: rgba(69, 10, 10, 0.45) !important;
            border-color: rgba(248, 113, 113, 0.32) !important;
        }

        body > .header {
            background-color: var(--header-bg, var(--background, #ffffff));
            display: block;
            flex: none;
            padding: 1rem 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
            box-sizing: border-box;
        }

        body > .header .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            min-height: 0;
            position: static;
            overflow: visible;
            transform: none;
        }

        body > .header .container::before,
        body > .header .container::after {
            content: none;
            display: none;
        }

        body > .header .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        body > .header .logo {
            color: var(--primary, #4f46e5);
            font-size: 1.35rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            margin: 0;
        }

        body > .header .main-nav,
        body > .header .nav-links,
        body > .header .auth-buttons,
        body > .header .user-profile-nav,
        body > .header .theme-toggle,
        body > .header .profile-link {
            display: flex;
            align-items: center;
        }

        body > .header .main-nav {
            gap: 1rem;
        }

        body > .header .nav-links,
        body > .header .auth-buttons {
            gap: 0.75rem;
        }

        body > .header .nav-link,
        body > .header .profile-link {
            color: var(--foreground, #333333);
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius, 0.75rem);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        body > .header .nav-link:hover,
        body > .header .profile-link:hover {
            background-color: rgba(79, 70, 229, 0.1);
        }

        body > .header .profile-link {
            gap: 0.5rem;
            padding: 0.35rem 0.6rem;
        }

        body > .header .profile-avatar-small {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #3730a3) !important;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            flex: 0 0 34px;
            border: 2px solid rgba(79, 70, 229, 0.18);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.22);
        }

        body > .header .profile-link {
            color: #1f2937 !important;
        }

        body.dark > .header .profile-link {
            color: #e5e7eb !important;
        }

        body > .header .profile-username {
            max-width: 9rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        body > .header .theme-toggle {
            gap: 0.45rem;
        }

        body > .header .switch {
            flex: 0 0 auto;
        }

        .simple-footer {
            background-color: var(--footer-bg, var(--background, #ffffff));
            color: var(--footer-text, var(--muted-foreground, #6c757d));
            padding: 1.25rem 1rem;
            text-align: center;
            border-top: 1px solid var(--border-color, var(--border, rgba(0, 0, 0, 0.08)));
            margin-top: auto;
        }

        .simple-footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .simple-footer p {
            margin: 0;
            font-size: 0.95rem;
        }

        @media (max-width: 760px) {
            body > .header .header-container,
            body > .header .main-nav,
            body > .header .nav-links {
                flex-wrap: wrap;
            }

            body > .header .header-container {
                align-items: flex-start;
            }

            body > .header .main-nav {
                justify-content: flex-start;
                width: 100%;
            }

            body > .header .profile-username {
                display: none;
            }
        }
    `;
    document.head.appendChild(style);
}

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

        if (isHomePage()) {
            const template = document.createElement('template');
            template.innerHTML = html;
            template.content.querySelector('[data-home-link]')?.remove();
            html = template.innerHTML;
        }
        
        // Find or create header container
        let headerContainer = document.querySelector('body > header');
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

        const existingFooter = document.querySelector('body > footer');
        const footerContainer = document.createElement('div');
        footerContainer.innerHTML = html;
        const footer = footerContainer.firstChild;

        if (existingFooter) {
            existingFooter.replaceWith(footer);
        } else {
            document.body.appendChild(footer);
        }
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
    updateHeaderAuthState(null);
    validateStoredSession();
}

function loadAiWidget() {
    if (window.kvDarbsAiWidgetLoaded || document.querySelector('script[data-ai-widget-loader]')) {
        return;
    }

    const script = document.createElement('script');
    script.src = basePath + 'scripts/ai-widget.js?v=3';
    script.async = true;
    script.dataset.aiWidgetLoader = 'true';
    document.body.appendChild(script);
}

function getStoredUser() {
    if (!isLocalStorageAvailable()) {
        return null;
    }

    try {
        return JSON.parse(localStorage.getItem('user'));
    } catch (error) {
        localStorage.removeItem('user');
        return null;
    }
}

function getAvatarBackground(avatar) {
    const presets = {
        'preset-1': 'linear-gradient(135deg, #4f46e5, #06b6d4)',
        'preset-2': 'linear-gradient(135deg, #16a34a, #84cc16)',
        'preset-3': 'linear-gradient(135deg, #db2777, #f97316)',
        'preset-4': 'linear-gradient(135deg, #7c3aed, #facc15)',
        'preset-5': 'linear-gradient(135deg, #0f766e, #38bdf8)',
        'preset-6': 'linear-gradient(135deg, #334155, #a855f7)'
    };

    return presets[avatar] || null;
}

function renderHeaderAvatar(element, user) {
    if (!element || !user || !user.username) {
        return;
    }

    const avatar = user.avatar || '';
    element.textContent = user.username.charAt(0).toUpperCase();
    element.style.backgroundImage = '';
    element.style.backgroundSize = '';
    element.style.backgroundPosition = '';
    element.style.color = '';

    const presetBackground = getAvatarBackground(avatar);
    if (presetBackground) {
        element.style.background = presetBackground;
        return;
    }

    if (avatar && !avatar.startsWith('preset-')) {
        const avatarUrl = avatar.startsWith('http') || avatar.startsWith('/') ? avatar : basePath + avatar;
        element.style.background = '';
        element.style.backgroundImage = `url("${avatarUrl}")`;
        element.style.backgroundSize = 'cover';
        element.style.backgroundPosition = 'center';
        element.style.color = 'transparent';
    }
}

function updateHeaderAuthState(user) {
    const userProfileNav = document.getElementById('userProfileNav');
    const authButtons = document.getElementById('authButtons');
    const headerProfileAvatar = document.getElementById('headerProfileAvatar');
    const headerUsername = document.getElementById('headerUsername') || document.getElementById('headerLietotājvārds');

    if (!userProfileNav || !authButtons) {
        return;
    }

    if (user && user.username) {
        userProfileNav.style.display = 'flex';
        authButtons.style.display = 'none';

        renderHeaderAvatar(headerProfileAvatar, user);

        if (headerUsername) {
            headerUsername.textContent = user.username;
        }
    } else {
        userProfileNav.style.display = 'none';
        authButtons.style.display = 'flex';
    }
}

async function validateStoredSession() {
    if (!isLocalStorageAvailable()) {
        return;
    }

    const token = localStorage.getItem('userSessionToken');
    if (!token) {
        localStorage.removeItem('user');
        updateHeaderAuthState(null);
        return;
    }

    try {
        const response = await fetch(basePath + 'api/check_session.php', {
            headers: {
                'X-Session-Token': token,
                'Accept': 'application/json'
            },
            credentials: 'include'
        });
        const data = await response.json();

        if (data.success) {
            const user = data.user && data.user.username ? data.user : getStoredUser();

            if (user && user.username) {
                localStorage.setItem('user', JSON.stringify(user));
            }

            updateHeaderAuthState(user);
        } else {
            localStorage.removeItem('user');
            localStorage.removeItem('userSessionToken');
            updateHeaderAuthState(null);
        }
    } catch (error) {
        console.warn('Session check skipped:', error);
    }
}
