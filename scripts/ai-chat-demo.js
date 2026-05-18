document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const userProfileNav = document.getElementById('userProfileNav');
    const authButtons = document.getElementById('authButtons');
    const headerUsername = document.getElementById('headerUsername');
    const headerProfileAvatar = document.getElementById('headerProfileAvatar');
    const chatFeed = document.getElementById('chatFeed');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const clearChatBtn = document.getElementById('clearChatBtn');
    const suggestionButtons = document.querySelectorAll('.suggestion-btn');

    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark');
        if (themeToggle) {
            themeToggle.checked = true;
        }
    }

    if (themeToggle) {
        themeToggle.addEventListener('change', function() {
            if (themeToggle.checked) {
                body.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        });
    }

    const user = JSON.parse(localStorage.getItem('user'));
    if (user && userProfileNav && authButtons) {
        userProfileNav.style.display = 'flex';
        authButtons.style.display = 'none';

        const firstLetter = (user.username || user.first_name || 'U').charAt(0).toUpperCase();
        headerProfileAvatar.textContent = firstLetter;
        headerUsername.textContent = user.username || user.first_name || 'Lietotājs';
    }

    function addMessage(role, text) {
        const message = document.createElement('div');
        message.className = `message ${role}`;

        const label = document.createElement('strong');
        label.textContent = role === 'user' ? 'Tu' : 'AI Palīgs';

        const content = document.createElement('div');
        content.textContent = text;

        message.appendChild(label);
        message.appendChild(content);
        chatFeed.appendChild(message);
        chatFeed.scrollTop = chatFeed.scrollHeight;
    }

    function buildResponse(prompt) {
        const text = prompt.toLowerCase();

        if (text.includes('present simple')) {
            return 'Present Simple lieto ieradumiem, regulārām darbībām un faktiem. Īsā formula: subject + base verb, bet ar he/she/it parasti pievieno -s. Piemērs: "She studies every evening."';
        }

        if (text.includes('present perfect') || text.includes('past simple')) {
            return 'Īsa atšķirība: Past Simple runā par pabeigtu darbību noteiktā laikā pagātnē, bet Present Perfect savieno pagātni ar tagadni. Meklē atslēgvārdus: "yesterday" bieži iet ar Past Simple, bet "already", "yet", "ever" bieži ar Present Perfect.';
        }

        if (text.includes('daily routine')) {
            return 'Te ir 5 vārdi par daily routine: wake up, get dressed, have breakfast, go to work, go to bed. Ja gribi, nākamajā solī varu no tiem uztaisīt mazu vingrinājumu.';
        }

        if (text.includes('phrasal verbs')) {
            return 'Mini tests:\n1. Complete: Please ___ the lights before you leave.\n2. Choose: "give up" means a) continue b) stop trying c) return.\n3. Write your own sentence with "look after".';
        }

        if (text.includes('pārbaudi manu teikumu') || text.includes('check my sentence') || text.includes('she go')) {
            return 'Teikumā "She go to school every day" darbības vārdam vajag formu ar -s, jo subjekts ir she. Pareizi būtu: "She goes to school every day."';
        }

        if (text.includes('māji') || text.includes('hint')) {
            return 'Mājiens: vispirms nosaki, par ko ir teikums. Vai tā ir rutīna, darbība tieši tagad vai pieredze līdz šim brīdim? Kad to sapratīsi, pareizais laiks kļūs daudz skaidrāks.';
        }

        if (text.includes('test')) {
            return 'Es varu palīdzēt ar testiem vairākos veidos: sagatavot 3 jautājumus, pārbaudīt atbildes vai izskaidrot kļūdas pēc rezultāta. Pasaki tēmu, un mēs to varam simulēt.';
        }

        return 'Šis demo rāda, kā AI palīgs varētu atbildēt tavā projektā. Nākamajā solī mēs varam pieslēgt īstu OpenAI modeli, lai atbildes būtu daudz gudrākas un pielāgotas lietotājam.';
    }

    function handlePrompt(prompt) {
        const cleanPrompt = prompt.trim();
        if (!cleanPrompt) {
            return;
        }

        addMessage('user', cleanPrompt);
        chatInput.value = '';

        setTimeout(function() {
            addMessage('ai', buildResponse(cleanPrompt));
        }, 450);
    }

    if (chatForm) {
        chatForm.addEventListener('submit', function(event) {
            event.preventDefault();
            handlePrompt(chatInput.value);
        });
    }

    suggestionButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            handlePrompt(button.dataset.prompt || '');
        });
    });

    if (clearChatBtn) {
        clearChatBtn.addEventListener('click', function() {
            chatFeed.innerHTML = '';
            addMessage('ai', 'Saruna notīrīta. Uzraksti jaunu jautājumu, un demo atkal parādīs, kā AI palīgs varētu palīdzēt.');
        });
    }
});
