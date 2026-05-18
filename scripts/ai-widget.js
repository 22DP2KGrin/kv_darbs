(function() {
    if (window.kvDarbsAiWidgetLoaded) {
        return;
    }

    window.kvDarbsAiWidgetLoaded = true;

    const MAX_HISTORY = 12;

    function getScriptBasePath() {
        let script = document.currentScript;
        if (!script) {
            const scripts = Array.from(document.getElementsByTagName('script'));
            script = scripts.reverse().find(s => s.src && s.src.includes('ai-widget.js'));
        }

        if (script && script.src) {
            try {
                const url = new URL(script.src, window.location.origin);
                return url.pathname.replace(/\/scripts\/ai-widget(\.min)?\.js$/, '');
            } catch (error) {
                console.error('Failed to parse ai-widget script URL:', error);
            }
        }

        return '.';
    }

    function getApiEndpoint() {
        const basePath = getScriptBasePath();
        return basePath + '/api/chat.php';
    }

    function getPageType() {
        const path = window.location.pathname.toLowerCase();

        if (
            path.includes('/english/test') ||
            path.includes('/french/test') ||
            path.includes('/spanish/test') ||
            path.includes('/latvian/test')
        ) {
            return 'test';
        }

        if (path.includes('/exercises/')) {
            return 'exercise';
        }

        if (
            path.includes('exercises_english') ||
            path.includes('exercises_french') ||
            path.includes('exercises_spanish') ||
            path.includes('exercises_latvian')
        ) {
            return 'exercise_hub';
        }

        return 'general';
    }

    function getText(selector) {
        const node = document.querySelector(selector);
        return node ? node.textContent.trim() : '';
    }

    function getVisibleQuestionText() {
        const candidates = [
            '#questionText',
            '.question.active p:first-child',
            '.question-container.active .question',
            '.question-container.active .question-text',
            '.question.active .question-text',
            '.question.active',
            '.question-container .question',
            '.question-text'
        ];

        for (const selector of candidates) {
            const nodes = document.querySelectorAll(selector);
            for (const node of nodes) {
                const style = window.getComputedStyle(node);
                if (style.display === 'none' || style.visibility === 'hidden') {
                    continue;
                }

                const text = node.textContent.trim();
                if (text.length > 8) {
                    return text.replace(/\s+/g, ' ');
                }
            }
        }

        return '';
    }

    function getVisibleOptions() {
        const optionSelectors = ['.option', 'label', '.answer-option'];
        const options = [];

        for (const selector of optionSelectors) {
            const nodes = document.querySelectorAll(selector);
            for (const node of nodes) {
                const style = window.getComputedStyle(node);
                if (style.display === 'none' || style.visibility === 'hidden') {
                    continue;
                }

                const text = node.textContent.trim().replace(/\s+/g, ' ');
                if (text.length > 0 && text.length < 140) {
                    options.push(text);
                }

                if (options.length >= 4) {
                    return options;
                }
            }
        }

        return options;
    }

    function normalizeText(text) {
        return (text || '')
            .toString()
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .replace(/[.,!?;:"'`«»“”„()]/g, '')
            .trim();
    }

    function getScriptText() {
        return Array.from(document.scripts)
            .map(function(script) {
                return script.textContent || '';
            })
            .join('\n');
    }

    function extractBracketBlock(text, startIndex, openChar, closeChar) {
        let depth = 0;
        let inString = false;
        let quote = '';
        let escaped = false;

        for (let index = startIndex; index < text.length; index++) {
            const char = text[index];

            if (inString) {
                if (escaped) {
                    escaped = false;
                } else if (char === '\\') {
                    escaped = true;
                } else if (char === quote) {
                    inString = false;
                    quote = '';
                }
                continue;
            }

            if (char === '"' || char === "'" || char === '`') {
                inString = true;
                quote = char;
                continue;
            }

            if (char === openChar) {
                depth++;
            } else if (char === closeChar) {
                depth--;
                if (depth === 0) {
                    return text.slice(startIndex, index + 1);
                }
            }
        }

        return '';
    }

    function parseQuestionsArray() {
        const scriptText = getScriptText();
        const declarationIndex = scriptText.search(/\b(?:const|let|var)\s+questions\s*=/);

        if (declarationIndex === -1) {
            return [];
        }

        const arrayStart = scriptText.indexOf('[', declarationIndex);
        if (arrayStart === -1) {
            return [];
        }

        const arrayText = extractBracketBlock(scriptText, arrayStart, '[', ']');
        if (!arrayText) {
            return [];
        }

        try {
            const parsed = Function('"use strict"; return (' + arrayText + ');')();
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function getAnswerFromCorrectAnswersMap(questionNode) {
        if (!questionNode || !questionNode.id) {
            return null;
        }

        const scriptText = getScriptText();
        const match = scriptText.match(new RegExp(questionNode.id + "\\s*:\\s*['\\\"]([^'\\\"]+)['\\\"]"));
        if (!match) {
            return null;
        }

        const correctValue = match[1];
        const input = questionNode.querySelector('input[value="' + correctValue + '"]');
        const label = input ? questionNode.querySelector('label[for="' + input.id + '"]') || input.nextElementSibling : null;
        const answer = label ? label.textContent.trim() : correctValue;

        return {
            question: questionNode.textContent.trim().replace(/\s+/g, ' '),
            answer: answer,
            value: correctValue
        };
    }

    function getCurrentAnswerFromQuestionsArray(questions) {
        if (!questions.length) {
            return null;
        }

        const visibleQuestion = normalizeText(getVisibleQuestionText());
        if (!visibleQuestion) {
            return null;
        }

        const match = questions.find(function(question) {
            return visibleQuestion.includes(normalizeText(question.question)) ||
                normalizeText(question.question).includes(visibleQuestion);
        });

        if (!match || !Array.isArray(match.options)) {
            return null;
        }

        return {
            question: match.question,
            answer: match.options[match.correctAnswer],
            value: match.correctAnswer
        };
    }

    function getTestAnswers() {
        const answers = [];
        const questions = parseQuestionsArray();

        questions.forEach(function(question) {
            if (!question || !Array.isArray(question.options)) {
                return;
            }

            const answer = question.options[question.correctAnswer];
            if (!question.question || answer === undefined) {
                return;
            }

            answers.push({
                question: question.question,
                answer: answer,
                value: question.correctAnswer
            });
        });

        if (answers.length > 0) {
            return answers.slice(0, 30);
        }

        document.querySelectorAll('.question[id]').forEach(function(questionNode) {
            const answer = getAnswerFromCorrectAnswersMap(questionNode);
            if (answer) {
                answers.push(answer);
            }
        });

        return answers.slice(0, 30);
    }

    function getCurrentCorrectAnswer() {
        const activeQuestionNode = document.querySelector('.question.active[id]');
        const mappedAnswer = getAnswerFromCorrectAnswersMap(activeQuestionNode);
        if (mappedAnswer) {
            return mappedAnswer;
        }

        return getCurrentAnswerFromQuestionsArray(parseQuestionsArray());
    }

    function buildPageContext() {
        return {
            pageType: getPageType(),
            pageTitle: document.title || '',
            sectionTitle: getText('.exercise-title') || getText('.page-title') || getText('.quiz-title'),
            currentQuestion: getVisibleQuestionText(),
            currentCorrectAnswer: getCurrentCorrectAnswer(),
            testAnswers: getTestAnswers(),
            visibleOptions: getVisibleOptions(),
            activeTab: getText('.tab-button.active'),
            url: window.location.pathname
        };
    }

    function getSuggestions(context) {
        if (context.pageType === 'test' || context.pageType === 'exercise') {
            return [
                {
                    label: 'Paskaidro šo jautājumu',
                    action: 'explain_question',
                    prompt: context.currentQuestion
                        ? `Paskaidro šo jautājumu vienkārši: ${context.currentQuestion}`
                        : 'Paskaidro šo uzdevumu vienkārši.'
                },
                {
                    label: 'Dod mājienu',
                    action: 'hint',
                    prompt: context.currentQuestion
                        ? `Dod man mājienu par šo jautājumu, neatklājot pilno atbildi: ${context.currentQuestion}`
                        : 'Dod man mājienu par šo uzdevumu, neatklājot pilno atbildi.'
                },
                {
                    label: 'Paskaidro tēmu',
                    action: 'explain_topic',
                    prompt: `Paskaidro tēmu "${context.sectionTitle || context.pageTitle}" ļoti vienkārši.`
                }
            ];
        }

        if (context.pageType === 'exercise_hub') {
            return [
                {
                    label: 'Ko man izvēlēties?',
                    action: 'planning',
                    prompt: 'Palīdzi izvēlēties piemērotu vingrinājumu manam līmenim.'
                },
                {
                    label: 'Kā mācīties efektīvāk?',
                    action: 'study_plan',
                    prompt: 'Iedod īsu plānu, kā efektīvi mācīties valodas šajā platformā.'
                }
            ];
        }

        return [
            {
                label: 'Jautājums par vietni',
                action: 'site_help',
                prompt: 'Pastāsti, kā izmantot šo vietni, profilu, testus un vingrinājumus.'
            },
            {
                label: 'Palīdzi ar uzdevumiem',
                action: 'general_help',
                prompt: 'Pastāsti, kā tu vari palīdzēt ar valodu uzdevumiem šajā vietnē.'
            }
        ];
    }

    function createStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .ai-widget-launcher {
                position: fixed;
                right: 24px;
                bottom: 24px;
                width: 68px;
                height: 68px;
                border: none;
                border-radius: 50%;
                background: linear-gradient(135deg, #4f46e5, #10b981);
                color: #fff;
                box-shadow: 0 18px 40px rgba(79, 70, 229, 0.28);
                z-index: 9998;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 28px;
                animation: aiWidgetFloat 4s ease-in-out infinite;
            }

            .ai-widget-launcher:hover {
                transform: translateY(-2px) scale(1.02);
            }

            .ai-widget-launcher::after {
                content: "";
                position: absolute;
                inset: -6px;
                border-radius: 50%;
                border: 1px solid rgba(79, 70, 229, 0.18);
            }

            .ai-widget-panel.hidden {
                opacity: 0;
                pointer-events: none;
                transform: translateY(8px);
            }

            .ai-widget-panel {
                position: fixed;
                right: 24px;
                bottom: 104px;
                width: min(390px, calc(100vw - 24px));
                height: min(70vh, 640px);
                background: #ffffff;
                border-radius: 28px;
                box-shadow: 0 28px 60px rgba(15, 23, 42, 0.22);
                z-index: 9999;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                transition: opacity 0.22s ease, transform 0.22s ease;
            }

            .ai-widget-header {
                padding: 18px 20px;
                background: linear-gradient(135deg, #4f46e5, #10b981);
                color: #fff;
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
            }

            .ai-widget-title {
                font-size: 18px;
                font-weight: 700;
                margin-bottom: 4px;
            }

            .ai-widget-subtitle {
                font-size: 13px;
                opacity: 0.92;
            }

            .ai-widget-close {
                border: none;
                background: rgba(255, 255, 255, 0.18);
                color: #fff;
                width: 34px;
                height: 34px;
                border-radius: 50%;
                cursor: pointer;
                font-size: 18px;
            }

            .ai-widget-feed {
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                background: linear-gradient(180deg, rgba(79, 70, 229, 0.05), transparent 18%), #f8fafc;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .ai-widget-message {
                max-width: 88%;
                padding: 12px 14px;
                border-radius: 18px;
                line-height: 1.45;
                font-size: 14px;
                white-space: pre-wrap;
            }

            .ai-widget-message.ai {
                background: #ffffff;
                color: #1f2937;
                border: 1px solid #e5e7eb;
                border-bottom-left-radius: 8px;
            }

            .ai-widget-message.user {
                margin-left: auto;
                background: linear-gradient(135deg, #4f46e5, #10b981);
                color: #fff;
                border-bottom-right-radius: 8px;
            }

            .ai-widget-suggestions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                padding: 0 16px 12px;
                background: #f8fafc;
            }

            .ai-widget-chip {
                border: 1px solid #dbe3f0;
                background: #ffffff;
                color: #334155;
                padding: 8px 12px;
                border-radius: 999px;
                font-size: 13px;
                cursor: pointer;
            }

            .ai-widget-form {
                padding: 14px 16px 16px;
                background: #ffffff;
                border-top: 1px solid #e5e7eb;
            }

            .ai-widget-shell {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 10px;
                align-items: end;
            }

            .ai-widget-input {
                min-height: 86px;
                resize: vertical;
                border-radius: 18px;
                border: 1px solid #d1d5db;
                padding: 12px 14px;
                font: inherit;
            }

            .ai-widget-send {
                border: none;
                background: #4f46e5;
                color: #fff;
                border-radius: 16px;
                padding: 12px 14px;
                cursor: pointer;
                font-weight: 700;
            }

            .ai-widget-note {
                margin-top: 8px;
                font-size: 12px;
                color: #64748b;
            }

            @keyframes aiWidgetFloat {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-6px);
                }
            }

            @media (max-width: 640px) {
                .ai-widget-launcher {
                    width: 60px;
                    height: 60px;
                    right: 16px;
                    bottom: 16px;
                }

                .ai-widget-panel {
                    right: 12px;
                    bottom: 88px;
                    width: calc(100vw - 24px);
                    height: 72vh;
                }

                .ai-widget-shell {
                    grid-template-columns: 1fr;
                }
            }
        `;

        document.head.appendChild(style);
    }

    function createWidget() {
        createStyles();

        const context = buildPageContext();
        const suggestions = getSuggestions(context);
        const history = [];

        try {
            localStorage.removeItem('kv_darbs_ai_widget_history');
        } catch (error) {
            // Chat history is intentionally not shared between accounts.
        }

        const launcher = document.createElement('button');
        launcher.className = 'ai-widget-launcher';
        launcher.type = 'button';
        launcher.setAttribute('aria-label', 'Atvērt AI palīgu');
        launcher.innerHTML = 'AI';

        const panel = document.createElement('section');
        panel.className = 'ai-widget-panel hidden';
        panel.innerHTML = `
            <div class="ai-widget-header">
                <div>
                    <div class="ai-widget-title">AI Palīgs</div>
                    <div class="ai-widget-subtitle">Palīdz saprast jautājumu, tēmu un kļūdas tieši lapas kontekstā.</div>
                </div>
                <button class="ai-widget-close" type="button" aria-label="Aizvērt">×</button>
            </div>
            <div class="ai-widget-feed" id="aiWidgetFeed"></div>
            <div class="ai-widget-suggestions" id="aiWidgetSuggestions"></div>
            <form class="ai-widget-form" id="aiWidgetForm">
                <div class="ai-widget-shell">
                    <textarea class="ai-widget-input" id="aiWidgetInput" placeholder="Piemērs: paskaidro šo jautājumu vienkārši."></textarea>
                    <button class="ai-widget-send" type="submit">Sūtīt</button>
                </div>
                <div class="ai-widget-note">Atbildes nāk no backend un var izmantot pašreizējās lapas kontekstu.</div>
            </form>
        `;

        document.body.appendChild(panel);
        document.body.appendChild(launcher);

        const feed = panel.querySelector('#aiWidgetFeed');
        const suggestionsWrap = panel.querySelector('#aiWidgetSuggestions');
        const form = panel.querySelector('#aiWidgetForm');
        const input = panel.querySelector('#aiWidgetInput');
        const closeBtn = panel.querySelector('.ai-widget-close');

        function renderMessage(role, content) {
            const item = document.createElement('div');
            item.className = 'ai-widget-message ' + role;
            item.textContent = content;
            feed.appendChild(item);
            feed.scrollTop = feed.scrollHeight;
        }

        function renderSuggestions() {
            suggestionsWrap.innerHTML = '';

            suggestions.forEach(function(suggestion) {
                const button = document.createElement('button');
                button.className = 'ai-widget-chip';
                button.type = 'button';
                button.textContent = suggestion.label;
                button.addEventListener('click', function() {
                    sendMessage(suggestion.prompt, suggestion.action);
                });
                suggestionsWrap.appendChild(button);
            });
        }

        function restoreHistory() {
            if (history.length > 0) {
                history.forEach(function(message) {
                    renderMessage(message.role, message.content);
                });
                return;
            }

            let intro = 'Sveika! Es esmu AI palīgs. Vari jautāt jebko, un es atbildēšu pēc iespējas skaidrāk.';
            if (context.pageType === 'test' || context.pageType === 'exercise') {
                intro = context.currentQuestion
                    ? 'Es redzu, ka tu esi uzdevumā. Varu paskaidrot pašreizējo jautājumu vai dot mājienu, neatklājot pilno atbildi.'
                    : 'Es redzu, ka tu esi uzdevumā. Varu paskaidrot tēmu, stratēģiju vai dot mājienu.';
            }

            renderMessage('ai', intro);
            history.push({ role: 'ai', content: intro });
        }

        async function sendMessage(text, action) {
            const cleanText = (text || '').trim();
            if (!cleanText) {
                return;
            }

            renderMessage('user', cleanText);
            history.push({ role: 'user', content: cleanText });
            input.value = '';

            const loadingMessage = document.createElement('div');
            loadingMessage.className = 'ai-widget-message ai';
            loadingMessage.textContent = 'Domāju par labāko skaidrojumu...';
            feed.appendChild(loadingMessage);
            feed.scrollTop = feed.scrollHeight;

            try {
                const response = await fetch(getApiEndpoint(), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action || 'general',
                        pageContext: buildPageContext(),
                        messages: history.slice(-MAX_HISTORY)
                    })
                });

                const responseText = await response.text();
                let data = null;

                try {
                    data = JSON.parse(responseText);
                } catch (error) {
                    throw new Error('Serveris atgrieza nederīgu atbildi. Pārbaudi, vai PHP lapa tiešām darbojas caur serveri.');
                }

                loadingMessage.remove();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'AI atbilde nav pieejama.');
                }

                renderMessage('ai', data.reply);
                history.push({ role: 'ai', content: data.reply });
            } catch (error) {
                loadingMessage.remove();
                const fallback = 'AI palīgs kļūda: ' + error.message;
                renderMessage('ai', fallback);
                history.push({ role: 'ai', content: fallback });
                console.error('AI widget error:', error);
            }
        }

        launcher.addEventListener('click', function() {
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                input.focus();
            }
        });

        closeBtn.addEventListener('click', function() {
            panel.classList.add('hidden');
        });

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            sendMessage(input.value, 'general');
        });

        renderSuggestions();
        restoreHistory();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createWidget);
    } else {
        createWidget();
    }
})();
